<?php
/**
 * Shortcodes for Bonus Hunt Guesser.
 *
 * PHP 7.4 safe, WP 6.3.5+ compatible.
 * Registers all shortcodes on init (once) and avoids parse errors.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
				exit;
			}

if ( ! class_exists( 'BHG_Shortcodes' ) ) {

		/**
		 * Handles shortcode registration and rendering.
		 */
		class BHG_Shortcodes {

				/**
				 * Flag to prevent enqueueing prize assets multiple times per request.
				 *
				 * @var bool
				 */
				private $prize_assets_enqueued = false;

				/**
				 * Cached profile visibility settings.
				 *
				 * @var array|null
				 */
				private $profile_visibility_settings = null;

						/**
						 * Registers all shortcodes.
						 */
				public function __construct() {
				// Core shortcodes.
				add_shortcode( 'bhg_active_hunt', array( $this, 'active_hunt_shortcode' ) );
				add_shortcode( 'bhg_guess_form', array( $this, 'guess_form_shortcode' ) );
				add_shortcode( 'bhg_leaderboard', array( $this, 'leaderboard_shortcode' ) );
				add_shortcode( 'bhg_tournaments', array( $this, 'tournaments_shortcode' ) );
				add_shortcode( 'bhg_winner_notifications', array( $this, 'winner_notifications_shortcode' ) );
				add_shortcode( 'bhg_user_profile', array( $this, 'user_profile_shortcode' ) );

				// Addons.
				add_shortcode( 'bhg_best_guessers', array( $this, 'best_guessers_shortcode' ) );
				add_shortcode( 'bhg_user_guesses', array( $this, 'user_guesses_shortcode' ) );
								add_shortcode( 'bhg_hunts', array( $this, 'hunts_shortcode' ) );
								add_shortcode( 'bhg_leaderboards', array( $this, 'leaderboards_shortcode' ) );
								add_shortcode( 'bhg_prizes', array( $this, 'prizes_shortcode' ) );
								add_shortcode( 'my_bonushunts', array( $this, 'my_bonushunts_shortcode' ) );
								add_shortcode( 'my_tournaments', array( $this, 'my_tournaments_shortcode' ) );
								add_shortcode( 'my_prizes', array( $this, 'my_prizes_shortcode' ) );
								add_shortcode( 'my_rankings', array( $this, 'my_rankings_shortcode' ) );
								add_shortcode( 'bhg_jackpot_current', array( $this, 'jackpot_current_shortcode' ) );
								add_shortcode( 'bhg_jackpot_latest', array( $this, 'jackpot_latest_shortcode' ) );
								add_shortcode( 'bhg_jackpot_ticker', array( $this, 'jackpot_ticker_shortcode' ) );
								add_shortcode( 'bhg_jackpot_winners', array( $this, 'jackpot_winners_shortcode' ) );

								// Legacy/aliases.
								add_shortcode( 'bonus_hunt_leaderboard', array( $this, 'leaderboard_shortcode' ) );
				add_shortcode( 'bonus_hunt_login', array( $this, 'login_hint_shortcode' ) );
				add_shortcode( 'bhg_active', array( $this, 'active_hunt_shortcode' ) );
		}
		/**
		 * Validates a database table name against known tables.
		 *
		 * @param string $table Database table name to validate.
		 * @return string Sanitized table name or empty string if invalid.
		 */
				private function sanitize_table( $table ) {
						global $wpdb;

$allowed = array(
$wpdb->prefix . 'bhg_bonus_hunts',
$wpdb->prefix . 'bhg_guesses',
$wpdb->prefix . 'bhg_tournaments',
$wpdb->prefix . 'bhg_tournament_results',
$wpdb->prefix . 'bhg_affiliate_websites',
$wpdb->prefix . 'bhg_hunt_winners',
$wpdb->prefix . 'bhg_tournaments_hunts',
$wpdb->prefix . 'bhg_prizes',
$wpdb->prefix . 'bhg_hunt_prizes',
$wpdb->prefix . 'bhg_jackpots',
$wpdb->prefix . 'bhg_jackpot_events',
$wpdb->users,
$wpdb->usermeta,
);

						return in_array( $table, $allowed, true ) ? $table : '';
				}

	/**
	* Calculates start and end datetime for a given timeline keyword.
	*
	* @param string $timeline Timeline keyword.
	* @return array|null Array with 'start' and 'end' in `Y-m-d H:i:s` or null for no restriction.
	*/
		private function get_timeline_range( $timeline ) {
				$timeline = strtolower( (string) $timeline );

				if ( '' === $timeline ) {
						return null;
				}

				$aliases = array(
						'day'         => 'day',
						'today'       => 'day',
						'this_day'    => 'day',
						'week'        => 'week',
						'this_week'   => 'week',
						'weekly'      => 'week',
						'month'       => 'month',
						'this_month'  => 'month',
						'monthly'     => 'month',
						'year'        => 'year',
						'this_year'   => 'year',
						'yearly'      => 'year',
						'quarter'     => 'quarter',
						'quarterly'   => 'quarter',
						'this_quarter' => 'quarter',
						'last_year'   => 'last_year',
						'all_time'    => 'all_time',
						'alltime'     => 'all_time',
				);

				$canonical = isset( $aliases[ $timeline ] ) ? $aliases[ $timeline ] : $timeline;

				$tz  = wp_timezone();
				$now = new DateTimeImmutable( 'now', $tz );

				switch ( $canonical ) {
						case 'day':
								$start_dt = $now->setTime( 0, 0, 0 );
								$end_dt   = $now->setTime( 23, 59, 59 );
								break;

						case 'week':
								$week     = get_weekstartend( $now->format( 'Y-m-d' ) );
								$start_dt = ( new DateTimeImmutable( '@' . $week['start'] ) )->setTimezone( $tz );
								$end_dt   = ( new DateTimeImmutable( '@' . $week['end'] ) )->setTimezone( $tz );
								break;

						case 'month':
								$start_dt = $now->modify( 'first day of this month' )->setTime( 0, 0, 0 );
								$end_dt   = $now->modify( 'last day of this month' )->setTime( 23, 59, 59 );
								break;

						case 'year':
								$start_dt = $now->setDate( (int) $now->format( 'Y' ), 1, 1 )->setTime( 0, 0, 0 );
								$end_dt   = $now->setDate( (int) $now->format( 'Y' ), 12, 31 )->setTime( 23, 59, 59 );
								break;

						case 'quarter':
								$year         = (int) $now->format( 'Y' );
								$month        = (int) $now->format( 'n' );
								$quarter      = (int) floor( ( $month - 1 ) / 3 ) + 1;
								$start_month  = ( ( $quarter - 1 ) * 3 ) + 1;
								$start_dt     = $now->setDate( $year, $start_month, 1 )->setTime( 0, 0, 0 );
								$end_dt       = $start_dt->modify( '+2 months' )->modify( 'last day of this month' )->setTime( 23, 59, 59 );
								break;

						case 'last_year':
								$year     = (int) $now->format( 'Y' ) - 1;
								$start_dt = $now->setDate( $year, 1, 1 )->setTime( 0, 0, 0 );
								$end_dt   = $now->setDate( $year, 12, 31 )->setTime( 23, 59, 59 );
								break;

						case 'all_time':
						default:
								return null;
				}

				return array(
						'start' => $start_dt->format( 'Y-m-d H:i:s' ),
						'end'   => $end_dt->format( 'Y-m-d H:i:s' ),
				);
		}

		/**
		 * Normalize prize layout keyword.
		 *
		 * @param string $layout Layout keyword.
		 * @return string
		 */
		private function normalize_prize_layout( $layout ) {
				$layout = strtolower( (string) $layout );

				return in_array( $layout, array( 'grid', 'carousel' ), true ) ? $layout : 'grid';
		}

/**
 * Normalize prize card size keyword.
 *
 * @param string $size Size keyword.
 * @return string
 */
private function normalize_prize_size( $size ) {
$size = strtolower( (string) $size );

return in_array( $size, array( 'small', 'medium', 'big' ), true ) ? $size : 'medium';
}

/**
 * Normalize a yes/no shortcode attribute.
 *
 * @param mixed $value         Raw attribute value.
 * @param bool  $allow_inherit Whether to allow inherit/default keywords.
 * @return string Either '1', '0', or 'inherit'.
 */
private function normalize_yes_no_attr( $value, $allow_inherit = true ) {
$value = strtolower( trim( (string) $value ) );

if ( $allow_inherit && in_array( $value, array( 'inherit', 'default', '' ), true ) ) {
return 'inherit';
}

if ( in_array( $value, array( 'no', '0', 'false', 'off' ), true ) ) {
return '0';
}

return '1';
}

/**
 * Normalize a click action shortcode attribute.
 *
 * @param mixed $value Raw attribute value.
 * @return string Normalized keyword or empty string when invalid.
 */
private function normalize_click_action_attr( $value ) {
if ( ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$action = sanitize_key( (string) $value );

if ( '' === $action ) {
return '';
}

if ( in_array( $action, array( 'inherit', 'default' ), true ) ) {
return 'inherit';
}

return BHG_Prizes::sanitize_click_action( $action, 'link' );
}

/**
 * Normalize a link target shortcode attribute.
 *
 * @param mixed $value         Raw attribute value.
 * @param bool  $allow_inherit Whether to accept inherit/default keywords.
 * @return string Normalized target keyword or empty string when invalid.
 */
private function normalize_link_target_attr( $value, $allow_inherit = true ) {
if ( ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$target = sanitize_key( (string) $value );

if ( $allow_inherit && in_array( $target, array( 'inherit', 'default', '' ), true ) ) {
return 'inherit';
}

return BHG_Prizes::sanitize_link_target( $target, '_self' );
}

		/**
		 * Locate a shortcode view file.
		 *
		 * @param string $view View identifier relative to the views directory.
		 * @return string
		 */
		private function get_view_path( $view ) {
				$view = trim( str_replace( array( '..', '\\' ), '', (string) $view ) );

				if ( '' === $view ) {
						return '';
				}

				$candidates = array(
						BHG_PLUGIN_DIR . 'templates/' . $view . '.php',
						BHG_PLUGIN_DIR . 'includes/views/' . $view . '.php',
				);

				/**
				 * Filters the candidate paths used to locate shortcode views.
				 *
				 * @param string[] $candidates Candidate file paths.
				 * @param string   $view       Requested view identifier.
				 */
				$candidates = apply_filters( 'bhg_shortcode_view_paths', $candidates, $view );

				foreach ( $candidates as $candidate ) {
						if ( ! $candidate ) {
								continue;
						}

						$candidate = wp_normalize_path( $candidate );

						if ( file_exists( $candidate ) ) {
								return $candidate;
						}
				}

				return '';
		}

	   /**
		* Ensure shared profile assets are loaded.
		*
		* @return void
		*/
	   private function enqueue_profile_assets() {
			   $base_url = defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ );
			   $version  = defined( 'BHG_VERSION' ) ? BHG_VERSION : null;

			   wp_enqueue_style(
					   'bhg-shortcodes',
					   $base_url . 'assets/css/bhg-shortcodes.css',
					   array(),
					   $version
			   );
	   }

	   /**
		* Ensure prize-specific assets are loaded.
		*/
	   private function enqueue_prize_assets() {
				if ( $this->prize_assets_enqueued ) {
						return;
				}

			   $base_url = defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ );
			   $version  = defined( 'BHG_VERSION' ) ? BHG_VERSION : null;

			   $this->enqueue_profile_assets();

				wp_enqueue_style(
						'bhg-prizes',
						$base_url . 'assets/css/bhg-prizes.css',
						array( 'bhg-shortcodes' ),
						$version
				);

				wp_enqueue_script(
						'bhg-prizes',
						$base_url . 'assets/js/bhg-prizes.js',
						array(),
						$version,
						true
				);

				$this->prize_assets_enqueued = true;
		}

		/**
		 * Determine whether a profile section should be displayed.
		 *
		 * @param string $section Section key.
		 * @return bool
		 */
		private function is_profile_section_enabled( $section ) {
				if ( null === $this->profile_visibility_settings ) {
						$settings = get_option( 'bhg_plugin_settings', array() );
						$sections = array();

						if ( isset( $settings['profile_sections'] ) && is_array( $settings['profile_sections'] ) ) {
								foreach ( $settings['profile_sections'] as $key => $value ) {
										$sections[ $key ] = (int) $value;
								}
						}

						$this->profile_visibility_settings = $sections;
				}

				if ( '' === $section ) {
						return true;
				}

				if ( empty( $this->profile_visibility_settings ) ) {
						return true;
				}

				if ( ! array_key_exists( $section, $this->profile_visibility_settings ) ) {
						return true;
				}

				return (bool) $this->profile_visibility_settings[ $section ];
		}

/**
 * Render prizes section markup.
 *
 * @param array  $prizes  Prize rows.
 * @param string $layout  Layout keyword.
 * @param string $size    Image size keyword.
 * @param array  $display Display overrides.
 * @param array  $options Section-level options.
 * @return string
 */
private function render_prize_section( $prizes, $layout, $size, $display = array(), $options = array() ) {
if ( empty( $prizes ) || ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$layout = $this->normalize_prize_layout( $layout );
$size   = $this->normalize_prize_size( $size );

$this->enqueue_prize_assets();

$section_options = BHG_Prizes::prepare_section_options( $options );
$display_options = BHG_Prizes::prepare_display_overrides( $display );

$limit = isset( $section_options['limit'] ) ? (int) $section_options['limit'] : 0;
if ( $limit > 0 && count( $prizes ) > $limit ) {
$prizes = array_slice( $prizes, 0, $limit );
}

if ( empty( $prizes ) ) {
return '';
}

$count             = count( $prizes );
$heading_text      = isset( $section_options['heading_text'] ) ? $section_options['heading_text'] : '';
$hide_heading      = ! empty( $section_options['hide_heading'] );
$carousel_visible  = isset( $section_options['carousel_visible'] ) ? max( 1, (int) $section_options['carousel_visible'] ) : 1;
$carousel_autoplay = ! empty( $section_options['carousel_autoplay'] );
$carousel_interval = isset( $section_options['carousel_interval'] ) ? max( 1000, (int) $section_options['carousel_interval'] ) : 5000;
$carousel_pages    = max( 1, (int) ceil( $count / max( 1, $carousel_visible ) ) );

if ( '' === $heading_text ) {
$heading_text = bhg_t( 'label_prizes', 'Prizes' );
}

$view          = $this->get_view_path( 'prizes/section' );
$card_renderer = array( $this, 'render_prize_card' );
$context       = array(
'layout'            => $layout,
'carousel_visible'  => $carousel_visible,
'carousel_autoplay' => $carousel_autoplay,
'carousel_interval' => $carousel_interval,
'display_defaults'  => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_display_defaults() : array(),
);

if ( $view ) {
$view_layout        = $layout;
$view_size          = $size;
$view_prizes        = $prizes;
$view_display       = $display_options;
$view_context       = $context;
$view_title         = $hide_heading ? '' : $heading_text;
$view_count         = $count;
$view_pages         = $carousel_pages;
$view_card_renderer = $card_renderer;

ob_start();
include $view;

return ob_get_clean();
}

ob_start();
echo '<div class="bhg-prizes-block bhg-prizes-layout-' . esc_attr( $layout ) . ' size-' . esc_attr( $size ) . '">';
if ( ! $hide_heading && '' !== $heading_text ) {
echo '<h4 class="bhg-prizes-title">' . esc_html( $heading_text ) . '</h4>';
}

if ( 'carousel' === $layout ) {
$show_nav = $carousel_pages > 1;
echo '<div class="bhg-prize-carousel" data-count="' . (int) $count . '" data-visible="' . esc_attr( $carousel_visible ) . '" data-pages="' . esc_attr( $carousel_pages ) . '" data-autoplay="' . ( $carousel_autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $carousel_interval ) . '">';
if ( $show_nav ) {
echo '<button type="button" class="bhg-prize-nav bhg-prize-prev" aria-label="' . esc_attr( bhg_t( 'previous', 'Previous' ) ) . '"><span aria-hidden="true">&lsaquo;</span></button>';
}
echo '<div class="bhg-prize-track-wrapper"><div class="bhg-prize-track">';
foreach ( $prizes as $prize ) {
echo $this->render_prize_card( $prize, $size, $display_options, $context );
}
echo '</div></div>';
if ( $show_nav ) {
echo '<button type="button" class="bhg-prize-nav bhg-prize-next" aria-label="' . esc_attr( bhg_t( 'next', 'Next' ) ) . '"><span aria-hidden="true">&rsaquo;</span></button>';
echo '<div class="bhg-prize-dots" role="tablist">';
for ( $i = 0; $i < $carousel_pages; $i++ ) {
$active = 0 === $i ? ' active' : '';
echo '<button type="button" class="bhg-prize-dot' . esc_attr( $active ) . '" data-index="' . esc_attr( $i ) . '" aria-label="' . esc_attr( sprintf( bhg_t( 'prize_slide_label', 'Go to prize %d' ), $i + 1 ) ) . '"></button>';
}
echo '</div>';
}
echo '</div>';
} else {
echo '<div class="bhg-prizes-grid">';
foreach ( $prizes as $prize ) {
echo $this->render_prize_card( $prize, $size, $display_options, $context );
}
echo '</div>';
}

echo '</div>';

return ob_get_clean();
}

	/**
	* Retrieve bonus hunts the current user has participated in.
	*
	* @param int $user_id User identifier.
	* @param int $limit   Maximum number of hunts to return.
	* @return array[]
	*/
	private function get_user_bonus_hunt_rows( $user_id, $limit = 50 ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$hunts_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
		$guesses_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
		$winners_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );

		if ( ! $hunts_tbl || ! $guesses_tbl ) {
		return array();
	}

		$limit = (int) $limit;
		if ( $limit <= 0 ) {
		$limit = 50;
	} elseif ( $limit > 200 ) {
		$limit = 200;
	}

		$order_expression = "COALESCE(h.closed_at, h.updated_at, h.created_at, g.created_at)";
		$select_columns   = "h.id, h.title, h.status, h.final_balance, h.closed_at, h.winners_count, g.guess, g.created_at AS guess_date";

		if ( $winners_tbl ) {
		$select_columns .= ', w.position, w.diff';
	} else {
		$select_columns .= ', NULL AS position, NULL AS diff';
	}

		$sql  = "SELECT {$select_columns} FROM {$hunts_tbl} h INNER JOIN {$guesses_tbl} g ON g.hunt_id = h.id AND g.user_id = %d";
		$args = array( $user_id );

				if ( $winners_tbl ) {
						$sql  .= " LEFT JOIN {$winners_tbl} w ON w.hunt_id = h.id AND w.user_id = %d AND w.eligible = 1";
						$args[] = $user_id;
				}

		$sql   .= ' WHERE g.user_id = %d ORDER BY ' . $order_expression . ' DESC, h.id DESC LIMIT %d';
		$args[] = $user_id;
		$args[] = $limit;

		$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $args ) );
		$rows     = $wpdb->get_results( $prepared );

		if ( empty( $rows ) ) {
		return array();
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$hunt_id       = isset( $row->id ) ? (int) $row->id : 0;
		$guess         = isset( $row->guess ) ? (float) $row->guess : 0.0;
		$final_balance = null;

		if ( isset( $row->final_balance ) && '' !== $row->final_balance && null !== $row->final_balance ) {
		$final_balance = (float) $row->final_balance;
	}

		$difference = null;
		if ( null !== $final_balance ) {
		$difference = abs( $final_balance - $guess );
	}

		$winners_limit = isset( $row->winners_count ) ? (int) $row->winners_count : 0;
		if ( $winners_limit <= 0 ) {
		$winners_limit = 1;
	}

		$placement  = isset( $row->position ) ? (int) $row->position : 0;
		$is_winner  = ( $placement > 0 && $placement <= $winners_limit );
		$guess_date = isset( $row->guess_date ) ? (string) $row->guess_date : '';

		$formatted[] = array(
		'hunt_id'       => $hunt_id,
		'title'         => isset( $row->title ) ? (string) $row->title : '',
		'status'        => isset( $row->status ) ? (string) $row->status : '',
		'guess'         => $guess,
		'final_balance' => $final_balance,
		'difference'    => $difference,
		'position'      => $placement,
		'winners_count' => $winners_limit,
		'is_winner'     => $is_winner,
		'closed_at'     => isset( $row->closed_at ) ? (string) $row->closed_at : '',
		'guess_date'    => $guess_date,
		);
	}

		return $formatted;
	}

	/**
	* Retrieve tournament standings for a user.
	*
	* @param int $user_id User identifier.
	* @return array[]
	*/
	private function get_user_tournament_rows( $user_id ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$results_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
		$tours_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );

		if ( ! $results_tbl || ! $tours_tbl ) {
		return array();
	}

		$sql = "SELECT t.id, t.title, t.status, t.start_date, t.end_date, r.points, r.wins, r.last_win_date\n\t\t\tFROM {$results_tbl} r\n\t\t\tINNER JOIN {$tours_tbl} t ON t.id = r.tournament_id\n\t\t\tWHERE r.user_id = %d\n\t\t\tORDER BY r.points DESC, r.wins DESC, r.last_win_date ASC, t.title ASC";
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

		if ( empty( $rows ) ) {
		return array();
	}

		$tournament_ids = array();
		foreach ( $rows as $row ) {
		$tid = isset( $row->id ) ? (int) $row->id : 0;
		if ( $tid > 0 ) {
		$tournament_ids[ $tid ] = $tid;
	}
	}

		$ranking_map = array();

		if ( ! empty( $tournament_ids ) ) {
		$placeholders = implode( ', ', array_fill( 0, count( $tournament_ids ), '%d' ) );
		$args         = array_values( $tournament_ids );
		$sql_ranks    = "SELECT tournament_id, user_id, points, wins, last_win_date\n\t\t\tFROM {$results_tbl}\n\t\t\tWHERE tournament_id IN ({$placeholders})\n\t\t\tORDER BY tournament_id ASC, points DESC, wins DESC, last_win_date ASC, user_id ASC";
		$prepared     = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql_ranks ), $args ) );
		$rank_rows    = $wpdb->get_results( $prepared );

		if ( $rank_rows ) {
		$current_tournament = null;
		$position_counter   = 0;
		$current_rank       = 0;
		$last_points        = null;
		$last_wins          = null;

		foreach ( $rank_rows as $rank_row ) {
		$tid = isset( $rank_row->tournament_id ) ? (int) $rank_row->tournament_id : 0;
		$uid = isset( $rank_row->user_id ) ? (int) $rank_row->user_id : 0;

		if ( $tid <= 0 || $uid <= 0 ) {
		continue;
	}

		if ( $tid !== $current_tournament ) {
		$current_tournament = $tid;
		$position_counter   = 0;
		$current_rank       = 0;
		$last_points        = null;
		$last_wins          = null;
	}

		++$position_counter;

		$points = isset( $rank_row->points ) ? (int) $rank_row->points : 0;
		$wins   = isset( $rank_row->wins ) ? (int) $rank_row->wins : 0;

		if ( null === $last_points || $points !== $last_points || $wins !== $last_wins ) {
		$current_rank = $position_counter;
		$last_points  = $points;
		$last_wins    = $wins;
	}

		if ( ! isset( $ranking_map[ $tid ] ) ) {
		$ranking_map[ $tid ] = array();
	}

		$ranking_map[ $tid ][ $uid ] = $current_rank;
	}
	}
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$tid = isset( $row->id ) ? (int) $row->id : 0;
		if ( $tid <= 0 ) {
		continue;
	}

		$rank = null;
		if ( isset( $ranking_map[ $tid ][ $user_id ] ) ) {
		$rank = (int) $ranking_map[ $tid ][ $user_id ];
	}

		$formatted[] = array(
		'tournament_id' => $tid,
		'title'         => isset( $row->title ) ? (string) $row->title : '',
		'status'        => isset( $row->status ) ? (string) $row->status : '',
		'points'        => isset( $row->points ) ? (int) $row->points : 0,
		'wins'          => isset( $row->wins ) ? (int) $row->wins : 0,
		'last_win_date' => isset( $row->last_win_date ) ? (string) $row->last_win_date : '',
		'rank'          => $rank,
		);
	}

		return $formatted;
	}

	/**
	* Retrieve prizes associated with hunts the user has won.
	*
	* @param int $user_id User identifier.
	* @return array[]
	*/
	private function get_user_prize_rows( $user_id ) {
		global $wpdb;

		$user_id = (int) $user_id;

		if ( $user_id <= 0 ) {
		return array();
	}

		$winners_tbl  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
		$hunts_tbl    = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
		$relation_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_prizes' ) );
		$prizes_tbl   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_prizes' ) );

		if ( ! $winners_tbl || ! $hunts_tbl || ! $relation_tbl || ! $prizes_tbl ) {
		return array();
	}

				$sql = "SELECT DISTINCT p.id AS prize_id, p.title AS prize_title, p.category, h.title AS hunt_title, h.closed_at, w.position\n\t\t\tFROM {$winners_tbl} w\n\t\t\tINNER JOIN {$hunts_tbl} h ON h.id = w.hunt_id\n\t\t\tINNER JOIN {$relation_tbl} hp ON hp.hunt_id = w.hunt_id\n\t\t\tINNER JOIN {$prizes_tbl} p ON p.id = hp.prize_id\n\t\t\tWHERE w.user_id = %d AND w.eligible = 1\n\t\t\tORDER BY h.closed_at DESC, w.position ASC, p.title ASC";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

		if ( empty( $rows ) ) {
		return array();
	}

		$formatted = array();

		foreach ( $rows as $row ) {
		$formatted[] = array(
		'prize_id'   => isset( $row->prize_id ) ? (int) $row->prize_id : 0,
		'title'      => isset( $row->prize_title ) ? (string) $row->prize_title : '',
		'category'   => isset( $row->category ) ? (string) $row->category : '',
		'hunt_title' => isset( $row->hunt_title ) ? (string) $row->hunt_title : '',
		'closed_at'  => isset( $row->closed_at ) ? (string) $row->closed_at : '',
		'position'   => isset( $row->position ) ? (int) $row->position : 0,
		);
	}

		return $formatted;
	}

/**
 * Render a single prize card.
 *
 * @param object $prize   Prize row.
 * @param string $size    Image size keyword.
 * @param array  $display Display overrides.
 * @param array  $context Additional context.
 * @return string
 */
private function render_prize_card( $prize, $size, $display = array(), $context = array() ) {
if ( ! class_exists( 'BHG_Prizes' ) ) {
return '';
}

$display  = is_array( $display ) ? $display : array();
$context  = is_array( $context ) ? $context : array();
$layout   = isset( $context['layout'] ) ? $context['layout'] : 'grid';
$defaults = array();

if ( isset( $context['display_defaults'] ) && is_array( $context['display_defaults'] ) ) {
$defaults = $context['display_defaults'];
}

$style_attr = BHG_Prizes::build_style_attr( $prize );
$image_url  = BHG_Prizes::get_image_url( $prize, $size );
$image_id   = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_image_id_for_size( $prize, $size ) : 0;
$category   = isset( $prize->category ) ? (string) $prize->category : '';
$wp_size    = 'medium';

if ( 'small' === $size ) {
$wp_size = 'thumbnail';
} elseif ( 'big' === $size ) {
$wp_size = 'large';
}

$show_title       = BHG_Prizes::resolve_display_flag(
isset( $display['show_title'] ) ? $display['show_title'] : null,
isset( $prize->show_title ) ? $prize->show_title : 1,
array_key_exists( 'show_title', $defaults ) ? $defaults['show_title'] : null
);
$show_description = BHG_Prizes::resolve_display_flag(
isset( $display['show_description'] ) ? $display['show_description'] : null,
isset( $prize->show_description ) ? $prize->show_description : 1,
array_key_exists( 'show_description', $defaults ) ? $defaults['show_description'] : null
);
$show_category    = BHG_Prizes::resolve_display_flag(
isset( $display['show_category'] ) ? $display['show_category'] : null,
isset( $prize->show_category ) ? $prize->show_category : 1,
array_key_exists( 'show_category', $defaults ) ? $defaults['show_category'] : null
);
$show_image       = BHG_Prizes::resolve_display_flag(
isset( $display['show_image'] ) ? $display['show_image'] : null,
isset( $prize->show_image ) ? $prize->show_image : 1,
array_key_exists( 'show_image', $defaults ) ? $defaults['show_image'] : null
);

$action_override = isset( $display['click_action'] ) ? $display['click_action'] : 'inherit';
$prize_action    = isset( $prize->click_action ) ? $prize->click_action : 'link';
$default_click   = isset( $defaults['click_action'] ) ? $defaults['click_action'] : 'inherit';
$click_action    = BHG_Prizes::resolve_click_action( $action_override, $prize_action, $default_click );

$link_href = '';
$target    = '_self';
$rel_attr  = '';
if ( in_array( $click_action, array( 'link', 'new' ), true ) ) {
$link_href = BHG_Prizes::get_prize_link( $prize );
if ( '' === $link_href ) {
$click_action = 'none';
} else {
        if ( 'new' === $click_action ) {
            $target   = '_blank';
            $rel_attr = ' rel="noopener noreferrer"';
        } else {
            $default_link_target = isset( $defaults['link_target'] ) ? $defaults['link_target'] : 'inherit';
            $target              = BHG_Prizes::resolve_link_target(
                isset( $display['link_target'] ) ? $display['link_target'] : 'inherit',
                isset( $prize->link_target ) ? $prize->link_target : '_self',
                $default_link_target
            );
            $rel_attr = '_blank' === $target ? ' rel="noopener noreferrer"' : '';
        }
}
} elseif ( 'image' === $click_action ) {
$link_href = BHG_Prizes::get_full_image_url( $prize );
if ( '' === $link_href ) {
$click_action = 'none';
}
}

if ( 'image' === $click_action && ! $show_image ) {
$click_action = 'none';
}

$classes = array( 'bhg-prize-card' );
if ( $style_attr ) {
$classes[] = 'bhg-prize-card--custom';
}
if ( ! empty( $prize->css_border ) || ! empty( $prize->css_border_color ) ) {
$classes[] = 'bhg-prize-card--has-border';
}
if ( ! empty( $prize->css_background ) ) {
$classes[] = 'bhg-prize-card--has-background';
}
if ( $show_image && $image_url ) {
$classes[] = 'bhg-prize-card--has-image';
}
if ( 'none' !== $click_action && '' !== $link_href ) {
$classes[] = 'bhg-prize-card--linked';
if ( 'image' === $click_action ) {
$classes[] = 'bhg-prize-card--popup';
}
}

$classes    = array_map( 'sanitize_html_class', $classes );
$class_attr = trim( implode( ' ', array_filter( $classes ) ) );

$tag        = 'div';
$attributes = '';
if ( 'none' !== $click_action && '' !== $link_href ) {
$tag        = 'a';
$attributes = ' href="' . esc_url( $link_href ) . '"';
if ( '_blank' === $target ) {
$attributes .= ' target="_blank"' . $rel_attr;
} elseif ( '_self' !== $target ) {
$attributes .= ' target="' . esc_attr( $target ) . '"';
}
if ( 'image' === $click_action ) {
$attributes .= ' data-bhg-prize-popup="image" data-bhg-prize-alt="' . esc_attr( $prize->title ) . '"';
}
}

ob_start();
echo '<' . $tag . ' class="' . esc_attr( $class_attr ) . '"' . $style_attr . $attributes . '>';
if ( $show_image && $image_url ) {
$img_attr = '';
$srcset   = $image_id ? wp_get_attachment_image_srcset( $image_id, $wp_size ) : '';
$sizes    = $image_id ? wp_get_attachment_image_sizes( $image_id, $wp_size ) : '';
if ( $srcset ) {
$img_attr .= ' srcset="' . esc_attr( $srcset ) . '"';
}
if ( $sizes ) {
$img_attr .= ' sizes="' . esc_attr( $sizes ) . '"';
} else {
$img_attr .= ' sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"';
}
echo '<div class="bhg-prize-image"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $prize->title ) . '"' . $img_attr . '></div>';
}
echo '<div class="bhg-prize-body">';
if ( $show_title ) {
echo '<h5 class="bhg-prize-title">' . esc_html( $prize->title ) . '</h5>';
}
if ( $show_category && $category ) {
$category_label = esc_html( ucwords( str_replace( '_', ' ', $category ) ) );
$category_link  = '';
if ( BHG_Prizes::resolve_display_flag(
isset( $display['category_links'] ) ? $display['category_links'] : null,
! empty( $prize->category_link_url ),
array_key_exists( 'category_links', $defaults ) ? $defaults['category_links'] : null
) ) {
$category_link = BHG_Prizes::get_category_link( $prize );
}
if ( $category_link ) {
$category_target = BHG_Prizes::resolve_link_target(
isset( $display['category_target'] ) ? $display['category_target'] : 'inherit',
isset( $prize->category_link_target ) ? $prize->category_link_target : '_self',
isset( $defaults['category_target'] ) ? $defaults['category_target'] : 'inherit'
);
$rel = '_blank' === $category_target ? ' rel="noopener noreferrer"' : '';
$category_label = '<a href="' . esc_url( $category_link ) . '" target="' . esc_attr( $category_target ) . '"' . $rel . '>' . $category_label . '</a>';
}
echo '<div class="bhg-prize-category">' . $category_label . '</div>';
}
if ( $show_description && ! empty( $prize->description ) ) {
$description = wp_kses_post( wpautop( $prize->description ) );
echo '<div class="bhg-prize-description">' . $description . '</div>';
}
echo '</div>';
echo '</' . $tag . '>';

return ob_get_clean();
}


										/**
										 * Minimal login hint used by some themes.
										 *
										 * @param array $atts Shortcode attributes. Unused.
										 * @return string HTML output.
										 */
		public function login_hint_shortcode( $atts = array() ) {
				unset( $atts ); // Parameter unused but kept for shortcode signature.

				if ( is_user_logged_in() ) {
								return '';
			}
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

			$login_url = function_exists( 'bhg_get_login_url' ) ? bhg_get_login_url( $redirect ) : wp_login_url( $redirect );

			return '<p>' . esc_html( bhg_t( 'notice_login_to_continue', 'Please log in to continue.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( $login_url ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
		}

			/**
			 * Renders list of open hunts.
			 *
			 * @param array $atts Shortcode attributes.
			 * @return string HTML output.
			 */
			   public function active_hunt_shortcode( $atts ) {
						$atts = shortcode_atts(
								array(
										'prize_layout' => 'grid',
										'prize_size'   => 'medium',
								),
								$atts,
								'bhg_active_hunt'
						);

						$prize_layout = $this->normalize_prize_layout( isset( $atts['prize_layout'] ) ? $atts['prize_layout'] : 'grid' );
						$prize_size   = $this->normalize_prize_size( isset( $atts['prize_size'] ) ? $atts['prize_size'] : 'medium' );

				   global $wpdb;
				   $hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			   if ( ! $hunts_table ) {
					   return '';
			   }

				   $cache_key = 'bhg_active_hunts';
				   $hunts     = wp_cache_get( $cache_key, 'bhg' );
					   if ( false === $hunts ) {
							   $aff_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );

							   if ( $aff_table ) {
									   $sql = "SELECT h.*, aff.name AS affiliate_site_name FROM {$hunts_table} h LEFT JOIN {$aff_table} aff ON aff.id = h.affiliate_site_id WHERE h.status = %s ORDER BY h.created_at DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names sanitized above.
							   } else {
									   $sql = "SELECT * FROM {$hunts_table} WHERE status = %s ORDER BY created_at DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above.
							   }

					   $hunts = $wpdb->get_results(
							   $wpdb->prepare(
									   $sql,
									   'open'
							   )
					   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					   wp_cache_set( $cache_key, $hunts, 'bhg', 300 );
					   }

					   if ( empty( $hunts ) ) {
					   return '<div class="bhg-active-hunt"><p>' . esc_html( bhg_t( 'notice_no_active_hunts', 'No active bonus hunts at the moment.' ) ) . '</p></div>';
					   }

					   if ( ! class_exists( 'BHG_Bonus_Hunts' ) && defined( 'BHG_PLUGIN_DIR' ) ) {
							   $hunts_class = BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts.php';
							   if ( file_exists( $hunts_class ) ) {
									   require_once $hunts_class;
							   }
					   }

					   $hunts_map = array();
					   foreach ( $hunts as $hunt ) {
							   $hunts_map[ (int) $hunt->id ] = $hunt;
					   }

			   $selected_hunt_id = 0;
			   if ( isset( $_GET['bhg_hunt'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Viewing data.
				   $selected_hunt_id = absint( wp_unslash( $_GET['bhg_hunt'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			   }

			   if ( $selected_hunt_id <= 0 || ! isset( $hunts_map[ $selected_hunt_id ] ) ) {
				   $first_hunt       = reset( $hunts );
				   $selected_hunt_id = $first_hunt ? (int) $first_hunt->id : 0;
			   }

			   if ( $selected_hunt_id <= 0 ) {
				   return '';
			   }

					   $selected_hunt = $hunts_map[ $selected_hunt_id ];

					   $prize_sets = array(
							   'regular' => array(),
							   'premium' => array(),
					   );
					   if ( class_exists( 'BHG_Prizes' ) ) {
							   $prize_sets = BHG_Prizes::get_prizes_for_hunt(
									   $selected_hunt_id,
									   array(
											   'active_only' => true,
											   'grouped'     => true,
									   )
							   );
					   }

					   $per_page = (int) apply_filters( 'bhg_active_hunt_per_page', 30 );
					   if ( $per_page <= 0 ) {
							   $per_page = 30;
					   }

			   $current_page = 1;
			   if ( isset( $_GET['bhg_hunt_page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Viewing data.
				   $current_page = max( 1, absint( wp_unslash( $_GET['bhg_hunt_page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			   }

				   $guesses_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
				   $users_table   = esc_sql( $this->sanitize_table( $wpdb->users ) );
			   if ( ! $guesses_table || ! $users_table ) {
					   return '';
			   }

			   $offset        = ( $current_page - 1 ) * $per_page;
			   $final_balance = isset( $selected_hunt->final_balance ) ? $selected_hunt->final_balance : null;
			   $final_balance = '' === $final_balance ? null : $final_balance;
			   $has_final     = null !== $final_balance;

			   if ( $has_final ) {
							   $sql = sprintf(
									   'SELECT g.id, g.user_id, g.guess, g.created_at, u.display_name, u.user_login, (%%f - g.guess) AS diff FROM %1$s g LEFT JOIN %2$s u ON u.ID = g.user_id WHERE g.hunt_id = %%d ORDER BY ABS(%%f - g.guess) ASC, g.id ASC LIMIT %%d OFFSET %%d',
									   $guesses_table,
									   $users_table
							   );
				   $rows = $wpdb->get_results(
					   $wpdb->prepare(
						   $sql,
						   (float) $final_balance,
						   $selected_hunt_id,
						   $per_page,
						   $offset
					   )
				   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			   } else {
							   $sql = sprintf(
									   'SELECT g.id, g.user_id, g.guess, g.created_at, u.display_name, u.user_login, NULL AS diff FROM %1$s g LEFT JOIN %2$s u ON u.ID = g.user_id WHERE g.hunt_id = %%d ORDER BY g.created_at ASC, g.id ASC LIMIT %%d OFFSET %%d',
									   $guesses_table,
									   $users_table
							   );
				   $rows = $wpdb->get_results(
					   $wpdb->prepare(
						   $sql,
						   $selected_hunt_id,
						   $per_page,
						   $offset
					   )
				   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			   }

			   $total_guesses = (int) $wpdb->get_var(
				   $wpdb->prepare(
					   "SELECT COUNT(*) FROM {$guesses_table} WHERE hunt_id = %d",
					   $selected_hunt_id
				   )
			   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

					   $total_pages = $total_guesses > 0 ? (int) ceil( $total_guesses / $per_page ) : 1;

					   $win_limit_notice = '';
					   if ( $has_final && method_exists( 'BHG_Bonus_Hunts', 'get_ineligible_winner_ids' ) && function_exists( 'bhg_get_win_limit_config' ) && function_exists( 'bhg_build_win_limit_notice' ) ) {
							   $skipped_ids = BHG_Bonus_Hunts::get_ineligible_winner_ids( $selected_hunt_id );
							   if ( ! empty( $skipped_ids ) ) {
									   $limit_config = bhg_get_win_limit_config( 'hunt' );
									   $limit_count  = isset( $limit_config['count'] ) ? (int) $limit_config['count'] : 0;
									   $limit_period = isset( $limit_config['period'] ) ? $limit_config['period'] : 'none';
									   $notice_text  = bhg_build_win_limit_notice( 'hunt', $limit_count, $limit_period, count( $skipped_ids ) );
									   if ( '' !== $notice_text ) {
											   $win_limit_notice = $notice_text;
									   }
							   }
					   }

				   wp_enqueue_style(
					   'bhg-shortcodes',
					   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
					   array(),
					   defined( 'BHG_VERSION' ) ? BHG_VERSION : null
				   );
			   wp_enqueue_script(
				   'bhg-shortcodes-js',
				   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/js/bhg-shortcodes.js',
				   array(),
				   defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
				   true
			   );

			   $hunt_site_id = isset( $selected_hunt->affiliate_site_id ) ? (int) $selected_hunt->affiliate_site_id : 0;

			   ob_start();
			   echo '<div class="bhg-active-hunt">';

			   if ( count( $hunts ) > 1 ) {
				   echo '<form class="bhg-hunt-selector" method="get">';
				   if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserving query vars.
					   foreach ( wp_unslash( $_GET ) as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						   if ( in_array( $key, array( 'bhg_hunt', 'bhg_hunt_page' ), true ) ) {
							   continue;
						   }
						   if ( is_array( $value ) ) {
							   continue;
						   }
						   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '">';
				}
		}

				   echo '<label for="bhg-hunt-select">' . esc_html( bhg_t( 'label_choose_hunt', 'Choose a hunt:' ) ) . '</label>';
				   echo '<select id="bhg-hunt-select" class="bhg-hunt-select" name="bhg_hunt">';
				   foreach ( $hunts as $hunt ) {
					   $hunt_id = (int) $hunt->id;
					   echo '<option value="' . esc_attr( $hunt_id ) . '"' . selected( $hunt_id, $selected_hunt_id, false ) . '>' . esc_html( $hunt->title ) . '</option>';
				   }
				   echo '</select>';
				   echo '<noscript><button type="submit" class="button button-primary">' . esc_html( bhg_t( 'button_apply', 'Apply' ) ) . '</button></noscript>';
				   echo '</form>';
			   }

			   echo '<div class="bhg-hunt-card">';
			   echo '<h3>' . esc_html( $selected_hunt->title ) . '</h3>';
					   echo '<ul class="bhg-hunt-meta">';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ) . ':</strong> ' . esc_html( bhg_format_money( (float) $selected_hunt->starting_balance ) ) . '</li>';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ) . ':</strong> ' . (int) $selected_hunt->num_bonuses . '</li>';

					   $opened_at = isset( $selected_hunt->created_at ) ? (string) $selected_hunt->created_at : '';
					   if ( '' !== $opened_at && '0000-00-00 00:00:00' !== $opened_at ) {
							   $opened_label = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $opened_at, true );
							   echo '<li><strong>' . esc_html( bhg_t( 'label_opened', 'Opened' ) ) . ':</strong> ' . esc_html( $opened_label ) . '</li>';
					   }

					   $closed_at      = isset( $selected_hunt->closed_at ) ? (string) $selected_hunt->closed_at : '';
					   $closed_display = '-';
					   if ( '' !== $closed_at && '0000-00-00 00:00:00' !== $closed_at ) {
							   $closed_display = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $closed_at, true );
					   }
					   echo '<li><strong>' . esc_html( bhg_t( 'label_closed', 'Closed' ) ) . ':</strong> ' . esc_html( $closed_display ) . '</li>';

					   $affiliate_name = isset( $selected_hunt->affiliate_site_name ) ? $selected_hunt->affiliate_site_name : '';
					   if ( '' === $affiliate_name && isset( $selected_hunt->affiliate_site_id ) && (int) $selected_hunt->affiliate_site_id > 0 ) {
							   $sites_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
							   if ( $sites_table ) {
									   $affiliate_name = $wpdb->get_var(
											   $wpdb->prepare(
													   "SELECT name FROM {$sites_table} WHERE id = %d",
													   (int) $selected_hunt->affiliate_site_id
											   )
									   ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							   }
					   }
					   $affiliate_display = '' !== $affiliate_name ? $affiliate_name : '-';
					   echo '<li><strong>' . esc_html( bhg_t( 'label_affiliate_site', 'Affiliate Site' ) ) . ':</strong> ' . esc_html( $affiliate_display ) . '</li>';
					   if ( ! empty( $selected_hunt->prizes ) ) {
							   echo '<li><strong>' . esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ) . ':</strong> ' . wp_kses_post( $selected_hunt->prizes ) . '</li>';
					   }
					   if ( $has_final ) {
							   echo '<li><strong>' . esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ) . ':</strong> ' . esc_html( bhg_format_money( (float) $final_balance ) ) . '</li>';
					   }
					   echo '</ul>';

					   if ( '' !== $win_limit_notice ) {
							   echo '<div class="bhg-win-limit-notice">' . esc_html( $win_limit_notice ) . '</div>';
					   }

					   $regular_prizes = isset( $prize_sets['regular'] ) && is_array( $prize_sets['regular'] ) ? $prize_sets['regular'] : array();
					   $premium_prizes = isset( $prize_sets['premium'] ) && is_array( $prize_sets['premium'] ) ? $prize_sets['premium'] : array();
					   $show_premium   = false;

					   if ( ! empty( $premium_prizes ) && is_user_logged_in() ) {
							   if ( $hunt_site_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
									   $show_premium = bhg_is_user_affiliate_for_site( get_current_user_id(), $hunt_site_id );
							   } elseif ( function_exists( 'bhg_is_user_affiliate' ) ) {
									   $show_premium = bhg_is_user_affiliate( get_current_user_id() );
							   } else {
									   $show_premium = (bool) get_user_meta( get_current_user_id(), 'bhg_is_affiliate', true );
							   }
					   }

					   if ( $show_premium && ! empty( $premium_prizes ) ) {
							   echo '<div class="bhg-hunt-prizes bhg-hunt-prizes-premium">';
							   echo '<h4 class="bhg-prize-heading bhg-prize-heading-premium">' . esc_html( bhg_t( 'premium_prizes_heading', 'Premium Prizes' ) ) . '</h4>';
							   echo $this->render_prize_section( $premium_prizes, $prize_layout, $prize_size );
							   if ( ! empty( $regular_prizes ) ) {
									   echo '<h4 class="bhg-prize-heading bhg-prize-heading-regular">' . esc_html( bhg_t( 'regular_prizes_heading', 'Regular Prizes' ) ) . '</h4>';
									   echo $this->render_prize_section( $regular_prizes, $prize_layout, $prize_size );
							   }
							   echo '</div>';
					   } elseif ( ! empty( $regular_prizes ) ) {
							   echo '<div class="bhg-hunt-prizes">';
							   echo $this->render_prize_section( $regular_prizes, $prize_layout, $prize_size );
							   echo '</div>';
					   }
					   echo '</div>';

			   echo '<div class="bhg-table-wrapper">';
			   if ( empty( $rows ) ) {
				   echo '<p class="bhg-no-guesses">' . esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses have been submitted for this hunt yet.' ) ) . '</p>';
			   } else {
				   echo '<table class="bhg-leaderboard bhg-active-hunt-table">';
				   echo '<thead><tr>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</th>';
				   echo '<th scope="col">' . esc_html( bhg_t( 'label_guess', 'Guess' ) ) . '</th>';
				   if ( $has_final ) {
					   echo '<th scope="col">' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
				   }
				   echo '</tr></thead><tbody>';
				   foreach ( $rows as $index => $row ) {
					   $position   = $offset + $index + 1;
					   $user_login = ! empty( $row->display_name ) ? $row->display_name : $row->user_login;
					   $user_label = $user_login ? $user_login : bhg_t( 'label_unknown_user', 'Unknown user' );
					   $aff_dot    = bhg_render_affiliate_dot( (int) $row->user_id, $hunt_site_id );

					   echo '<tr>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_position', 'Position' ) ) . '">' . (int) $position . '</td>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_username', 'Username' ) ) . '">' . esc_html( $user_label ) . ' ' . wp_kses_post( $aff_dot ) . '</td>';
					   echo '<td data-label="' . esc_attr( bhg_t( 'label_guess', 'Guess' ) ) . '">' . esc_html( bhg_format_money( (float) $row->guess ) ) . '</td>';
					   if ( $has_final ) {
						   $diff = isset( $row->diff ) ? (float) $row->diff : 0.0;
						   echo '<td data-label="' . esc_attr( bhg_t( 'label_difference', 'Difference' ) ) . '">' . esc_html( bhg_format_money( $diff ) ) . '</td>';
					   }
					   echo '</tr>';
				   }
				   echo '</tbody></table>';
			   }
			   echo '</div>';

					   if ( $total_pages > 1 ) {
							   $pagination_links = paginate_links(
									   array(
											   'base'      => esc_url_raw( add_query_arg( array( 'bhg_hunt_page' => '%#%', 'bhg_hunt' => $selected_hunt_id ) ) ),
											   'format'    => '',
						   'current'   => $current_page,
						   'total'     => $total_pages,
						   'type'      => 'array',
						   'prev_text' => esc_html__( '&laquo;', 'bonus-hunt-guesser' ),
						   'next_text' => esc_html__( '&raquo;', 'bonus-hunt-guesser' ),
					   )
				   );

				   if ( ! empty( $pagination_links ) ) {
					   echo '<nav class="bhg-pagination" aria-label="' . esc_attr( bhg_t( 'label_pagination', 'Pagination' ) ) . '">';
					   echo '<ul class="bhg-pagination-list">';
					   foreach ( $pagination_links as $link ) {
						   $class = false !== strpos( $link, 'current' ) ? ' class="bhg-current-page"' : '';
						   echo '<li' . $class . '>' . wp_kses_post( $link ) . '</li>';
					   }
					   echo '</ul>';
					   echo '</nav>';
				   }
			   }

			   echo '</div>';

			   return ob_get_clean();
		   }

					/**
					 * Renders the guess submission form.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
		public function guess_form_shortcode( $atts ) {
				$atts    = shortcode_atts( array( 'hunt_id' => 0 ), $atts, 'bhg_guess_form' );
				$hunt_id = (int) $atts['hunt_id'];

			if ( ! is_user_logged_in() ) {
				$raw      = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base     = wp_validate_redirect( $raw, home_url( '/' ) );
				$redirect = esc_url_raw( add_query_arg( array(), $base ) );

			$login_url = function_exists( 'bhg_get_login_url' ) ? bhg_get_login_url( $redirect ) : wp_login_url( $redirect );

			return '<p>' . esc_html( bhg_t( 'notice_login_to_guess', 'Please log in to submit your guess.' ) ) . '</p>'
				. '<p><a class="button button-primary" href="' . esc_url( $login_url ) . '">' . esc_html( bhg_t( 'button_log_in', 'Log in' ) ) . '</a></p>';
			}

						global $wpdb;
												$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_table ) {
					return '';
			}

						$cache_key  = 'bhg_open_hunts';
						$open_hunts = wp_cache_get( $cache_key, 'bhg' );
			if ( false === $open_hunts ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
								$open_hunts = $wpdb->get_results(
									$wpdb->prepare(
											   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
										"SELECT id, title FROM {$hunts_table} WHERE status = %s AND guessing_enabled = %d ORDER BY created_at DESC",
										'open',
										1
									)
								); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					wp_cache_set( $cache_key, $open_hunts, 'bhg', 300 );
			}

			if ( $hunt_id <= 0 ) {
				if ( ! $open_hunts ) {
					return '<p>' . esc_html( bhg_t( 'notice_no_open_hunt', 'No open hunt found to guess.' ) ) . '</p>';
				}
				if ( count( $open_hunts ) === 1 ) {
					$hunt_id = (int) $open_hunts[0]->id;
				}
			}

						$user_id = get_current_user_id();
						$table   = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
			if ( ! $table ) {
				return '';
			}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$existing_id = $hunt_id > 0 ? (int) $wpdb->get_var(
					$wpdb->prepare(
							   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT id FROM {$table} WHERE user_id = %d AND hunt_id = %d",
						$user_id,
						$hunt_id
					)
				) : 0; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$existing_guess = $existing_id ? (float) $wpdb->get_var(
					$wpdb->prepare(
							   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT guess FROM {$table} WHERE id = %d",
						$existing_id
					)
				) : ''; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

						$settings        = get_option( 'bhg_plugin_settings' );
						$min             = isset( $settings['min_guess_amount'] ) ? (float) $settings['min_guess_amount'] : 0;
						$max             = isset( $settings['max_guess_amount'] ) ? (float) $settings['max_guess_amount'] : 100000;
						$redirect_target = ! empty( $settings['post_submit_redirect'] ) ? wp_validate_redirect( $settings['post_submit_redirect'], '' ) : '';
						$button_label    = $existing_id ? bhg_t( 'button_edit_guess', 'Edit Guess' ) : bhg_t( 'button_submit_guess', 'Submit Guess' );

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start(); ?>
												<form class="bhg-guess-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
																															   <input type="hidden" name="action" value="bhg_submit_guess">
																												<?php wp_nonce_field( 'bhg_submit_guess', 'bhg_submit_guess_nonce' ); ?>
												<?php if ( $redirect_target ) : ?>
														<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_target ); ?>">
												<?php endif; ?>

					<?php if ( $open_hunts && count( $open_hunts ) > 1 ) : ?>
					<label for="bhg-hunt-select">
						<?php
						echo esc_html( bhg_t( 'label_choose_hunt', 'Choose a hunt:' ) );
						?>
</label>
					<select id="bhg-hunt-select" name="hunt_id" required>
						<option value="">
						<?php
						echo esc_html( bhg_t( 'label_select_hunt', 'Select a hunt' ) );
						?>
</option>
												<?php foreach ( $open_hunts as $oh ) : ?>
														<option value="<?php echo esc_attr( (int) $oh->id ); ?>" <?php selected( $hunt_id, (int) $oh->id ); ?>>
																<?php echo esc_html( $oh->title ); ?>
														</option>
												<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="hidden" name="hunt_id" value="<?php echo esc_attr( $hunt_id ); ?>">
				<?php endif; ?>

				<label for="bhg-guess" class="bhg-guess-label">
				<?php
				echo esc_html( bhg_t( 'label_guess_final_balance', 'Your guess (final balance):' ) );
				?>
</label>
				<input type="number" step="0.01" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
					id="bhg-guess" name="guess" value="<?php echo esc_attr( $existing_guess ); ?>" required>
				<div class="bhg-error-message"></div>
								<button type="submit" class="bhg-submit-btn button button-primary"><?php echo esc_html( $button_label ); ?></button>
			</form>
				<?php
				return ob_get_clean();
		}

					/**
					 * Displays a leaderboard for a hunt.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
		public function leaderboard_shortcode( $atts ) {
				$a = shortcode_atts(
					array(
						'hunt_id'  => 0,
						'orderby'  => 'guess', // guess|user|position.
						'order'    => 'ASC',
											   'fields'   => 'position,user,guess',
											   'paged'    => 1,
											   'per_page' => 30,
											   'search'   => '',
					),
					$atts,
					'bhg_leaderboard'
				);

				global $wpdb;
			$hunt_id = (int) $a['hunt_id'];
			if ( $hunt_id <= 0 ) {
																$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
				if ( ! $hunts_table ) {
										return '';
				}
				$cache_key = 'bhg_latest_hunt_id';
				$hunt_id   = wp_cache_get( $cache_key, 'bhg' );
				if ( false === $hunt_id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
												$hunt_id = (int) $wpdb->get_var(
													$wpdb->prepare(
															   /* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
														"SELECT id FROM {$hunts_table} ORDER BY created_at DESC LIMIT %d",
														1
													)
												); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						wp_cache_set( $cache_key, $hunt_id, 'bhg', 300 );
				}
				if ( $hunt_id <= 0 ) {
						return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
				}
			}

						$g = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
						$u = esc_sql( $this->sanitize_table( $wpdb->users ) );
			if ( ! $g || ! $u ) {
				return '';
			}

								$allowed_orders = array( 'ASC', 'DESC' );
								$order          = strtoupper( sanitize_key( $a['order'] ) );
						if ( ! in_array( $order, $allowed_orders, true ) ) {
								$order = 'ASC';
						}
								$direction_key = strtolower( $order );
								$allowed_orderby = array(
									'guess'    => 'g.guess',
									'user'     => 'u.user_login',
									'position' => 'g.id', // stable proxy.
								);
								$orderby_key     = sanitize_key( $a['orderby'] );
								if ( ! isset( $allowed_orderby[ $orderby_key ] ) ) {
												$orderby_key = 'guess';
								}
								$orderby = $allowed_orderby[ $orderby_key ];

															   $paged    = isset( $_GET['bhg_page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   ? max( 1, absint( wp_unslash( $_GET['bhg_page'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   : (int) $a['paged']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   $paged    = max( 1, $paged );
															   $per_page = max( 1, (int) $a['per_page'] );
															   $offset   = ( $paged - 1 ) * $per_page;

															   $search = isset( $_GET['bhg_search'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
															   : (string) $a['search'];

																$fields_raw    = explode( ',', (string) $a['fields'] );
																$allowed_field = array( 'position', 'user', 'guess' );
																$fields        = array_values( array_intersect( $allowed_field, array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) ) ) );
																if ( empty( $fields ) ) {
																								$fields = $allowed_field;
																}

															   $where  = array( 'g.hunt_id = %d' );
															   $params = array( $hunt_id );
															   if ( '' !== $search ) {
																	   $where[]  = 'u.user_login LIKE %s';
																	   $params[] = '%' . $wpdb->esc_like( $search ) . '%';
															   }
															   $where_sql = implode( ' AND ', $where );

															   $total_cache = 'bhg_leaderboard_total_' . $hunt_id . '_' . md5( $search );
															   $total       = wp_cache_get( $total_cache, 'bhg' );
															   if ( false === $total ) {
																			   // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names sanitized above.
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
																	   $count_sql = "SELECT COUNT(*) FROM {$g} g LEFT JOIN {$u} u ON u.ID = g.user_id WHERE {$where_sql}";
																	   $total     = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
																	   wp_cache_set( $total_cache, $total, 'bhg', 300 );
															   }
															   if ( $total < 1 ) {
																					   return '<p>' . esc_html( bhg_t( 'notice_no_guesses_yet', 'No guesses yet.' ) ) . '</p>';
															   }

																								$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
															   if ( ! $hunts_table ) {
																								return '';
															   }
																								$order_by_clause = sprintf( '%s %s', $orderby, $order );
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
															   $sql  = "SELECT g.user_id, g.guess, u.user_login, h.affiliate_site_id FROM {$g} g LEFT JOIN {$u} u ON u.ID = g.user_id LEFT JOIN {$hunts_table} h ON h.id = g.hunt_id WHERE {$where_sql} ORDER BY {$order_by_clause} LIMIT %d OFFSET %d";
															   $rows = $wpdb->get_results( $wpdb->prepare( $sql, ...array_merge( $params, array( $per_page, $offset ) ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

															   $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
															   $base_url    = remove_query_arg( array( 'bhg_page', 'bhg_orderby', 'bhg_order' ), $current_url );
															   if ( '' === $search ) {
																	   $base_url = remove_query_arg( 'bhg_search', $base_url );
															   }

															   $toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search ) {
																	   $dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
																	   $args = array(
																			   'bhg_orderby' => $field,
																			   'bhg_order'   => $dir,
																	   );
																	   if ( '' !== $search ) {
																			   $args['bhg_search'] = $search;
																	   }
																	   return add_query_arg( $args, $base_url );
															   };

						wp_enqueue_style(
							'bhg-shortcodes',
							( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
							array(),
							defined( 'BHG_VERSION' ) ? BHG_VERSION : null
						);

											   ob_start();
											   echo '<div class="bhg-leaderboard-wrapper">';
											   echo '<form method="get" class="bhg-search-form">';
											   foreach ( $_GET as $raw_key => $v ) {
													   $key = sanitize_key( wp_unslash( $raw_key ) );
													   if ( 'bhg_search' === $key ) {
															   continue;
													   }
													   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
											   }
											   echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
											   echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
											   echo '</form>';
											   echo '<table class="bhg-leaderboard">';
											   echo '<thead><tr>';
					   foreach ( $fields as $field ) {
								if ( 'position' === $field ) {
										echo '<th class="sortable" data-column="position"><a href="' . esc_url( $toggle( 'position' ) ) . '">' . esc_html( bhg_t( 'sc_position', 'Position' ) ) . '</a></th>';
								} elseif ( 'user' === $field ) {
										echo '<th class="sortable" data-column="user"><a href="' . esc_url( $toggle( 'user' ) ) . '">' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</a></th>';
								} elseif ( 'guess' === $field ) {
										echo '<th class="sortable" data-column="guess"><a href="' . esc_url( $toggle( 'guess' ) ) . '">' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</a></th>';
								}
					   }
						echo '</tr></thead><tbody>';

												$pos       = $offset + 1;
												$need_user = in_array( 'user', $fields, true );
			foreach ( $rows as $r ) {
				if ( $need_user ) {
					$site_id                         = isset( $r->affiliate_site_id ) ? (int) $r->affiliate_site_id : 0;
											$aff_dot = bhg_render_affiliate_dot( (int) $r->user_id, $site_id );
											/* translators: %d: user ID. */
											$user_label = $r->user_login ? $r->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $r->user_id );
				}

				echo '<tr>';
				foreach ( $fields as $field ) {
					if ( 'position' === $field ) {
						echo '<td data-column="position">' . (int) $pos . '</td>';
					} elseif ( 'user' === $field ) {
																											echo '<td data-column="user">' . esc_html( $user_label ) . ' ' . wp_kses_post( $aff_dot ) . '</td>';
					} elseif ( 'guess' === $field ) {
						echo '<td data-column="guess">' . esc_html( bhg_format_money( (float) $r->guess ) ) . '</td>';
					}
				}
								echo '</tr>';
																++$pos;
			}
												echo '</tbody></table>';

																								$pages = (int) ceil( $total / $per_page );
						if ( $pages > 1 ) {
										$pagination = paginate_links(
												array(
														'base'     => add_query_arg( 'bhg_page', '%#%', $base_url ),
														'format'   => '',
														'current'  => $paged,
														'total'    => $pages,
														'add_args' => array_filter(
																array(
																		'bhg_search'  => $search,
																		'bhg_orderby' => $orderby_key,
																		'bhg_order'   => $direction_key,
																)
														),
												)
										);
										if ( $pagination ) {
												echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
										}
						}
											   echo '</div>';

												return ob_get_clean();
		}

					/**
					 * Renders a table of guesses for a user.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
				  // phpcs:disable
				  public function user_guesses_shortcode( $atts ) {
		$a = shortcode_atts(
		  array(
				  'id'       => 0,
				  'aff'      => '',
				  'website'  => 0,
				  'status'   => '',
				  'timeline' => '',
		  'fields'   => 'hunt,user,guess,final',
				  'orderby'  => 'hunt',
				  'order'    => 'DESC',
				  'paged'    => 1,
				  'search'   => '',
		  ),
		  $atts,
		  'bhg_user_guesses'
		);

		$fields_raw    = explode( ',', (string) $a['fields'] );
		$allowed_field = array( 'hunt', 'guess', 'final', 'user', 'site' );
		$fields_arr    = array_values(
				array_unique(
						array_intersect(
								$allowed_field,
								array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
						)
				)
		);
		if ( empty( $fields_arr ) ) {
				$fields_arr = array( 'hunt', 'user', 'guess', 'final' );
		}

		$need_site  = in_array( 'site', $fields_arr, true );
		$need_users = in_array( 'user', $fields_arr, true );

		$paged               = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
		$search              = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
		$limit               = 30;
		$offset              = ( $paged - 1 ) * $limit;
		$has_orderby_query   = isset( $_GET['bhg_orderby'] );
		$orderby_request     = $has_orderby_query ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
		$has_order_query     = isset( $_GET['bhg_order'] );
		$order_request       = $has_order_query ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );
		$has_order_attribute = array_key_exists( 'order', $atts );

						global $wpdb;

						$g  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_guesses' ) );
						$h  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
						$w  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
						$um = esc_sql( $this->sanitize_table( $wpdb->usermeta ) );
						$u  = esc_sql( $this->sanitize_table( $wpdb->users ) );
			if ( ! $g || ! $h ) {
				return '';
		}
						if ( $need_site && ! $w ) {
								return '';
						}

						if ( $need_users && ! $u ) {
								return '';
						}

			// Ensure hunts table has created_at column. If missing, inform admin to run upgrades manually.
	$has_created_at = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$h} LIKE %s", 'created_at' ) );
			if ( empty( $has_created_at ) ) {
								error_log( 'Bonus Hunt Guesser: missing required column created_at in table ' . $h );
								return '<p>' . esc_html( bhg_t( 'notice_db_update_required', 'Database upgrade required. Please run plugin upgrades.' ) ) . '</p>';
			}

						$order_column = 'id';
						if ( $has_created_at ) {
								$order_column = 'created_at';
						}

						$hunt_id = (int) $a['id'];
						if ( $hunt_id <= 0 ) {
								$hunt_id = (int) $wpdb->get_var(
										$wpdb->prepare(
												/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
												"SELECT id FROM {$h} WHERE status = %s ORDER BY {$order_column} DESC LIMIT 1",
												'open'
										)
								);
						}
						if ( $hunt_id <= 0 ) {
								$hunt_id = (int) $wpdb->get_var(
										/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
										"SELECT id FROM {$h} ORDER BY {$order_column} DESC LIMIT 1"
								);
						}
						if ( $hunt_id <= 0 ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
						}

		$where  = array( 'g.hunt_id = %d' );
		$params = array( $hunt_id );

		$hunt_context = $wpdb->get_row(
				$wpdb->prepare(
						/* phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name sanitized above. */
						"SELECT final_balance FROM {$h} WHERE id = %d",
						$hunt_id
				)
		);
		$hunt_has_final_balance = ( $hunt_context && null !== $hunt_context->final_balance );
		$is_open_hunt           = ! $hunt_has_final_balance;

			$aff_raw    = array_key_exists( 'aff', $atts ) ? $atts['aff'] : '';
			$aff_filter = sanitize_key( (string) $aff_raw );
			if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
				$aff_filter = 'yes';
			} elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
				$aff_filter = 'no';
			} else {
				$aff_filter = '';
			}

			if ( '' !== $aff_filter && ! $um ) {
				return '';
			}

			$aff_yes_values = array( '1', 'yes', 'true', 'on' );
			$aff_yes_sql    = array();
			foreach ( $aff_yes_values as $val ) {
				$aff_yes_sql[] = "'" . esc_sql( $val ) . "'";
			}
			$aff_yes_list = implode( ',', $aff_yes_sql );

			$count_joins  = array( "INNER JOIN {$h} h ON h.id = g.hunt_id" );
			$select_joins = $count_joins;

			if ( in_array( $a['status'], array( 'open', 'closed' ), true ) ) {
				$where[]  = 'h.status = %s';
				$params[] = $a['status'];
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

			if ( 'yes' === $aff_filter ) {
				$count_joins[]  = "INNER JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$select_joins[] = "INNER JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$where[]        = "CAST(um_aff.meta_value AS CHAR) IN ({$aff_yes_list})";
			} elseif ( 'no' === $aff_filter ) {
				$count_joins[]  = "LEFT JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$select_joins[] = "LEFT JOIN {$um} um_aff ON um_aff.user_id = g.user_id AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
				$where[]        = "(um_aff.user_id IS NULL OR CAST(um_aff.meta_value AS CHAR) = '' OR CAST(um_aff.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
			}

		// Timeline handling (explicit range).
						$timeline = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : sanitize_key( $a['timeline'] );
						$range    = $this->get_timeline_range( $timeline );
						if ( $range ) {
								$where[]  = 'g.created_at BETWEEN %s AND %s';
								$params[] = $range['start'];
								$params[] = $range['end'];
						}

						if ( '' !== $search ) {
								$where[]  = 'h.title LIKE %s';
								$params[] = '%' . $wpdb->esc_like( $search ) . '%';
						}

						$direction_map = array(
								'asc'  => 'ASC',
								'desc' => 'DESC',
						);
						$default_direction_key = strtolower( sanitize_key( $a['order'] ) );
						if ( ! isset( $direction_map[ $default_direction_key ] ) ) {
								$default_direction_key = 'desc';
						}
						$order_request_key = strtolower( $order_request );
						$direction_key     = isset( $direction_map[ $order_request_key ] ) ? $order_request_key : $default_direction_key;
						if ( $is_open_hunt && ! $has_order_query && ! $has_order_attribute ) {
								$direction_key = 'asc';
						}
						$direction = $direction_map[ $direction_key ];

		$orderby_map = array(
				'guess'      => 'g.guess',
				'hunt'       => $has_created_at ? 'h.created_at' : 'h.id',
				'final'      => 'h.final_balance',
				'time'       => 'g.created_at',
				'difference' => 'difference',
		);
		$default_orderby_key = sanitize_key( $a['orderby'] );
		if ( $is_open_hunt && ! $has_orderby_query ) {
				$default_orderby_key = 'time';
		}
		if ( ! isset( $orderby_map[ $default_orderby_key ] ) ) {
				$default_orderby_key = $is_open_hunt ? 'time' : 'hunt';
		}
		$orderby_request_key = sanitize_key( $orderby_request );
		$orderby_key         = isset( $orderby_map[ $orderby_request_key ] ) ? $orderby_request_key : $default_orderby_key;
		$orderby             = $orderby_map[ $orderby_key ];

		if ( $is_open_hunt ) {
				if ( 'difference' === $orderby_key || 'final' === $orderby_key || 'hunt' === $orderby_key ) {
						$order_sql = sprintf( ' ORDER BY g.created_at %s', $direction );
				} else {
						$order_sql = sprintf( ' ORDER BY %s %s', $orderby, $direction );
				}
		} elseif ( 'difference' === $orderby_key ) {
				$order_sql = sprintf(
						' ORDER BY CASE WHEN h.final_balance IS NULL THEN 1 ELSE 0 END ASC, CASE WHEN h.final_balance IS NULL THEN g.created_at END %1$s, ABS(h.final_balance - g.guess) %1$s',
						$direction
				);
		} else {
				$order_sql = sprintf( ' ORDER BY %s %s', $orderby, $direction );
		}

			$count_params    = $params;
			$count_join_sql  = $count_joins ? ' ' . implode( ' ', $count_joins ) . ' ' : ' ';
			$count_where_sql = implode( ' AND ', $where );
			$count_sql       = "SELECT COUNT(*) FROM {$g} g{$count_join_sql}WHERE {$count_where_sql}";
						$total        = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$count_params ) );

		if ( $need_site ) {
				$select_joins[] = "LEFT JOIN {$w} w ON w.id = h.affiliate_site_id";
		}

		if ( $need_users ) {
				$count_joins[]  = "LEFT JOIN {$u} u ON u.ID = g.user_id";
				$select_joins[] = "LEFT JOIN {$u} u ON u.ID = g.user_id";
		}

			$select_join_sql = $select_joins ? ' ' . implode( ' ', $select_joins ) . ' ' : ' ';
			$where_sql       = implode( ' AND ', $where );

		$sql = 'SELECT g.guess, g.created_at, g.user_id, h.title, h.final_balance, h.affiliate_site_id, CASE WHEN h.final_balance IS NOT NULL THEN (h.final_balance - g.guess) END AS difference';
		if ( $need_site ) {
				$sql .= ', w.name AS site_name';
		}
		if ( $need_users ) {
				$sql .= ', u.display_name AS user_display_name, u.user_login AS user_login';
		}
		$sql .= " FROM {$g} g{$select_join_sql}WHERE {$where_sql}{$order_sql} LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;
		$query    = $wpdb->prepare( $sql, ...$params );

						// db call ok; no-cache ok.
						$rows  = $wpdb->get_results( $query );
						$pages = (int) ceil( $total / $limit );

						$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
						$base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
						if ( '' === $search ) {
								$base_url = remove_query_arg( 'bhg_search', $base_url );
						}

						$toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search ) {
								$dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
								$args = array(
										'bhg_orderby' => $field,
										'bhg_order'   => $dir,
								);
								if ( '' !== $search ) {
										$args['bhg_search'] = $search;
								}
								return add_query_arg( $args, $base_url );
						};

						if ( ! $rows ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_guesses_found', 'No guesses found.' ) ) . '</p>';
						}

		$show_aff = $need_users;

		wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
		);

		ob_start();
		  echo '<form method="get" class="bhg-search-form">';
		  foreach ( $_GET as $raw_key => $v ) {
				  $key = sanitize_key( wp_unslash( $raw_key ) );
				  if ( in_array( $key, array( 'bhg_search', 'bhg_timeline' ), true ) ) {
						  continue;
				  }
				  $value = is_array( $v ) ? reset( $v ) : $v;
				  echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $value ) ) ) . '">';
		  }
		  echo '<label class="bhg-timeline-filter">';
		  echo '<span>' . esc_html( bhg_t( 'label_timeline_colon', 'Timeline:' ) ) . '</span> ';
		  echo '<select name="bhg_timeline">';
		  $timeline_options = array(
				  'all_time'  => bhg_t( 'label_all_time', 'All-Time' ),
				  'this_week' => bhg_t( 'label_this_week', 'This Week' ),
				  'this_month'=> bhg_t( 'label_this_month', 'This Month' ),
				  'this_year' => bhg_t( 'label_this_year', 'This Year' ),
				  'last_year' => bhg_t( 'label_last_year', 'Last Year' ),
		  );
		  $selected_timeline = $timeline;
		  if ( '' === $selected_timeline ) {
				  $selected_timeline = 'all_time';
		  }
		  if ( ! array_key_exists( $selected_timeline, $timeline_options ) ) {
				  $alias_map = array(
						  'week'      => 'this_week',
						  'month'     => 'this_month',
						  'year'      => 'this_year',
						  'alltime'   => 'all_time',
				  );
				  if ( isset( $alias_map[ $selected_timeline ] ) ) {
						  $selected_timeline = $alias_map[ $selected_timeline ];
				  }
		  }
		  foreach ( $timeline_options as $key => $label ) {
				  echo '<option value="' . esc_attr( $key ) . '"' . selected( $selected_timeline, $key, false ) . '>' . esc_html( $label ) . '</option>';
		  }
		  echo '</select>';
		  echo '</label>';
		  echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
		  echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
		  echo '</form>';

		echo '<table class="bhg-user-guesses"><thead><tr>';
		echo '<th><a href="' . esc_url( $toggle( 'hunt' ) ) . '">' . esc_html( bhg_t( 'sc_hunt', 'Hunt' ) ) . '</a></th>';
		if ( $need_users ) {
				echo '<th>' . esc_html( bhg_t( 'label_user', 'User' ) ) . '</th>';
		}
		echo '<th><a href="' . esc_url( $toggle( 'guess' ) ) . '">' . esc_html( bhg_t( 'sc_guess', 'Guess' ) ) . '</a></th>';
		if ( $need_site ) {
				echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
		}
		echo '<th><a href="' . esc_url( $toggle( 'final' ) ) . '">' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . '</a></th>';
		echo '<th><a href="' . esc_url( $toggle( 'difference' ) ) . '">' . esc_html( bhg_t( 'sc_difference', 'Difference' ) ) . '</a></th>';
		echo '</tr></thead><tbody>';

						foreach ( $rows as $row ) {
								echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
				if ( $need_users ) {
						$user_display = '';
						if ( isset( $row->user_display_name ) && '' !== (string) $row->user_display_name ) {
								$user_display = (string) $row->user_display_name;
						} elseif ( isset( $row->user_login ) && '' !== (string) $row->user_login ) {
								$user_display = (string) $row->user_login;
						} else {
								$user_display = sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
						}

						$user_cell = '';
						if ( $show_aff ) {
								$user_cell .= bhg_render_affiliate_dot( (int) $row->user_id, (int) $row->affiliate_site_id ) . ' ';
						}
						$user_cell .= '<span class="bhg-user-name">' . esc_html( $user_display ) . '</span>';
						echo '<td>' . wp_kses_post( $user_cell ) . '</td>';
				}
				echo '<td>' . esc_html( bhg_format_money( (float) $row->guess ) ) . '</td>';
				if ( $need_site ) {
						echo '<td>' . esc_html( $row->site_name ? $row->site_name : bhg_t( 'label_emdash', '—' ) ) . '</td>';
				}
				echo '<td>' . ( isset( $row->final_balance ) ? esc_html( bhg_format_money( (float) $row->final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '<td>' . ( isset( $row->difference ) ? esc_html( bhg_format_money( (float) $row->difference ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
				echo '</tr>';
		}
		echo '</tbody></table>';

		  $pagination = paginate_links(
				  array(
						  'base'      => add_query_arg( 'bhg_paged', '%#%', $base_url ),
						  'format'    => '',
						  'current'   => $paged,
						  'total'     => max( 1, $pages ),
						  'add_args'  => array_filter(
								  array(
										  'bhg_orderby' => $orderby_key,
										  'bhg_order'   => $direction_key,
										  'bhg_search'  => $search,
										  'bhg_timeline'=> $timeline,
								  )
						  ),
				  )
		  );
		  if ( $pagination ) {
				  echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
		  }

		  return ob_get_clean();
		}

					/**
					 * Displays a list of hunts.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
				public function hunts_shortcode( $atts ) {
						$a = shortcode_atts(
								array(
										'id'       => 0,
										'aff'      => 'no',
										'website'  => 0,
										'status'   => '',
										'timeline' => '',
										'fields'   => 'title,start,final,status',
										'orderby'  => 'created',
										'order'    => 'DESC',
										'paged'    => 1,
										'search'   => '',
								),
								$atts,
								'bhg_hunts'
						);

						$fields_raw    = explode( ',', (string) $a['fields'] );
						$allowed_field = array( 'title', 'start', 'final', 'winners', 'status', 'user', 'site' );
						$fields_arr    = array_values(
				array_unique(
					array_intersect(
						$allowed_field,
						array_map( 'sanitize_key', array_map( 'trim', $fields_raw ) )
					)
				)
			);
						if ( empty( $fields_arr ) ) {
								$fields_arr = array( 'title', 'start', 'final', 'status' );
						}

						$need_site_field = in_array( 'site', $fields_arr, true );

						$paged           = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
						$search          = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
						$limit           = 30;
						$offset          = ( $paged - 1 ) * $limit;
						$orderby_request = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
						$order_request   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );

												global $wpdb;
												$h         = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
												$aff_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
			if ( ! $h || ! $aff_table ) {
				return '';
			}

			$where  = array();
			$params = array();

			$id = (int) $a['id'];
			if ( $id > 0 ) {
				$where[]  = 'h.id = %d';
				$params[] = $id;
			}

			if ( in_array( $a['status'], array( 'open', 'closed' ), true ) ) {
				$where[]  = 'h.status = %s';
				$params[] = $a['status'];
			}

			$website = (int) $a['website'];
			if ( $website > 0 ) {
				$where[]  = 'h.affiliate_site_id = %d';
				$params[] = $website;
			}

											// Timeline handling.
						$timeline = sanitize_key( $a['timeline'] );
						$range    = $this->get_timeline_range( $timeline );
						if ( $range ) {
								$where[]  = 'h.created_at BETWEEN %s AND %s';
								$params[] = $range['start'];
								$params[] = $range['end'];
						}

						if ( '' !== $search ) {
								$where[]  = 'h.title LIKE %s';
								$params[] = '%' . $wpdb->esc_like( $search ) . '%';
						}

						$direction_map = array(
								'asc'  => 'ASC',
								'desc' => 'DESC',
						);
						$direction_key = strtolower( $order_request );
						if ( ! isset( $direction_map[ $direction_key ] ) ) {
								$direction_key = strtolower( sanitize_key( $a['order'] ) );
						}
						if ( ! isset( $direction_map[ $direction_key ] ) ) {
								$direction_key = 'desc';
						}
						$direction = $direction_map[ $direction_key ];

						$orderby_map = array(
								'title'   => 'h.title',
								'start'   => 'h.starting_balance',
								'final'   => 'h.final_balance',
								'winners' => 'h.winners_count',
								'status'  => 'h.status',
								'created' => 'h.created_at',
						);
						$default_orderby = sanitize_key( $a['orderby'] );
						if ( '' === $default_orderby || ! isset( $orderby_map[ $default_orderby ] ) ) {
								$default_orderby = 'created';
						}
						if ( '' === $orderby_request ) {
								$orderby_request = $default_orderby;
						}
						$orderby_key = isset( $orderby_map[ $orderby_request ] ) ? $orderby_request : $default_orderby;
						$orderby     = $orderby_map[ $orderby_key ];
						$order_sql   = sprintf( ' ORDER BY %s %s', $orderby, $direction );

						$count_sql = "SELECT COUNT(*) FROM {$h} h";
						if ( $where ) {
								$count_sql .= ' WHERE ' . implode( ' AND ', $where );
						}
						$total = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : $wpdb->get_var( $count_sql ) );

						$select = "SELECT h.id, h.title, h.starting_balance, h.final_balance, h.winners_count, h.status, h.created_at, h.closed_at, h.guessing_enabled";
						$join   = '';
						if ( $need_site_field ) {
								$select .= ', a.name AS site_name';
								$join    = " LEFT JOIN {$aff_table} a ON a.id = h.affiliate_site_id";
						}
						$sql = $select . " FROM {$h} h" . $join;
						if ( $where ) {
								$sql .= ' WHERE ' . implode( ' AND ', $where );
						}
						$sql     .= $order_sql . ' LIMIT %d OFFSET %d';
						$params[] = $limit;
						$params[] = $offset;

						// db call ok; no-cache ok.
						$sql  = $wpdb->prepare( $sql, ...$params );
						$rows  = $wpdb->get_results( $sql );
						$pages = (int) ceil( $total / $limit );

						$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
						$base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
						if ( '' === $search ) {
								$base_url = remove_query_arg( 'bhg_search', $base_url );
						}
						$toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search ) {
								$dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
								$args = array(
										'bhg_orderby' => $field,
										'bhg_order'   => $dir,
								);
								if ( '' !== $search ) {
										$args['bhg_search'] = $search;
								}
								return add_query_arg( $args, $base_url );
						};

						if ( ! $rows ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ) . '</p>';
						}

						$show_site = $need_site_field;

						wp_enqueue_style(
								'bhg-shortcodes',
								( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
								array(),
								defined( 'BHG_VERSION' ) ? BHG_VERSION : null
						);

						ob_start();
						echo '<form method="get" class="bhg-search-form">';
						foreach ( $_GET as $raw_key => $v ) {
								$key = sanitize_key( wp_unslash( $raw_key ) );
								if ( 'bhg_search' === $key ) {
										continue;
								}
								echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
						}

						echo '<div class="bhg-search-control">';
						echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
						echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
						echo '</div>';

						echo '</form>';

						echo '<table class="bhg-hunts"><thead><tr>';
						echo '<th><a href="' . esc_url( $toggle( 'title' ) ) . '">' . esc_html( bhg_t( 'sc_title', 'Title' ) ) . '</a></th>';
						echo '<th><a href="' . esc_url( $toggle( 'start' ) ) . '">' . esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ) . '</a></th>';
						echo '<th><a href="' . esc_url( $toggle( 'final' ) ) . '">' . esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ) . '</a></th>';
						echo '<th><a href="' . esc_url( $toggle( 'winners' ) ) . '">' . esc_html( bhg_t( 'sc_winners', 'Winners' ) ) . '</a></th>';
						echo '<th><a href="' . esc_url( $toggle( 'status' ) ) . '">' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</a></th>';
						echo '<th>' . esc_html( bhg_t( 'label_details', 'Details' ) ) . '</th>';
						if ( $show_site ) {
								echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
						}
						echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				echo '<tr>';
				echo '<td>' . esc_html( $row->title ) . '</td>';
							echo '<td>' . esc_html( bhg_format_money( (float) $row->starting_balance ) ) . '</td>';
								echo '<td>' . ( isset( $row->final_balance ) ? esc_html( bhg_format_money( (float) $row->final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
								$winners_display = isset( $row->winners_count ) ? number_format_i18n( (int) $row->winners_count ) : bhg_t( 'label_emdash', '—' );
								echo '<td>' . esc_html( $winners_display ) . '</td>';
								$status_key = strtolower( (string) $row->status );
								echo '<td>' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</td>';
								$details_html = esc_html( bhg_t( 'label_emdash', '—' ) );
								if ( 'closed' === $status_key ) {
										$results_url = function_exists( 'bhg_get_hunt_results_url' ) ? bhg_get_hunt_results_url( (int) $row->id ) : '';
										if ( '' !== $results_url ) {
												$details_html = '<a class="bhg-hunt-link" href="' . esc_url( $results_url ) . '">' . esc_html( bhg_t( 'link_show_results', 'Show Results' ) ) . '</a>';
										}
								} elseif ( 'open' === $status_key ) {
										$guessing_enabled = isset( $row->guessing_enabled ) ? (int) $row->guessing_enabled : 1;
										if ( $guessing_enabled > 0 ) {
												$guess_url = function_exists( 'bhg_get_guess_submission_url' ) ? bhg_get_guess_submission_url( (int) $row->id ) : '';
												if ( '' !== $guess_url ) {
														$details_html = '<a class="bhg-hunt-link" href="' . esc_url( $guess_url ) . '">' . esc_html( bhg_t( 'link_guess_now', 'Guess Now' ) ) . '</a>';
												}
										} else {
												$details_html = esc_html( bhg_t( 'label_guessing_closed', 'Guessing Closed' ) );
										}
								}
								echo '<td>' . $details_html . '</td>';
								if ( $show_site ) {
										echo '<td>' . ( $row->site_name ? esc_html( $row->site_name ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
								}
				echo '</tr>';
			}
						echo '</tbody></table>';

						$pagination = paginate_links(
								array(
										'base'     => add_query_arg( 'bhg_paged', '%#%', $base_url ),
										'format'   => '',
										'current'  => $paged,
										'total'    => max( 1, $pages ),
										'add_args' => array_filter(
												array(
														'bhg_orderby' => $orderby_key,
														'bhg_order'   => $direction_key,
														'bhg_search'  => $search,
												)
										),
								)
						);
						if ( $pagination ) {
								echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
						}

						return ob_get_clean();
		}

					/**
					 * Displays overall wins leaderboards.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
				public function leaderboards_shortcode( $atts ) {
					   $a = shortcode_atts(
							   array(
									   'fields'     => 'pos,user,wins,avg_hunt,avg_tournament',
										'ranking'    => 1,
										'timeline'   => '',
										'orderby'    => 'wins',
										'order'      => 'DESC',
										'search'     => '',
										'tournament' => '',
										'bonushunt'  => '',
										'website'    => '',
										'aff'        => '',
								),
								$atts,
								'bhg_leaderboards'
						);

						$raw_fields     = array_map( 'trim', explode( ',', (string) $a['fields'] ) );
						$allowed_fields = array( 'pos', 'user', 'wins', 'avg', 'avg_hunt', 'avg_tournament', 'aff', 'site', 'hunt', 'tournament' );
						$normalized     = array();
						foreach ( $raw_fields as $field ) {
								$key = sanitize_key( $field );
								if ( 'avg' === $key ) {
										$key = 'avg_hunt';
								}
								if ( in_array( $key, $allowed_fields, true ) ) {
										$normalized[] = $key;
								}
						}
						$fields_arr = array_values( array_unique( $normalized ) );
					   if ( empty( $fields_arr ) ) {
							   $fields_arr = array( 'pos', 'user', 'wins', 'avg_hunt', 'avg_tournament' );
						}

						global $wpdb;

						$search = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );

						$attr_timeline = sanitize_key( $a['timeline'] );
						if ( '' === $attr_timeline ) {
								$attr_timeline = 'all_time';
						}

						$timeline_request = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : '';
						$timeline         = '' !== $timeline_request ? $timeline_request : $attr_timeline;

						$timeline_aliases = array(
								'day'          => 'day',
								'today'        => 'day',
								'this_day'     => 'day',
								'week'         => 'week',
								'this_week'    => 'week',
								'weekly'       => 'week',
								'month'        => 'month',
								'this_month'   => 'month',
								'monthly'      => 'month',
								'year'         => 'year',
								'this_year'    => 'year',
								'yearly'       => 'year',
								'quarter'      => 'quarter',
								'this_quarter' => 'quarter',
								'quarterly'    => 'quarter',
								'last_year'    => 'last_year',
								'all_time'     => 'all_time',
								'alltime'      => 'all_time',
						);
						if ( isset( $timeline_aliases[ $timeline ] ) ) {
								$timeline = $timeline_aliases[ $timeline ];
						}

						$timeline_filter = ( 'all_time' === $timeline ) ? '' : $timeline;

						$limit  = min( 10, max( 1, (int) $a['ranking'] ) );
						$offset = 0;

						$orderby_request = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
						$order_request   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );

						$shortcode_tournament = isset( $a['tournament'] ) ? $a['tournament'] : '';
						$shortcode_hunt       = isset( $a['bonushunt'] ) ? $a['bonushunt'] : '';
						$shortcode_site       = isset( $a['website'] ) ? $a['website'] : '';
						$shortcode_aff        = isset( $a['aff'] ) ? $a['aff'] : '';

						$raw_tournament = isset( $_GET['bhg_tournament'] ) ? wp_unslash( $_GET['bhg_tournament'] ) : $shortcode_tournament;
						$raw_hunt       = isset( $_GET['bhg_hunt'] ) ? wp_unslash( $_GET['bhg_hunt'] ) : $shortcode_hunt;
						$raw_site       = isset( $_GET['bhg_site'] ) ? wp_unslash( $_GET['bhg_site'] ) : $shortcode_site;
						$raw_aff        = isset( $_GET['bhg_aff'] ) ? wp_unslash( $_GET['bhg_aff'] ) : $shortcode_aff;

						$tournament_id = max( 0, absint( $raw_tournament ) );
						$hunt_id       = max( 0, absint( $raw_hunt ) );
						$website_id    = max( 0, absint( $raw_site ) );

						$aff_filter = sanitize_key( (string) $raw_aff );
						if ( in_array( $aff_filter, array( 'yes', 'true', '1' ), true ) ) {
								$aff_filter = 'yes';
						} elseif ( in_array( $aff_filter, array( 'no', 'false', '0' ), true ) ) {
								$aff_filter = 'no';
						} else {
								$aff_filter = '';
						}

						// Preload dropdown data for filters.
						$tournaments = array();
						$hunts       = array();
						$sites       = array();

						$tournaments_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
						$hunts_table       = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
						$hunt_map_table    = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
						$sites_table       = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );

						if ( $tournaments_table ) {
								$tournament_limits = array();
								if ( '' !== $shortcode_tournament && '0' !== (string) $shortcode_tournament ) {
										$tournament_limits[] = absint( $shortcode_tournament );
								}
								if ( ! empty( $tournament_limits ) && $tournament_id > 0 && ! in_array( $tournament_id, $tournament_limits, true ) ) {
										$tournament_limits[] = $tournament_id;
								}
								$tournament_limits = array_values( array_unique( array_filter( $tournament_limits ) ) );

								if ( ! empty( $tournament_limits ) ) {
										$placeholders = implode( ',', array_fill( 0, count( $tournament_limits ), '%d' ) );
										$sql          = "SELECT id, title FROM {$tournaments_table} WHERE id IN ({$placeholders}) ORDER BY title ASC";
										// db call ok; value list prepared.
										$tournaments  = $wpdb->get_results( $wpdb->prepare( $sql, ...$tournament_limits ) );
								} else {
										$sql         = "SELECT id, title FROM {$tournaments_table} ORDER BY created_at DESC, id DESC";
										// db call ok; limited columns.
										$tournaments = $wpdb->get_results( $sql );
								}

								if ( $tournament_id > 0 ) {
										$has_selected = false;
										foreach ( $tournaments as $tournament ) {
												if ( (int) $tournament->id === $tournament_id ) {
														$has_selected = true;
														break;
												}
										}
										if ( ! $has_selected ) {
												$sql          = $wpdb->prepare( "SELECT id, title FROM {$tournaments_table} WHERE id = %d", $tournament_id );
												$selected_row = $wpdb->get_row( $sql );
												if ( $selected_row ) {
														$tournaments[] = $selected_row;
												}
										}
								}
						}

						if ( $hunts_table ) {
								$hunts_where  = array();
								$hunts_params = array();
								$joins        = '';

								$hunt_limits = array();
								if ( '' !== $shortcode_hunt && '0' !== (string) $shortcode_hunt ) {
										$hunt_limits[] = absint( $shortcode_hunt );
								}
								if ( ! empty( $hunt_limits ) && $hunt_id > 0 && ! in_array( $hunt_id, $hunt_limits, true ) ) {
										$hunt_limits[] = $hunt_id;
								}
								$hunt_limits = array_values( array_unique( array_filter( $hunt_limits ) ) );

								if ( $hunt_map_table ) {
										$joins = " LEFT JOIN {$hunt_map_table} ht ON ht.hunt_id = h.id";
								}

								if ( ! empty( $hunt_limits ) ) {
										$placeholders = implode( ',', array_fill( 0, count( $hunt_limits ), '%d' ) );
										$hunts_where[] = "h.id IN ({$placeholders})";
										$hunts_params   = array_merge( $hunts_params, $hunt_limits );
								} else {
										if ( $tournament_id > 0 ) {
												if ( $hunt_map_table ) {
														$hunts_where[] = '(h.tournament_id = %d OR ht.tournament_id = %d)';
														$hunts_params[] = $tournament_id;
														$hunts_params[] = $tournament_id;
												} else {
														$hunts_where[] = 'h.tournament_id = %d';
														$hunts_params[] = $tournament_id;
												}
										}
										if ( $website_id > 0 ) {
												$hunts_where[] = 'h.affiliate_site_id = %d';
												$hunts_params[] = $website_id;
										}
								}

								$hunts_sql = "SELECT DISTINCT h.id, h.title FROM {$hunts_table} h{$joins}";
								if ( ! empty( $hunts_where ) ) {
										$hunts_sql .= ' WHERE ' . implode( ' AND ', $hunts_where );
								}
								$hunts_sql .= ' ORDER BY h.created_at DESC, h.id DESC';

								if ( ! empty( $hunts_params ) ) {
										$hunts = $wpdb->get_results( $wpdb->prepare( $hunts_sql, ...$hunts_params ) );
								} else {
										$hunts = $wpdb->get_results( $hunts_sql );
								}

								if ( $hunt_id > 0 ) {
										$has_selected_hunt = false;
										foreach ( $hunts as $hunt ) {
												if ( (int) $hunt->id === $hunt_id ) {
														$has_selected_hunt = true;
														break;
												}
										}
										if ( ! $has_selected_hunt ) {
												$hunt_sql      = $wpdb->prepare( "SELECT id, title FROM {$hunts_table} WHERE id = %d", $hunt_id );
												$selected_hunt = $wpdb->get_row( $hunt_sql );
												if ( $selected_hunt ) {
														$hunts[] = $selected_hunt;
												}
										}
								}
						}

						if ( $sites_table ) {
								$site_limits = array();
								if ( '' !== $shortcode_site && '0' !== (string) $shortcode_site ) {
										$site_limits[] = absint( $shortcode_site );
								}
								if ( ! empty( $site_limits ) && $website_id > 0 && ! in_array( $website_id, $site_limits, true ) ) {
										$site_limits[] = $website_id;
								}
								$site_limits = array_values( array_unique( array_filter( $site_limits ) ) );

								if ( ! empty( $site_limits ) ) {
										$placeholders = implode( ',', array_fill( 0, count( $site_limits ), '%d' ) );
										$sql          = "SELECT id, name FROM {$sites_table} WHERE id IN ({$placeholders}) ORDER BY name ASC";
										$sites        = $wpdb->get_results( $wpdb->prepare( $sql, ...$site_limits ) );
								} else {
										$sql   = "SELECT id, name FROM {$sites_table} ORDER BY name ASC";
										$sites = $wpdb->get_results( $sql );
								}

								if ( $website_id > 0 ) {
										$has_selected_site = false;
										foreach ( $sites as $site ) {
												if ( (int) $site->id === $website_id ) {
														$has_selected_site = true;
														break;
												}
										}
										if ( ! $has_selected_site ) {
												$sql           = $wpdb->prepare( "SELECT id, name FROM {$sites_table} WHERE id = %d", $website_id );
												$selected_site = $wpdb->get_row( $sql );
												if ( $selected_site ) {
														$sites[] = $selected_site;
												}
										}
								}
						}

						if ( '' === $orderby_request ) {
								$orderby_request = 'wins';
						}
						if ( 'avg' === $orderby_request ) {
								$orderby_request = 'avg_hunt';
						}
						$direction_key = strtolower( $order_request );
						if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
								$direction_key = strtolower( sanitize_key( $a['order'] ) );
								if ( ! in_array( $direction_key, array( 'asc', 'desc' ), true ) ) {
										$direction_key = 'desc';
								}
						}

						// Optional timeline filter.
						$prep_where = array();
						$where      = array();
						$range      = $this->get_timeline_range( $timeline_filter );
						if ( $range ) {
								$where[]      = 'COALESCE(hw.created_at, h.closed_at, h.created_at) BETWEEN %s AND %s';
								$prep_where[] = $range['start'];
								$prep_where[] = $range['end'];
						}

						if ( '' !== $search ) {
								$where[]      = 'u.user_login LIKE %s';
								$prep_where[] = '%' . $wpdb->esc_like( $search ) . '%';
						}

						if ( $hunt_id > 0 ) {
								$where[]      = 'hw.hunt_id = %d';
								$prep_where[] = $hunt_id;
						}

						if ( $website_id > 0 ) {
								$where[]      = 'h.affiliate_site_id = %d';
								$prep_where[] = $website_id;
						}

						$need_avg_hunt        = in_array( 'avg_hunt', $fields_arr, true );
						$need_avg_tournament  = in_array( 'avg_tournament', $fields_arr, true );
						$need_site            = in_array( 'site', $fields_arr, true );
						$need_tournament_name = in_array( 'tournament', $fields_arr, true );
						$need_hunt_name       = in_array( 'hunt', $fields_arr, true );
						$need_aff             = in_array( 'aff', $fields_arr, true );
						$need_site_details    = $need_site || $need_aff;

						$r  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
						$u  = esc_sql( $this->sanitize_table( $wpdb->users ) );
						$t  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
			$w  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
						$hw = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_hunt_winners' ) );
			$h  = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
						$ht = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments_hunts' ) );
			$um = esc_sql( $this->sanitize_table( $wpdb->usermeta ) );
						if ( ! $r || ! $u || ! $t || ! $w || ! $hw || ! $h || ! $um || ! $ht ) {
								return '';
						}

			$aff_yes_values = array( '1', 'yes', 'true', 'on' );
			$aff_yes_sql    = array();
			foreach ( $aff_yes_values as $val ) {
				$aff_yes_sql[] = "'" . esc_sql( $val ) . "'";
						}
			$aff_yes_list = implode( ',', $aff_yes_sql );

						$joins = array(
								"INNER JOIN {$h} h ON h.id = hw.hunt_id",
								"INNER JOIN {$u} u ON u.ID = hw.user_id",
						);

						if ( $tournament_id > 0 ) {
								$joins[]     = "LEFT JOIN {$ht} ht ON ht.hunt_id = h.id";
								$where[]      = '(ht.tournament_id = %d OR (ht.hunt_id IS NULL AND h.tournament_id = %d))';
								$prep_where[] = $tournament_id;
								$prep_where[] = $tournament_id;
						}

						if ( 'yes' === $aff_filter || 'true' === $aff_filter || '1' === $aff_filter ) {
								$joins[]   = "INNER JOIN {$um} um_aff ON um_aff.user_id = u.ID AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
								$where[] = "CAST(um_aff.meta_value AS CHAR) IN ({$aff_yes_list})";
						} elseif ( 'no' === $aff_filter || 'false' === $aff_filter || '0' === $aff_filter ) {
								$joins[]   = "LEFT JOIN {$um} um_aff ON um_aff.user_id = u.ID AND um_aff.meta_key = '" . esc_sql( 'bhg_is_affiliate' ) . "'";
								$where[]   = "(um_aff.user_id IS NULL OR CAST(um_aff.meta_value AS CHAR) = '' OR CAST(um_aff.meta_value AS CHAR) NOT IN ({$aff_yes_list}))";
						}

						$where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

						$base_joins_sql = ' ' . implode( ' ', $joins ) . ' ';

						$count_sql = "SELECT COUNT(DISTINCT hw.user_id) FROM {$hw} hw{$base_joins_sql}{$where_sql}";
						if ( empty( $prep_where ) ) {
								$total = (int) $wpdb->get_var( $count_sql );
						} else {
								$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$prep_where ) );
						}

						if ( $total <= 0 ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_data_available', 'No data available.' ) ) . '</p>';
						}

						$select_parts = array(
								'hw.user_id',
								'u.user_login',
								'COUNT(*) AS total_wins',
						);

						if ( $need_avg_hunt || 'avg_hunt' === $orderby_request ) {
								$need_avg_hunt = true;
								$select_parts[] = 'AVG(hw.position) AS avg_hunt_pos';
						}

						if ( $need_avg_tournament || 'avg_tournament' === $orderby_request ) {
								$need_avg_tournament = true;
								$tournament_filter_join = '';
								if ( $tournament_id > 0 ) {
										$tournament_filter_join = $wpdb->prepare( ' WHERE tr.tournament_id = %d', $tournament_id );
								}
								$select_parts[] = 'tour_avg.avg_tournament_pos';
								$tour_join      = "LEFT JOIN (SELECT ranks.user_id, AVG(ranks.rank_position) AS avg_tournament_pos FROM (SELECT tr.user_id, tr.tournament_id, (SELECT 1 + COUNT(*) FROM {$r} tr2 WHERE tr2.tournament_id = tr.tournament_id AND (tr2.wins > tr.wins OR (tr2.wins = tr.wins AND tr2.user_id < tr.user_id))) AS rank_position FROM {$r} tr{$tournament_filter_join}) AS ranks GROUP BY ranks.user_id) AS tour_avg ON tour_avg.user_id = hw.user_id";
						} else {
								$tour_join = '';
						}

						$sub_filters = array();
						if ( $tournament_id > 0 ) {
								$sub_filters[] = $wpdb->prepare( '(ht2.tournament_id = %d OR (ht2.hunt_id IS NULL AND h2.tournament_id = %d))', $tournament_id, $tournament_id );
						}
						if ( $hunt_id > 0 ) {
								$sub_filters[] = $wpdb->prepare( 'hw2.hunt_id = %d', $hunt_id );
						}
						if ( $website_id > 0 ) {
								$sub_filters[] = $wpdb->prepare( 'h2.affiliate_site_id = %d', $website_id );
						}
						if ( $range ) {
								$sub_filters[] = $wpdb->prepare( 'COALESCE(hw2.created_at, h2.closed_at, h2.created_at) BETWEEN %s AND %s', $range['start'], $range['end'] );
						}
						$sub_where_parts = array( 'hw2.user_id = hw.user_id' );
						if ( $sub_filters ) {
								$sub_where_parts = array_merge( $sub_where_parts, $sub_filters );
						}
						$sub_where_sql = ' WHERE ' . implode( ' AND ', $sub_where_parts );

						if ( $need_site_details ) {
								$site_subquery_template = "(SELECT %s FROM {$hw} hw2 INNER JOIN {$h} h2 ON h2.id = hw2.hunt_id LEFT JOIN {$ht} ht2 ON ht2.hunt_id = h2.id LEFT JOIN {$w} w2 ON w2.id = h2.affiliate_site_id{$sub_where_sql} ORDER BY COALESCE(hw2.created_at, h2.closed_at, h2.created_at) DESC, hw2.id DESC LIMIT 1)";
								$select_parts[]         = sprintf( $site_subquery_template, 'h2.affiliate_site_id' ) . ' AS site_id';
								if ( $need_site ) {
										$select_parts[] = sprintf( $site_subquery_template, 'w2.name' ) . ' AS site_name';
								}
						}

						if ( $need_hunt_name ) {
								$select_parts[] = "(SELECT h2.title FROM {$hw} hw2 INNER JOIN {$h} h2 ON h2.id = hw2.hunt_id LEFT JOIN {$ht} ht2 ON ht2.hunt_id = h2.id{$sub_where_sql} ORDER BY COALESCE(hw2.created_at, h2.closed_at, h2.created_at) DESC, hw2.id DESC LIMIT 1) AS hunt_title";
						}

						if ( $need_tournament_name ) {
								$tournament_where_sql = $sub_where_sql . ' AND (ht2.tournament_id IS NOT NULL OR h2.tournament_id IS NOT NULL)';
								$select_parts[]       = "(SELECT COALESCE(t2.title, t2_legacy.title) FROM {$hw} hw2 INNER JOIN {$h} h2 ON h2.id = hw2.hunt_id LEFT JOIN {$ht} ht2 ON ht2.hunt_id = h2.id LEFT JOIN {$t} t2 ON t2.id = ht2.tournament_id LEFT JOIN {$t} t2_legacy ON t2_legacy.id = h2.tournament_id{$tournament_where_sql} ORDER BY COALESCE(hw2.created_at, h2.closed_at, h2.created_at) DESC, hw2.id DESC LIMIT 1) AS tournament_title";
						}

						$select_sql = 'SELECT ' . implode( ', ', $select_parts ) . " FROM {$hw} hw{$base_joins_sql}";
						if ( $tour_join ) {
								$select_sql .= ' ' . $tour_join . ' ';
						}
						$select_sql .= $where_sql;
						$select_sql .= ' GROUP BY hw.user_id, u.user_login';

						$orderby_key = $orderby_request;
						$orderby_map = array(
								'wins'           => 'total_wins',
								'user'           => 'u.user_login',
								'avg_hunt'       => 'avg_hunt_pos',
								'avg_tournament' => 'avg_tournament_pos',
						);
						$direction_map = array(
								'asc'  => 'ASC',
								'desc' => 'DESC',
						);
						$direction     = isset( $direction_map[ $direction_key ] ) ? $direction_map[ $direction_key ] : $direction_map['desc'];
						$orderby       = isset( $orderby_map[ $orderby_key ] ) ? $orderby_map[ $orderby_key ] : $orderby_map['wins'];
						$select_sql   .= sprintf( ' ORDER BY %s %s LIMIT %%d OFFSET %%d', $orderby, $direction );

						$query_params = $prep_where;
						$query_params[] = $limit;
						$query_params[] = $offset;
						$query       = $wpdb->prepare( $select_sql, ...$query_params );
						// db call ok; no-cache ok.
						$rows        = $wpdb->get_results( $query );
						if ( ! $rows ) {
								return '<p>' . esc_html( bhg_t( 'notice_no_data_available', 'No data available.' ) ) . '</p>';
						}
						$pages = (int) ceil( $total / $limit );

						$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) ) : home_url( '/' );
						$base_url    = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
						if ( '' === $search ) {
								$base_url = remove_query_arg( 'bhg_search', $base_url );
						}
						$toggle = function ( $field ) use ( $base_url, $orderby_key, $direction_key, $search, $tournament_id, $hunt_id, $aff_filter, $website_id, $timeline ) {
								$dir  = ( $orderby_key === $field && 'asc' === $direction_key ) ? 'desc' : 'asc';
								$args = array(
										'bhg_orderby' => $field,
										'bhg_order'   => $dir,
								);
								if ( '' !== $search ) {
										$args['bhg_search'] = $search;
								}
								if ( $tournament_id > 0 ) {
										$args['bhg_tournament'] = $tournament_id;
								}
								if ( $hunt_id > 0 ) {
										$args['bhg_hunt'] = $hunt_id;
								}
								if ( '' !== $aff_filter ) {
										$args['bhg_aff'] = $aff_filter;
								}
								if ( $website_id > 0 ) {
										$args['bhg_site'] = $website_id;
								}
								if ( '' !== $timeline ) {
										$args['bhg_timeline'] = $timeline;
								}
								return add_query_arg( $args, $base_url );
						};

						wp_enqueue_style(
								'bhg-shortcodes',
								( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
								array(),
								defined( 'BHG_VERSION' ) ? BHG_VERSION : null
						);

						ob_start();
						echo '<form method="get" class="bhg-search-form">';
						foreach ( $_GET as $raw_key => $v ) {
								$key = sanitize_key( wp_unslash( $raw_key ) );
								if ( in_array( $key, array( 'bhg_search', 'bhg_tournament', 'bhg_hunt', 'bhg_site', 'bhg_aff', 'bhg_timeline' ), true ) ) {
										continue;
								}
								echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
						}

						echo '<div class="bhg-filter-controls">';

						$timeline_options = array(
								'all_time'  => bhg_t( 'option_timeline_all_time', 'All-Time' ),
								'day'       => bhg_t( 'option_timeline_today', 'Today' ),
								'week'      => bhg_t( 'option_timeline_this_week', 'This Week' ),
								'month'     => bhg_t( 'option_timeline_this_month', 'This Month' ),
								'quarter'   => bhg_t( 'option_timeline_this_quarter', 'This Quarter' ),
								'year'      => bhg_t( 'option_timeline_this_year', 'This Year' ),
								'last_year' => bhg_t( 'option_timeline_last_year', 'Last Year' ),
						);

						if ( '' !== $timeline && ! isset( $timeline_options[ $timeline ] ) ) {
								$timeline_options[ $timeline ] = ucwords( str_replace( '_', ' ', $timeline ) );
						}

						echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_timeline', 'Timeline' ) );
						echo '<select name="bhg_timeline" class="bhg-filter-select">';
						foreach ( $timeline_options as $value => $label ) {
								echo '<option value="' . esc_attr( $value ) . '"' . selected( $timeline, $value, false ) . '>' . esc_html( $label ) . '</option>';
						}
						echo '</select></label>';

						if ( ! empty( $tournaments ) ) {
								echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_tournament', 'Tournament' ) );
								echo '<select name="bhg_tournament" class="bhg-filter-select">';
								echo '<option value="">' . esc_html( bhg_t( 'option_all_tournaments', 'All tournaments' ) ) . '</option>';
								foreach ( $tournaments as $tournament ) {
										echo '<option value="' . (int) $tournament->id . '"' . selected( $tournament_id, (int) $tournament->id, false ) . '>' . esc_html( $tournament->title ) . '</option>';
								}
								echo '</select></label>';
						}

						if ( ! empty( $hunts ) ) {
								echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_hunt', 'Bonus Hunt' ) );
								echo '<select name="bhg_hunt" class="bhg-filter-select">';
								echo '<option value="">' . esc_html( bhg_t( 'option_all_hunts', 'All bonus hunts' ) ) . '</option>';
								foreach ( $hunts as $hunt ) {
										echo '<option value="' . (int) $hunt->id . '"' . selected( $hunt_id, (int) $hunt->id, false ) . '>' . esc_html( $hunt->title ) . '</option>';
								}
								echo '</select></label>';
						}

						if ( ! empty( $sites ) ) {
								echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_site', 'Affiliate Site' ) );
								echo '<select name="bhg_site" class="bhg-filter-select">';
								echo '<option value="">' . esc_html( bhg_t( 'option_all_sites', 'All affiliate sites' ) ) . '</option>';
								foreach ( $sites as $site ) {
										echo '<option value="' . (int) $site->id . '"' . selected( $website_id, (int) $site->id, false ) . '>' . esc_html( $site->name ) . '</option>';
								}
								echo '</select></label>';
						}

						echo '<label class="bhg-filter-label">' . esc_html( bhg_t( 'label_filter_affiliate', 'Affiliate Status' ) );
						echo '<select name="bhg_aff" class="bhg-filter-select">';
						echo '<option value="">' . esc_html( bhg_t( 'option_aff_all', 'All users' ) ) . '</option>';
						echo '<option value="yes"' . selected( $aff_filter, 'yes', false ) . '>' . esc_html( bhg_t( 'option_aff_only', 'Affiliates only' ) ) . '</option>';
						echo '<option value="no"' . selected( $aff_filter, 'no', false ) . '>' . esc_html( bhg_t( 'option_aff_none', 'Non-affiliates only' ) ) . '</option>';
						echo '</select></label>';

						echo '</div>';

						echo '<div class="bhg-search-control">';
						echo '<input type="text" name="bhg_search" value="' . esc_attr( $search ) . '">';
						echo '<button type="submit">' . esc_html( bhg_t( 'button_search', 'Search' ) ) . '</button>';
						echo '</div>';

						echo '</form>';

						echo '<table class="bhg-leaderboard">';
						echo '<thead><tr>';
						foreach ( $fields_arr as $field ) {
								if ( 'pos' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'sc_position', 'Position' ) ) . '</th>';
								} elseif ( 'user' === $field ) {
										echo '<th><a href="' . esc_url( $toggle( 'user' ) ) . '">' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</a></th>';
								} elseif ( 'wins' === $field ) {
										echo '<th><a href="' . esc_url( $toggle( 'wins' ) ) . '">' . esc_html( bhg_t( 'label_times_won', 'Times Won' ) ) . '</a></th>';
								} elseif ( 'avg_hunt' === $field ) {
										echo '<th><a href="' . esc_url( $toggle( 'avg_hunt' ) ) . '">' . esc_html( bhg_t( 'sc_avg_rank', 'Avg Hunt Pos' ) ) . '</a></th>';
								} elseif ( 'avg_tournament' === $field ) {
										echo '<th><a href="' . esc_url( $toggle( 'avg_tournament' ) ) . '">' . esc_html( bhg_t( 'sc_avg_tournament_pos', 'Avg Tournament Pos' ) ) . '</a></th>';
								} elseif ( 'aff' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_affiliate', 'Affiliate' ) ) . '</th>';
								} elseif ( 'site' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_site', 'Site' ) ) . '</th>';
								} elseif ( 'hunt' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_hunt', 'Hunt' ) ) . '</th>';
								} elseif ( 'tournament' === $field ) {
										echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
								}
						}
						echo '</tr></thead><tbody>';

						$pos = $offset + 1;
						foreach ( $rows as $row ) {
								if ( $need_aff ) {
										$site_id = isset( $row->site_id ) ? (int) $row->site_id : 0;
										$aff     = bhg_render_affiliate_dot( (int) $row->user_id, $site_id );
								}
										/* translators: %d: user ID. */
										$user_label = $row->user_login ? $row->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $row->user_id );
										echo '<tr>';
				foreach ( $fields_arr as $field ) {
					if ( 'pos' === $field ) {
						echo '<td>' . (int) $pos . '</td>';
					} elseif ( 'user' === $field ) {
						echo '<td>' . esc_html( $user_label ) . '</td>';
										} elseif ( 'wins' === $field ) {
												echo '<td>' . (int) $row->total_wins . '</td>';
										} elseif ( 'avg_hunt' === $field ) {
												echo '<td>' . ( isset( $row->avg_hunt_pos ) ? esc_html( number_format_i18n( (float) $row->avg_hunt_pos, 2 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
										} elseif ( 'avg_tournament' === $field ) {
												echo '<td>' . ( isset( $row->avg_tournament_pos ) ? esc_html( number_format_i18n( (float) $row->avg_tournament_pos, 2 ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
										} elseif ( 'aff' === $field ) {
														echo '<td>' . wp_kses_post( $aff ) . '</td>';
										} elseif ( 'site' === $field ) {
														echo '<td>' . esc_html( $row->site_name ? $row->site_name : bhg_t( 'label_emdash', '—' ) ) . '</td>';
										} elseif ( 'hunt' === $field ) {
							echo '<td>' . esc_html( $row->hunt_title ? $row->hunt_title : bhg_t( 'label_emdash', '—' ) ) . '</td>';
					} elseif ( 'tournament' === $field ) {
							echo '<td>' . esc_html( $row->tournament_title ? $row->tournament_title : bhg_t( 'label_emdash', '—' ) ) . '</td>';
					}
				}
					echo '</tr>';
					++$pos;
			}
						echo '</tbody></table>';

						return ob_get_clean();
				}

					/**
					 * Lists tournaments or shows tournament details.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
		public function tournaments_shortcode( $atts ) {
			global $wpdb;

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

				// Details screen.
				$details_id = isset( $_GET['bhg_tournament_id'] ) ? absint( wp_unslash( $_GET['bhg_tournament_id'] ) ) : 0;
						if ( $details_id > 0 ) {
								$t = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
								$r = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournament_results' ) );
								$u = esc_sql( $this->sanitize_table( $wpdb->users ) );
								$s = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
								if ( ! $t || ! $r || ! $u || ! $s ) {
										return '';
								}

								$column_parts = array(
										't.id',
										't.title',
										't.description',
										't.start_date',
										't.end_date',
										't.status',
										't.prizes',
										't.affiliate_site_id',
										't.affiliate_website',
										't.affiliate_url_visible',
										's.name AS affiliate_site_name',
										's.url AS affiliate_site_url',
								);
								$columns = implode( ', ', $column_parts );

								// db call ok; no-cache ok.
								$tournament = $wpdb->get_row(
										$wpdb->prepare(
												"SELECT {$columns} FROM {$t} t LEFT JOIN {$s} s ON s.id = t.affiliate_site_id WHERE t.id = %d",
												$details_id
										)
								);
								if ( ! $tournament ) {
										return '<p>' . esc_html( bhg_t( 'notice_tournament_not_found', 'Tournament not found.' ) ) . '</p>';
								}

								$prize_ids = array();
								if ( ! empty( $tournament->prizes ) ) {
										$decoded_prizes = json_decode( $tournament->prizes, true );
										if ( is_array( $decoded_prizes ) ) {
												foreach ( $decoded_prizes as $maybe_prize ) {
														$prize_id = absint( $maybe_prize );
														if ( $prize_id > 0 ) {
																$prize_ids[] = $prize_id;
														}
												}
										}
								}

								$prizes = array();
								if ( ! empty( $prize_ids ) && class_exists( 'BHG_Prizes' ) && method_exists( 'BHG_Prizes', 'get_prizes_by_ids' ) ) {
										$prizes = BHG_Prizes::get_prizes_by_ids( $prize_ids );
										if ( ! empty( $prizes ) ) {
												$prizes = array_values(
														array_filter(
																$prizes,
																static function ( $prize ) {
																		return ! isset( $prize->active ) || (int) $prize->active === 1;
																}
														)
												);
										}
								}

								$orderby        = isset( $_GET['orderby'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ) : 'wins';
								$allowed_orders = array( 'asc', 'desc' );
								$order          = isset( $_GET['order'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'desc';

								$allowed = array(
										'wins'        => 'r.wins',
										'username'    => 'u.user_login',
										'last_win_at' => 'r.last_win_date',
								);
								if ( ! isset( $allowed[ $orderby ] ) ) {
										$orderby = 'wins';
								}
								if ( ! in_array( $order, $allowed_orders, true ) ) {
										$order = 'desc';
								}
								$orderby_column = $allowed[ $orderby ];
								$order          = strtoupper( $order );

								$query = $wpdb->prepare(
										"SELECT r.user_id, r.wins, r.last_win_date, u.user_login FROM {$r} r INNER JOIN {$u} u ON u.ID = r.user_id WHERE r.tournament_id = %d ORDER BY " . esc_sql( $orderby_column ) . ' ' . esc_sql( $order ) . ', r.user_id ASC',
										$tournament->id
								);
								$rows = $wpdb->get_results( $query );

								$base   = remove_query_arg( array( 'orderby', 'order' ) );
								$toggle = function ( $key ) use ( $orderby, $order, $base ) {
										$next = ( $orderby === $key && strtolower( $order ) === 'asc' ) ? 'desc' : 'asc';
										return esc_url(
												add_query_arg(
														array(
																'orderby' => $key,
																'order'   => $next,
														),
														$base
												)
										);
								};

								ob_start();
								echo '<div class="bhg-tournament-details">';
								echo '<p><a href="' . esc_url( remove_query_arg( 'bhg_tournament_id' ) ) . '">&larr; ' . esc_html( bhg_t( 'label_back_to_tournaments', 'Back to tournaments' ) ) . '</a></p>';
								$heading = $tournament->title ? $tournament->title : bhg_t( 'label_tournament', 'Tournament' );
								echo '<h3>' . esc_html( $heading ) . '</h3>';
								echo '<p><strong>' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->start_date ) ) . ' &nbsp; ';
								echo '<strong>' . esc_html( bhg_t( 'sc_end', 'End' ) ) . ':</strong> ' . esc_html( mysql2date( get_option( 'date_format' ), $tournament->end_date ) ) . ' &nbsp; ';
								$status_key = strtolower( (string) $tournament->status );
								echo '<strong>' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . ':</strong> ' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</p>';

								if ( ! empty( $tournament->description ) ) {
										echo '<div class="bhg-tournament-description">' . wp_kses_post( wpautop( $tournament->description ) ) . '</div>';
								}

								$affiliate_name = isset( $tournament->affiliate_site_name ) ? (string) $tournament->affiliate_site_name : '';
								$affiliate_url  = isset( $tournament->affiliate_website ) ? (string) $tournament->affiliate_website : '';
								if ( '' === $affiliate_url && isset( $tournament->affiliate_site_url ) ) {
										$affiliate_url = (string) $tournament->affiliate_site_url;
								}
								$show_affiliate_url = isset( $tournament->affiliate_url_visible ) ? (int) $tournament->affiliate_url_visible : 0;

								if ( '' !== $affiliate_name || '' !== $affiliate_url ) {
										echo '<p class="bhg-tournament-affiliate"><strong>' . esc_html( bhg_t( 'label_affiliate', 'Affiliate' ) ) . ':</strong> ';
										if ( $show_affiliate_url && '' !== $affiliate_url ) {
												$link_text = '' !== $affiliate_name ? $affiliate_name : $affiliate_url;
												echo '<a href="' . esc_url( $affiliate_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link_text ) . '</a>';
												if ( '' !== $affiliate_name && $link_text !== $affiliate_name ) {
														echo ' (' . esc_html( $affiliate_name ) . ')';
												}
										} elseif ( '' !== $affiliate_name ) {
												echo esc_html( $affiliate_name );
										} else {
												echo esc_html( $affiliate_url );
										}
										echo '</p>';
								}

								if ( ! empty( $prizes ) ) {
										echo '<div class="bhg-tournament-prizes">' . wp_kses_post( $this->render_prize_section( $prizes, 'grid', 'medium' ) ) . '</div>';
								}

								if ( ! $rows ) {
										echo '<p>' . esc_html( bhg_t( 'notice_no_results_yet', 'No results yet.' ) ) . '</p>';
										echo '</div>';
										return ob_get_clean();
								}

								echo '<table class="bhg-leaderboard bhg-leaderboard--tournament">';
								echo '<thead><tr>';
								echo '<th>' . esc_html( bhg_t( 'label_hash', '#' ) ) . '</th>';
								echo '<th><a href="' . esc_url( $toggle( 'username' ) ) . '">' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</a></th>';
								echo '<th><a href="' . esc_url( $toggle( 'wins' ) ) . '">' . esc_html( bhg_t( 'sc_wins', 'Wins' ) ) . '</a></th>';
								echo '<th><a href="' . esc_url( $toggle( 'last_win_at' ) ) . '">' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</a></th>';
								echo '</tr></thead><tbody>';

								$pos = 1;
								foreach ( $rows as $row ) {
										$row_classes = array( 'bhg-tournament-row' );
										if ( isset( $row->wins ) && (int) $row->wins > 0 ) {
												$row_classes[] = 'bhg-tournament-row--winner';
										}
										if ( $pos <= 3 ) {
												$row_classes[] = 'bhg-tournament-row--top-three';
										}
										if ( 1 === $pos ) {
												$row_classes[] = 'bhg-tournament-row--first';
										}
										$class_attr = ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"';

										echo '<tr' . $class_attr . '>';
										echo '<td>' . (int) $pos . '</td>';
										++$pos;
										echo '<td>' . esc_html(
												$row->user_login ? $row->user_login : sprintf(
														/* translators: %d: user ID. */
														bhg_t( 'label_user_hash', 'user#%d' ),
														(int) $row->user_id
												)
										) . '</td>';
										echo '<td>' . (int) $row->wins . '</td>';
										echo '<td>' . ( $row->last_win_date ? esc_html( mysql2date( get_option( 'date_format' ), $row->last_win_date ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ) ) . '</td>';
										echo '</tr>';
								}
								echo '</tbody></table>';
								echo '</div>';

								return ob_get_clean();
						}

						// List view with filters.
					   $a = shortcode_atts(
							   array(
									   'status'     => 'active',
									   'tournament' => 0,
									   'website'    => 0,
									   'timeline'   => '',
									   'paged'      => 1,
									   'orderby'    => 'start_date',
									   'order'      => 'desc',
									   'search'     => '',
							   ),
							   $atts,
							   'bhg_tournaments'
					   );

											   $t = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_tournaments' ) );
					   if ( ! $t ) {
									   return '';
					   }
					   $where  = array();
					   $params = array();

					   $status     = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : sanitize_key( $a['status'] );
					   $timeline   = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : sanitize_key( $a['timeline'] );
					   $tournament = absint( $a['tournament'] );
					   $website    = absint( $a['website'] );
					   $paged      = isset( $_GET['bhg_paged'] ) ? max( 1, (int) wp_unslash( $_GET['bhg_paged'] ) ) : max( 1, (int) $a['paged'] );
					   $search     = isset( $_GET['bhg_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_search'] ) ) : sanitize_text_field( $a['search'] );
					   $limit      = 30;
					   $offset     = ( $paged - 1 ) * $limit;

					   $orderby_param = isset( $_GET['bhg_orderby'] ) ? sanitize_key( wp_unslash( $_GET['bhg_orderby'] ) ) : sanitize_key( $a['orderby'] );
					   $order_param   = isset( $_GET['bhg_order'] ) ? sanitize_key( wp_unslash( $_GET['bhg_order'] ) ) : sanitize_key( $a['order'] );
					   $allowed_orderby = array(
							   'title'      => 'title',
							   'start_date' => 'start_date',
							   'end_date'   => 'end_date',
							   'status'     => 'status',
					   );
					   $orderby_column = isset( $allowed_orderby[ $orderby_param ] ) ? $allowed_orderby[ $orderby_param ] : 'start_date';
					   $order_param    = in_array( strtolower( $order_param ), array( 'asc', 'desc' ), true ) ? strtoupper( $order_param ) : 'DESC';

					   if ( $tournament > 0 ) {
							   $where[]  = 'id = %d';
							   $params[] = $tournament;
					   }
					   if ( in_array( $status, array( 'active', 'closed' ), true ) ) {
							   $where[]  = 'status = %s';
							   $params[] = $status;
					   }

					   // Accept explicit time window or derive range from timeline keyword.
					   $range = $this->get_timeline_range( $timeline );
					   if ( $range ) {
							   $range_start = substr( $range['start'], 0, 10 );
							   $range_end   = substr( $range['end'], 0, 10 );
							   $where[]     = '( (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s) )';
							   $params[]    = $range_end;
							   $params[]    = $range_start;
					   }

					   if ( $website > 0 ) {
							   $where[]  = 'affiliate_site_id = %d';
							   $params[] = $website;
					   }

					   if ( '' !== $search ) {
							   $where[]  = 'title LIKE %s';
							   $params[] = '%' . $wpdb->esc_like( $search ) . '%';
					   }

					   $where_sql = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';

					   $count_sql = "SELECT COUNT(*) FROM {$t}{$where_sql}";
					   $total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) ) : $wpdb->get_var( $count_sql ) );

					   $sql         = 'SELECT * FROM ' . $t . $where_sql . ' ORDER BY ' . $orderby_column . ' ' . $order_param . ' LIMIT %d OFFSET %d'; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Order by clause sanitized via whitelist.
					   $query_args  = array_merge( $params, array( $limit, $offset ) );
					   $rows        = $wpdb->get_results( $wpdb->prepare( $sql, ...$query_args ) ); // db call ok; no-cache ok.
					   if ( ! $rows ) {
							   return '<p>' . esc_html( bhg_t( 'notice_no_tournaments_found', 'No tournaments found.' ) ) . '</p>';
					   }

					   $current_url = isset( $_SERVER['REQUEST_URI'] )
					   ? esc_url_raw( wp_validate_redirect( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), home_url( '/' ) ) )
					   : home_url( '/' );

					   $base_url = remove_query_arg( array( 'bhg_orderby', 'bhg_order', 'bhg_paged' ), $current_url );
					   if ( '' === $search ) {
							   $base_url = remove_query_arg( 'bhg_search', $base_url );
					   }

					   ob_start();
					   echo '<form method="get" class="bhg-tournament-filters">';
											   // Keep other query args.
					   foreach ( $_GET as $raw_key => $v ) {
							   $key = sanitize_key( wp_unslash( $raw_key ) );
							   if ( in_array( $key, array( 'bhg_timeline', 'bhg_status', 'bhg_tournament_id', 'bhg_search', 'bhg_paged' ), true ) ) {
									   continue;
							   }
							   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( is_array( $v ) ? reset( $v ) : wp_unslash( $v ) ) . '">';
					   }

					   echo '<label class="bhg-tournament-label">' . esc_html( bhg_t( 'label_timeline_colon', 'Timeline:' ) ) . ' ';
					   echo '<select name="bhg_timeline">';
						$timelines    = array(
								'all_time'  => bhg_t( 'label_all_time', 'All Time' ),
								'day'       => bhg_t( 'label_today', 'Today' ),
								'week'      => bhg_t( 'label_this_week', 'This week' ),
								'month'     => bhg_t( 'label_this_month', 'This month' ),
								'year'      => bhg_t( 'label_this_year', 'This year' ),
								'last_year' => bhg_t( 'label_last_year', 'Last year' ),
								'this_week' => bhg_t( 'label_this_week_legacy', 'This week (legacy alias)' ),
								'this_month'=> bhg_t( 'label_this_month_legacy', 'This month (legacy alias)' ),
								'this_year' => bhg_t( 'label_this_year_legacy', 'This year (legacy alias)' ),
								'weekly'    => bhg_t( 'label_weekly', 'Weekly' ),
								'monthly'   => bhg_t( 'label_monthly', 'Monthly' ),
								'yearly'    => bhg_t( 'label_yearly', 'Yearly' ),
								'quarterly' => bhg_t( 'label_quarterly', 'Quarterly' ),
								'alltime'   => bhg_t( 'label_all_time', 'All-Time' ),
						);
			$timeline_key = isset( $_GET['bhg_timeline'] ) ? sanitize_key( wp_unslash( $_GET['bhg_timeline'] ) ) : $timeline;
			foreach ( $timelines as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $timeline_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></label>';

			echo '<label>' . esc_html( bhg_t( 'status', 'Status:' ) ) . ' ';
			echo '<select name="bhg_status">';
			$statuses   = array(
				'active' => bhg_t( 'label_active', 'Active' ),
				'closed' => bhg_t( 'label_closed', 'Closed' ),
				'all'    => bhg_t( 'label_all', 'All' ),
			);
			$status_key = isset( $_GET['bhg_status'] ) ? sanitize_key( wp_unslash( $_GET['bhg_status'] ) ) : $status;
			foreach ( $statuses as $key => $label ) {
				echo '<option value="' . esc_attr( $key ) . '"' . selected( $status_key, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
					   echo '</select></label> ';

					   echo '<label>' . esc_html( bhg_t( 'label_search', 'Search' ) ) . ' <input type="text" name="bhg_search" value="' . esc_attr( $search ) . '"></label> ';

					   echo '<button class="button bhg-filter-button" type="submit">' . esc_html( bhg_t( 'button_filter', 'Filter' ) ) . '</button>';
					   echo '</form>';

					   $toggle = function ( $key ) use ( $orderby_param, $order_param, $base_url, $search ) {
							   $next = ( $orderby_param === $key && 'ASC' === $order_param ) ? 'desc' : 'asc';
							   $args = array(
									   'bhg_orderby' => $key,
									   'bhg_order'   => $next,
							   );
							   if ( '' !== $search ) {
									   $args['bhg_search'] = $search;
							   }
							   return add_query_arg( $args, $base_url );
					   };

			echo '<table class="bhg-tournaments">';
			echo '<thead><tr>';
			echo '<th><a href="' . esc_url( $toggle( 'title' ) ) . '">' . esc_html( bhg_t( 'label_name', 'Name' ) ) . '</a></th>';
			echo '<th><a href="' . esc_url( $toggle( 'start_date' ) ) . '">' . esc_html( bhg_t( 'sc_start', 'Start' ) ) . '</a></th>';
			echo '<th><a href="' . esc_url( $toggle( 'end_date' ) ) . '">' . esc_html( bhg_t( 'sc_end', 'End' ) ) . '</a></th>';
			echo '<th><a href="' . esc_url( $toggle( 'status' ) ) . '">' . esc_html( bhg_t( 'sc_status', 'Status' ) ) . '</a></th>';
			echo '<th>' . esc_html( bhg_t( 'label_details', 'Details' ) ) . '</th>';
			echo '</tr></thead><tbody>';

			foreach ( $rows as $row ) {
				$detail_url = add_query_arg( 'bhg_tournament_id', (int) $row->id, remove_query_arg( array( 'orderby', 'order' ), $current_url ) );
				echo '<tr>';
				echo '<td data-label="' . esc_attr( bhg_t( 'label_name', 'Name' ) ) . '"><a href="' . esc_url( $detail_url ) . '">' . esc_html( $row->title ? $row->title : bhg_t( 'label_unnamed_tournament', 'Untitled tournament' ) ) . '</a></td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_start', 'Start' ) ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $row->start_date ) ) . '</td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_end', 'End' ) ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $row->end_date ) ) . '</td>';
				$status_key = strtolower( (string) $row->status );
				echo '<td data-label="' . esc_attr( bhg_t( 'sc_status', 'Status' ) ) . '">' . esc_html( bhg_t( $status_key, ucfirst( $status_key ) ) ) . '</td>';
				echo '<td data-label="' . esc_attr( bhg_t( 'label_details', 'Details' ) ) . '"><a href="' . esc_url( $detail_url ) . '">' . esc_html( bhg_t( 'label_show_details', 'Show details' ) ) . '</a></td>';
				echo '</tr>';
			}

					   echo '</tbody></table>';

					   $pages = (int) ceil( $total / $limit );
					   if ( $pages > 1 ) {
							   $pagination = paginate_links(
									   array(
											   'base'      => add_query_arg( 'bhg_paged', '%#%', $base_url ),
											   'format'    => '',
											   'current'   => $paged,
											   'total'     => $pages,
											   'add_args'  => array_filter(
													   array(
															   'bhg_orderby' => $orderby_param,
															   'bhg_order'   => strtolower( $order_param ),
															   'bhg_search'  => $search,
													   )
											   ),
									   )
							   );
							   if ( $pagination ) {
									   echo '<div class="bhg-pagination">' . wp_kses_post( $pagination ) . '</div>';
							   }
					   }

					   return ob_get_clean();
			   }

				/**
				 * Render standalone prizes shortcode.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function prizes_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Prizes' ) ) {
								return '';
						}

$atts = shortcode_atts(
array(
'category'         => '',
'design'           => 'grid',
'size'             => 'medium',
'active'           => 'yes',
'visible'          => '',
'limit'            => '',
'total'            => '',
'autoplay'         => '',
'interval'         => '',
'hide_heading'     => '',
'heading'          => '',
'heading_text'     => '',
'show_title'       => '',
'show_description' => '',
'show_category'    => '',
'show_image'       => '',
'category_links'   => '',
'click_action'     => '',
'link_target'      => '',
'category_target'  => '',
),
$atts,
'bhg_prizes'
);

						$args = array();

						$category = isset( $atts['category'] ) ? sanitize_key( $atts['category'] ) : '';
						if ( $category && in_array( $category, BHG_Prizes::get_categories(), true ) ) {
								$args['category'] = $category;
						}

						$active = isset( $atts['active'] ) ? strtolower( (string) $atts['active'] ) : 'yes';
						if ( in_array( $active, array( 'yes', 'no', '1', '0' ), true ) ) {
								$args['active'] = in_array( $active, array( 'yes', '1' ), true ) ? 1 : 0;
						}

						$layout = $this->normalize_prize_layout( isset( $atts['design'] ) ? $atts['design'] : 'grid' );
						$size   = $this->normalize_prize_size( isset( $atts['size'] ) ? $atts['size'] : 'medium' );

$prizes = BHG_Prizes::get_prizes( $args );

if ( empty( $prizes ) ) {
return '<div class="bhg-prizes-shortcode"><p>' . esc_html( bhg_t( 'no_prizes_yet', 'No prizes found.' ) ) . '</p></div>';
}

wp_enqueue_style(
'bhg-shortcodes',
( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
array(),
defined( 'BHG_VERSION' ) ? BHG_VERSION : null
);

$section_options = array();
$display_overrides = array();

if ( '' !== $atts['visible'] ) {
$section_options['carousel_visible'] = max( 1, absint( $atts['visible'] ) );
}

$limit_attr = '' !== $atts['limit'] ? absint( $atts['limit'] ) : ( '' !== $atts['total'] ? absint( $atts['total'] ) : 0 );
if ( $limit_attr > 0 ) {
$section_options['carousel_total'] = $limit_attr;
$section_options['limit']          = $limit_attr;
}

if ( '' !== $atts['autoplay'] ) {
$auto = $this->normalize_yes_no_attr( $atts['autoplay'], false );
$section_options['carousel_autoplay'] = '1' === $auto;
}

if ( '' !== $atts['interval'] ) {
$section_options['carousel_interval'] = max( 1000, absint( $atts['interval'] ) );
}

if ( '' !== $atts['hide_heading'] ) {
$hide = $this->normalize_yes_no_attr( $atts['hide_heading'], false );
$section_options['hide_heading'] = '1' === $hide;
}

$heading_attr = '' !== $atts['heading'] ? $atts['heading'] : $atts['heading_text'];
if ( '' !== $heading_attr ) {
$section_options['heading_text'] = sanitize_text_field( wp_unslash( $heading_attr ) );
}

foreach ( array( 'show_title', 'show_description', 'show_category', 'show_image', 'category_links' ) as $key ) {
if ( '' !== $atts[ $key ] ) {
$display_overrides[ $key ] = $this->normalize_yes_no_attr( $atts[ $key ] );
}
}

if ( '' !== $atts['click_action'] ) {
$display_overrides['click_action'] = $this->normalize_click_action_attr( $atts['click_action'] );
}

if ( '' !== $atts['link_target'] ) {
$display_overrides['link_target'] = $this->normalize_link_target_attr( $atts['link_target'] );
}

if ( '' !== $atts['category_target'] ) {
$display_overrides['category_target'] = $this->normalize_link_target_attr( $atts['category_target'] );
}

$content = $this->render_prize_section( $prizes, $layout, $size, $display_overrides, $section_options );

						if ( '' === $content ) {
								return '';
						}

						return '<div class="bhg-prizes-shortcode">' . $content . '</div>';
				}

					/**
					 * Minimal winners widget: latest closed hunts.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
public function winner_notifications_shortcode( $atts ) {
global $wpdb;

			$a = shortcode_atts(
				array( 'limit' => 5 ),
				$atts,
				'bhg_winner_notifications'
			);

						$hunts_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_table ) {
					return '';
			}
						// db call ok; no-cache ok.
						$sql                   = $wpdb->prepare(
							"SELECT id, title, final_balance, winners_count, closed_at FROM {$hunts_table} WHERE status = 'closed' ORDER BY closed_at DESC LIMIT %d",
							(int) $a['limit']
						);
										$hunts = $wpdb->get_results( $sql );

			if ( ! $hunts ) {
				return '<p>' . esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ) . '</p>';
			}

			wp_enqueue_style(
				'bhg-shortcodes',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);

			ob_start();
			echo '<div class="bhg-winner-notifications">';
			foreach ( $hunts as $hunt ) {
				$winners = function_exists( 'bhg_get_top_winners_for_hunt' )
				? bhg_get_top_winners_for_hunt( $hunt->id, (int) $hunt->winners_count )
				: array();

				echo '<div class="bhg-winner">';
				echo '<p><strong>' . esc_html( $hunt->title ) . '</strong></p>';
				if ( null !== $hunt->final_balance ) {
					echo '<p><em>' . esc_html( bhg_t( 'sc_final', 'Final' ) ) . ':</em> ' . esc_html( bhg_format_money( (float) $hunt->final_balance ) ) . '</p>';
				}

				if ( $winners ) {
					echo '<ul class="bhg-winner-list">';
					foreach ( $winners as $w ) {
						$u  = get_userdata( (int) $w->user_id );
						$nm = $u ? $u->user_login : sprintf( bhg_t( 'label_user_number', 'User #%d' ), (int) $w->user_id );
											echo '<li>' . esc_html( $nm ) . ' ' . esc_html( bhg_t( 'label_emdash', '—' ) ) . ' ' . esc_html( bhg_format_money( (float) $w->guess ) ) . ' (' . esc_html( bhg_format_money( (float) $w->diff ) ) . ')</li>';
					}
					echo '</ul>';
				}

				echo '</div>';
			}
			echo '</div>';
return ob_get_clean();
}

/**
 * Render the current user's bonus hunts list.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_bonushunts_shortcode( $atts ) {
$atts = shortcode_atts(
array(
'limit' => 50,
),
$atts,
'my_bonushunts'
);

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_bonushunts' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$limit   = max( 1, min( 200, (int) $atts['limit'] ) );
$rows    = $this->get_user_bonus_hunt_rows( $user_id, $limit );

$currency_callback = function_exists( 'bhg_format_money' ) ? 'bhg_format_money' : static function( $value ) {
return number_format_i18n( $value, 2 );
};

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--hunts">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_bonushunts', 'My Bonus Hunts' ) ) . '</h2>';

if ( empty( $rows ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_hunts_user', 'You have not participated in any bonus hunts yet.' ) ) . '</p>';
} else {
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_guess', 'Guess' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $rows as $row ) {
$guess_display = call_user_func( $currency_callback, $row['guess'] );
$final_display = null === $row['final_balance'] ? null : call_user_func( $currency_callback, $row['final_balance'] );
$diff_display  = null === $row['difference'] ? null : call_user_func( $currency_callback, $row['difference'] );
$closed_at     = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : null;
$classes       = array( 'bhg-profile-row' );

if ( ! empty( $row['is_winner'] ) ) {
$classes[] = 'bhg-profile-row--winner';
}

echo '<tr class="' . esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ) . '">';
echo '<td>' . esc_html( $row['title'] ) . '</td>';
echo '<td>' . esc_html( $guess_display ) . '</td>';
echo '<td>' . ( null === $final_display ? '&mdash;' : esc_html( $final_display ) ) . '</td>';
echo '<td>' . ( null === $diff_display ? '&mdash;' : esc_html( $diff_display ) ) . '</td>';
echo '<td>' . ( $row['position'] > 0 ? (int) $row['position'] : '&mdash;' ) . '</td>';
echo '<td>' . ( $closed_at ? esc_html( $closed_at ) : '&mdash;' ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's tournament standings.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_tournaments_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_tournaments' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id     = get_current_user_id();
$tournaments = $this->get_user_tournament_rows( $user_id );

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--tournaments">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_tournaments', 'My Tournaments' ) ) . '</h2>';

if ( empty( $tournaments ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_tournaments_user', 'You have not participated in any tournaments yet.' ) ) . '</p>';
} else {
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_wins', 'Wins' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $tournaments as $row ) {
$last_win = $row['last_win_date'] ? mysql2date( get_option( 'date_format' ), $row['last_win_date'] ) : '';
echo '<tr>';
echo '<td>' . esc_html( $row['title'] ) . '</td>';
echo '<td>' . esc_html( (int) $row['points'] ) . '</td>';
echo '<td>' . esc_html( (int) $row['wins'] ) . '</td>';
echo '<td>' . ( null === $row['rank'] ? '&mdash;' : esc_html( (string) (int) $row['rank'] ) ) . '</td>';
echo '<td>' . ( $last_win ? esc_html( $last_win ) : '&mdash;' ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's prize history.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_prizes_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_prizes' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$prizes  = $this->get_user_prize_rows( $user_id );

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--prizes">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_prizes', 'My Prizes' ) ) . '</h2>';

if ( empty( $prizes ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_prizes_user', 'You have not won any prizes yet.' ) ) . '</p>';
} else {
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_prize', 'Prize' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_category', 'Category' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $prizes as $row ) {
$closed_at = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : '';
echo '<tr>';
echo '<td>' . esc_html( $row['title'] ) . '</td>';
echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $row['category'] ) ) ) . '</td>';
echo '<td>' . esc_html( $row['hunt_title'] ) . '</td>';
echo '<td>' . ( $row['position'] > 0 ? esc_html( (string) (int) $row['position'] ) : '&mdash;' ) . '</td>';
echo '<td>' . ( $closed_at ? esc_html( $closed_at ) : '&mdash;' ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

echo '</div>';

return ob_get_clean();
}

/**
 * Render the current user's ranking summary.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
public function my_rankings_shortcode( $atts ) {
unset( $atts );

if ( ! is_user_logged_in() ) {
return '<p>' . esc_html( bhg_t( 'notice_profile_login_required', 'Please log in to view this section.' ) ) . '</p>';
}

if ( ! $this->is_profile_section_enabled( 'my_rankings' ) ) {
return '';
}

$this->enqueue_profile_assets();

$user_id = get_current_user_id();
$hunts   = $this->get_user_bonus_hunt_rows( $user_id, 200 );
$wins    = array_filter(
$hunts,
static function( $row ) {
return ! empty( $row['is_winner'] );
}
);
$tournaments = $this->get_user_tournament_rows( $user_id );

$total_points = 0;
$best_rank    = null;

foreach ( $tournaments as $row ) {
$total_points += (int) $row['points'];
if ( isset( $row['rank'] ) && $row['rank'] && ( null === $best_rank || (int) $row['rank'] < $best_rank ) ) {
$best_rank = (int) $row['rank'];
}
}

$currency_callback = function_exists( 'bhg_format_money' ) ? 'bhg_format_money' : static function( $value ) {
return number_format_i18n( $value, 2 );
};

ob_start();
echo '<div class="bhg-profile-section bhg-profile-section--rankings">';
echo '<h2>' . esc_html( bhg_t( 'profile_section_my_rankings', 'My Rankings' ) ) . '</h2>';

echo '<ul class="bhg-profile-summary">';
echo '<li>' . esc_html( bhg_t( 'label_total_hunt_wins', 'Total hunt wins' ) ) . ': ' . esc_html( (string) count( $wins ) ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_total_tournament_points', 'Total tournament points' ) ) . ': ' . esc_html( (string) $total_points ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_best_tournament_rank', 'Best tournament rank' ) ) . ': ' . ( null === $best_rank ? '&mdash;' : esc_html( (string) $best_rank ) ) . '</li>';
echo '<li>' . esc_html( bhg_t( 'label_tournaments_played', 'Tournaments played' ) ) . ': ' . esc_html( (string) count( $tournaments ) ) . '</li>';
echo '</ul>';

if ( empty( $wins ) && empty( $tournaments ) ) {
echo '<p>' . esc_html( bhg_t( 'notice_no_rankings_user', 'No ranking data is available yet.' ) ) . '</p>';
} else {
if ( ! empty( $wins ) ) {
echo '<h3>' . esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ) . '</h3>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_position', 'Position' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_difference', 'Difference' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $wins as $row ) {
$diff_display  = null === $row['difference'] ? null : call_user_func( $currency_callback, $row['difference'] );
$closed_at     = $row['closed_at'] ? mysql2date( get_option( 'date_format' ), $row['closed_at'] ) : '';

echo '<tr>';
echo '<td>' . esc_html( $row['title'] ) . '</td>';
echo '<td>' . esc_html( (string) (int) $row['position'] ) . '</td>';
echo '<td>' . ( null === $diff_display ? '&mdash;' : esc_html( $diff_display ) ) . '</td>';
echo '<td>' . ( $closed_at ? esc_html( $closed_at ) : '&mdash;' ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}

if ( ! empty( $tournaments ) ) {
echo '<h3>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</h3>';
echo '<table class="bhg-profile-table"><thead><tr>';
echo '<th>' . esc_html( bhg_t( 'label_tournament', 'Tournament' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_points', 'Points' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_wins', 'Wins' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_rank', 'Rank' ) ) . '</th>';
echo '<th>' . esc_html( bhg_t( 'label_last_win', 'Last win' ) ) . '</th>';
echo '</tr></thead><tbody>';

foreach ( $tournaments as $row ) {
$last_win = $row['last_win_date'] ? mysql2date( get_option( 'date_format' ), $row['last_win_date'] ) : '';

echo '<tr>';
echo '<td>' . esc_html( $row['title'] ) . '</td>';
echo '<td>' . esc_html( (string) (int) $row['points'] ) . '</td>';
echo '<td>' . esc_html( (string) (int) $row['wins'] ) . '</td>';
echo '<td>' . ( null === $row['rank'] ? '&mdash;' : esc_html( (string) (int) $row['rank'] ) ) . '</td>';
echo '<td>' . ( $last_win ? esc_html( $last_win ) : '&mdash;' ) . '</td>';
echo '</tr>';
}

echo '</tbody></table>';
}
}

echo '</div>';

return ob_get_clean();
}

					/**
					 * Minimal profile view: affiliate status badge.
					 *
					 * @param array $atts Shortcode attributes.
					 * @return string HTML output.
					 */
			   public function user_profile_shortcode( $atts ) {
					   unset( $atts ); // Parameter unused but kept for shortcode signature.
					   if ( ! is_user_logged_in() ) {
							   return '<p>' . esc_html( bhg_t( 'notice_login_view_content', 'Please log in to view this content.' ) ) . '</p>';
					   }
					   wp_enqueue_style(
							   'bhg-shortcodes',
							   ( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
							   array(),
							   defined( 'BHG_VERSION' ) ? BHG_VERSION : null
					   );
					  $user        = wp_get_current_user();
					  $user_id     = $user->ID;
					  $real_name   = trim( (string) get_user_meta( $user_id, 'bhg_real_name', true ) );
					  if ( '' === $real_name ) {
							  $real_name = trim( $user->get( 'first_name' ) . ' ' . $user->get( 'last_name' ) );
					  }
					  if ( '' === $real_name ) {
							  $real_name = (string) $user->display_name;
					  }
					   $username    = $user->user_login;
					   $email       = $user->user_email;
					   $is_affiliate = (int) get_user_meta( $user_id, 'bhg_is_affiliate', true );
					   $badge       = $is_affiliate ? '<span class="bhg-aff-green" aria-hidden="true"></span>' : '<span class="bhg-aff-red" aria-hidden="true"></span>';
					   $aff_text    = $is_affiliate ? bhg_t( 'label_affiliate', 'Affiliate' ) : bhg_t( 'label_not_affiliate', 'Not Affiliate' );
					   $edit_link   = '';
					   if ( current_user_can( 'edit_user', $user_id ) ) {
							   $edit_link = get_edit_user_link( $user_id );
					   }
					  $output  = '<div class="bhg-user-profile"><table class="bhg-user-profile-table">';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_name', 'Name' ) ) . '</th><td>' . esc_html( $real_name ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_username', 'Username' ) ) . '</th><td>' . esc_html( $username ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_email', 'Email' ) ) . '</th><td>' . esc_html( $email ) . '</td></tr>';
					  $output .= '<tr><th>' . esc_html( bhg_t( 'label_affiliate_status', 'Affiliate Status' ) ) . '</th><td>' . wp_kses_post( $badge ) . ' ' . esc_html( $aff_text ) . '</td></tr>';

					  $site_rows = array();
					  if ( function_exists( 'bhg_get_user_affiliate_websites' ) ) {
							  $site_ids = array_filter( array_map( 'absint', (array) bhg_get_user_affiliate_websites( (int) $user_id ) ) );
							  if ( $site_ids ) {
									  global $wpdb;
									  $sites_table = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_affiliate_websites' ) );
									  if ( $sites_table ) {
											  $placeholders = implode( ',', array_fill( 0, count( $site_ids ), '%d' ) );
											  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $placeholders created using prepare-safe values.
											  $query = "SELECT id, name FROM {$sites_table} WHERE id IN ({$placeholders}) ORDER BY id ASC";
											  // db call ok; no-cache ok.
											  $site_rows = $wpdb->get_results( $wpdb->prepare( $query, $site_ids ) );
									  }
							  }
					  }

					  if ( $site_rows ) {
							  $count = 1;
							  foreach ( $site_rows as $site_row ) {
									  $label = sprintf( bhg_t( 'label_affiliate_website_number', 'Affiliate Website %d' ), $count );
									  $output .= '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $site_row->name ) . '</td></tr>';
									  $count++;
							  }
					  }

					  $output .= '</table>';
					   if ( $edit_link ) {
							   $output .= '<p><a href="' . esc_url( $edit_link ) . '">' . esc_html( bhg_t( 'link_edit_profile', 'Edit Profile' ) ) . '</a></p>';
					   }
					   $output .= '</div>';
					   return $output;
			   }

				/**
				 * Render the current jackpot amount.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function jackpot_current_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Jackpots' ) ) {
								return '';
						}

						$atts = shortcode_atts(
								array(
										'id' => 0,
								),
								$atts,
								'bhg_jackpot_current'
						);

						$jackpot_id = absint( $atts['id'] );

						if ( $jackpot_id <= 0 ) {
								return '';
						}

						$amount = BHG_Jackpots::instance()->get_formatted_amount( $jackpot_id );

						if ( '' === $amount ) {
								return '';
						}

						return '<span class="bhg-jackpot-amount" data-jackpot-id="' . esc_attr( $jackpot_id ) . '">' . esc_html( $amount ) . '</span>';
				}

				/**
				 * Render a list of the latest jackpot hits.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function jackpot_latest_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Jackpots' ) ) {
								return '';
						}

						$atts = shortcode_atts(
								array(
										'limit'     => 5,
										'affiliate' => 0,
										'year'      => 0,
										'empty'     => '',
								),
								$atts,
								'bhg_jackpot_latest'
						);

						$limit = max( 1, absint( $atts['limit'] ) );
						$args  = array( 'limit' => $limit );

						if ( ! empty( $atts['affiliate'] ) ) {
								$args['affiliate'] = absint( $atts['affiliate'] );
						}

						if ( ! empty( $atts['year'] ) ) {
								$args['year'] = absint( $atts['year'] );
						}

						$rows = BHG_Jackpots::instance()->get_latest_hits( $args );

						if ( empty( $rows ) ) {
								return $atts['empty'] ? '<div class="bhg-jackpot-latest-empty">' . esc_html( $atts['empty'] ) . '</div>' : '';
						}

						$format_amount = static function ( $amount ) {
								if ( function_exists( 'bhg_format_money' ) ) {
										return bhg_format_money( $amount );
								}

								return number_format_i18n( (float) $amount, 2 );
						};

						ob_start();
						echo '<div class="bhg-jackpot-latest"><ul class="bhg-jackpot-latest-list">';
						foreach ( $rows as $row ) {
								$winner     = '';
								$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
								$amount     = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
								$title      = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
								$created_at = isset( $row['created_at'] ) ? $row['created_at'] : '';
								$hunt_title = isset( $row['hunt_title'] ) ? $row['hunt_title'] : '';

								if ( $user_id ) {
										$user = get_userdata( $user_id );
										if ( $user ) {
												$winner = $user->display_name;
										}
								}

								echo '<li class="bhg-jackpot-latest-item">';
								if ( $title ) {
										echo '<span class="bhg-jackpot-name">' . esc_html( $title ) . '</span> ';
								}
								echo '<span class="bhg-jackpot-winning-amount">' . esc_html( $format_amount( $amount ) ) . '</span>';
								if ( $winner ) {
										echo ' <span class="bhg-jackpot-winner">' . esc_html( $winner ) . '</span>';
								}
								if ( $hunt_title ) {
										echo ' <span class="bhg-jackpot-hunt">' . esc_html( $hunt_title ) . '</span>';
								}
								if ( $created_at ) {
										echo ' <time datetime="' . esc_attr( $created_at ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $created_at ) ) . '</time>';
								}
								echo '</li>';
						}
						echo '</ul></div>';

						return ob_get_clean();
				}

				/**
				 * Render a ticker of jackpots or winners.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function jackpot_ticker_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Jackpots' ) ) {
								return '';
						}

						$atts = shortcode_atts(
								array(
										'mode' => 'amount',
								),
								$atts,
								'bhg_jackpot_ticker'
						);

						$mode    = sanitize_key( $atts['mode'] );
						$jackets = BHG_Jackpots::instance()->get_ticker_items( $mode );

						if ( empty( $jackets ) ) {
								return '';
						}

						$format_amount = static function ( $amount ) {
								if ( function_exists( 'bhg_format_money' ) ) {
										return bhg_format_money( $amount );
								}

								return number_format_i18n( (float) $amount, 2 );
						};

						ob_start();
						echo '<div class="bhg-jackpot-ticker bhg-jackpot-ticker--' . esc_attr( $mode ) . '"><ul>';
						if ( 'winners' === $mode ) {
								foreach ( $jackets as $row ) {
										$user_label = '';
										$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
										if ( $user_id ) {
												$user = get_userdata( $user_id );
												if ( $user ) {
														$user_label = $user->display_name;
												}
										}
										$amount = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
										$title  = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
										echo '<li><span class="bhg-ticker-jackpot">' . esc_html( $title ) . '</span> <span class="bhg-ticker-amount">' . esc_html( $format_amount( $amount ) ) . '</span>';
										if ( $user_label ) {
												echo ' <span class="bhg-ticker-winner">' . esc_html( $user_label ) . '</span>';
										}
										echo '</li>';
								}
						} else {
								foreach ( $jackets as $row ) {
										$title  = isset( $row['title'] ) ? $row['title'] : '';
										$amount = isset( $row['current_amount'] ) ? (float) $row['current_amount'] : 0.0;
										echo '<li><span class="bhg-ticker-jackpot">' . esc_html( $title ) . '</span> <span class="bhg-ticker-amount">' . esc_html( $format_amount( $amount ) ) . '</span></li>';
								}
						}
						echo '</ul></div>';

						return ob_get_clean();
				}

				/**
				 * Render a winners list/table for jackpots.
				 *
				 * @param array $atts Shortcode attributes.
				 * @return string
				 */
				public function jackpot_winners_shortcode( $atts ) {
						if ( ! class_exists( 'BHG_Jackpots' ) ) {
								return '';
						}

						$atts = shortcode_atts(
								array(
										'layout'    => 'table',
										'limit'     => 10,
										'affiliate' => 0,
										'year'      => 0,
										'empty'     => '',
								),
								$atts,
								'bhg_jackpot_winners'
						);

						$args = array( 'limit' => max( 1, absint( $atts['limit'] ) ) );

						if ( ! empty( $atts['affiliate'] ) ) {
								$args['affiliate'] = absint( $atts['affiliate'] );
						}

						if ( ! empty( $atts['year'] ) ) {
								$args['year'] = absint( $atts['year'] );
						}

						$rows = BHG_Jackpots::instance()->get_winner_rows( $args );

						if ( empty( $rows ) ) {
								return $atts['empty'] ? '<div class="bhg-jackpot-winners-empty">' . esc_html( $atts['empty'] ) . '</div>' : '';
						}

						$layout         = sanitize_key( $atts['layout'] );
						$format_amount  = static function ( $amount ) {
								if ( function_exists( 'bhg_format_money' ) ) {
										return bhg_format_money( $amount );
								}

								return number_format_i18n( (float) $amount, 2 );
						};

						if ( 'list' === $layout ) {
								ob_start();
								echo '<ul class="bhg-jackpot-winners">';
								foreach ( $rows as $row ) {
										$title      = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
										$amount     = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
										$created_at = isset( $row['created_at'] ) ? $row['created_at'] : '';
										$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
										$hunt_title = isset( $row['hunt_title'] ) ? $row['hunt_title'] : '';
										$user_name  = '';
										if ( $user_id ) {
												$user = get_userdata( $user_id );
												if ( $user ) {
														$user_name = $user->display_name;
												}
										}
										echo '<li class="bhg-jackpot-winner">';
										if ( $title ) {
												echo '<span class="bhg-jackpot-name">' . esc_html( $title ) . '</span> ';
										}
										echo '<span class="bhg-jackpot-amount">' . esc_html( $format_amount( $amount ) ) . '</span>';
										if ( $user_name ) {
												echo ' <span class="bhg-jackpot-winner-name">' . esc_html( $user_name ) . '</span>';
										}
										if ( $hunt_title ) {
												echo ' <span class="bhg-jackpot-hunt">' . esc_html( $hunt_title ) . '</span>';
										}
										if ( $created_at ) {
												echo ' <time datetime="' . esc_attr( $created_at ) . '">' . esc_html( mysql2date( get_option( 'date_format' ), $created_at ) ) . '</time>';
										}
										echo '</li>';
								}
								echo '</ul>';
								return ob_get_clean();
						}

						ob_start();
						echo '<table class="bhg-jackpot-winners-table"><thead><tr>';
						echo '<th>' . esc_html( bhg_t( 'label_title', 'Title' ) ) . '</th>';
						echo '<th>' . esc_html( bhg_t( 'label_amount', 'Amount' ) ) . '</th>';
						echo '<th>' . esc_html( bhg_t( 'label_winner', 'Winner' ) ) . '</th>';
						echo '<th>' . esc_html( bhg_t( 'label_date', 'Date' ) ) . '</th>';
						echo '<th>' . esc_html( bhg_t( 'label_bonus_hunt', 'Bonus Hunt' ) ) . '</th>';
						echo '</tr></thead><tbody>';
						foreach ( $rows as $row ) {
								$title      = isset( $row['jackpot_title'] ) ? $row['jackpot_title'] : '';
								$amount     = isset( $row['amount_after'] ) ? (float) $row['amount_after'] : 0.0;
								$created_at = isset( $row['created_at'] ) ? $row['created_at'] : '';
								$user_id    = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
								$hunt_title = isset( $row['hunt_title'] ) ? $row['hunt_title'] : '';
								$user_name  = '';
								if ( $user_id ) {
										$user = get_userdata( $user_id );
										if ( $user ) {
												$user_name = $user->display_name;
										}
								}
								echo '<tr>';
								echo '<td>' . esc_html( $title ) . '</td>';
								echo '<td>' . esc_html( $format_amount( $amount ) ) . '</td>';
								echo '<td>' . esc_html( $user_name ) . '</td>';
								echo '<td>' . esc_html( $created_at ? mysql2date( get_option( 'date_format' ), $created_at ) : '' ) . '</td>';
								echo '<td>' . esc_html( $hunt_title ) . '</td>';
								echo '</tr>';
						}
						echo '</tbody></table>';

						return ob_get_clean();
				}

										/**
										 * Simple wins leaderboard with tabs.
										 *
										 * @param array $atts Shortcode attributes.
										 * @return string HTML output.
										 */
		public function best_guessers_shortcode( $atts ) {
			global $wpdb;

						$wins_tbl  = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
						$tours_tbl = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
						$users_tbl = esc_sql( $wpdb->users );

			$now_ts        = time();
			$current_month = wp_date( 'Y-m', $now_ts );
			$current_year  = wp_date( 'Y', $now_ts );

			$periods = array(
								'overall' => array(
										'label' => esc_html( bhg_t( 'label_overall', 'Overall' ) ),
										'start' => '',
										'end'   => '',
								),
								'monthly' => array(
										'label' => esc_html( bhg_t( 'label_monthly', 'Monthly' ) ),
										'start' => $current_month . '-01',
										'end'   => wp_date( 'Y-m-t', strtotime( $current_month . '-01', $now_ts ) ),
								),
								'yearly'  => array(
										'label' => esc_html( bhg_t( 'label_yearly', 'Yearly' ) ),
										'start' => $current_year . '-01-01',
										'end'   => $current_year . '-12-31',
								),
								'alltime' => array(
										'label' => esc_html( bhg_t( 'label_all_time', 'All-Time' ) ),
										'start' => '',
										'end'   => '',
								),
			);

			$results = array();


			foreach ( $periods as $key => $info ) {
				$where_clauses = array();
				$params        = array();

				if ( ! empty( $info['start'] ) && ! empty( $info['end'] ) ) {
					$where_clauses[] = 't.start_date IS NOT NULL';
					$where_clauses[] = 't.end_date IS NOT NULL';
					$where_clauses[] = 't.start_date >= %s';
					$where_clauses[] = 't.end_date <= %s';
					$params[]        = $info['start'];
					$params[]        = $info['end'];
				} elseif ( 'alltime' === $key ) {
					$where_clauses[] = '(t.start_date IS NULL OR t.start_date = "0000-00-00")';
					$where_clauses[] = '(t.end_date IS NULL OR t.end_date = "0000-00-00")';
				}

				$sql = 'SELECT u.ID as user_id, u.user_login, SUM(r.wins) as total_wins'
					. " FROM {$wins_tbl} r"
					. " INNER JOIN {$users_tbl} u ON u.ID = r.user_id"
					. " INNER JOIN {$tours_tbl} t ON t.id = r.tournament_id";

				if ( ! empty( $where_clauses ) ) {
					$sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
				}

				$sql .= "
											GROUP BY u.ID, u.user_login";

				if ( ! empty( $params ) ) {
					$sql = $wpdb->prepare( $sql, ...$params );
				}

				$sql               .= ' ORDER BY total_wins DESC, u.user_login ASC LIMIT 50';
				$results[ $key ] = $wpdb->get_results( $sql );
			}

						$hunts_tbl = esc_sql( $this->sanitize_table( $wpdb->prefix . 'bhg_bonus_hunts' ) );
			if ( ! $hunts_tbl ) {
					return '';
			}
						$hunts_sql             = "SELECT id, title FROM {$hunts_tbl} WHERE status = 'closed' ORDER BY created_at DESC LIMIT 50";
										$hunts = $wpdb->get_results( $hunts_sql );

				wp_enqueue_style(
					'bhg-shortcodes',
					( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/css/bhg-shortcodes.css',
					array(),
					defined( 'BHG_VERSION' ) ? BHG_VERSION : null
				);
			wp_enqueue_script(
				'bhg-shortcodes-js',
				( defined( 'BHG_PLUGIN_URL' ) ? BHG_PLUGIN_URL : plugins_url( '/', __FILE__ ) ) . 'assets/js/bhg-shortcodes.js',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
				true
			);

				ob_start();
				echo '<ul class="bhg-tabs">';
				$first = true;
			foreach ( $periods as $key => $info ) {
				if ( $first ) {
					echo '<li class="active"><a href="#bhg-tab-' . esc_html( $key ) . '">' . esc_html( $info['label'] ) . '</a></li>';
					$first = false;
				} else {
						echo '<li><a href="#bhg-tab-' . esc_html( $key ) . '">' . esc_html( $info['label'] ) . '</a></li>';
				}
			}
			if ( $hunts ) {
					echo '<li><a href="#bhg-tab-hunts">' . esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ) . '</a></li>';
			}
				echo '</ul>';

				$first = true;
			foreach ( $periods as $key => $info ) {
					$classes = 'bhg-tab-pane';
				if ( $first ) {
						$classes .= ' active';
						$first    = false;
				}
					echo '<div id="bhg-tab-' . esc_attr( $key ) . '" class="' . esc_attr( $classes ) . '">';
					$rows = isset( $results[ $key ] ) ? $results[ $key ] : array();
				if ( ! $rows ) {
						echo '<p>' . esc_html( bhg_t( 'notice_no_data_yet', 'No data yet.' ) ) . '</p>';
				} else {
					echo '<table class="bhg-leaderboard"><thead><tr><th>' . esc_html( bhg_t( 'label_hash', '#' ) ) . '</th><th>' . esc_html( bhg_t( 'sc_user', 'User' ) ) . '</th><th>' . esc_html( bhg_t( 'sc_wins', 'Wins' ) ) . '</th></tr></thead><tbody>';
						$pos = 1;
					foreach ( $rows as $r ) {
							/* translators: %d: user ID. */
							$user_label = $r->user_login ? $r->user_login : sprintf( bhg_t( 'label_user_hash', 'user#%d' ), (int) $r->user_id );
							echo '<tr><td>' . (int) $pos . '</td><td>' . esc_html( $user_label ) . '</td><td>' . (int) $r->total_wins . '</td></tr>';
							++$pos;
					}
						echo '</tbody></table>';
				}
					echo '</div>';
			}

			if ( $hunts ) {
				$raw  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
				$base = esc_url_raw( remove_query_arg( 'hunt_id', wp_validate_redirect( $raw, home_url( '/' ) ) ) );
				echo '<div id="bhg-tab-hunts" class="bhg-tab-pane">';
				echo '<ul class="bhg-hunt-history">';
				foreach ( $hunts as $hunt ) {
						$url = add_query_arg( 'hunt_id', (int) $hunt->id, $base );
						echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $hunt->title ) . '</a></li>';
				}
				echo '</ul>';
				echo '</div>';
			}

				return ob_get_clean();
		}
	}
}

// Register once on init even if no other bootstrap instantiates the class.
if ( ! function_exists( 'bhg_register_shortcodes_once' ) ) {
		/**
		 * Register shortcodes once on init.
		 *
		 * @return void
		 */
	function bhg_register_shortcodes_once() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed
			static $done = false;
		if ( $done ) {
				return;
		}
			$done = true;
		if ( class_exists( 'BHG_Shortcodes' ) ) {
				new BHG_Shortcodes();
		}
	}
		add_action( 'init', 'bhg_register_shortcodes_once', 20 );
}