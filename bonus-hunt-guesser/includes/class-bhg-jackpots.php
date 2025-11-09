<?php
/**
 * Jackpot management for Bonus Hunt Guesser.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles CRUD, hunt-close integration, and history logs for jackpots.
 */
class BHG_Jackpots {

	/**
	 * Singleton instance.
	 *
	 * @var BHG_Jackpots|null
	 */
	protected static $instance = null;

	/**
	 * Retrieve singleton instance.
	 *
	 * @return BHG_Jackpots
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Jackpot table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'bhg_jackpots';
	}

	/**
	 * Jackpot events log table.
	 *
	 * @return string
	 */
	public static function events_table() {
		global $wpdb;

		return $wpdb->prefix . 'bhg_jackpot_events';
	}

	/**
	 * Fetch jackpot by ID.
	 *
	 * @param int $jackpot_id Jackpot ID.
	 * @return array|null
	 */
	public function get_jackpot( $jackpot_id ) {
		global $wpdb;

		$jackpot_id = absint( $jackpot_id );

		if ( $jackpot_id <= 0 ) {
			return null;
		}

		$table = self::table_name();

		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name derived from plugin prefix.
				"SELECT * FROM {$table} WHERE id = %d",
				$jackpot_id
			),
			ARRAY_A
		);
	}

	/**
	 * Retrieve jackpots for admin/shortcodes.
	 *
	 * @param array $args Query overrides.
	 * @return array
	 */
	public function get_jackpots( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status' => array( 'active', 'pending', 'hit' ),
			'limit'  => 0,
		);
		$args     = wp_parse_args( $args, $defaults );

		$where   = array();
		$params  = array();
		$where[] = '1=1';

		if ( ! empty( $args['status'] ) ) {
			$statuses = (array) $args['status'];
			$statuses = array_filter(
				array_map( 'sanitize_key', $statuses ),
				static function ( $status ) {
					return '' !== $status;
				}
			);

			if ( ! empty( $statuses ) ) {
				$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
				$where[]      = 'status IN (' . $placeholders . ')';
				$params       = array_merge( $params, $statuses );
			}
		}

		$sql = 'SELECT * FROM ' . self::table_name() . ' WHERE ' . implode( ' AND ', $where ) . ' ORDER BY updated_at DESC, id DESC';

		if ( ! empty( $args['limit'] ) ) {
			$sql     .= ' LIMIT %d';
			$params[] = (int) $args['limit'];
		}

		if ( empty( $params ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- dynamic filters assembled from sanitized values.
			return $wpdb->get_results( $sql, ARRAY_A );
		}

		$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $params ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- prepared statement generated above.
		return $wpdb->get_results( $prepared, ARRAY_A );
	}

	/**
	 * Insert or update a jackpot.
	 *
	 * @param array $data Jackpot data.
	 * @return int Jackpot ID on success.
	 */
	public function save_jackpot( array $data ) {
		global $wpdb;

		$jackpot_id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
		$now        = current_time( 'mysql' );

		$link_mode = isset( $data['link_mode'] ) ? sanitize_key( $data['link_mode'] ) : 'all';
		if ( ! in_array( $link_mode, array( 'all', 'selected', 'affiliate', 'period' ), true ) ) {
			$link_mode = 'all';
		}

		$config = array();
		switch ( $link_mode ) {
			case 'selected':
				$config_ids = isset( $data['linked_hunts'] ) ? (array) $data['linked_hunts'] : array();
				foreach ( $config_ids as $hunt_id ) {
					$hunt_id = absint( $hunt_id );
					if ( $hunt_id > 0 ) {
						$config['hunts'][ $hunt_id ] = $hunt_id;
					}
				}
				break;
			case 'affiliate':
				$affiliate_ids = isset( $data['linked_affiliates'] ) ? (array) $data['linked_affiliates'] : array();
				foreach ( $affiliate_ids as $affiliate_id ) {
					$affiliate_id = absint( $affiliate_id );
					if ( $affiliate_id > 0 ) {
						$config['affiliates'][ $affiliate_id ] = $affiliate_id;
					}
				}
				break;
			case 'period':
				$period = isset( $data['linked_period'] ) ? sanitize_key( $data['linked_period'] ) : 'this_month';
				if ( ! in_array( $period, array( 'this_month', 'this_year', 'all_time' ), true ) ) {
					$period = 'this_month';
				}
				$config['period'] = $period;
				break;
		}

		$payload = array(
			'title'           => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
			'start_amount'    => isset( $data['start_amount'] ) ? (float) $data['start_amount'] : 0.0,
			'increase_amount' => isset( $data['increase_amount'] ) ? (float) $data['increase_amount'] : 0.0,
			'link_mode'       => $link_mode,
			'link_config'     => wp_json_encode( $config ),
			'status'          => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'active',
			'updated_at'      => $now,
		);

		if ( empty( $payload['title'] ) ) {
			return 0;
		}

		if ( $payload['start_amount'] < 0 ) {
			$payload['start_amount'] = 0.0;
		}

		if ( $payload['increase_amount'] < 0 ) {
			$payload['increase_amount'] = 0.0;
		}

		if ( ! in_array( $payload['status'], array( 'active', 'pending', 'hit', 'inactive' ), true ) ) {
			$payload['status'] = 'active';
		}

		if ( $jackpot_id > 0 ) {
			$updated = $wpdb->update(
				self::table_name(),
				$payload,
				array( 'id' => $jackpot_id ),
				array( '%s', '%f', '%f', '%s', '%s', '%s', '%s' ),
				array( '%d' )
			);

			if ( false === $updated ) {
				return 0;
			}

			return $jackpot_id;
		}

		$payload['created_at']     = $now;
		$payload['current_amount'] = $payload['start_amount'];

		$inserted = $wpdb->insert(
			self::table_name(),
			$payload,
			array( '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%f' )
		);

		if ( false === $inserted ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Delete a jackpot.
	 *
	 * @param int $jackpot_id Jackpot ID.
	 * @return bool
	 */
	public function delete_jackpot( $jackpot_id ) {
		global $wpdb;

		$jackpot_id = absint( $jackpot_id );

		if ( $jackpot_id <= 0 ) {
			return false;
		}

		$deleted = $wpdb->delete( self::table_name(), array( 'id' => $jackpot_id ), array( '%d' ) );
		if ( false === $deleted ) {
			return false;
		}

		$wpdb->delete( self::events_table(), array( 'jackpot_id' => $jackpot_id ), array( '%d' ) );

		return true;
	}

	/**
	 * Reset jackpot to initial state.
	 *
	 * @param int $jackpot_id Jackpot ID.
	 * @return bool
	 */
	public function reset_jackpot( $jackpot_id ) {
		global $wpdb;

		$jackpot = $this->get_jackpot( $jackpot_id );
		if ( ! $jackpot ) {
			return false;
		}

		$now     = current_time( 'mysql' );
		$updated = $wpdb->update(
			self::table_name(),
			array(
				'current_amount' => isset( $jackpot['start_amount'] ) ? (float) $jackpot['start_amount'] : 0.0,
				'status'         => 'active',
				'hit_user_id'    => 0,
				'hit_hunt_id'    => 0,
				'hit_guess_id'   => 0,
				'hit_at'         => null,
				'updated_at'     => $now,
			),
			array( 'id' => (int) $jackpot['id'] ),
			array( '%f', '%s', '%d', '%d', '%d', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return false;
		}

		$this->log_event(
			(int) $jackpot['id'],
			'reset',
			isset( $jackpot['current_amount'] ) ? (float) $jackpot['current_amount'] : 0.0,
			isset( $jackpot['start_amount'] ) ? (float) $jackpot['start_amount'] : 0.0,
			0,
			0,
			array( 'reason' => 'reset' )
		);

		return true;
	}

	/**
	 * Handle hunt closure for jackpots.
	 *
	 * @param int   $hunt_id       Hunt ID.
	 * @param float $final_balance Final balance.
	 * @param array $guess_rows    Guess rows considered for winner selection.
	 * @param array $context       Additional context (affiliate IDs, closed_at).
	 * @return void
	 */
	public function handle_hunt_closure( $hunt_id, $final_balance, array $guess_rows, array $context = array() ) {
		$eligible = $this->get_jackpots_for_hunt( $hunt_id, $context );

		if ( empty( $eligible ) ) {
			return;
		}

		$final_formatted = number_format( (float) $final_balance, 2, '.', '' );
		$exact_guess     = null;

		foreach ( $guess_rows as $row ) {
			$row_guess = isset( $row->guess ) ? number_format( (float) $row->guess, 2, '.', '' ) : null;
			if ( $row_guess === $final_formatted ) {
				$exact_guess = array(
					'guess_id' => isset( $row->id ) ? (int) $row->id : 0,
					'user_id'  => isset( $row->user_id ) ? (int) $row->user_id : 0,
				);
				break;
			}
		}

		foreach ( $eligible as $jackpot ) {
			if ( $exact_guess && $exact_guess['user_id'] > 0 ) {
				$this->record_hit( $jackpot, $exact_guess['user_id'], $hunt_id, $exact_guess['guess_id'], (float) $final_balance );
			} else {
				$this->record_miss( $jackpot, $hunt_id );
			}
		}
	}

	/**
	 * Retrieve jackpots applicable to a hunt.
	 *
	 * @param int   $hunt_id Hunt ID.
	 * @param array $context Context data.
	 * @return array
	 */
	protected function get_jackpots_for_hunt( $hunt_id, array $context ) {
		$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
			return array();
		}

		$jackpots = $this->get_jackpots(
			array(
				'status' => array( 'active' ),
			)
		);

		if ( empty( $jackpots ) ) {
			return array();
		}

		$affiliate_id     = isset( $context['affiliate_id'] ) ? (int) $context['affiliate_id'] : 0;
		$affiliate_site   = isset( $context['affiliate_site_id'] ) ? (int) $context['affiliate_site_id'] : 0;
		$closed_at        = isset( $context['closed_at'] ) ? (string) $context['closed_at'] : '';
		$closed_timestamp = $closed_at ? mysql2date( 'U', $closed_at, false ) : time();

		$eligible = array();

		foreach ( $jackpots as $jackpot ) {
			$link_mode   = isset( $jackpot['link_mode'] ) ? sanitize_key( $jackpot['link_mode'] ) : 'all';
			$link_config = array();
			if ( ! empty( $jackpot['link_config'] ) ) {
				$decoded = json_decode( $jackpot['link_config'], true );
				if ( is_array( $decoded ) ) {
					$link_config = $decoded;
				}
			}

			$applies = false;

			switch ( $link_mode ) {
				case 'selected':
					$hunts   = isset( $link_config['hunts'] ) ? array_map( 'intval', (array) $link_config['hunts'] ) : array();
					$applies = in_array( $hunt_id, $hunts, true );
					break;
				case 'affiliate':
					$ids = isset( $link_config['affiliates'] ) ? array_map( 'intval', (array) $link_config['affiliates'] ) : array();
					if ( $affiliate_site && in_array( $affiliate_site, $ids, true ) ) {
						$applies = true;
					} elseif ( $affiliate_id && in_array( $affiliate_id, $ids, true ) ) {
						$applies = true;
					}
					break;
				case 'period':
					$period = isset( $link_config['period'] ) ? sanitize_key( $link_config['period'] ) : 'this_month';
					if ( 'all_time' === $period ) {
						$applies = true;
					} elseif ( 'this_year' === $period ) {
						$applies = gmdate( 'Y', $closed_timestamp ) === gmdate( 'Y', time() );
					} else {
						$applies = gmdate( 'Ym', $closed_timestamp ) === gmdate( 'Ym', time() );
					}
					break;
				case 'all':
				default:
					$applies = true;
					break;
			}

			if ( $applies ) {
				$eligible[] = $jackpot;
			}
		}

		return $eligible;
	}

	/**
	 * Record jackpot hit event.
	 *
	 * @param array $jackpot    Jackpot row.
	 * @param int   $user_id    Winning user ID.
	 * @param int   $hunt_id    Hunt ID.
	 * @param int   $guess_id   Guess ID.
	 * @param float $final_balance Final balance.
	 * @return void
	 */
	protected function record_hit( array $jackpot, $user_id, $hunt_id, $guess_id, $final_balance ) {
		global $wpdb;

		$jackpot_id     = isset( $jackpot['id'] ) ? (int) $jackpot['id'] : 0;
		$current_amount = isset( $jackpot['current_amount'] ) ? (float) $jackpot['current_amount'] : 0.0;
		$now            = current_time( 'mysql' );

		if ( $jackpot_id <= 0 ) {
			return;
		}

		$wpdb->update(
			self::table_name(),
			array(
				'status'         => 'hit',
				'hit_user_id'    => $user_id,
				'hit_hunt_id'    => $hunt_id,
				'hit_guess_id'   => $guess_id,
				'hit_at'         => $now,
				'current_amount' => $current_amount,
				'updated_at'     => $now,
			),
			array( 'id' => $jackpot_id ),
			array( '%s', '%d', '%d', '%d', '%s', '%f', '%s' ),
			array( '%d' )
		);

		$this->log_event(
			$jackpot_id,
			'hit',
			$current_amount,
			$current_amount,
			$user_id,
			$hunt_id,
			array(
				'guess_id'      => $guess_id,
				'final_balance' => $final_balance,
			)
		);
	}

	/**
	 * Record jackpot miss (increment amount).
	 *
	 * @param array $jackpot Jackpot row.
	 * @param int   $hunt_id Hunt ID.
	 * @return void
	 */
	protected function record_miss( array $jackpot, $hunt_id ) {
		global $wpdb;

		$jackpot_id     = isset( $jackpot['id'] ) ? (int) $jackpot['id'] : 0;
		$current_amount = isset( $jackpot['current_amount'] ) ? (float) $jackpot['current_amount'] : 0.0;
		$increase       = isset( $jackpot['increase_amount'] ) ? (float) $jackpot['increase_amount'] : 0.0;
		$new_amount     = $current_amount + $increase;
		$now            = current_time( 'mysql' );

		if ( $jackpot_id <= 0 ) {
			return;
		}

		$wpdb->update(
			self::table_name(),
			array(
				'current_amount' => $new_amount,
				'updated_at'     => $now,
			),
			array( 'id' => $jackpot_id ),
			array( '%f', '%s' ),
			array( '%d' )
		);

		$this->log_event( $jackpot_id, 'increase', $current_amount, $new_amount, 0, $hunt_id );
	}

	/**
	 * Store jackpot event log.
	 *
	 * @param int    $jackpot_id Jackpot ID.
	 * @param string $event_type Event type.
	 * @param float  $amount_before Amount before change.
	 * @param float  $amount_after Amount after change.
	 * @param int    $user_id User ID.
	 * @param int    $hunt_id Hunt ID.
	 * @param array  $meta Additional meta.
	 * @return void
	 */
	protected function log_event( $jackpot_id, $event_type, $amount_before, $amount_after, $user_id = 0, $hunt_id = 0, array $meta = array() ) {
		global $wpdb;

		$jackpot_id = absint( $jackpot_id );
		if ( $jackpot_id <= 0 ) {
			return;
		}

		$wpdb->insert(
			self::events_table(),
			array(
				'jackpot_id'    => $jackpot_id,
				'event_type'    => sanitize_key( $event_type ),
				'amount_before' => (float) $amount_before,
				'amount_after'  => (float) $amount_after,
				'user_id'       => absint( $user_id ),
				'hunt_id'       => absint( $hunt_id ),
				'meta'          => ! empty( $meta ) ? wp_json_encode( $meta ) : null,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%f', '%f', '%d', '%d', '%s', '%s' )
		);
	}

	/**
	 * Retrieve latest hit events for shortcodes.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public function get_latest_hits( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'     => 5,
			'affiliate' => 0,
			'year'      => 0,
		);
		$args     = wp_parse_args( $args, $defaults );

		$events_table  = self::events_table();
		$jackpot_table = self::table_name();
		$hunts_table   = $wpdb->prefix . 'bhg_bonus_hunts';

		$where  = array( "e.event_type = 'hit'" );
		$params = array();

		if ( ! empty( $args['affiliate'] ) ) {
			$where[]  = 'h.affiliate_site_id = %d';
			$params[] = (int) $args['affiliate'];
		}

		if ( ! empty( $args['year'] ) ) {
			$where[]  = 'YEAR(e.created_at) = %d';
			$params[] = (int) $args['year'];
		}

		$sql = "SELECT e.*, j.title AS jackpot_title, j.current_amount, h.title AS hunt_title
                FROM {$events_table} e
                LEFT JOIN {$jackpot_table} j ON j.id = e.jackpot_id
                LEFT JOIN {$hunts_table} h ON h.id = e.hunt_id
                WHERE " . implode( ' AND ', $where ) . '
                ORDER BY e.created_at DESC, e.id DESC
                LIMIT %d';

		$params[] = max( 1, (int) $args['limit'] );

		$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $params ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- prepared statement generated above.
		return $wpdb->get_results( $prepared, ARRAY_A );
	}

	/**
	 * Retrieve jackpots for ticker view.
	 *
	 * @param string $mode Mode.
	 * @return array
	 */
	public function get_ticker_items( $mode = 'amount' ) {
		$mode = sanitize_key( $mode );

		if ( 'winners' === $mode ) {
			return $this->get_latest_hits( array( 'limit' => 10 ) );
		}

		return $this->get_jackpots(
			array(
				'status' => array( 'active', 'pending' ),
			)
		);
	}

	/**
	 * Retrieve jackpot winner history rows.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public function get_winner_rows( $args = array() ) {
		return $this->get_latest_hits( $args );
	}

	/**
	 * Get formatted amount for a jackpot.
	 *
	 * @param int $jackpot_id Jackpot ID.
	 * @return string
	 */
	public function get_formatted_amount( $jackpot_id ) {
		$jackpot = $this->get_jackpot( $jackpot_id );
		if ( ! $jackpot ) {
			return '';
		}

		$amount = isset( $jackpot['current_amount'] ) ? (float) $jackpot['current_amount'] : 0.0;

		if ( function_exists( 'bhg_format_money' ) ) {
			return bhg_format_money( $amount );
		}

		return number_format_i18n( $amount, 2 );
	}
}
