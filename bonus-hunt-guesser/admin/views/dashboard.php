<?php
/**
 * Dashboard: Latest Hunts overview.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) )
	);
}

global $wpdb;

$hunts_table       = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$hunts_count       = (int) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic table name; static query.
$wpdb->get_var( "SELECT COUNT(*) FROM {$hunts_table}" );
$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$tournaments_count = (int) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic table name; static query.
$wpdb->get_var( "SELECT COUNT(*) FROM {$tournaments_table}" );

$user_counts = count_users();
$users_count = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;

$hunts_controller = null;
if ( class_exists( 'BHG_Bonus_Hunts_Controller' ) ) {
	$hunts_controller = BHG_Bonus_Hunts_Controller::get_instance();
}

$hunts = array();
if ( $hunts_controller && method_exists( $hunts_controller, 'get_latest_hunts' ) ) {
	$hunts = $hunts_controller->get_latest_hunts( 3 );
} elseif ( function_exists( 'bhg_get_latest_closed_hunts' ) ) {
	$legacy_hunts = bhg_get_latest_closed_hunts( 3 );
	foreach ( (array) $legacy_hunts as $legacy_hunt ) {
		$hunt_id       = isset( $legacy_hunt->id ) ? (int) $legacy_hunt->id : 0;
		$winners_count = isset( $legacy_hunt->winners_count ) ? (int) $legacy_hunt->winners_count : 0;
		$winners_count = $winners_count > 0 ? $winners_count : 25;
		$winners       = array();

		if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
			$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_count );
		}

		$hunts[] = array(
			'hunt'    => $legacy_hunt,
			'winners' => $winners,
		);
	}
}

$hunts_for_display = array();
foreach ( (array) $hunts as $entry ) {
	$hunt    = null;
	$winners = array();

	if ( is_array( $entry ) && isset( $entry['hunt'] ) ) {
		$hunt    = $entry['hunt'];
		$winners = isset( $entry['winners'] ) ? $entry['winners'] : array();
	} elseif ( is_object( $entry ) ) {
		$hunt = $entry;
	}

	if ( null === $hunt ) {
		continue;
	}

	if ( empty( $winners ) && isset( $hunt->id ) && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
		$limit   = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
		$limit   = $limit > 0 ? $limit : 25;
		$winners = bhg_get_top_winners_for_hunt( (int) $hunt->id, $limit );
	}

	$hunts_for_display[] = array(
		'hunt'    => $hunt,
		'winners' => is_array( $winners ) ? $winners : array(),
	);
}
?>
<div class="wrap bhg-admin bhg-wrap bhg-dashboard">
	<h1 class="bhg-dashboard-heading"><?php echo esc_html( bhg_t( 'menu_dashboard', 'Dashboard' ) ); ?></h1>

	<main class="bhg-dashboard-cards">
		<section class="bhg-dashboard-card" aria-labelledby="bhg-dashboard-summary-title" role="region">
			<header class="bhg-card-header">
				<h2 id="bhg-dashboard-summary-title" class="bhg-card-title"><?php echo esc_html( bhg_t( 'summary', 'Summary' ) ); ?></h2>
			</header>
			<div class="bhg-card-content">
				<table class="bhg-dashboard-table bhg-summary-table">
					<thead>
						<tr>
							<th><span class="dashicons dashicons-book-alt"></span> <?php echo esc_html( bhg_t( 'hunts', 'Hunts' ) ); ?></th>
							<th><span class="dashicons dashicons-groups"></span> <?php echo esc_html( bhg_t( 'users', 'Users' ) ); ?></th>
							<th><span class="dashicons dashicons-awards"></span> <?php echo esc_html( bhg_t( 'tournaments', 'Tournaments' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo esc_html( number_format_i18n( $hunts_count ) ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $users_count ) ); ?></td>
							<td><?php echo esc_html( number_format_i18n( $tournaments_count ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section class="bhg-dashboard-card" aria-labelledby="bhg-dashboard-latest-title" role="region">
			<header class="bhg-card-header">
				<h2 id="bhg-dashboard-latest-title" class="bhg-card-title"><?php echo esc_html( bhg_t( 'label_latest_hunts', 'Latest Hunts' ) ); ?></h2>
			</header>
			<div class="bhg-card-content">
				<?php if ( ! empty( $hunts_for_display ) ) : ?>
					<table class="bhg-dashboard-table bhg-latest-hunts-table">
						<thead>
							<tr>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_bonus_hunt', 'Bonushunt' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_all_winners', 'All Winners' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_start_balance', 'Start Balance' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_final_balance', 'Final Balance' ) ); ?></th>
								<th scope="col"><?php echo esc_html( bhg_t( 'label_closed_at', 'Closed At' ) ); ?></th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach ( $hunts_for_display as $entry ) :
							$hunt          = $entry['hunt'];
							$winners       = is_array( $entry['winners'] ) ? $entry['winners'] : array();
							$title         = isset( $hunt->title ) ? (string) $hunt->title : '';
							$start         = isset( $hunt->starting_balance ) ? (float) $hunt->starting_balance : 0.0;
							$final         = isset( $hunt->final_balance ) ? $hunt->final_balance : null;
							$closed        = isset( $hunt->closed_at ) ? (string) $hunt->closed_at : '';
							$final_display = null === $final ? '–' : bhg_format_money( (float) $final );
							$closed_time   = '–';
						if ( $closed ) {
							$timestamp = strtotime( $closed );
							if ( $timestamp ) {
									$closed_time = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
							}
						}
						$row_classes = array();
						if ( ! empty( $winners ) ) {
								$row_classes[] = 'bhg-latest-hunt--has-winners';
						}
						if ( count( $winners ) > 1 ) {
								$row_classes[] = 'bhg-latest-hunt--multiple-winners';
						}
						$row_class_attr = ! empty( $row_classes ) ? ' class="' . esc_attr( implode( ' ', $row_classes ) ) . '"' : '';
						$winner_rows    = ! empty( $winners ) ? array_values( $winners ) : array( null );
						$rowspan        = max( 1, count( $winner_rows ) );
						?>
						<?php
						foreach ( $winner_rows as $index => $winner ) :
								$is_first  = ( 0 === $index );
								$row_attr  = $is_first ? $row_class_attr : '';
								$placement = '';
								$name      = '';
								$guess_out = '';
								$diff_out  = '';
							if ( $winner ) {
								$position  = isset( $winner->position ) ? (int) $winner->position : ( $index + 1 );
								$placement = sprintf( '#%d', max( 1, $position ) );
								$user_id   = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
								if ( isset( $winner->display_name ) && '' !== trim( (string) $winner->display_name ) ) {
										$name = (string) $winner->display_name;
								} elseif ( $user_id > 0 ) {
										$user = get_userdata( $user_id );
										$name = $user ? $user->display_name : sprintf( bhg_t( 'label_user_number', 'User #%d' ), $user_id );
								} else {
										$name = bhg_t( 'unknown_user', 'Unknown User' );
								}
								$guess_value = isset( $winner->guess ) ? (float) $winner->guess : 0.0;
								$diff_value  = isset( $winner->diff ) ? (float) $winner->diff : null;
								$guess_out   = sprintf( '%s %s', bhg_t( 'label_guess', 'Guess:' ), bhg_format_money( $guess_value ) );
								if ( null !== $diff_value ) {
										$diff_out = sprintf( '%s %s', bhg_t( 'label_difference', 'Difference:' ), bhg_format_money( abs( $diff_value ) ) );
								}
							}
							?>
														<tr<?php echo $row_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
																<?php if ( $is_first ) : ?>
																<td rowspan="<?php echo esc_attr( $rowspan ); ?>">
																		<strong><?php echo esc_html( $title ); ?></strong>
																</td>
																<?php endif; ?>
																<td class="bhg-winners-cell">
																		<?php if ( $winner ) : ?>
																		<div class="bhg-winner-row">
																				<span class="bhg-winner-rank"><?php echo esc_html( $placement ); ?></span>
																				<span class="bhg-winner-details">
																						<strong class="bhg-winner-name"><?php echo esc_html( $name ); ?></strong>
																						<span class="bhg-winner-stats"><?php echo esc_html( $guess_out ); ?>
																						<?php
																						if ( $diff_out ) :
																							?>
																							· <?php echo esc_html( $diff_out ); ?><?php endif; ?></span>
																				</span>
																		</div>
																		<?php else : ?>
																		<span class="bhg-empty-state"><?php echo esc_html( bhg_t( 'notice_no_winners_yet', 'No winners yet.' ) ); ?></span>
																		<?php endif; ?>
																</td>
																<?php if ( $is_first ) : ?>
																<td rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( bhg_format_money( $start ) ); ?></td>
																<td rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( $final_display ); ?></td>
																<td rowspan="<?php echo esc_attr( $rowspan ); ?>"><?php echo esc_html( $closed_time ); ?></td>
																<?php endif; ?>
														</tr>
<?php endforeach; ?>
<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php echo esc_html( bhg_t( 'notice_no_closed_hunts', 'No closed hunts yet.' ) ); ?></p>
				<?php endif; ?>
			</div>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=bhg-bonus-hunts' ) ); ?>" class="button button-primary bhg-dashboard-button"><?php echo esc_html( bhg_t( 'view_all_hunts', 'View All Hunts' ) ); ?></a></p>
		</section>
	</main>
</div>
