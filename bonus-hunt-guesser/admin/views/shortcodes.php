<?php
/**
 * Shortcodes reference screen.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shortcodes = array(
	array(
		'tag'         => 'bhg_active_hunt',
		'description' => bhg_t( 'shortcode_active_hunt_desc', 'Displays the currently active bonus hunt with prize details and participant highlights.' ),
		'attributes'  => array(
			'prize_layout' => bhg_t( 'shortcode_attr_prize_layout', 'Layout for prize cards: grid or carousel.' ),
			'prize_size'   => bhg_t( 'shortcode_attr_prize_size', 'Prize card size: small, medium, or big.' ),
		),
		'aliases'     => array( 'bhg_active' ),
	),
	array(
		'tag'         => 'bhg_guess_form',
		'description' => bhg_t( 'shortcode_guess_form_desc', 'Shows the guess submission form for the selected hunt.' ),
		'attributes'  => array(
			'hunt_id' => bhg_t( 'shortcode_attr_hunt_id', 'Optional numeric hunt ID; defaults to the latest open hunt.' ),
		),
	),
	array(
		'tag'         => 'bhg_leaderboard',
		'description' => bhg_t( 'shortcode_leaderboard_desc', 'Leaderboard table for a specific bonus hunt.' ),
		'attributes'  => array(
			'hunt_id'  => bhg_t( 'shortcode_attr_leaderboard_hunt', 'Numeric hunt ID (defaults to latest closed hunt).' ),
			'orderby'  => bhg_t( 'shortcode_attr_leaderboard_orderby', 'Sort column: guess, user, or position.' ),
			'order'    => bhg_t( 'shortcode_attr_leaderboard_order', 'Sort direction: ASC or DESC.' ),
			'fields'   => bhg_t( 'shortcode_attr_leaderboard_fields', 'Comma list of columns to show (position,user,guess).' ),
			'paged'    => bhg_t( 'shortcode_attr_leaderboard_paged', 'Starting page number (defaults to 1).' ),
			'per_page' => bhg_t( 'shortcode_attr_leaderboard_per_page', 'Rows per page (defaults to 30).' ),
			'search'   => bhg_t( 'shortcode_attr_leaderboard_search', 'Default search term for usernames.' ),
		),
		'aliases'     => array( 'bonus_hunt_leaderboard' ),
	),
	array(
		'tag'         => 'bhg_user_guesses',
		'description' => bhg_t( 'shortcode_user_guesses_desc', 'Paginated list of guesses with timeline and affiliate filters.' ),
		'attributes'  => array(
			'id'       => bhg_t( 'shortcode_attr_user_guesses_id', 'Limit results to a specific hunt ID.' ),
			'aff'      => bhg_t( 'shortcode_attr_user_guesses_aff', 'yes or no to require affiliate users.' ),
			'website'  => bhg_t( 'shortcode_attr_user_guesses_website', 'Affiliate website ID filter.' ),
			'status'   => bhg_t( 'shortcode_attr_user_guesses_status', 'Hunt status filter: open or closed.' ),
			'timeline' => bhg_t( 'shortcode_attr_timeline', 'Limit guesses by timeline (week, month, year, last_year, all_time).' ),
			'fields'   => bhg_t( 'shortcode_attr_user_guesses_fields', 'Columns to display (hunt,user,guess,final,site).' ),
			'orderby'  => bhg_t( 'shortcode_attr_orderby', 'Default sort column (hunt, guess, final, difference).' ),
			'order'    => bhg_t( 'shortcode_attr_order', 'Sort direction: ASC or DESC.' ),
			'paged'    => bhg_t( 'shortcode_attr_paged', 'Starting page for the table (defaults to 1).' ),
			'search'   => bhg_t( 'shortcode_attr_search', 'Default search term applied to the view.' ),
		),
	),
	array(
		'tag'         => 'bhg_hunts',
		'description' => bhg_t( 'shortcode_hunts_desc', 'Directory of bonus hunts with status, prize, and site filters.' ),
		'attributes'  => array(
			'id'       => bhg_t( 'shortcode_attr_hunts_id', 'Display a specific hunt ID (optional).' ),
			'aff'      => bhg_t( 'shortcode_attr_hunts_aff', 'yes to show only affiliate hunts; no for non-affiliate.' ),
			'website'  => bhg_t( 'shortcode_attr_hunts_site', 'Affiliate website ID filter.' ),
			'status'   => bhg_t( 'shortcode_attr_hunts_status', 'Filter by hunt status: open or closed.' ),
			'timeline' => bhg_t( 'shortcode_attr_timeline', 'Limit hunts by timeline (week, month, quarter, year, all_time).' ),
			'fields'   => bhg_t( 'shortcode_attr_hunts_fields', 'Columns to display (title,start,final,winners,status,site).' ),
			'orderby'  => bhg_t( 'shortcode_attr_orderby', 'Default sort column (title,start,final,winners,status,created).' ),
			'order'    => bhg_t( 'shortcode_attr_order', 'Sort direction: ASC or DESC.' ),
			'paged'    => bhg_t( 'shortcode_attr_paged', 'Starting page number for the table.' ),
			'search'   => bhg_t( 'shortcode_attr_search', 'Default search string for hunt titles.' ),
		),
	),
	array(
		'tag'         => 'bhg_leaderboards',
		'description' => bhg_t( 'shortcode_leaderboards_desc', 'Overall rankings with sortable wins and averages.' ),
		'attributes'  => array(
			'fields'     => bhg_t( 'shortcode_attr_leaderboards_fields', 'Columns to display (pos,user,wins,avg,aff,site,hunt,tournament).' ),
			'ranking'    => bhg_t( 'shortcode_attr_leaderboards_ranking', 'Ranking scope ID or slug to load (defaults to 1).' ),
			'timeline'   => bhg_t( 'shortcode_attr_timeline', 'Timeline keyword for filtering results.' ),
			'orderby'    => bhg_t( 'shortcode_attr_orderby', 'Default sort column (wins, avg, user, etc.).' ),
			'order'      => bhg_t( 'shortcode_attr_order', 'Sort direction: ASC or DESC.' ),
			'search'     => bhg_t( 'shortcode_attr_search', 'Default search term for usernames.' ),
			'tournament' => bhg_t( 'shortcode_attr_leaderboards_tournament', 'Limit rankings to a specific tournament ID.' ),
			'bonushunt'  => bhg_t( 'shortcode_attr_leaderboards_hunt', 'Limit rankings to a specific hunt ID.' ),
			'website'    => bhg_t( 'shortcode_attr_leaderboards_site', 'Filter by affiliate website ID.' ),
			'aff'        => bhg_t( 'shortcode_attr_leaderboards_aff', 'yes or no to show only affiliate winners.' ),
		),
	),
	array(
		'tag'         => 'bhg_tournaments',
		'description' => bhg_t( 'shortcode_tournaments_desc', 'Lists tournaments with filters for timeline, status, and search.' ),
		'attributes'  => array(),
		'notes'       => bhg_t( 'shortcode_tournaments_notes', 'Use query parameters (bhg_timeline, bhg_status, bhg_search, bhg_orderby, bhg_order) to filter the table.' ),
	),
	array(
		'tag'         => 'bhg_prizes',
		'description' => bhg_t( 'shortcode_prizes_desc', 'Displays prizes in a grid or carousel layout.' ),
'attributes'  => array(
'category'        => bhg_t( 'shortcode_attr_prizes_category', 'Filter by prize category slug.' ),
'design'          => bhg_t( 'shortcode_attr_prizes_design', 'Layout style: grid or carousel.' ),
'size'            => bhg_t( 'shortcode_attr_prizes_size', 'Prize card size: small, medium, or big.' ),
'active'          => bhg_t( 'shortcode_attr_prizes_active', 'yes to show only active prizes; no to include archived ones.' ),
'visible'         => bhg_t( 'shortcode_attr_prizes_visible', 'Number of cards visible in carousel mode (defaults to saved setting).' ),
'limit'           => bhg_t( 'shortcode_attr_prizes_limit', 'Maximum number of prizes to render (0 shows all).' ),
'autoplay'        => bhg_t( 'shortcode_attr_prizes_autoplay', 'yes or no to enable automatic carousel rotation.' ),
'interval'        => bhg_t( 'shortcode_attr_prizes_interval', 'Autoplay interval in milliseconds (minimum 1000).' ),
'hide_heading'    => bhg_t( 'shortcode_attr_prizes_hide_heading', 'yes to hide the default heading.' ),
'heading'         => bhg_t( 'shortcode_attr_prizes_heading', 'Custom heading text to display above the section.' ),
'show_title'      => bhg_t( 'shortcode_attr_prizes_show_title', 'yes/no to override showing the prize title.' ),
'show_description'=> bhg_t( 'shortcode_attr_prizes_show_description', 'yes/no to override showing the description.' ),
'show_category'   => bhg_t( 'shortcode_attr_prizes_show_category', 'yes/no to override showing the category badge.' ),
'show_image'      => bhg_t( 'shortcode_attr_prizes_show_image', 'yes/no to override showing the prize image.' ),
'category_links'  => bhg_t( 'shortcode_attr_prizes_category_links', 'yes/no to control whether category badges link to the configured URL.' ),
'click_action'    => bhg_t( 'shortcode_attr_prizes_click_action', 'Override click behaviour: link, new, image, none, or inherit.' ),
'link_target'     => bhg_t( 'shortcode_attr_prizes_link_target', 'Override link target: _self, _blank, or inherit.' ),
'category_target' => bhg_t( 'shortcode_attr_prizes_category_target', 'Override category link target: _self, _blank, or inherit.' ),
),
),
	array(
		'tag'         => 'bhg_winner_notifications',
		'description' => bhg_t( 'shortcode_winner_notifications_desc', 'Compact widget showing the latest closed hunts and winners.' ),
		'attributes'  => array(
			'limit' => bhg_t( 'shortcode_attr_limit', 'Number of hunts to list (defaults to 5).' ),
		),
	),
	array(
		'tag'         => 'bhg_best_guessers',
		'description' => bhg_t( 'shortcode_best_guessers_desc', 'Tabbed leaderboard of top guessers (overall, monthly, yearly, all-time).' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bhg_user_profile',
		'description' => bhg_t( 'shortcode_user_profile_desc', 'Shows the logged-in user profile summary with affiliate status.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'my_bonushunts',
		'description' => bhg_t( 'shortcode_my_bonushunts_desc', 'Profile block listing hunts the current user has joined.' ),
		'attributes'  => array(
			'limit' => bhg_t( 'shortcode_attr_profile_limit', 'Maximum number of hunts to show (default 50).' ),
		),
	),
	array(
		'tag'         => 'my_tournaments',
		'description' => bhg_t( 'shortcode_my_tournaments_desc', 'Profile block of tournaments the current user has joined.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'my_prizes',
		'description' => bhg_t( 'shortcode_my_prizes_desc', 'Profile block showing prizes won by the current user.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'my_rankings',
		'description' => bhg_t( 'shortcode_my_rankings_desc', 'Profile block summarising the user’s personal rankings.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bhg_nav',
		'description' => bhg_t( 'shortcode_bhg_nav_desc', 'Renders a specific Bonus Hunt menu area.' ),
		'attributes'  => array(
			'area' => bhg_t( 'shortcode_attr_nav_area', 'Menu selection: guest, user, or admin.' ),
		),
	),
	array(
		'tag'         => 'bhg_menu',
		'description' => bhg_t( 'shortcode_bhg_menu_desc', 'Automatically selects the correct menu for the visitor’s role.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bhg_ad',
		'description' => bhg_t( 'shortcode_bhg_ad_desc', 'Outputs a single advertising row with placement targeting.' ),
		'attributes'  => array(
			'id'     => bhg_t( 'shortcode_attr_ad_id', 'ID of the advertisement row to render.' ),
			'ad'     => bhg_t( 'shortcode_attr_ad_alias', 'Alias for id; useful when embedding through page builders.' ),
			'status' => bhg_t( 'shortcode_attr_ad_status', 'Filter by status: active, inactive, or all.' ),
		),
		'aliases'     => array( 'bhg_advertising' ),
	),
	array(
		'tag'         => 'bonus_hunt_login',
		'description' => bhg_t( 'shortcode_bonus_hunt_login_desc', 'Displays a login prompt with redirect for guests.' ),
		'attributes'  => array(),
	),
	array(
		'tag'         => 'bhg_jackpot_current',
		'description' => bhg_t( 'shortcode_jackpot_current_desc', 'Displays the current amount for a specific jackpot.' ),
		'attributes'  => array(
			'id' => bhg_t( 'shortcode_attr_jackpot_id', 'Numeric jackpot ID to display.' ),
		),
	),
	array(
		'tag'         => 'bhg_jackpot_latest',
		'description' => bhg_t( 'shortcode_jackpot_latest_desc', 'List of the most recent jackpot wins.' ),
		'attributes'  => array(
			'limit'     => bhg_t( 'shortcode_attr_limit', 'Number of results to show.' ),
			'affiliate' => bhg_t( 'shortcode_attr_jackpot_affiliate', 'Optional affiliate website ID filter.' ),
			'year'      => bhg_t( 'shortcode_attr_jackpot_year', 'Filter wins by calendar year.' ),
			'empty'     => bhg_t( 'shortcode_attr_empty_text', 'Fallback message when no wins are available.' ),
		),
	),
	array(
		'tag'         => 'bhg_jackpot_ticker',
		'description' => bhg_t( 'shortcode_jackpot_ticker_desc', 'Ticker of live jackpot totals or recent winners.' ),
		'attributes'  => array(
			'mode' => bhg_t( 'shortcode_attr_jackpot_mode', 'Display mode: amount (default) or winners.' ),
		),
	),
	array(
		'tag'         => 'bhg_jackpot_winners',
		'description' => bhg_t( 'shortcode_jackpot_winners_desc', 'Formatted table or list of jackpot winners.' ),
		'attributes'  => array(
			'layout'    => bhg_t( 'shortcode_attr_jackpot_layout', 'Layout style: table or list.' ),
			'limit'     => bhg_t( 'shortcode_attr_limit', 'Number of rows to display.' ),
			'affiliate' => bhg_t( 'shortcode_attr_jackpot_affiliate', 'Affiliate website ID filter.' ),
			'year'      => bhg_t( 'shortcode_attr_jackpot_year', 'Limit winners to a specific year.' ),
			'empty'     => bhg_t( 'shortcode_attr_empty_text', 'Fallback message when no winners are available.' ),
		),
	),
);
?>
<div class="wrap">
	<h1><?php echo esc_html( bhg_t( 'menu_shortcodes', 'Shortcodes' ) ); ?></h1>
	<p class="description"><?php echo esc_html( bhg_t( 'shortcode_overview_intro', 'Use the following shortcodes inside posts, pages, or widgets to surface Bonus Hunt functionality.' ) ); ?></p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_shortcode', 'Shortcode' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_description', 'Description' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_attributes', 'Attributes' ) ); ?></th>
				<th scope="col"><?php echo esc_html( bhg_t( 'label_notes', 'Notes' ) ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $shortcodes as $shortcode ) : ?>
				<tr>
					<td><code>[<?php echo esc_html( $shortcode['tag'] ); ?>]</code></td>
					<td><?php echo esc_html( $shortcode['description'] ); ?></td>
					<td>
						<?php if ( ! empty( $shortcode['attributes'] ) ) : ?>
							<ul>
								<?php foreach ( $shortcode['attributes'] as $attribute => $detail ) : ?>
									<li><code><?php echo esc_html( $attribute ); ?></code> — <?php echo esc_html( $detail ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</td>
					<td>
						<?php
						$notes = isset( $shortcode['notes'] ) ? $shortcode['notes'] : '';
						if ( ! empty( $shortcode['aliases'] ) ) {
							$alias_list = array_map( 'esc_html', (array) $shortcode['aliases'] );
							/* translators: %s: comma separated list of aliases. */
							$alias_text = sprintf( bhg_t( 'shortcode_aliases_fmt', 'Aliases: %s' ), implode( ', ', $alias_list ) );
							$notes      = $notes ? $alias_text . ' — ' . $notes : $alias_text;
						}
						echo $notes ? esc_html( $notes ) : '&mdash;';
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
