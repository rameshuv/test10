<?php
/**
 * Admin view template for displaying hunt results.
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

$hunt_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
if ( ! $hunt_id ) {
	wp_die( esc_html( bhg_t( 'missing_hunt_id', 'Missing hunt id' ) ) );
}

check_admin_referer( 'bhg_view_results_' . $hunt_id, 'bhg_nonce' );

if ( ! function_exists( 'bhg_get_hunt' ) || ! function_exists( 'bhg_get_all_ranked_guesses' ) ) {
	wp_die(
		esc_html( bhg_t( 'notice_required_helpers_missing', 'Required helper functions are missing. Please include class-bhg-bonus-hunts.php helpers.' ) )
	);
}

$hunt = bhg_get_hunt( $hunt_id );
if ( ! $hunt ) {
	wp_die( esc_html( bhg_t( 'hunt_not_found', 'Hunt not found' ) ) );
}

$rows          = bhg_get_all_ranked_guesses( $hunt_id );
$winners_count = (int) ( $hunt->winners_count ?? 3 );
?>
<div class="wrap">
		<h1><?php echo esc_html( sprintf( bhg_t( 'title_results_s', 'Results — %s' ), $hunt->title ) ); ?></h1>
		<?php if ( ! empty( $rows ) ) : ?>
		<table class="widefat striped">
		<thead><tr>
				<th><?php echo esc_html( bhg_t( 'label_hash', '#' ) ); ?></th>
				<th>
				<?php
				echo esc_html( bhg_t( 'sc_user', 'User' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'sc_guess', 'Guess' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'label_diff_short', 'Diff' ) );
				?>
</th>
		</tr></thead>
		<tbody>
				<?php
				$i = 1; foreach ( $rows as $r ) :
						$highlight = ( $i <= $winners_count );
						$u         = get_userdata( (int) $r->user_id );
						$name      = $u ? $u->user_login : ( '#' . $r->user_id );
					?>
				<tr<?php echo $highlight ? ' style="background:#e6ffed;"' : ''; ?>>
						<td><?php echo (int) $i; ?></td>
						<td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $r->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
						<td><?php echo esc_html( number_format_i18n( (float) $r->guess, 2 ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (float) $r->diff, 2 ) ); ?></td>
						</tr>
						<?php
						++$i;
				endforeach;
				?>
		</tbody>
		</table>
		<?php else : ?>
		<div class="notice notice-info"><p><?php echo esc_html( bhg_t( 'notice_no_guesses_yet', 'There are no winners yet.' ) ); ?></p></div>
		<?php endif; ?>
</div>
