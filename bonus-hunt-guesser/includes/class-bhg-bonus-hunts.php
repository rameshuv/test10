<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bonus hunt data helpers.
 */
class BHG_Bonus_Hunts {

	/**
	 * Retrieve latest hunts with winners.
	 *
	 * @param int $limit Number of hunts to fetch. Default 3.
	 * @return array List of hunts and winners.
	 */
	public static function get_latest_hunts_with_winners( $limit = 3 ) {
				global $wpdb;
				$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
				$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
				$users_table   = esc_sql( $wpdb->users );
		$limit                 = max( 1, (int) $limit );

								$hunts = $wpdb->get_results(
									$wpdb->prepare(
										"SELECT id, title, starting_balance, final_balance, winners_count, closed_at
                                                                                FROM {$hunts_table}
                                                                                WHERE status = %s AND final_balance IS NOT NULL AND closed_at IS NOT NULL
                                                                                ORDER BY closed_at DESC
                                                                                LIMIT %d",
										'closed',
										$limit
									)
								);

		$out = array();

				$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

		foreach ( (array) $hunts as $h ) {
				$winners_count = max( 1, (int) $h->winners_count );
				$winners       = array();

			if ( $winners_table ) {
						$winners = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT w.user_id, u.display_name, w.guess, w.diff
                                                FROM {$winners_table} w
                                                LEFT JOIN {$users_table} u ON u.ID = w.user_id
                                                WHERE w.hunt_id = %d AND w.eligible = 1
                                                ORDER BY w.position ASC
                                                LIMIT %d",
								$h->id,
								$winners_count
							)
						);
			}

			if ( empty( $winners ) ) {
							$winners = $wpdb->get_results(
								$wpdb->prepare(
									"SELECT g.user_id, u.display_name, g.guess,
                                                        (%f - g.guess) AS diff
                                                FROM {$guesses_table} g
                                                LEFT JOIN {$users_table} u ON u.ID = g.user_id
                                                WHERE g.hunt_id = %d
                                                ORDER BY ABS(%f - g.guess) ASC, g.id ASC
                                                LIMIT %d",
									$h->final_balance,
									$h->id,
									$h->final_balance,
									$winners_count
								)
							);
			}

							$out[] = array(
								'hunt'    => $h,
								'winners' => $winners,
							);
		}

		return $out;
	}

	/**
	 * Calculate timeframe bounds for results filters.
	 *
	 * @param string $timeframe Timeframe key (month|year|all).
	 * @return array{start:?string,end:?string} Start/end bounds or null when unbounded.
	 */
	public static function get_results_timeframe_bounds( $timeframe ) {
		$timeframe = strtolower( (string) $timeframe );
		if ( ! in_array( $timeframe, array( 'month', 'year', 'all' ), true ) ) {
			$timeframe = 'month';
		}

		if ( 'all' === $timeframe ) {
			return array(
				'start' => null,
				'end'   => null,
			);
		}

		try {
			$timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( wp_timezone_string() );
		} catch ( Exception $e ) {
			$timezone = new DateTimeZone( 'UTC' );
		}

		$now = new DateTimeImmutable( 'now', $timezone );

		if ( 'year' === $timeframe ) {
			$start = $now->setDate( (int) $now->format( 'Y' ), 1, 1 )->setTime( 0, 0, 0 );
			$end   = $start->modify( '+1 year' )->modify( '-1 second' );
		} else {
			$start = $now->setDate( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), 1 )->setTime( 0, 0, 0 );
			$end   = $start->modify( '+1 month' )->modify( '-1 second' );
		}

		return array(
			'start' => $start->format( 'Y-m-d H:i:s' ),
			'end'   => $end->format( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Retrieve closed hunts for the results selector filtered by timeframe.
	 *
	 * @param string $timeframe Timeframe key.
	 * @param int    $limit     Maximum number of hunts to return.
	 * @return array<int,object> Hunt rows.
	 */
	public static function get_closed_hunts_for_selector( $timeframe, $limit = 50 ) {
		global $wpdb;

		$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$limit       = max( 1, (int) $limit );
		$bounds      = self::get_results_timeframe_bounds( $timeframe );
		$where       = array(
			"status = 'closed'",
			'final_balance IS NOT NULL',
			'closed_at IS NOT NULL',
		);
		$params      = array();

		if ( ! empty( $bounds['start'] ) ) {
			$where[]  = 'closed_at >= %s';
			$params[] = $bounds['start'];
		}

		if ( ! empty( $bounds['end'] ) ) {
			$where[]  = 'closed_at <= %s';
			$params[] = $bounds['end'];
		}

		$where_sql = implode( ' AND ', $where );
		$params[]  = $limit;

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id, title, closed_at FROM {$hunts_table} WHERE {$where_sql} ORDER BY closed_at DESC, id DESC LIMIT %d",
				...$params
			)
		);
	}

	/**
	 * Retrieve tournaments for the results selector filtered by timeframe.
	 *
	 * @param string $timeframe Timeframe key.
	 * @param int    $limit     Maximum number of tournaments to return.
	 * @return array<int,object> Tournament rows.
	 */
	public static function get_tournaments_for_selector( $timeframe, $limit = 50 ) {
		global $wpdb;

		$tours_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
		$limit       = max( 1, (int) $limit );
		$bounds      = self::get_results_timeframe_bounds( $timeframe );
		$reference   = 'COALESCE(end_date, start_date, DATE(updated_at), DATE(created_at))';
		$where       = array( "{$reference} IS NOT NULL" );
		$params      = array();

		if ( ! empty( $bounds['start'] ) ) {
			$where[]  = "{$reference} >= %s";
			$params[] = substr( $bounds['start'], 0, 10 );
		}

		if ( ! empty( $bounds['end'] ) ) {
			$where[]  = "{$reference} <= %s";
			$params[] = substr( $bounds['end'], 0, 10 );
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$params[]  = $limit;

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id, title, type, start_date, end_date FROM {$tours_table} {$where_sql} ORDER BY {$reference} DESC, id DESC LIMIT %d",
				...$params
			)
		);
	}

	/**
	 * Retrieve a hunt by ID.
	 *
	 * @param int $hunt_id Hunt ID.
	 * @return object|null Hunt data or null if not found.
	 */
	public static function get_hunt( $hunt_id ) {
		global $wpdb;
		$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$hunts_table} WHERE id=%d",
				(int) $hunt_id
			)
		);
	}

	/**
	 * Retrieve the most recent hunt suitable for displaying results.
	 *
	 * Falls back to the newest closed hunt (with a final balance) and, if none
	 * exist yet, the most recently created hunt regardless of status.
	 *
	 * @return int Hunt ID or 0 when no hunts exist.
	 */
	public static function get_default_results_hunt_id() {
		global $wpdb;
		$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

		// Prefer hunts that have been closed and have a final balance recorded.
		$closed_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$hunts_table}
					WHERE status = %s AND final_balance IS NOT NULL
					ORDER BY COALESCE(closed_at, updated_at, created_at) DESC, id DESC
					LIMIT 1",
				'closed'
			)
		);

		if ( $closed_id > 0 ) {
			return $closed_id;
		}

		// Otherwise fall back to the most recently created hunt.
		$latest_id = (int) $wpdb->get_var(
			"SELECT id FROM {$hunts_table} ORDER BY id DESC LIMIT 1"
		);

		return $latest_id > 0 ? $latest_id : 0;
	}

	/**
	 * Get ranked guesses for a hunt.
	 *
	 * @param int $hunt_id Hunt ID.
	 * @return array List of guesses.
	 */
	public static function get_hunt_guesses_ranked( $hunt_id ) {
			global $wpdb;
			$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
			$users_table   = esc_sql( $wpdb->users );
		$hunt              = self::get_hunt( $hunt_id );

		if ( ! $hunt ) {
			return array(); }

		if ( null !== $hunt->final_balance ) {
													return $wpdb->get_results(
														$wpdb->prepare(
															"SELECT g.*, u.display_name, (%f - g.guess) AS diff
                                                                          FROM {$guesses_table} g
                                                                          LEFT JOIN {$users_table} u ON u.ID = g.user_id
                                                                          WHERE g.hunt_id = %d
                                                                          ORDER BY ABS(%f - g.guess) ASC, g.id ASC",
															$hunt->final_balance,
															$hunt_id,
															$hunt->final_balance
														)
													);
		}

												return $wpdb->get_results(
													$wpdb->prepare(
														"SELECT g.*, u.display_name, NULL AS diff
                                                                                  FROM {$guesses_table} g
                                                                                  LEFT JOIN {$users_table} u ON u.ID = g.user_id
                                                                                  WHERE g.hunt_id = %d
                                                                                  ORDER BY g.created_at ASC, g.id ASC",
														$hunt_id
													)
												);
	}

		/**
		 * Retrieve user IDs for official hunt winners.
		 *
		 * @param int $hunt_id Hunt ID.
		 * @return int[] List of user IDs ordered by placement.
		 */
	public static function get_hunt_winner_ids( $hunt_id ) {
			global $wpdb;

			$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

		if ( ! $winners_table ) {
				return array();
		}

			$rows = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT user_id FROM {$winners_table} WHERE hunt_id = %d AND eligible = 1 ORDER BY position ASC",
					(int) $hunt_id
				)
			);

		if ( empty( $rows ) ) {
				return array();
		}

			return array_map( 'intval', $rows );
	}

		/**
		 * Retrieve user IDs that were marked ineligible for a hunt due to win limits.
		 *
		 * @param int $hunt_id Hunt ID.
		 * @return int[] List of user IDs.
		 */
	public static function get_ineligible_winner_ids( $hunt_id ) {
			global $wpdb;

			$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );

		if ( ! $winners_table ) {
				return array();
		}

			$rows = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT user_id FROM {$winners_table} WHERE hunt_id = %d AND eligible = 0",
					(int) $hunt_id
				)
			);

		if ( empty( $rows ) ) {
				return array();
		}

			return array_map( 'intval', $rows );
	}
}
