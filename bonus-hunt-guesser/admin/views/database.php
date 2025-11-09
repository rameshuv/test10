<?php
/**
 * Database maintenance admin view.
 *
 * Provides cleanup and optimization tools for Bonus Hunt Guesser tables.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Handle form submissions.
$db_action        = isset( $_POST['bhg_action'] ) ? sanitize_key( wp_unslash( $_POST['bhg_action'] ) ) : '';
$cleanup_request  = isset( $_POST['bhg_db_cleanup'] ) ? sanitize_text_field( wp_unslash( $_POST['bhg_db_cleanup'] ) ) : '';
$optimize_request = isset( $_POST['bhg_db_optimize'] ) ? sanitize_text_field( wp_unslash( $_POST['bhg_db_optimize'] ) ) : '';

if ( 'db_cleanup' === $db_action && ! empty( $cleanup_request ) ) {
	check_admin_referer( 'bhg_db_cleanup_action', 'bhg_nonce' );

	// Perform database cleanup.
	bhg_database_cleanup();
	$cleanup_completed = true;
} elseif ( 'db_optimize' === $db_action && ! empty( $optimize_request ) ) {
		check_admin_referer( 'bhg_db_optimize_action', 'bhg_nonce' );

		// Perform database optimization.
		bhg_database_optimize();
		$optimize_completed = true;
}

?>
<div class="wrap">
<h1><?php echo esc_html( bhg_t( 'database', 'Database' ) ); ?></h1>
<?php if ( ! empty( $cleanup_completed ) ) : ?>
<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'database_cleanup_completed', 'Database cleanup completed. All plugin tables are now empty.' ) ); ?></p></div>
<?php endif; ?>
<?php if ( ! empty( $optimize_completed ) ) : ?>
<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'database_optimization_completed', 'Database optimization completed.' ) ); ?></p></div>
<?php endif; ?>
<form method="post">
<?php wp_nonce_field( 'bhg_db_cleanup_action', 'bhg_nonce' ); ?>
<input type="hidden" name="bhg_action" value="db_cleanup" />
<?php submit_button( bhg_t( 'cleanup_database', 'Cleanup Database' ), 'secondary', 'bhg_db_cleanup', false ); ?>
</form>
<form method="post" class="bhg-margin-top-small">
<?php wp_nonce_field( 'bhg_db_optimize_action', 'bhg_nonce' ); ?>
<input type="hidden" name="bhg_action" value="db_optimize" />
<?php submit_button( bhg_t( 'optimize_database', 'Optimize Database' ), 'secondary', 'bhg_db_optimize', false ); ?>
</form>
</div>

