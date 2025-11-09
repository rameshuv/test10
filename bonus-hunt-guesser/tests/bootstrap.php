<?php
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

$GLOBALS['bhg_test_options']      = array();
$GLOBALS['bhg_test_user_meta']    = array();
$GLOBALS['bhg_test_posts']        = array();
$GLOBALS['bhg_test_post_meta']    = array();
$GLOBALS['bhg_test_cache']        = array();
$GLOBALS['bhg_test_next_post_id'] = 1;

if ( ! function_exists( 'esc_sql' ) ) {
	function esc_sql( $string ) {
		return $string;
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}

		return stripslashes( (string) $value );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		$value = (string) $value;
		$value = preg_replace( '/[\r\n\t\0\x0B]/', '', $value );

		return trim( $value );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( $email, FILTER_SANITIZE_EMAIL );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return json_encode( $data );
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $email ) {
		return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'sanitize_hex_color_no_hash' ) ) {
	function sanitize_hex_color_no_hash( $color ) {
		$color = preg_replace( '/[^0-9a-fA-F]/', '', (string) $color );

		if ( strlen( $color ) === 3 || strlen( $color ) === 6 ) {
			return strtolower( $color );
		}

		return '';
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return esc_html( $text );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return filter_var( $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return esc_url( $url );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $string ) {
		return strip_tags( (string) $string );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title ) {
		$title = strtolower( preg_replace( '/[^A-Za-z0-9\-\s]/', '', (string) $title ) );
		$title = trim( preg_replace( '/\s+/', '-', $title ), '-' );

		return $title;
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, $decimals = 0 ) {
		return number_format( (float) $number, (int) $decimals, '.', ',' );
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		return array_merge( $defaults, (array) $args );
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $show ) {
		if ( 'admin_email' === $show ) {
			return 'admin@example.com';
		}

		if ( 'name' === $show ) {
			return 'Bonus Hunt Test';
		}

		return '';
	}
}

if ( ! function_exists( 'wp_specialchars_decode' ) ) {
	function wp_specialchars_decode( $string, $quote_style = ENT_QUOTES ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return html_entity_decode( (string) $string, $quote_style, 'UTF-8' );
	}
}

if ( ! function_exists( 'get_locale' ) ) {
	function get_locale() {
		return 'en_US';
	}
}

if ( ! function_exists( 'get_available_languages' ) ) {
	function get_available_languages() {
		return array();
	}
}

if ( ! function_exists( 'wp_cache_get' ) ) {
	function wp_cache_get( $key, $group = '' ) {
		global $bhg_test_cache;

		if ( isset( $bhg_test_cache[ $group ][ $key ] ) ) {
			return $bhg_test_cache[ $group ][ $key ];
		}

		return false;
	}
}

if ( ! function_exists( 'wp_cache_set' ) ) {
	function wp_cache_set( $key, $value, $group = '', $expire = 0 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_cache;

		if ( ! isset( $bhg_test_cache[ $group ] ) ) {
			$bhg_test_cache[ $group ] = array();
		}

		$bhg_test_cache[ $group ][ $key ] = $value;

		return true;
	}
}

if ( ! function_exists( 'wp_cache_delete' ) ) {
	function wp_cache_delete( $key, $group = '' ) {
		global $bhg_test_cache;

		if ( isset( $bhg_test_cache[ $group ][ $key ] ) ) {
			unset( $bhg_test_cache[ $group ][ $key ] );
		}

		return true;
	}
}

if ( ! function_exists( 'wp_cache_flush_group' ) ) {
	function wp_cache_flush_group( $group ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_cache;

		if ( isset( $bhg_test_cache[ $group ] ) ) {
			unset( $bhg_test_cache[ $group ] );
		}

		return true;
	}
}

if ( ! function_exists( 'maybe_serialize' ) ) {
	function maybe_serialize( $data ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			return serialize( $data );
		}

		if ( is_serialized( $data, false ) ) {
			return $data;
		}

		return $data;
	}
}

if ( ! function_exists( 'is_serialized' ) ) {
	function is_serialized( $data, $strict = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( ! is_string( $data ) ) {
			return false;
		}

		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}

		return (bool) preg_match( '/^[aOsibd]:/', $data );
	}
}

if ( ! function_exists( 'maybe_unserialize' ) ) {
	function maybe_unserialize( $data ) {
		if ( is_serialized( $data, false ) ) {
			$value = @unserialize( trim( $data ) );
			if ( false === $value && 'b:0;' !== $data ) {
				return $data;
			}

			return $value;
		}

		return $data;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		global $bhg_test_options;

		return array_key_exists( $name, $bhg_test_options ) ? $bhg_test_options[ $name ] : $default;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	function add_option( $name, $value ) {
		global $bhg_test_options;

		if ( array_key_exists( $name, $bhg_test_options ) ) {
			return false;
		}

		$bhg_test_options[ $name ] = $value;

		return true;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value ) {
		global $bhg_test_options;

		$bhg_test_options[ $name ] = $value;

		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $name ) {
		global $bhg_test_options;

		unset( $bhg_test_options[ $name ] );

		return true;
	}
}

if ( ! function_exists( 'get_user_meta' ) ) {
	function get_user_meta( $user_id, $key, $single = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_user_meta;

		$user_id = (int) $user_id;
		if ( isset( $bhg_test_user_meta[ $user_id ][ $key ] ) ) {
			$value = $bhg_test_user_meta[ $user_id ][ $key ];

			return $single ? $value : array( $value );
		}

		return $single ? '' : array();
	}
}

if ( ! function_exists( 'update_user_meta' ) ) {
	function update_user_meta( $user_id, $key, $value ) {
		global $bhg_test_user_meta, $wpdb;

		$user_id = (int) $user_id;
		if ( ! isset( $bhg_test_user_meta[ $user_id ] ) ) {
			$bhg_test_user_meta[ $user_id ] = array();
		}

		$bhg_test_user_meta[ $user_id ][ $key ] = $value;

		if ( isset( $wpdb ) && method_exists( $wpdb, 'set_usermeta' ) ) {
			$wpdb->set_usermeta( $user_id, $key, $value );
		}

		return true;
	}
}

if ( ! function_exists( 'delete_user_meta' ) ) {
	function delete_user_meta( $user_id, $key ) {
		global $bhg_test_user_meta, $wpdb;

		$user_id = (int) $user_id;
		if ( isset( $bhg_test_user_meta[ $user_id ][ $key ] ) ) {
			unset( $bhg_test_user_meta[ $user_id ][ $key ] );
		}

		if ( isset( $wpdb ) && method_exists( $wpdb, 'delete_usermeta' ) ) {
			$wpdb->delete_usermeta( $user_id, $key );
		}

		return true;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $post_id, $key, $single = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_post_meta;

		$post_id = (int) $post_id;
		if ( isset( $bhg_test_post_meta[ $post_id ][ $key ] ) ) {
			$value = $bhg_test_post_meta[ $post_id ][ $key ];

			return $single ? $value : array( $value );
		}

		return $single ? '' : array();
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( $post_id, $key, $value ) {
		global $bhg_test_post_meta;

		$post_id = (int) $post_id;
		if ( ! isset( $bhg_test_post_meta[ $post_id ] ) ) {
			$bhg_test_post_meta[ $post_id ] = array();
		}

		$bhg_test_post_meta[ $post_id ][ $key ] = $value;

		return true;
	}
}

if ( ! function_exists( 'metadata_exists' ) ) {
	function metadata_exists( $type, $id, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( 'post' !== $type ) {
			return false;
		}

		$meta = get_post_meta( $id, $key, true );

		return '' !== $meta && null !== $meta;
	}
}

if ( ! function_exists( 'wp_insert_post' ) ) {
	function wp_insert_post( $postarr, $wp_error = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_posts, $bhg_test_next_post_id;

		$post_id = $bhg_test_next_post_id++;

		$bhg_test_posts[ $post_id ] = array(
			'ID'           => $post_id,
			'post_title'   => isset( $postarr['post_title'] ) ? $postarr['post_title'] : '',
			'post_name'    => isset( $postarr['post_name'] ) ? $postarr['post_name'] : sanitize_title( $postarr['post_title'] ),
			'post_content' => isset( $postarr['post_content'] ) ? $postarr['post_content'] : '',
			'post_status'  => isset( $postarr['post_status'] ) ? $postarr['post_status'] : 'draft',
			'post_type'    => isset( $postarr['post_type'] ) ? $postarr['post_type'] : 'page',
			'post_author'  => isset( $postarr['post_author'] ) ? (int) $postarr['post_author'] : 0,
		);

		return $post_id;
	}
}

if ( ! function_exists( 'get_page_by_path' ) ) {
	function get_page_by_path( $path, $output = OBJECT, $post_type = 'page' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		global $bhg_test_posts;

		foreach ( $bhg_test_posts as $post ) {
			if ( $post['post_name'] === $path && $post['post_type'] === $post_type ) {
				return ( OBJECT === $output ) ? (object) $post : $post;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'get_post_status' ) ) {
	function get_post_status( $post_id ) {
		global $bhg_test_posts;

		if ( is_object( $post_id ) ) {
			$post_id = $post_id->ID;
		}

		$post_id = (int) $post_id;

		return isset( $bhg_test_posts[ $post_id ] ) ? $bhg_test_posts[ $post_id ]['post_status'] : false;
	}
}

if ( ! function_exists( 'wp_update_post' ) ) {
	function wp_update_post( $postarr ) {
		global $bhg_test_posts;

		$post_id = isset( $postarr['ID'] ) ? (int) $postarr['ID'] : 0;
		if ( ! $post_id || ! isset( $bhg_test_posts[ $post_id ] ) ) {
			return 0;
		}

		$bhg_test_posts[ $post_id ] = array_merge( $bhg_test_posts[ $post_id ], $postarr );

		return $post_id;
	}
}

if ( ! function_exists( 'wp_untrash_post' ) ) {
	function wp_untrash_post( $post_id ) {
		global $bhg_test_posts;

		$post_id = (int) $post_id;
		if ( isset( $bhg_test_posts[ $post_id ] ) ) {
			$bhg_test_posts[ $post_id ]['post_status'] = 'draft';
		}

		return $post_id;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post_id ) {
		global $bhg_test_posts;

		$post_id = (int) $post_id;

		return isset( $bhg_test_posts[ $post_id ] ) ? (object) $bhg_test_posts[ $post_id ] : null;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public function __construct( $code = '', $message = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		}
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return 1;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		throw new RuntimeException( (string) $message );
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '' ) {
		return 'http://example.com/wp-admin/' . ltrim( $path, '/' );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		$path = ltrim( (string) $path, '/' );

		return 'http://example.com' . ( '' !== $path ? '/' . $path : '' );
	}
}

if ( ! function_exists( 'mysql2date' ) ) {
	function mysql2date( $format, $date, $translate = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		$timestamp = strtotime( (string) $date );

		if ( false === $timestamp ) {
			return ''; // Maintain WordPress behaviour of returning empty string on failure.
		}

		return gmdate( $format, $timestamp );
	}
}

if ( ! function_exists( 'network_admin_url' ) ) {
	function network_admin_url( $path = '' ) {
		return admin_url( $path );
	}
}

if ( ! function_exists( 'is_network_admin' ) ) {
	function is_network_admin() {
		return false;
	}
}

if ( ! function_exists( 'wp_register_style' ) ) {
	function wp_register_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_register_script' ) ) {
	function wp_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_add_inline_style' ) ) {
	function wp_add_inline_style( $handle, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script( $handle, $name, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return 'test-nonce';
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return 'test-nonce' === $nonce;
	}
}

if ( ! function_exists( 'check_admin_referer' ) ) {
	function check_admin_referer( $action, $name = '_wpnonce' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action, $name = '_wpnonce' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return '<input type="hidden" name="' . esc_attr( $name ) . '" value="test-nonce" />';
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $content ) {
		return $content;
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $location ) {
		return $location;
	}
}

if ( ! function_exists( 'wp_redirect' ) ) {
	function wp_redirect( $location ) {
		return $location;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return $value;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'remove_action' ) ) {
	function remove_action( $hook, $callback, $priority = 10 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'remove_filter' ) ) {
	function remove_filter( $hook, $callback, $priority = 10 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return true;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return '2024-01-01 00:00:00';
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );

		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'bhg_get_hunt_tournament_ids' ) ) {
	function bhg_get_hunt_tournament_ids( $hunt_id ) {
		global $wpdb;

		if ( ! isset( $wpdb->tournaments_hunts ) ) {
			return array();
		}

		$ids = array();
		foreach ( $wpdb->tournaments_hunts as $map ) {
			if ( (int) $map['hunt_id'] === (int) $hunt_id ) {
				$ids[] = (int) $map['tournament_id'];
			}
		}

		return array_values( array_unique( $ids ) );
	}
}

if ( ! function_exists( 'wp_list_pluck' ) ) {
	function wp_list_pluck( $input_list, $field ) {
		$values = array();

		foreach ( (array) $input_list as $item ) {
			if ( is_object( $item ) && isset( $item->{$field} ) ) {
				$values[] = $item->{$field};
			} elseif ( is_array( $item ) && isset( $item[ $field ] ) ) {
				$values[] = $item[ $field ];
			}
		}

		return $values;
	}
}

if ( ! class_exists( 'BHG_DB' ) ) {
	class BHG_DB {
		public static function migrate() {}
	}
}

require_once __DIR__ . '/support/class-mock-wpdb.php';

global $wpdb;
$wpdb = new MockWPDB();
$wpdb->set_table_exists( 'wp_usermeta' );

require_once __DIR__ . '/../includes/class-bhg-utils.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/core-pages.php';
require_once __DIR__ . '/../includes/class-bhg-models.php';
