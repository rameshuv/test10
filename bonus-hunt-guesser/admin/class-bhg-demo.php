<?php
/**
 * Demo Tools Admin Class
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Demo Tools administration and data reset.
 */
class BHG_Demo {

	/**
	 * Singleton instance.
	 *
	 * @var BHG_Demo|null
	 */
	protected static $instance = null;

	/**
	 * Parent menu slug.
	 *
	 * @var string
	 */
	protected $parent_slug = 'bhg';

	/**
	 * Capability required to manage demo data.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Submenu slug.
	 *
	 * @var string
	 */
	protected $menu_slug = 'bhg-demo-tools';

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->capability = apply_filters( 'bhg_demo_tools_capability', $this->capability );

		add_action( 'admin_menu', array( $this, 'maybe_register_menu' ), 20 );
		add_action( 'admin_post_bhg_demo_reset', array( $this, 'handle_reset' ) );
	}

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return BHG_Demo
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the submenu entry under the Bonus Hunt menu.
	 *
	 * @param string|null $parent_slug Optional parent slug override.
	 * @param string|null $capability  Optional capability override.
	 * @return void
	 */
	public function register_menu( $parent_slug = null, $capability = null ) {
		if ( null !== $parent_slug ) {
			$this->parent_slug = sanitize_title( (string) $parent_slug );
		}

		if ( null !== $capability && is_string( $capability ) && '' !== $capability ) {
			$this->capability = $capability;
		}

		add_submenu_page(
			$this->parent_slug,
			bhg_t( 'demo_tools', 'Demo Tools' ),
			bhg_t( 'demo_tools', 'Demo Tools' ),
			$this->capability,
			$this->menu_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Ensure the submenu exists when the admin menu is built.
	 *
	 * @return void
	 */
	public function maybe_register_menu() {
		if ( $this->submenu_exists() ) {
			return;
		}

		$this->register_menu();
	}

	/**
	 * Render the Demo Tools page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html( bhg_t( 'you_do_not_have_permission_to_access_this_page', 'You do not have permission to access this page' ) ) );
		}

		$counts     = $this->get_counts();
		$action_url = class_exists( 'BHG_Utils' ) ? BHG_Utils::admin_url( 'admin-post.php' ) : admin_url( 'admin-post.php' );

		require BHG_PLUGIN_DIR . 'admin/views/demo-data.php';
	}

	/**
	 * Handle demo data reset requests.
	 *
	 * @return void
	 */
	public function handle_reset() {
		if ( ! current_user_can( $this->capability ) ) {
			$this->redirect( 'noaccess' );
		}

		$nonce_valid = false;

		if ( class_exists( 'BHG_Utils' ) ) {
			$nonce_valid = BHG_Utils::verify_nonce( 'bhg_demo_reset' );
		} elseif ( isset( $_POST['bhg_demo_reset_nonce'] ) ) {
			$nonce_valid = wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['bhg_demo_reset_nonce'] ) ),
				'bhg_demo_reset'
			);
		}

		if ( ! $nonce_valid ) {
			$this->redirect( 'nonce' );
		}

		$result = false;

		if ( function_exists( 'bhg_reset_demo_and_seed' ) ) {
			try {
					bhg_reset_demo_and_seed();
					$result = true;
			} catch ( Throwable $throwable ) {
					do_action( 'bhg_demo_reset_error', $throwable );
			}
		}

		$this->redirect( $result ? 'demo_reset_ok' : 'demo_reset_error' );
	}

	/**
	 * Redirect back to the Demo Tools screen with an optional notice code.
	 *
	 * @param string $message Optional message code.
	 * @return void
	 */
	protected function redirect( $message = '' ) {
		$url = class_exists( 'BHG_Utils' ) ? BHG_Utils::admin_url( 'admin.php?page=' . $this->menu_slug ) : admin_url( 'admin.php?page=' . $this->menu_slug );

		if ( '' !== $message ) {
			$url = add_query_arg( 'bhg_msg', $message, $url );
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Determine if the submenu already exists.
	 *
	 * @return bool
	 */
	protected function submenu_exists() {
		global $submenu;

		if ( ! isset( $submenu[ $this->parent_slug ] ) || ! is_array( $submenu[ $this->parent_slug ] ) ) {
			return false;
		}

		foreach ( $submenu[ $this->parent_slug ] as $item ) {
			if ( isset( $item[2] ) && $this->menu_slug === $item[2] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gather diagnostic counts for relevant demo tables.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected function get_counts() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$tables = array(
			'hunts'        => array(
				'label' => bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ),
				'table' => "{$prefix}bhg_bonus_hunts",
			),
			'guesses'      => array(
				'label' => bhg_t( 'guesses', 'Guesses' ),
				'table' => "{$prefix}bhg_guesses",
			),
			'tournaments'  => array(
				'label' => bhg_t( 'tournaments', 'Tournaments:' ),
				'table' => "{$prefix}bhg_tournaments",
			),
			'ads'          => array(
				'label' => bhg_t( 'ads', 'Ads:' ),
				'table' => "{$prefix}bhg_ads",
			),
			'translations' => array(
				'label' => bhg_t( 'menu_translations', 'Translations' ),
				'table' => "{$prefix}bhg_translations",
			),
		);

		foreach ( $tables as $key => $info ) {
			$table  = esc_sql( $info['table'] );
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

			if ( $exists === $table ) {
				$tables[ $key ]['count'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			} else {
				$tables[ $key ]['count'] = null;
			}
		}

		return $tables;
	}
}
