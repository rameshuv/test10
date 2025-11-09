<?php
/**
 * Ads handling for Bonus Hunt Guesser.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ads management.
 */
class BHG_Ads {

	/**
	 * Allowed ad placement values.
	 *
	 * @var string[]
	 */
	private static $allowed_placements = array( 'footer', 'bottom', 'sidebar', 'shortcode' );

	/**
	 * Retrieve allowed ad placements.
	 *
	 * @return string[]
	 */
	public static function get_allowed_placements() {
		$placements = array_unique( array_merge( array( 'none' ), self::$allowed_placements ) );
		$placements = array_map( 'sanitize_key', $placements );

		/**
		 * Filters the list of allowed ad placement identifiers.
		 *
		 * @param string[] $placements Allowed placement identifiers.
		 */
		return apply_filters( 'bhg_ads_allowed_placements', $placements );
	}

	/**
	 * Initialize front-end hooks for ads.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_footer', array( 'BHG_Ads', 'render_footer' ) );
		add_shortcode( 'bhg_ad', array( 'BHG_Ads', 'shortcode' ) );
		add_shortcode( 'bhg_advertising', array( 'BHG_Ads', 'shortcode' ) );
	}

	/**
	 * Checks if front-end ads are enabled in plugin settings.
	 *
	 * @return bool
	 */
	protected static function ads_enabled() {
			$settings = get_option( 'bhg_plugin_settings', array() );
			$enabled  = isset( $settings['ads_enabled'] ) ? (int) $settings['ads_enabled'] : 1;
			return 1 === $enabled;
	}

	/**
	 * Determine current user's affiliate status (global toggle).
	 *
	 * @return bool
	 */
	protected static function user_is_affiliate( $affiliate_site_id = 0 ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$uid = get_current_user_id();

		if ( $affiliate_site_id > 0 && function_exists( 'bhg_is_user_affiliate_for_site' ) ) {
			return bhg_is_user_affiliate_for_site( $uid, $affiliate_site_id );
		}

		if ( function_exists( 'bhg_is_user_affiliate' ) ) {
			return bhg_is_user_affiliate( $uid );
		}

		return (bool) get_user_meta( $uid, 'bhg_is_affiliate', true );
	}

	/**
	 * Whether current visitor matches the ad's visibility setting.
	 *
	 * @param string $visibility Visibility rule.
	 *
	 * @return bool
	 */
	protected static function visibility_ok( $visibility, $context = array() ) {
		$visibility        = is_string( $visibility ) ? strtolower( $visibility ) : 'all';
		$affiliate_site_id = isset( $context['affiliate_site_id'] ) ? (int) $context['affiliate_site_id'] : 0;

		switch ( $visibility ) {
			case 'logged_in':
				return is_user_logged_in();
			case 'guests':
				return ! is_user_logged_in();
			case 'affiliates':
				return self::user_is_affiliate( $affiliate_site_id );
			case 'non_affiliates':
				if ( ! is_user_logged_in() ) {
					return false;
				}
				return ! self::user_is_affiliate( $affiliate_site_id );
			case 'all':
			default:
				return true;
		}
	}

	/**
	 * Determine if an ad row should be shown for the current request.
	 *
	 * @param object $row     Database row containing ad data.
	 * @param array  $context Optional context (e.g. affiliate_site_id).
	 * @return bool
	 */
	public static function should_display_row( $row, $context = array() ) {
		$visibility = isset( $row->visible_to ) ? $row->visible_to : 'all';
		if ( ! self::visibility_ok( $visibility, $context ) ) {
			return false;
		}

		$targets = isset( $row->target_pages ) ? $row->target_pages : '';
		if ( ! self::page_target_ok( $targets ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Public wrapper for rendering an ad row.
	 *
	 * @param object $row Database row containing ad data.
	 * @return string
	 */
	public static function get_row_markup( $row ) {
		return self::render_ad_row( $row );
	}

	/**
	 * Whether the current page is one of the targeted pages (by slug), if any are set.
	 *
	 * @param string $target_pages Comma-separated list of target page slugs.
	 *
	 * @return bool
	 */
	protected static function page_target_ok( $target_pages ) {
		$target_pages = is_string( $target_pages ) ? trim( $target_pages ) : '';
		if ( '' === $target_pages ) {
			return true; // No restriction.
		}

		// Normalize list of slugs.
		$slugs = array_filter(
			array_map(
				function ( $s ) {
					return sanitize_title( wp_unslash( trim( $s ) ) );
				},
				explode( ',', $target_pages )
			)
		);

		if ( empty( $slugs ) ) {
			return true;
		}

		// On singular pages, check post_name; otherwise, do not show.
		if ( is_singular() ) {
			$post = get_post();
			if ( ! $post ) {
				return false;
			}
			$slug = $post->post_name;
			return in_array( $slug, $slugs, true );
		}
		return false;
	}

	/**
	 * Render a single ad row to HTML.
	 *
	 * @param object $row Database row object.
	 *
	 * @return string
	 */
	protected static function render_ad_row( $row ) {
		$content = isset( $row->content ) ? wp_kses_post( $row->content ) : '';
		if ( '' === $content ) {
			return '';
		}

		$placement = isset( $row->placement ) ? sanitize_html_class( $row->placement ) : 'none';
		$link      = isset( $row->link_url ) ? esc_url( $row->link_url ) : '';

		if ( $link ) {
			$content = sprintf(
				'<a class="bhg-ad-link" href="%1$s">%2$s</a>',
				$link,
				$content
			);
		}

		return sprintf(
			'<div class="bhg-ad bhg-ad-%1$s">%2$s</div>',
			esc_attr( $placement ),
			$content
		);
	}

	/**
	 * Fetch active ads for a placement.
	 *
	 * @param string $placement Ad placement.
	 *
	 * @return array
	 */
	protected static function get_ads_for_placement( $placement = 'footer' ) {
		global $wpdb;
		$table          = esc_sql( $wpdb->prefix . 'bhg_ads' );
		$allowed_tables = array( $table );
		if ( ! in_array( $table, $allowed_tables, true ) ) {
			return array();
		}

		$placement = sanitize_key( $placement );
		if ( ! in_array( $placement, self::get_allowed_placements(), true ) ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, content, link_url, placement, visible_to, target_pages FROM {$table} WHERE active = 1 AND placement = %s ORDER BY id DESC",
				$placement
			)
		);
	}

	/**
	 * Render footer-placed ads.
	 *
	 * @return void
	 */
	public static function render_footer() {
		if ( is_admin() ) {
			return;
		}
		if ( ! self::ads_enabled() ) {
			return;
		}

		$placements = array( 'footer', 'bottom' );
		foreach ( $placements as $place ) {
			$ads = self::get_ads_for_placement( $place );
			if ( empty( $ads ) ) {
				continue;
			}

			$out = array();
			foreach ( $ads as $row ) {
				if ( ! self::should_display_row( $row ) ) {
					continue;
				}

				$markup = self::get_row_markup( $row );
				if ( '' !== $markup ) {
					$out[] = $markup;
				}
			}

			$out = array_filter( $out );
			if ( ! empty( $out ) ) {
				echo '<div class="bhg-ads bhg-ads-' . esc_attr( $place ) . '">';
				echo implode( "\n", $out );
				echo '</div>';
			}
		}
	}

	/**
	 * Shortcode handler for rendering a single ad row regardless of placement.
	 *
	 * Usage examples:
	 * [bhg_ad id="123"]
	 * [bhg_advertising ad="123" status="inactive"]
	 *
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Content enclosed by shortcode (unused).
	 * @param string $tag     Shortcode tag.
	 *
	 * @return string
	 */
	public static function shortcode( $atts = array(), $content = '', $tag = '' ) {
		if ( ! self::ads_enabled() ) {
			return '';
		}

		$a = shortcode_atts(
			array(
				'id'     => 0,
				'ad'     => 0,
				'status' => 'active',
			),
			$atts,
			$tag
		);

		$id = $a['id'] ? (int) $a['id'] : (int) $a['ad'];
		if ( $id <= 0 ) {
			return '';
		}

		$status = strtolower( trim( $a['status'] ) );

		global $wpdb;
		$table          = esc_sql( $wpdb->prefix . 'bhg_ads' );
		$allowed_tables = array( $table );
		if ( ! in_array( $table, $allowed_tables, true ) ) {
				return '';
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, content, placement, visible_to, target_pages, active, link_url FROM {$table} WHERE id = %d",
				$id
			)
		);
		if ( ! $row ) {
			return '';
		}
		if ( ! in_array( $row->placement, self::get_allowed_placements(), true ) ) {
			return '';
		}

		if ( 'all' !== $status ) {
			$expected = ( 'inactive' === $status ) ? 0 : 1;
			if ( (int) $row->active !== $expected ) {
				return '';
			}
		}

		if ( ! self::visibility_ok( $row->visible_to ) ) {
			return '';
		}
		if ( ! self::page_target_ok( $row->target_pages ) ) {
			return '';
		}

		return self::render_ad_row( $row );
	}
}
