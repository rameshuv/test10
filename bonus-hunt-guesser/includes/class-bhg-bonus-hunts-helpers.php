<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
	* Helper functions for hunts and guesses used by admin dashboard, list and results.
	* DB tables assumed:
	*  - {$wpdb->prefix}bhg_bonus_hunts (id, title, starting_balance, final_balance, winners_count, status, closed_at)
	*  - {$wpdb->prefix}bhg_guesses (id, hunt_id, user_id, guess, created_at)
	*/

if ( ! function_exists( 'bhg_normalize_int_list' ) ) {
	function bhg_normalize_int_list( $ids ) {
		$ids        = is_array( $ids ) ? $ids : array( $ids );
		$normalized = array();

		foreach ( $ids as $value ) {
			$value = max( 0, absint( $value ) );
			if ( $value > 0 ) {
				$normalized[ $value ] = $value;
			}
		}

		return array_values( $normalized );
	}
}

if ( ! function_exists( 'bhg_get_hunt_tournament_ids' ) ) {
	function bhg_get_hunt_tournament_ids( $hunt_id ) {
		global $wpdb;

		$hunt_id      = absint( $hunt_id );
		$relation_tbl = $wpdb->prefix . 'bhg_tournaments_hunts';
		$hunts_tbl    = $wpdb->prefix . 'bhg_bonus_hunts';

		if ( $hunt_id <= 0 ) {
			return array();
		}

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT tournament_id FROM `{$relation_tbl}` WHERE hunt_id = %d ORDER BY created_at ASC, id ASC",
				$hunt_id
			)
		);

		$ids = bhg_normalize_int_list( $ids );

		if ( ! empty( $ids ) ) {
			return $ids;
		}

		$legacy = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT tournament_id FROM `{$hunts_tbl}` WHERE id = %d",
				$hunt_id
			)
		);

		return $legacy > 0 ? array( $legacy ) : array();
	}
}

if ( ! function_exists( 'bhg_get_tournament_hunt_ids' ) ) {
	function bhg_get_tournament_hunt_ids( $tournament_id ) {
		global $wpdb;

		$tournament_id = absint( $tournament_id );
		$table         = $wpdb->prefix . 'bhg_tournaments_hunts';

		if ( $tournament_id <= 0 ) {
			return array();
		}

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT hunt_id FROM `{$table}` WHERE tournament_id = %d ORDER BY created_at ASC, id ASC",
				$tournament_id
			)
		);

		return bhg_normalize_int_list( $ids );
	}
}

if ( ! function_exists( 'bhg_sync_legacy_hunt_tournament_column' ) ) {
	function bhg_sync_legacy_hunt_tournament_column( $hunt_id, $known_ids = null ) {
		global $wpdb;

		$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
			return;
		}

		$hunts_tbl = $wpdb->prefix . 'bhg_bonus_hunts';

		if ( null === $known_ids ) {
			$known_ids = bhg_get_hunt_tournament_ids( $hunt_id );
		}

		$known_ids = bhg_normalize_int_list( $known_ids );
		$primary   = $known_ids ? (int) reset( $known_ids ) : 0;

		$wpdb->update(
			$hunts_tbl,
			array( 'tournament_id' => $primary ),
			array( 'id' => $hunt_id ),
			array( '%d' ),
			array( '%d' )
		);
	}
}

if ( ! function_exists( 'bhg_set_hunt_tournaments' ) ) {
	function bhg_set_hunt_tournaments( $hunt_id, $tournament_ids ) {
		global $wpdb;

		$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
			return;
		}

		$table     = $wpdb->prefix . 'bhg_tournaments_hunts';
		$new_ids   = bhg_normalize_int_list( $tournament_ids );
		$current   = bhg_get_hunt_tournament_ids( $hunt_id );
		$to_add    = array_diff( $new_ids, $current );
		$to_remove = array_diff( $current, $new_ids );

		if ( ! empty( $to_remove ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $to_remove ), '%d' ) );
			$params       = array_merge( array( $hunt_id ), array_values( $to_remove ) );
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$table}` WHERE hunt_id = %d AND tournament_id IN ({$placeholders})",
					...$params
				)
			);
		}

		if ( ! empty( $to_add ) ) {
			$now = current_time( 'mysql' );
			foreach ( $to_add as $tid ) {
				$wpdb->insert(
					$table,
					array(
						'hunt_id'       => $hunt_id,
						'tournament_id' => $tid,
						'created_at'    => $now,
					),
					array( '%d', '%d', '%s' )
				);
			}
		}

		bhg_sync_legacy_hunt_tournament_column( $hunt_id, $new_ids );
	}
}

if ( ! function_exists( 'bhg_set_tournament_hunts' ) ) {
	function bhg_set_tournament_hunts( $tournament_id, $hunt_ids ) {
		global $wpdb;

		$tournament_id = absint( $tournament_id );
		if ( $tournament_id <= 0 ) {
			return;
		}

		$table     = $wpdb->prefix . 'bhg_tournaments_hunts';
		$new_hunts = bhg_normalize_int_list( $hunt_ids );
		$current   = bhg_get_tournament_hunt_ids( $tournament_id );
		$to_add    = array_diff( $new_hunts, $current );
		$to_remove = array_diff( $current, $new_hunts );
		$affected  = array_unique( array_merge( $to_add, $to_remove ) );

		if ( ! empty( $to_remove ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $to_remove ), '%d' ) );
			$params       = array_merge( array( $tournament_id ), array_values( $to_remove ) );
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$table}` WHERE tournament_id = %d AND hunt_id IN ({$placeholders})",
					...$params
				)
			);
		}

		if ( ! empty( $to_add ) ) {
			$now = current_time( 'mysql' );
			foreach ( $to_add as $hunt_id ) {
				$wpdb->insert(
					$table,
					array(
						'hunt_id'       => $hunt_id,
						'tournament_id' => $tournament_id,
						'created_at'    => $now,
					),
					array( '%d', '%d', '%s' )
				);
			}
		}

		foreach ( $affected as $hunt_id ) {
			bhg_sync_legacy_hunt_tournament_column( $hunt_id );
		}
	}
}

if ( ! function_exists( 'bhg_get_hunt' ) ) {
	function bhg_get_hunt( $hunt_id ) {
							global $wpdb;
							$t    = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
							$hunt = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id=%d", (int) $hunt_id ) );

		if ( $hunt ) {
				$hunt->tournament_ids = bhg_get_hunt_tournament_ids( (int) $hunt->id );
		}

							return $hunt;
	}
}

if ( ! function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	function bhg_get_latest_closed_hunts( $limit = 3 ) {
				global $wpdb;
				$t = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
				return $wpdb->get_results(
					$wpdb->prepare(
						"SELECT id, title, starting_balance, final_balance, winners_count, closed_at FROM {$t} WHERE status = %s ORDER BY closed_at DESC LIMIT %d",
						'closed',
						(int) $limit
					)
				);
	}
}

if ( ! function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
	function bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit = 3 ) {
			global $wpdb;
			$t_g = esc_sql( $wpdb->prefix . 'bhg_guesses' );
			$t_h = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );

							$hunt = $wpdb->get_row( $wpdb->prepare( "SELECT final_balance, winners_count FROM {$t_h} WHERE id=%d", (int) $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
				return array();
		}
		if ( $winners_limit ) {
						$limit = (int) $winners_limit;
		} elseif ( $hunt->winners_count ) {
						$limit = (int) $hunt->winners_count;
		} else {
						$limit = 3;
		}

			$limit = max( 1, min( $limit, 25 ) );

			$sql = $wpdb->prepare(
				sprintf(
					'SELECT g.user_id, g.guess, (%%f - g.guess) AS diff FROM `%s` g WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC LIMIT %%d',
					$t_g
				),
				(float) $hunt->final_balance,
				(int) $hunt_id,
				(float) $hunt->final_balance,
				(int) $limit
			);
			return $wpdb->get_results( $sql );
	}
}

if ( ! function_exists( 'bhg_get_all_ranked_guesses' ) ) {
	function bhg_get_all_ranked_guesses( $hunt_id ) {
				global $wpdb;
				$t_g                  = esc_sql( $wpdb->prefix . 'bhg_guesses' );
				$t_h                  = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
								$hunt = $wpdb->get_row( $wpdb->prepare( 'SELECT final_balance FROM `' . $t_h . '` WHERE id=%d', (int) $hunt_id ) );
		if ( ! $hunt || null === $hunt->final_balance ) {
				return array();
		}

				$sql = $wpdb->prepare(
					sprintf(
						'SELECT g.id, g.user_id, g.guess, (%%f - g.guess) AS diff FROM `%s` g WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC',
						$t_g
					),
					(float) $hunt->final_balance,
					(int) $hunt_id,
					(float) $hunt->final_balance
				);
				return $wpdb->get_results( $sql );
	}
}

if ( ! function_exists( 'bhg_get_hunt_participants' ) ) {
	function bhg_get_hunt_participants( $hunt_id, $paged = 1, $per_page = 30 ) {
				global $wpdb;
								$t_g    = esc_sql( $wpdb->prefix . 'bhg_guesses' );
								$offset = max( 0, ( (int) $paged - 1 ) * (int) $per_page );

				$rows  = $wpdb->get_results(
					$wpdb->prepare(
						sprintf(
							'SELECT id, user_id, guess, created_at FROM `%s` WHERE hunt_id = %%d ORDER BY created_at DESC LIMIT %%d OFFSET %%d',
							$t_g
						),
						(int) $hunt_id,
						(int) $per_page,
						(int) $offset
					)
				);
				$total = (int) $wpdb->get_var(
					$wpdb->prepare(
						sprintf( 'SELECT COUNT(*) FROM `%s` WHERE hunt_id = %%d', $t_g ),
						(int) $hunt_id
					)
				);
				return array(
					'rows'  => $rows,
					'total' => $total,
				);
	}
}

if ( ! function_exists( 'bhg_remove_guess' ) ) {
	/**
	 * Remove a guess by ID.
	 *
	 * @param int $guess_id Guess ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	function bhg_remove_guess( $guess_id ) {
		global $wpdb;
		$t_g = $wpdb->prefix . 'bhg_guesses';
		return $wpdb->delete( $t_g, array( 'id' => (int) $guess_id ), array( '%d' ) );
	}
}
