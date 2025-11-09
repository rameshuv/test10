<?php
/**
 * Helpers for Nextend Social Login profile data.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Nextend_Profile' ) ) {
	/**
	 * Utility methods for sanitizing profile data from Nextend.
	 */
	class BHG_Nextend_Profile {

		/**
		 * Sanitize raw profile data array.
		 *
		 * @param array $data Raw profile array from Nextend Social Login.
		 * @return array {
		 *     @type string $avatar       Avatar image URL.
		 *     @type string $display_name Display name from provider.
		 *     @type string $profile_url  Link to provider profile.
		 * }
		 */
		public static function sanitize_profile( $data ) {
			$data = is_array( $data ) ? $data : array();

			$avatar       = isset( $data['avatar'] ) ? esc_url_raw( $data['avatar'] ) : '';
			$display_name = isset( $data['displayName'] ) ? sanitize_text_field( $data['displayName'] ) : '';
			$profile_url  = isset( $data['profileUrl'] ) ? esc_url_raw( $data['profileUrl'] ) : '';

			return array(
				'avatar'       => $avatar,
				'display_name' => $display_name,
				'profile_url'  => $profile_url,
			);
		}
	}
}
