<?php
/**
 * Utility functions and helpers for Bonus Hunt Guesser plugin.
 *
 * @package Bonus_Hunt_Guesser
 */

// phpcs:disable WordPress.Files.FileOrganization

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General utility methods used throughout the plugin.
 */
class BHG_Utils {
	/**
	 * Register hooks used by utility functions.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes handled in the shortcode constructor.
	 *
	 * @return void
	 */
	public static function register_shortcodes() {
		// Handled in BHG_Shortcodes constructor, kept for legacy.
	}

	/**
	 * Retrieve plugin settings merged with defaults.
	 *
	 * @return array Plugin settings.
	 */
	public static function get_settings() {
		$defaults = array(
			'allow_guess_edit' => 1,
			'ads_enabled'      => 1,
			'email_from'       => get_bloginfo( 'admin_email' ),
		);
		$opt      = get_option( 'bhg_settings', array() );
		if ( ! is_array( $opt ) ) {
			$opt = array();
		}
		return wp_parse_args( $opt, $defaults );
	}

	/**
	 * Update plugin settings.
	 *
	 * @param array $data New settings data.
	 * @return array Updated settings.
	 */
	public static function update_settings( $data ) {
		$current = self::get_settings();
		$new     = array_merge( $current, $data );
		update_option( 'bhg_settings', $new );
		return $new;
	}

	/**
	 * Retrieve the "From" email address for notifications.
	 *
	 * @return string Email address.
	 */
	public static function get_email_from() {
		$settings = get_option( 'bhg_plugin_settings', array() );
		$email    = isset( $settings['email_from'] ) ? $settings['email_from'] : get_bloginfo( 'admin_email' );
		$email    = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			$email = sanitize_email( get_bloginfo( 'admin_email' ) );
		}

		return $email;
	}

	/**
	 * Require manage options capability or abort.
	 *
	 * @return void
	 */
	public static function require_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html(
					bhg_t(
						'you_do_not_have_permission_to_access_this_page',
						'You do not have permission to access this page'
					)
				)
			);
		}
	}

		/**
		 * Retrieve an admin URL, respecting network admin context.
		 *
		 * @param string $path Optional path relative to the admin URL.
		 * @return string Full admin URL for the current context.
		 */
	public static function admin_url( $path = '' ) {
			return is_network_admin() ? network_admin_url( $path ) : admin_url( $path );
	}

	/**
	 * Output a nonce field for the given action.
	 *
	 * @param string $action Action name.
	 * @return void
	 */
	public static function nonce_field( $action ) {
		wp_nonce_field( $action, $action . '_nonce' );
	}

	/**
	 * Verify a nonce for the given action.
	 *
	 * @param string $action Action name.
	 * @return bool Whether the nonce is valid.
	 */
	public static function verify_nonce( $action ) {
		return isset( $_POST[ $action . '_nonce' ] )
			&& wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST[ $action . '_nonce' ] ) ),
				$action
			);
	}

	/**
	 * Execute a callback during template redirect after conditionals are set up.
	 *
	 * @param callable $cb Callback to execute.
	 * @return void
	 */
	public static function safe_query_conditionals( callable $cb ) {
		add_action(
			'template_redirect',
			function () use ( $cb ) {
				$cb();
			}
		);
	}
}

// phpcs:enable WordPress.Files.FileOrganization
