<?php
/**
 * Settings view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

// Fetch existing settings.
$settings = get_option( 'bhg_plugin_settings', array() );

$hunt_limit       = isset( $settings['hunt_win_limit'] ) && is_array( $settings['hunt_win_limit'] ) ? $settings['hunt_win_limit'] : array();
$tournament_limit = isset( $settings['tournament_win_limit'] ) && is_array( $settings['tournament_win_limit'] ) ? $settings['tournament_win_limit'] : array();

$hunt_limit_count  = isset( $hunt_limit['count'] ) ? (int) $hunt_limit['count'] : 0;
$hunt_limit_period = isset( $hunt_limit['period'] ) ? sanitize_key( $hunt_limit['period'] ) : 'none';
$tour_limit_count  = isset( $tournament_limit['count'] ) ? (int) $tournament_limit['count'] : 0;
$tour_limit_period = isset( $tournament_limit['period'] ) ? sanitize_key( $tournament_limit['period'] ) : 'none';

$period_options = array(
	'none'    => bhg_t( 'limit_period_none', 'No limit' ),
	'week'    => bhg_t( 'limit_period_week', 'Per week' ),
	'month'   => bhg_t( 'limit_period_month', 'Per month' ),
	'quarter' => bhg_t( 'limit_period_quarter', 'Per quarter' ),
	'year'    => bhg_t( 'limit_period_year', 'Per year' ),
);

$message    = isset( $_GET['message'] ) ? sanitize_key( wp_unslash( $_GET['message'] ) ) : '';
$error_code = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
?>
<div class="wrap">
<h1><?php echo esc_html( bhg_t( 'bonus_hunt_guesser_settings', 'Bonus Hunt Guesser Settings' ) ); ?></h1>

<?php if ( 'saved' === $message ) : ?>
<div class="notice notice-success"><p><?php echo esc_html( bhg_t( 'settings_saved', 'Settings saved.' ) ); ?></p></div>
<?php elseif ( 'invalid_data' === $error_code ) : ?>
<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'invalid_settings', 'Invalid data submitted.' ) ); ?></p></div>
<?php elseif ( 'nonce_failed' === $error_code ) : ?>
<div class="notice notice-error"><p><?php echo esc_html( bhg_t( 'security_check_failed', 'Security check failed. Please try again.' ) ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php wp_nonce_field( 'bhg_settings', 'bhg_settings_nonce' ); ?>
<input type="hidden" name="action" value="bhg_save_settings">

<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="bhg_default_tournament_period"><?php echo esc_html( bhg_t( 'default_tournament_period', 'Default Tournament Period' ) ); ?></label></th>
<td>
<select name="bhg_default_tournament_period" id="bhg_default_tournament_period">
<?php
$periods        = array(
	'weekly'    => bhg_t( 'weekly', 'Weekly' ),
	'monthly'   => bhg_t( 'monthly', 'Monthly' ),
	'quarterly' => bhg_t( 'quarterly', 'Quarterly' ),
	'yearly'    => bhg_t( 'yearly', 'Yearly' ),
	'alltime'   => bhg_t( 'alltime', 'All-time' ),
);
$current_period = isset( $settings['default_tournament_period'] ) ? $settings['default_tournament_period'] : '';
foreach ( $periods as $key => $label ) :
	?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_period, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_currency"><?php echo esc_html( bhg_t( 'currency', 'Currency' ) ); ?></label></th>
<td>
<select name="bhg_currency" id="bhg_currency">
<?php
$currencies       = array(
	'eur' => bhg_t( 'eur', 'EUR' ),
	'usd' => bhg_t( 'usd', 'USD' ),
);
$current_currency = isset( $settings['currency'] ) ? $settings['currency'] : 'eur';
foreach ( $currencies as $key => $label ) :
	?>
<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_currency, $key ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_min_guess_amount"><?php echo esc_html( bhg_t( 'min_guess_amount', 'Minimum Guess Amount' ) ); ?></label></th>
<td><input type="number" class="small-text" id="bhg_min_guess_amount" name="bhg_min_guess_amount" value="<?php echo isset( $settings['min_guess_amount'] ) ? esc_attr( $settings['min_guess_amount'] ) : '0'; ?>" min="0"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_max_guess_amount"><?php echo esc_html( bhg_t( 'max_guess_amount', 'Maximum Guess Amount' ) ); ?></label></th>
<td><input type="number" class="small-text" id="bhg_max_guess_amount" name="bhg_max_guess_amount" value="<?php echo isset( $settings['max_guess_amount'] ) ? esc_attr( $settings['max_guess_amount'] ) : '100000'; ?>" min="0"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_allow_guess_changes"><?php echo esc_html( bhg_t( 'allow_guess_changes', 'Allow Guess Changes' ) ); ?></label></th>
<td>
<select name="bhg_allow_guess_changes" id="bhg_allow_guess_changes">
<option value="yes" <?php selected( isset( $settings['allow_guess_changes'] ) ? $settings['allow_guess_changes'] : '', 'yes' ); ?>><?php echo esc_html( bhg_t( 'yes', 'Yes' ) ); ?></option>
<option value="no" <?php selected( isset( $settings['allow_guess_changes'] ) ? $settings['allow_guess_changes'] : '', 'no' ); ?>><?php echo esc_html( bhg_t( 'no', 'No' ) ); ?></option>
</select>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'hunt_win_limit', 'Bonus hunt win limit' ) ); ?></th>
<td>
		<fieldset>
				<label>
						<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'hunt_win_limit_count', 'Maximum wins per period' ) ); ?></span>
						<input type="number" class="small-text" name="bhg_hunt_win_limit[count]" value="<?php echo esc_attr( max( 0, $hunt_limit_count ) ); ?>" min="0">
				</label>
				<label>
						<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'hunt_win_limit_period', 'Period window' ) ); ?></span>
						<select name="bhg_hunt_win_limit[period]">
								<?php foreach ( $period_options as $period_key => $period_label ) : ?>
								<option value="<?php echo esc_attr( $period_key ); ?>" <?php selected( $hunt_limit_period, $period_key ); ?>><?php echo esc_html( $period_label ); ?></option>
								<?php endforeach; ?>
						</select>
				</label>
				<p class="description"><?php echo esc_html( bhg_t( 'hunt_win_limit_help', 'Limit how many hunts a user can win within the selected rolling window. Set to zero for no restriction.' ) ); ?></p>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'tournament_win_limit', 'Tournament win limit' ) ); ?></th>
<td>
		<fieldset>
				<label>
						<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'tournament_win_limit_count', 'Maximum tournament wins per period' ) ); ?></span>
						<input type="number" class="small-text" name="bhg_tournament_win_limit[count]" value="<?php echo esc_attr( max( 0, $tour_limit_count ) ); ?>" min="0">
				</label>
				<label>
						<span class="screen-reader-text"><?php echo esc_html( bhg_t( 'tournament_win_limit_period', 'Tournament limit period' ) ); ?></span>
						<select name="bhg_tournament_win_limit[period]">
								<?php foreach ( $period_options as $period_key => $period_label ) : ?>
								<option value="<?php echo esc_attr( $period_key ); ?>" <?php selected( $tour_limit_period, $period_key ); ?>><?php echo esc_html( $period_label ); ?></option>
								<?php endforeach; ?>
						</select>
				</label>
				<p class="description"><?php echo esc_html( bhg_t( 'tournament_win_limit_help', 'Restrict how often a user can claim tournament wins within the chosen window. Set to zero for no restriction.' ) ); ?></p>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_ads_enabled"><?php echo esc_html( bhg_t( 'ads_enabled', 'Enable Ads' ) ); ?></label></th>
<td>
<input type="hidden" name="bhg_ads_enabled" value="0">
<input type="checkbox" id="bhg_ads_enabled" name="bhg_ads_enabled" value="1" <?php checked( ! empty( $settings['ads_enabled'] ) ); ?>>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_email_from"><?php echo esc_html( bhg_t( 'email_from', 'Email From Address' ) ); ?></label></th>
<td><input type="email" class="regular-text" id="bhg_email_from" name="bhg_email_from" value="<?php echo isset( $settings['email_from'] ) ? esc_attr( $settings['email_from'] ) : esc_attr( get_bloginfo( 'admin_email' ) ); ?>"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_post_submit_redirect"><?php echo esc_html( bhg_t( 'post_submit_redirect_url', 'Post-submit redirect URL' ) ); ?></label></th>
<td>
<input type="url" class="regular-text" id="bhg_post_submit_redirect" name="bhg_post_submit_redirect" value="<?php echo isset( $settings['post_submit_redirect'] ) ? esc_attr( $settings['post_submit_redirect'] ) : ''; ?>" placeholder="<?php echo esc_attr( bhg_t( 'post_submit_redirect_placeholder', 'https://example.com/thank-you' ) ); ?>">
<p class="description"><?php echo esc_html( bhg_t( 'post_submit_redirect_description', 'Send users to this URL after submitting or editing a guess. Leave blank to stay on the same page.' ) ); ?></p>
</td>
</tr>
<?php
$profile_sections = isset( $settings['profile_sections'] ) && is_array( $settings['profile_sections'] ) ? $settings['profile_sections'] : array();
$global_styles    = isset( $settings['global_styles'] ) && is_array( $settings['global_styles'] ) ? $settings['global_styles'] : array();

$title_styles = isset( $global_styles['title_block'] ) && is_array( $global_styles['title_block'] ) ? $global_styles['title_block'] : array();
$h2_styles    = isset( $global_styles['heading_2'] ) && is_array( $global_styles['heading_2'] ) ? $global_styles['heading_2'] : array();
$h3_styles    = isset( $global_styles['heading_3'] ) && is_array( $global_styles['heading_3'] ) ? $global_styles['heading_3'] : array();
$desc_styles  = isset( $global_styles['description'] ) && is_array( $global_styles['description'] ) ? $global_styles['description'] : array();
$body_styles  = isset( $global_styles['body_text'] ) && is_array( $global_styles['body_text'] ) ? $global_styles['body_text'] : array();

$hunt_limit        = isset( $settings['hunt_win_limit'] ) && is_array( $settings['hunt_win_limit'] ) ? $settings['hunt_win_limit'] : array();
$tournament_limit  = isset( $settings['tournament_win_limit'] ) && is_array( $settings['tournament_win_limit'] ) ? $settings['tournament_win_limit'] : array();
$hunt_limit_count  = isset( $hunt_limit['count'] ) ? (int) $hunt_limit['count'] : 0;
$hunt_limit_period = isset( $hunt_limit['period'] ) ? sanitize_key( $hunt_limit['period'] ) : 'none';
$tour_limit_count  = isset( $tournament_limit['count'] ) ? (int) $tournament_limit['count'] : 0;
$tour_limit_period = isset( $tournament_limit['period'] ) ? sanitize_key( $tournament_limit['period'] ) : 'none';
$period_options    = array(
	'none'    => bhg_t( 'limit_period_none', 'No limit' ),
	'week'    => bhg_t( 'limit_period_week', 'Per week' ),
	'month'   => bhg_t( 'limit_period_month', 'Per month' ),
	'quarter' => bhg_t( 'limit_period_quarter', 'Per quarter' ),
	'year'    => bhg_t( 'limit_period_year', 'Per year' ),
);
?>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'profile_visibility_settings', 'My Profile Blocks' ) ); ?></th>
<td>
		<fieldset>
				<?php
				$blocks = array(
					'my_bonushunts'  => bhg_t( 'profile_block_my_bonushunts', 'Show “My Bonus Hunts” block' ),
					'my_tournaments' => bhg_t( 'profile_block_my_tournaments', 'Show “My Tournaments” block' ),
					'my_prizes'      => bhg_t( 'profile_block_my_prizes', 'Show “My Prizes” block' ),
					'my_rankings'    => bhg_t( 'profile_block_my_rankings', 'Show “My Rankings” block' ),
				);
				foreach ( $blocks as $key => $label ) :
						$enabled = isset( $profile_sections[ $key ] ) ? (int) $profile_sections[ $key ] : 1;
					?>
						<label><input type="checkbox" name="bhg_profile_sections[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $enabled, 1 ); ?>> <?php echo esc_html( $label ); ?></label><br />
						<?php
				endforeach;
				?>
		</fieldset>
		<p class="description"><?php echo esc_html( bhg_t( 'profile_blocks_description', 'Toggle which sections appear on the user profile shortcodes.' ) ); ?></p>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'global_style_title_block', 'Title block styles' ) ); ?></th>
<td>
		<fieldset>
				<label><?php echo esc_html( bhg_t( 'style_background_color', 'Background color' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[title_block][background]" value="<?php echo isset( $title_styles['background'] ) ? esc_attr( $title_styles['background'] ) : ''; ?>" placeholder="#ffffff" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_border_radius', 'Border radius' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[title_block][radius]" value="<?php echo isset( $title_styles['radius'] ) ? esc_attr( $title_styles['radius'] ) : ''; ?>" placeholder="12px" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_padding', 'Padding' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[title_block][padding]" value="<?php echo isset( $title_styles['padding'] ) ? esc_attr( $title_styles['padding'] ) : ''; ?>" placeholder="16px" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_margin', 'Margin' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[title_block][margin]" value="<?php echo isset( $title_styles['margin'] ) ? esc_attr( $title_styles['margin'] ) : ''; ?>" placeholder="12px 0" />
				</label>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'global_style_heading_two', 'Heading (H2) styles' ) ); ?></th>
<td>
		<fieldset>
				<label><?php echo esc_html( bhg_t( 'style_font_size', 'Font size' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_2][size]" value="<?php echo isset( $h2_styles['size'] ) ? esc_attr( $h2_styles['size'] ) : ''; ?>" placeholder="1.5rem" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_font_weight', 'Font weight' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_2][weight]" value="<?php echo isset( $h2_styles['weight'] ) ? esc_attr( $h2_styles['weight'] ) : ''; ?>" placeholder="600" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_text_color', 'Text color' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_2][color]" value="<?php echo isset( $h2_styles['color'] ) ? esc_attr( $h2_styles['color'] ) : ''; ?>" placeholder="#0f172a" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_padding', 'Padding' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_2][padding]" value="<?php echo isset( $h2_styles['padding'] ) ? esc_attr( $h2_styles['padding'] ) : ''; ?>" placeholder="0 0 8px" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_margin', 'Margin' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_2][margin]" value="<?php echo isset( $h2_styles['margin'] ) ? esc_attr( $h2_styles['margin'] ) : ''; ?>" placeholder="16px 0" />
				</label>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'global_style_heading_three', 'Heading (H3) styles' ) ); ?></th>
<td>
		<fieldset>
				<label><?php echo esc_html( bhg_t( 'style_font_size', 'Font size' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_3][size]" value="<?php echo isset( $h3_styles['size'] ) ? esc_attr( $h3_styles['size'] ) : ''; ?>" placeholder="1.25rem" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_font_weight', 'Font weight' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_3][weight]" value="<?php echo isset( $h3_styles['weight'] ) ? esc_attr( $h3_styles['weight'] ) : ''; ?>" placeholder="500" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_text_color', 'Text color' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_3][color]" value="<?php echo isset( $h3_styles['color'] ) ? esc_attr( $h3_styles['color'] ) : ''; ?>" placeholder="#1e293b" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_padding', 'Padding' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_3][padding]" value="<?php echo isset( $h3_styles['padding'] ) ? esc_attr( $h3_styles['padding'] ) : ''; ?>" placeholder="0" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_margin', 'Margin' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[heading_3][margin]" value="<?php echo isset( $h3_styles['margin'] ) ? esc_attr( $h3_styles['margin'] ) : ''; ?>" placeholder="12px 0" />
				</label>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'global_style_description', 'Description text styles' ) ); ?></th>
<td>
		<fieldset>
				<label><?php echo esc_html( bhg_t( 'style_font_size', 'Font size' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[description][size]" value="<?php echo isset( $desc_styles['size'] ) ? esc_attr( $desc_styles['size'] ) : ''; ?>" placeholder="1rem" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_font_weight', 'Font weight' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[description][weight]" value="<?php echo isset( $desc_styles['weight'] ) ? esc_attr( $desc_styles['weight'] ) : ''; ?>" placeholder="400" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_text_color', 'Text color' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[description][color]" value="<?php echo isset( $desc_styles['color'] ) ? esc_attr( $desc_styles['color'] ) : ''; ?>" placeholder="#475569" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_padding', 'Padding' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[description][padding]" value="<?php echo isset( $desc_styles['padding'] ) ? esc_attr( $desc_styles['padding'] ) : ''; ?>" placeholder="0" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_margin', 'Margin' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[description][margin]" value="<?php echo isset( $desc_styles['margin'] ) ? esc_attr( $desc_styles['margin'] ) : ''; ?>" placeholder="0 0 12px" />
				</label>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php echo esc_html( bhg_t( 'global_style_body', 'Body text styles (p, span)' ) ); ?></th>
<td>
		<fieldset>
				<label><?php echo esc_html( bhg_t( 'style_font_size', 'Font size' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[body_text][size]" value="<?php echo isset( $body_styles['size'] ) ? esc_attr( $body_styles['size'] ) : ''; ?>" placeholder="1rem" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_padding', 'Padding' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[body_text][padding]" value="<?php echo isset( $body_styles['padding'] ) ? esc_attr( $body_styles['padding'] ) : ''; ?>" placeholder="0" />
				</label><br />
				<label><?php echo esc_html( bhg_t( 'style_margin', 'Margin' ) ); ?><br />
						<input type="text" class="regular-text" name="bhg_global_styles[body_text][margin]" value="<?php echo isset( $body_styles['margin'] ) ? esc_attr( $body_styles['margin'] ) : ''; ?>" placeholder="0 0 8px" />
				</label>
		</fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="bhg_remove_data_on_uninstall"><?php echo esc_html( bhg_t( 'remove_data_on_uninstall', 'Remove plugin data on uninstall' ) ); ?></label></th>
<td>
		<input type="hidden" name="bhg_remove_data_on_uninstall" value="0" />
		<input type="checkbox" id="bhg_remove_data_on_uninstall" name="bhg_remove_data_on_uninstall" value="1" <?php checked( ! empty( $settings['remove_data_on_uninstall'] ) ); ?> />
		<p class="description"><?php echo esc_html( bhg_t( 'remove_data_on_uninstall_help', 'Enable this option to delete plugin tables and settings when uninstalling. Leave unchecked to retain data.' ) ); ?></p>
</td>
</tr>
</tbody>
</table>

<?php submit_button( bhg_t( 'save_settings', 'Save Settings' ) ); ?>
</form>
</div>
