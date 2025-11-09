<?php
/**
 * Data layer utilities for Bonus Hunt Guesser.
 *
 * This class previously handled guess submissions directly. Guess handling is
 * now centralized through {@see bhg_handle_submit_guess()} in
 * `bonus-hunt-guesser.php`. The methods related to form handling and request
 * routing were removed to avoid duplication and ensure a single canonical
 * implementation.
 *
 * Runtime: PHP 7.4 · WordPress 6.3.5 · MySQL 5.5.5+
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class providing data layer utilities for Bonus Hunt Guesser.
 */
class BHG_Models {

	/**
	 * Close a bonus hunt and determine winners.
	 *
	 * @param int   $hunt_id       Hunt identifier.
	 * @param float $final_balance Final balance for the hunt.
	 *
	 * @return int[]|false Array of winning user IDs or false on failure.
	 */
	public static function close_hunt( $hunt_id, $final_balance ) {
		global $wpdb;

		if ( class_exists( 'BHG_DB' ) ) {
			BHG_DB::migrate();
		}

		$hunt_id       = (int) $hunt_id;
		$final_balance = (float) $final_balance;

		if ( $hunt_id <= 0 ) {
			return array();
		}

		$hunts_tbl   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$guesses_tbl = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$winners_tbl = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
		$tres_tbl    = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
		$tours_tbl   = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

		// Determine number of winners and tournament association for this hunt.
		$hunt_row      = $wpdb->get_row(
			$wpdb->prepare(
				' SELECT winners_count, tournament_id, affiliate_id, affiliate_site_id FROM ' . $hunts_tbl . ' WHERE id = %d ',
				$hunt_id
			)
		);
		$winners_count = $hunt_row ? (int) $hunt_row->winners_count : 0;
		if ( $winners_count <= 0 ) {
			$winners_count = 1;
		}
		$tournament_ids = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( $hunt_id ) : array();
		if ( empty( $tournament_ids ) && $hunt_row && ! empty( $hunt_row->tournament_id ) ) {
			$tournament_ids = array( (int) $hunt_row->tournament_id );
		}
		$tournament_ids   = array_map( 'intval', array_unique( $tournament_ids ) );
		$tournament_modes = array();
		if ( ! empty( $tournament_ids ) ) {
			$placeholders = implode( ', ', array_fill( 0, count( $tournament_ids ), '%d' ) );
			$mode_rows    = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, participants_mode FROM {$tours_tbl} WHERE id IN ({$placeholders})",
					$tournament_ids
				)
			);
			if ( ! empty( $mode_rows ) ) {
				foreach ( $mode_rows as $mode_row ) {
					$tid  = isset( $mode_row->id ) ? (int) $mode_row->id : 0;
					$mode = isset( $mode_row->participants_mode ) ? sanitize_key( $mode_row->participants_mode ) : 'winners';
					if ( $tid <= 0 ) {
						continue;
					}
					if ( ! in_array( $mode, array( 'winners', 'all' ), true ) ) {
						$mode = 'winners';
					}
					$tournament_modes[ $tid ] = $mode;
				}
			}
		}
		$has_all_mode = in_array( 'all', $tournament_modes, true );

		// Update hunt status and final details.
		$now     = current_time( 'mysql' );
		$updated = $wpdb->update(
			$hunts_tbl,
			array(
				'status'        => 'closed',
				'final_balance' => $final_balance,
				'closed_at'     => $now,
				'updated_at'    => $now,
			),
			array( 'id' => $hunt_id ),
			array( '%s', '%f', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			bhg_log( $wpdb->last_error );
			return false;
		}

		// Remove existing winners and reverse previous tournament tallies.
		$existing_winners = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT user_id, position FROM ' . $winners_tbl . ' WHERE hunt_id = %d',
				$hunt_id
			)
		);

		if ( null === $existing_winners && $wpdb->last_error ) {
			bhg_log( $wpdb->last_error );
			return false;
		}

		if ( ! empty( $existing_winners ) ) {
			$winner_positions = array();
			foreach ( $existing_winners as $existing_winner ) {
				$user_id  = isset( $existing_winner->user_id ) ? (int) $existing_winner->user_id : 0;
				$position = isset( $existing_winner->position ) ? (int) $existing_winner->position : 0;

				if ( $user_id <= 0 ) {
					continue;
				}

				if ( ! isset( $winner_positions[ $user_id ] ) ) {
					$winner_positions[ $user_id ] = array();
				}

				$winner_positions[ $user_id ][] = $position;
			}

			$deleted = $wpdb->delete( $winners_tbl, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
			if ( false === $deleted ) {
				bhg_log( $wpdb->last_error );
				return false;
			}

			if ( ! empty( $tournament_ids ) && ! empty( $winner_positions ) ) {
				foreach ( $tournament_ids as $tournament_id ) {
					$mode = isset( $tournament_modes[ $tournament_id ] ) ? $tournament_modes[ $tournament_id ] : 'winners';

					foreach ( $winner_positions as $user_id => $positions ) {
						$remove_count = 0;

						if ( 'all' === $mode ) {
							$remove_count = count( $positions );
						} else {
							foreach ( $positions as $position ) {
								if ( $position > 0 && $position <= $winners_count ) {
									++$remove_count;
								}
							}
						}

						if ( $remove_count <= 0 ) {
							continue;
						}

						$existing_result = $wpdb->get_row(
							$wpdb->prepare(
								'SELECT id, wins FROM ' . $tres_tbl . ' WHERE tournament_id = %d AND user_id = %d',
								(int) $tournament_id,
								$user_id
							)
						);

						if ( ! $existing_result ) {
							continue;
						}

						$remaining_wins = max( 0, (int) $existing_result->wins - (int) $remove_count );

						if ( $remaining_wins > 0 ) {
							$updated = $wpdb->update(
								$tres_tbl,
								array( 'wins' => $remaining_wins ),
								array( 'id' => (int) $existing_result->id ),
								array( '%d' ),
								array( '%d' )
							);

							if ( false === $updated ) {
								bhg_log( $wpdb->last_error );
								return false;
							}
						} else {
							$deleted_result = $wpdb->delete( $tres_tbl, array( 'id' => (int) $existing_result->id ), array( '%d' ) );
							if ( false === $deleted_result ) {
								bhg_log( $wpdb->last_error );
								return false;
							}
						}
					}
				}
			}
		}
		// Fetch winners based on proximity to final balance.
				$limit_config       = function_exists( 'bhg_get_win_limit_config' ) ? bhg_get_win_limit_config( 'hunt' ) : array(
					'count'  => 0,
					'period' => 'none',
				);
				$limit_count        = isset( $limit_config['count'] ) ? (int) $limit_config['count'] : 0;
				$limit_period       = isset( $limit_config['period'] ) ? (string) $limit_config['period'] : 'none';
				$limit_active       = ( $limit_count > 0 && 'none' !== $limit_period );
				$limit_window_start = '';

				if ( $limit_active && function_exists( 'bhg_get_period_window_start' ) ) {
						$limit_window_start = bhg_get_period_window_start( $limit_period );
				}

				$query      = 'SELECT id, user_id, guess, (%f - guess) AS diff, created_at FROM ' . $guesses_tbl . ' WHERE hunt_id = %d ORDER BY ABS(%f - guess) ASC, id ASC';
				$params     = array( (float) $final_balance, (int) $hunt_id, (float) $final_balance );
				$needs_full = $has_all_mode || $limit_active;
				if ( ! $needs_full ) {
						$query   .= ' LIMIT %d';
						$params[] = (int) $winners_count;
				}

				$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $query ), $params ) );
				$rows     = $wpdb->get_results( $prepared );
				if ( empty( $rows ) ) {
						return array();
				}

				// Record winners and update tournament results.
				$existing_counts = array();
				if ( $limit_active && '' !== $limit_window_start ) {
						$user_ids = array();
					foreach ( (array) $rows as $row ) {
						$uid = isset( $row->user_id ) ? (int) $row->user_id : 0;
						if ( $uid > 0 ) {
							$user_ids[ $uid ] = $uid;
						}
					}

					if ( ! empty( $user_ids ) ) {
							$placeholders = implode( ', ', array_fill( 0, count( $user_ids ), '%d' ) );
							$count_sql    = "SELECT user_id, COUNT(*) AS wins FROM {$winners_tbl} WHERE user_id IN ({$placeholders}) AND eligible = 1";
							$count_args   = array_values( $user_ids );

						if ( '' !== $limit_window_start ) {
							$count_sql   .= ' AND created_at >= %s';
							$count_args[] = $limit_window_start;
						}

							$count_sql .= ' GROUP BY user_id';

							$prepared_count = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $count_sql ), $count_args ) );
							$count_rows     = $wpdb->get_results( $prepared_count );

						if ( ! empty( $count_rows ) ) {
							foreach ( $count_rows as $count_row ) {
								$uid = isset( $count_row->user_id ) ? (int) $count_row->user_id : 0;
								if ( $uid > 0 ) {
									$existing_counts[ $uid ] = isset( $count_row->wins ) ? (int) $count_row->wins : 0;
								}
							}
						}
					}
				}

				$position           = 1;
				$should_recalculate = false;
				$awarded            = 0;
				$official_winners   = array();
				$recorded_user_ids  = array();

				foreach ( (array) $rows as $row ) {
						$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;

					if ( $user_id <= 0 ) {
						++$position;
						continue;
					}

					$eligible_by_limit = true;
					$current_wins      = isset( $existing_counts[ $user_id ] ) ? (int) $existing_counts[ $user_id ] : 0;

					if ( $limit_active && '' !== $limit_window_start && $current_wins >= $limit_count ) {
							$eligible_by_limit = false;
					}

							$eligible = $eligible_by_limit;

					if ( $eligible && $awarded >= $winners_count ) {
						$eligible = false;
					}

							$record_entry = $eligible;

					if ( ! $record_entry && $limit_active && ! $eligible_by_limit ) {
						$record_entry = true;
					}

					if ( ! $record_entry && $has_all_mode ) {
						$record_entry = true;
					}

					if ( $record_entry ) {
						$inserted = $wpdb->insert(
							$winners_tbl,
							array(
								'hunt_id'    => $hunt_id,
								'user_id'    => $user_id,
								'position'   => $position,
								'guess'      => isset( $row->guess ) ? (float) $row->guess : 0.0,
								'diff'       => isset( $row->diff ) ? (float) $row->diff : 0.0,
								'eligible'   => $eligible ? 1 : 0,
								'created_at' => $now,
							),
							array( '%d', '%d', '%d', '%f', '%f', '%d', '%s' )
						);

						if ( false === $inserted ) {
							bhg_log( $wpdb->last_error );
							return false;
						}

							$recorded_user_ids[ $user_id ] = $user_id;

						if ( ! empty( $tournament_ids ) ) {
							$should_recalculate = true;
						}
					}

					if ( $eligible ) {
						$official_winners[] = $user_id;
						++$awarded;
						if ( $limit_active ) {
								$existing_counts[ $user_id ] = $current_wins + 1;
						}
					}

							++$position;

					if ( ! $has_all_mode && $awarded >= $winners_count ) {
						break;
					}
				}

				if ( $should_recalculate && ! empty( $tournament_ids ) ) {
						self::recalculate_tournament_results( $tournament_ids );
				}

				if ( class_exists( 'BHG_Jackpots' ) ) {
						$jackpot_context = array(
							'affiliate_id'      => ( $hunt_row && isset( $hunt_row->affiliate_id ) ) ? (int) $hunt_row->affiliate_id : 0,
							'affiliate_site_id' => ( $hunt_row && isset( $hunt_row->affiliate_site_id ) ) ? (int) $hunt_row->affiliate_site_id : 0,
							'closed_at'         => $now,
						);

						BHG_Jackpots::instance()->handle_hunt_closure( $hunt_id, $final_balance, (array) $rows, $jackpot_context );
				}

				$official_winners  = array_map( 'intval', $official_winners );
				$recorded_user_ids = array_map( 'intval', array_values( $recorded_user_ids ) );

				if ( $has_all_mode && ! empty( $recorded_user_ids ) ) {
						return $recorded_user_ids;
				}

				return $official_winners;
	}

	/**
	 * Recalculate tournament leaderboards based on current hunt winners.
	 *
	 * @param int[] $tournament_ids Tournament identifiers to recalculate.
	 *
	 * @return void
	 */
	public static function recalculate_tournament_results( array $tournament_ids ) {
			global $wpdb;

		if ( empty( $tournament_ids ) ) {
				return;
		}

			$tournament_limit_config = function_exists( 'bhg_get_win_limit_config' ) ? bhg_get_win_limit_config( 'tournament' ) : array(
				'count'  => 0,
				'period' => 'none',
			);
			$t_limit_count           = isset( $tournament_limit_config['count'] ) ? (int) $tournament_limit_config['count'] : 0;
			$t_limit_period          = isset( $tournament_limit_config['period'] ) ? (string) $tournament_limit_config['period'] : 'none';
			$t_limit_active          = ( $t_limit_count > 0 && 'none' !== $t_limit_period );
			$t_limit_seconds         = 0;

			if ( $t_limit_active && function_exists( 'bhg_get_period_interval_seconds' ) ) {
					$t_limit_seconds = (int) bhg_get_period_interval_seconds( $t_limit_period );
				if ( $t_limit_seconds <= 0 ) {
						$t_limit_active = false;
				}
			}

			$normalized = array();
			foreach ( $tournament_ids as $tournament_id ) {
					$tournament_id = absint( $tournament_id );
				if ( $tournament_id > 0 ) {
						$normalized[ $tournament_id ] = $tournament_id;
				}
			}

			if ( empty( $normalized ) ) {
				return;
			}

			$hunts_tbl    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$winners_tbl  = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
			$results_tbl  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
			$relation_tbl = esc_sql( $wpdb->prefix . 'bhg_tournaments_hunts' );
			$tours_tbl    = esc_sql( $wpdb->prefix . 'bhg_tournaments' );

			foreach ( $normalized as $tournament_id ) {
					$tournament = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT participants_mode, points_map, ranking_scope FROM {$tours_tbl} WHERE id = %d",
							$tournament_id
						)
					);

				if ( ! $tournament ) {
					continue;
				}

					$participants_mode = isset( $tournament->participants_mode ) ? sanitize_key( $tournament->participants_mode ) : 'winners';
				if ( ! in_array( $participants_mode, array( 'winners', 'all' ), true ) ) {
						$participants_mode = 'winners';
				}

					$ranking_scope = isset( $tournament->ranking_scope ) ? sanitize_key( $tournament->ranking_scope ) : 'all';
				if ( ! in_array( $ranking_scope, array( 'all', 'closed', 'active' ), true ) ) {
						$ranking_scope = 'all';
				}

					$points_map = array();
				if ( ! empty( $tournament->points_map ) ) {
						$decoded = json_decode( $tournament->points_map, true );
					if ( is_array( $decoded ) && function_exists( 'bhg_sanitize_points_map' ) ) {
							$points_map = bhg_sanitize_points_map( $decoded );
					}
				}

				if ( empty( $points_map ) && function_exists( 'bhg_get_default_points_map' ) ) {
						$points_map = bhg_get_default_points_map();
				}

					$scope_clause = '';
				if ( 'active' === $ranking_scope ) {
						$scope_clause = " AND h.status = 'open'";
				} elseif ( 'closed' === $ranking_scope ) {
						$scope_clause = " AND h.status = 'closed'";
				}

					$query = "
                                SELECT
                                        hw.id AS hw_id,
                                        hw.user_id,
                                        hw.position,
                                        hw.eligible,
                                        hw.hunt_id,
                                        COALESCE(hw.created_at, h.closed_at, h.updated_at, h.created_at) AS event_date,
                                        h.winners_count
                                FROM {$winners_tbl} hw
                                INNER JOIN {$hunts_tbl} h ON h.id = hw.hunt_id
                                LEFT JOIN {$relation_tbl} ht ON ht.hunt_id = h.id
                                WHERE (ht.tournament_id = %d OR (ht.tournament_id IS NULL AND h.tournament_id = %d))
                                {$scope_clause}
                        ";

					$rows = $wpdb->get_results(
						$wpdb->prepare(
							$query,
							$tournament_id,
							$tournament_id
						)
					);

				if ( null === $rows && $wpdb->last_error ) {
					bhg_log( sprintf( 'Failed to fetch recalculated standings for tournament #%d: %s', $tournament_id, $wpdb->last_error ) );
					continue;
				}

					$deleted = $wpdb->delete( $results_tbl, array( 'tournament_id' => $tournament_id ), array( '%d' ) );
				if ( false === $deleted ) {
						bhg_log( sprintf( 'Failed to clear existing standings for tournament #%d: %s', $tournament_id, $wpdb->last_error ) );
						continue;
				}

				if ( empty( $rows ) ) {
						continue;
				}

					$results_map = array();

				if ( ! empty( $rows ) ) {
						usort(
							$rows,
							static function ( $a, $b ) {
										$event_a = isset( $a->event_date ) ? (string) $a->event_date : '';
										$event_b = isset( $b->event_date ) ? (string) $b->event_date : '';

								if ( $event_a !== $event_b ) {
										return strcmp( $event_a, $event_b );
								}

										$pos_a = isset( $a->position ) ? (int) $a->position : 0;
										$pos_b = isset( $b->position ) ? (int) $b->position : 0;

								if ( $pos_a !== $pos_b ) {
										return ( $pos_a < $pos_b ) ? -1 : 1;
								}

										$id_a = isset( $a->hw_id ) ? (int) $a->hw_id : 0;
										$id_b = isset( $b->hw_id ) ? (int) $b->hw_id : 0;

								if ( $id_a === $id_b ) {
										return 0;
								}

										return ( $id_a < $id_b ) ? -1 : 1;
							}
						);
				}

					$user_event_windows = array();

				foreach ( $rows as $row ) {
						$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;

					if ( $user_id <= 0 ) {
								continue;
					}

						$is_eligible = 1;
					if ( isset( $row->eligible ) ) {
							$is_eligible = (int) $row->eligible;
					}

					if ( 1 !== $is_eligible && 'all' !== $participants_mode ) {
							continue;
					}

						$position = isset( $row->position ) ? (int) $row->position : 0;
						$limit    = isset( $row->winners_count ) ? (int) $row->winners_count : 0;

					if ( $limit <= 0 ) {
							$limit = max( 1, count( $points_map ) );
					}

					if ( 'winners' === $participants_mode && ( $position <= 0 || $position > $limit ) ) {
							continue;
					}

						$counts_as_win = ( $position > 0 && $position <= $limit );

					if ( 'all' === $participants_mode ) {
							$counts_as_win = ( $position > 0 );
					}

						$event_date = '';
					if ( isset( $row->event_date ) && $row->event_date ) {
							$event_date = (string) $row->event_date;
					}

					if ( '' === $event_date ) {
							$event_date = current_time( 'mysql' );
					}

						$event_ts = $event_date ? mysql2date( 'U', $event_date, false ) : false;

					if ( $t_limit_active && $counts_as_win && $event_ts ) {
						if ( ! isset( $user_event_windows[ $user_id ] ) ) {
								$user_event_windows[ $user_id ] = array();
						}

							$window_start                   = $event_ts - $t_limit_seconds;
							$user_event_windows[ $user_id ] = array_values(
								array_filter(
									$user_event_windows[ $user_id ],
									static function ( $ts ) use ( $window_start ) {
													return (int) $ts >= (int) $window_start;
									}
								)
							);

						if ( count( $user_event_windows[ $user_id ] ) >= $t_limit_count ) {
							continue;
						}
					}

					if ( ! isset( $results_map[ $user_id ] ) ) {
							$results_map[ $user_id ] = array(
								'user_id'    => $user_id,
								'wins'       => 0,
								'points'     => 0,
								'last_event' => '',
							);
					}

						$points_awarded = 0;
					if ( $position > 0 && isset( $points_map[ $position ] ) ) {
							$points_awarded = (int) $points_map[ $position ];
					}

					if ( $points_awarded > 0 ) {
							$results_map[ $user_id ]['points'] += $points_awarded;
					}

					if ( $counts_as_win ) {
							++$results_map[ $user_id ]['wins'];
					}

					if ( '' === $results_map[ $user_id ]['last_event'] || strcmp( $event_date, $results_map[ $user_id ]['last_event'] ) > 0 ) {
							$results_map[ $user_id ]['last_event'] = $event_date;
					}

					if ( $t_limit_active && $counts_as_win && $event_ts ) {
							$user_event_windows[ $user_id ][] = $event_ts;
					}
				}

				if ( empty( $results_map ) ) {
						continue;
				}

					$results = array_values( $results_map );

					usort(
						$results,
						static function ( $a, $b ) {
									$points_a = isset( $a['points'] ) ? (int) $a['points'] : 0;
									$points_b = isset( $b['points'] ) ? (int) $b['points'] : 0;

							if ( $points_a !== $points_b ) {
									return ( $points_a < $points_b ) ? 1 : -1;
							}

									$wins_a = isset( $a['wins'] ) ? (int) $a['wins'] : 0;
									$wins_b = isset( $b['wins'] ) ? (int) $b['wins'] : 0;

							if ( $wins_a !== $wins_b ) {
									return ( $wins_a < $wins_b ) ? 1 : -1;
							}

									$date_a = isset( $a['last_event'] ) ? (string) $a['last_event'] : '';
									$date_b = isset( $b['last_event'] ) ? (string) $b['last_event'] : '';

							if ( $date_a !== $date_b ) {
									return strcmp( $date_a, $date_b );
							}

									$user_a = isset( $a['user_id'] ) ? (int) $a['user_id'] : 0;
									$user_b = isset( $b['user_id'] ) ? (int) $b['user_id'] : 0;

							if ( $user_a === $user_b ) {
									return 0;
							}

									return ( $user_a < $user_b ) ? -1 : 1;
						}
					);

				foreach ( $results as $result_row ) {
					$user_id = isset( $result_row['user_id'] ) ? (int) $result_row['user_id'] : 0;

					if ( $user_id <= 0 ) {
							continue;
					}

					$last_event = isset( $result_row['last_event'] ) ? (string) $result_row['last_event'] : current_time( 'mysql' );

					$inserted = $wpdb->insert(
						$results_tbl,
						array(
							'tournament_id' => $tournament_id,
							'user_id'       => $user_id,
							'wins'          => isset( $result_row['wins'] ) ? (int) $result_row['wins'] : 0,
							'points'        => isset( $result_row['points'] ) ? max( 0, (int) $result_row['points'] ) : 0,
							'last_win_date' => $last_event,
						),
						array( '%d', '%d', '%d', '%d', '%s' )
					);

					if ( false === $inserted ) {
						bhg_log( sprintf( 'Failed to store recalculated standings for tournament #%1$d and user#%2$d: %3$s', $tournament_id, $user_id, $wpdb->last_error ) );
					}
				}
			}
	}
}
