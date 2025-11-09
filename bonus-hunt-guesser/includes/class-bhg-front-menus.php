<?php
/**
 * Front-end menu handling.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end menus.
 */
class BHG_Front_Menus {

	/**
	 * Registered menu locations handled by the plugin.
	 *
	 * @var string[]
	 */
	private const MENU_LOCATIONS = array( 'bhg_menu_admin', 'bhg_menu_user', 'bhg_menu_guest' );

	/**
	 * Set up hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_locations' ) );
		add_shortcode( 'bhg_nav', array( $this, 'nav_shortcode' ) );
		add_shortcode( 'bhg_menu', array( __CLASS__, 'menu_shortcode' ) );
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'filter_menu_item_classes' ), 10, 4 );
		add_filter( 'nav_menu_link_attributes', array( __CLASS__, 'filter_menu_link_attributes' ), 10, 4 );
		add_filter( 'nav_menu_submenu_css_class', array( __CLASS__, 'filter_submenu_classes' ), 10, 3 );
	}

	/**
	 * Register menu locations.
	 *
	 * @return void
	 */
	public function register_locations() {
		register_nav_menus(
			array(
				'bhg_menu_admin' => bhg_t( 'bhg_menu_admin', 'BHG Menu — Admin/Moderators' ),
				'bhg_menu_user'  => bhg_t( 'bhg_menu_loggedin', 'BHG Menu — Logged-in Users' ),
				'bhg_menu_guest' => bhg_t( 'bhg_menu_guests', 'BHG Menu — Guests' ),
			)
		);
	}

	/**
	 * Render navigation based on provided attributes.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Menu markup.
	 */
	public function nav_shortcode( $atts ) {
		$a   = shortcode_atts( array( 'area' => 'guest' ), $atts, 'bhg_nav' );
		$loc = 'bhg_menu_guest';
		if ( 'admin' === $a['area'] && current_user_can( 'edit_posts' ) ) {
			$loc = 'bhg_menu_admin';
		} elseif ( 'user' === $a['area'] && is_user_logged_in() ) {
			$loc = 'bhg_menu_user';
		} elseif ( 'guest' === $a['area'] && ! is_user_logged_in() ) {
			$loc = 'bhg_menu_guest';
		} elseif ( is_user_logged_in() ) {
			$loc = 'bhg_menu_user';
		}

		$out = wp_nav_menu(
			array(
				'theme_location'  => $loc,
				'container'       => 'nav',
				'container_class' => self::container_classes( $loc, 'bhg-nav bhg-menu' ),
				'menu_class'      => 'bhg-menu__list menu',
				'echo'            => false,
				'fallback_cb'     => false,
			)
		);

		if ( ! $out ) {
			return '<!-- BHG menu not assigned: ' . esc_html( $loc ) . ' -->';
		}
		return $out;
	}

	/**
	 * Render the correct menu location based on role/login.
	 *
	 * @param array $args Menu arguments.
	 * @return string Menu markup.
	 */
	public static function render_role_menu( $args = array() ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'moderate_comments' ) ) {
			$loc = 'bhg_menu_admin';
		} elseif ( is_user_logged_in() ) {
			$loc = 'bhg_menu_user';
		} else {
			$loc = 'bhg_menu_guest';
		}

		$defaults = array(
			'theme_location'  => $loc,
			'container'       => 'nav',
			'container_class' => self::container_classes( $loc ),
			'menu_class'      => 'bhg-menu__list menu',
			'fallback_cb'     => false,
			'echo'            => false,
		);
		$args     = wp_parse_args( $args, $defaults );
		$menu     = wp_nav_menu( $args );
		if ( ! $menu ) {
			$menu = '<nav class="' . esc_attr( self::container_classes( $loc ) ) . '"><ul class="bhg-menu__list"><li class="bhg-menu__item"><span class="bhg-menu__link">' . esc_html( bhg_t( 'menu_not_assigned', 'Menu not assigned.' ) ) . '</span></li></ul></nav>';
		}
		return $menu;
	}

	/**
	 * Shortcode: [bhg_menu].
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Menu markup.
	 */
	public static function menu_shortcode( $atts ) {
		unset( $atts );
		return self::render_role_menu();
	}

	/**
	 * Append plugin specific classes to menu list items.
	 *
	 * @param array    $classes Existing classes.
	 * @param WP_Post  $item    Menu item.
	 * @param stdClass $args    Menu arguments.
	 * @param int      $depth   Depth level.
	 * @return array
	 */
	public static function filter_menu_item_classes( $classes, $item, $args, $depth ) {
		unset( $depth );

		if ( empty( $args->theme_location ) || ! self::is_plugin_location( $args->theme_location ) ) {
			return $classes;
		}

		$classes[] = 'bhg-menu__item';

		if ( ! empty( $item->current ) || in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
			$classes[] = 'bhg-menu__item--current';
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Ensure menu links receive plugin CSS hooks.
	 *
	 * @param array    $atts Existing attributes.
	 * @param WP_Post  $item Menu item.
	 * @param stdClass $args Menu arguments.
	 * @param int      $depth Depth level.
	 * @return array
	 */
	public static function filter_menu_link_attributes( $atts, $item, $args, $depth ) {
		unset( $depth );

		if ( empty( $args->theme_location ) || ! self::is_plugin_location( $args->theme_location ) ) {
			return $atts;
		}

		$link_classes = isset( $atts['class'] ) ? $atts['class'] . ' ' : '';
		$link_classes = trim( $link_classes . 'bhg-menu__link' );

		if ( ! empty( $item->current ) || in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current_page_item', (array) $item->classes, true ) ) {
			$link_classes .= ' bhg-menu__link--current';
		}

		$atts['class'] = trim( $link_classes );

		return $atts;
	}

	/**
	 * Add submenu styling classes when rendering plugin menus.
	 *
	 * @param array    $classes Submenu classes.
	 * @param stdClass $args    Menu arguments.
	 * @param int      $depth   Depth level.
	 * @return array
	 */
	public static function filter_submenu_classes( $classes, $args, $depth ) {
		unset( $depth );

		if ( empty( $args->theme_location ) || ! self::is_plugin_location( $args->theme_location ) ) {
			return $classes;
		}

		$classes[] = 'bhg-menu__sublist';

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Determine if location belongs to plugin managed menus.
	 *
	 * @param string $location Menu location slug.
	 * @return bool
	 */
	protected static function is_plugin_location( $location ) {
		return in_array( $location, self::MENU_LOCATIONS, true );
	}

	/**
	 * Build container class list for a menu location.
	 *
	 * @param string $location     Menu location slug.
	 * @param string $base_classes Base classes to include.
	 * @return string
	 */
	protected static function container_classes( $location, $base_classes = 'bhg-menu' ) {
		$base = preg_split( '/\s+/', trim( $base_classes ) );
		$base = array_filter( array_map( 'sanitize_html_class', (array) $base ) );

		if ( self::is_plugin_location( $location ) ) {
			$base[] = 'bhg-menu--' . sanitize_html_class( str_replace( 'bhg_menu_', '', $location ) );
		}

		return implode( ' ', array_unique( $base ) );
	}
}

/* Stage-5 menu help. */
if ( is_admin() ) {
	add_action(
		'admin_notices',
		function () {
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification
			if ( strpos( $page, 'bhg' ) !== false ) {
				echo '<div class="notice notice-info"><p>';
				esc_html_e( 'Reminder: Assign your BHG menus (Admin/Moderator, Logged-in, Guest) under Appearance → Menus → Manage Locations. Use shortcode [bhg_nav] to display.', 'bonus-hunt-guesser' );
				echo '</p></div>';
			}
		}
	);
}
