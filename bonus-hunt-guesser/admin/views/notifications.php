<?php
/**
 * Notifications settings view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

$notifications = function_exists( 'bhg_get_notification_settings' ) ? bhg_get_notification_settings() : array();
$message       = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error_code    = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$sections = array(
	'winner'     => array(
		'label'        => bhg_t( 'label_winner_email', 'Winner Email' ),
		'placeholders' => array( '{{username}}', '{{hunt_title}}', '{{final_balance}}', '{{position}}', '{{guess}}', '{{difference}}', '{{winner_summary}}', '{{winner_usernames}}', '{{winner_count}}', '{{site_name}}' ),
	),
	'tournament' => array(
		'label'        => bhg_t( 'label_tournament_email', 'Tournament Email' ),
		'placeholders' => array( '{{username}}', '{{tournament_title}}', '{{position}}', '{{wins}}', '{{leaderboard}}', '{{winner_usernames}}', '{{winner_count}}', '{{site_name}}' ),
	),
	'bonus_hunt' => array(
		'label'        => bhg_t( 'label_bonus_hunt_email', 'Bonus Hunt Email' ),
		'placeholders' => array( '{{username}}', '{{hunt_title}}', '{{final_balance}}', '{{winner_summary}}', '{{winner_usernames}}', '{{winner_count}}', '{{site_name}}' ),
	),
);
?>
<div class="wrap">
		<h1><?php echo esc_html( bhg_t( 'menu_notifications', 'Notifications' ) ); ?></h1>

		<?php if ( 'saved' === $message ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'notifications_saved', 'Notifications saved.' ) ); ?></p></div>
		<?php elseif ( 'nonce_failed' === $error_code ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'bhg_save_notifications', 'bhg_save_notifications_nonce' ); ?>
				<input type="hidden" name="action" value="bhg_save_notifications">

				<?php
				foreach ( $sections as $key => $section ) :
						$config            = isset( $notifications[ $key ] ) && is_array( $notifications[ $key ] ) ? $notifications[ $key ] : array();
						$enabled           = ! empty( $config['enabled'] );
							$subject       = isset( $config['title'] ) ? $config['title'] : '';
						$body              = isset( $config['body'] ) ? $config['body'] : '';
						$bcc_list          = isset( $config['bcc'] ) && is_array( $config['bcc'] ) ? $config['bcc'] : array();
						$bcc_value         = implode( "\n", $bcc_list );
						$placeholder_items = array();
					foreach ( $section['placeholders'] as $placeholder ) {
							$placeholder_items[] = '<code>' . esc_html( $placeholder ) . '</code>';
					}
						$placeholder_text = implode( ', ', $placeholder_items );
					?>
						<h2><?php echo esc_html( $section['label'] ); ?></h2>
						<table class="form-table" role="presentation">
								<tbody>
										<tr>
												<th scope="row"><?php echo esc_html( bhg_t( 'label_email_enable', 'Enable this email' ) ); ?></th>
												<td>
														<input type="hidden" name="notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="0">
														<label for="bhg-notification-<?php echo esc_attr( $key ); ?>-enabled">
																<input type="checkbox" id="bhg-notification-<?php echo esc_attr( $key ); ?>-enabled" name="notifications[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $enabled ); ?>>
																<?php echo esc_html( bhg_t( 'label_enable_checkbox', 'Enabled' ) ); ?>
														</label>
												</td>
										</tr>
										<tr>
												<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $key ); ?>-subject"><?php echo esc_html( bhg_t( 'label_email_subject', 'Email Subject' ) ); ?></label></th>
												<td>
																												<input type="text" class="regular-text" id="bhg-notification-<?php echo esc_attr( $key ); ?>-subject" name="notifications[<?php echo esc_attr( $key ); ?>][title]" value="<?php echo esc_attr( $subject ); ?>">
												</td>
										</tr>
										<tr>
												<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $key ); ?>-body"><?php echo esc_html( bhg_t( 'label_email_body', 'Email HTML Body' ) ); ?></label></th>
												<td>
														<textarea class="large-text code" rows="10" id="bhg-notification-<?php echo esc_attr( $key ); ?>-body" name="notifications[<?php echo esc_attr( $key ); ?>][body]"><?php echo esc_textarea( $body ); ?></textarea>
														<p class="description"><?php echo wp_kses_post( sprintf( bhg_t( 'notification_placeholders_hint', 'Available placeholders: %s' ), $placeholder_text ) ); ?></p>
												</td>
										</tr>
										<tr>
												<th scope="row"><label for="bhg-notification-<?php echo esc_attr( $key ); ?>-bcc"><?php echo esc_html( bhg_t( 'label_email_bcc', 'BCC Recipients' ) ); ?></label></th>
												<td>
														<textarea class="large-text code" rows="3" id="bhg-notification-<?php echo esc_attr( $key ); ?>-bcc" name="notifications[<?php echo esc_attr( $key ); ?>][bcc]"><?php echo esc_textarea( $bcc_value ); ?></textarea>
														<p class="description"><?php echo esc_html( bhg_t( 'notification_bcc_hint', 'Separate multiple email addresses with commas or new lines.' ) ); ?></p>
												</td>
										</tr>
								</tbody>
						</table>
						<hr>
				<?php endforeach; ?>

				<?php submit_button( bhg_t( 'save_changes', 'Save Changes' ) ); ?>
		</form>
</div>
