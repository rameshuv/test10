<?php
/**
 * Prize management utilities.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Provides CRUD helpers for prizes and hunt associations.
 */
class BHG_Prizes {

		/**
		 * Valid prize categories.
		 *
		 * @return string[]
		 */
public static function get_categories() {
return array( 'cash_money', 'casino_money', 'coupons', 'merchandise', 'various' );
}

/**
 * Allowed click actions for prize cards.
 *
 * @return string[]
 */
public static function get_click_actions() {
return array( 'link', 'new', 'image', 'none' );
}

/**
 * Sanitize a click action keyword.
 *
 * @param string $action  Raw action keyword.
 * @param string $default Default value when invalid.
 * @return string
 */
public static function sanitize_click_action( $action, $default = 'link' ) {
$action = sanitize_key( (string) $action );

if ( in_array( $action, self::get_click_actions(), true ) ) {
return $action;
}

return in_array( $default, self::get_click_actions(), true ) ? $default : 'link';
}

/**
 * Sanitize a link target keyword.
 *
 * @param string $target  Raw target keyword.
 * @param string $default Default fallback target.
 * @return string
 */
public static function sanitize_link_target( $target, $default = '_self' ) {
$target   = sanitize_key( (string) $target );
$allowed  = array( '_self', '_blank' );
$resolved = '_' === substr( $target, 0, 1 ) ? $target : '_' . $target;

if ( in_array( $resolved, $allowed, true ) ) {
return $resolved;
}

return in_array( $default, $allowed, true ) ? $default : '_self';
}

/**
 * Default display settings for prize sections.
 *
 * @return array
 */
public static function default_display_settings() {
return array(
'carousel_visible'  => 1,
'carousel_total'    => 0,
'carousel_autoplay' => 0,
'carousel_interval' => 5000,
'hide_heading'      => 0,
'heading_text'      => '',
'show_title'        => 1,
'show_description'  => 1,
'show_category'     => 1,
'show_image'        => 1,
'category_links'    => 1,
'click_action'      => 'inherit',
'link_target'       => 'inherit',
'category_target'   => 'inherit',
);
}

/**
 * Retrieve saved prize display settings.
 *
 * @return array
 */
public static function get_display_settings() {
$defaults = self::default_display_settings();
$settings = get_option( 'bhg_prize_display_settings', array() );

if ( ! is_array( $settings ) ) {
$settings = array();
}

return wp_parse_args( self::sanitize_display_settings( $settings ), $defaults );
}

/**
 * Sanitize display settings input.
 *
 * @param array $input Raw settings array.
 * @return array
 */
public static function sanitize_display_settings( $input ) {
$input    = is_array( $input ) ? $input : array();
$defaults = self::default_display_settings();

$visible  = isset( $input['carousel_visible'] ) ? absint( $input['carousel_visible'] ) : (int) $defaults['carousel_visible'];
$total    = isset( $input['carousel_total'] ) ? absint( $input['carousel_total'] ) : (int) $defaults['carousel_total'];
$autoplay = ! empty( $input['carousel_autoplay'] ) ? 1 : (int) $defaults['carousel_autoplay'];
$interval = isset( $input['carousel_interval'] ) ? absint( $input['carousel_interval'] ) : (int) $defaults['carousel_interval'];
$interval = $interval < 1000 ? 1000 : $interval;
$hide     = ! empty( $input['hide_heading'] ) ? 1 : (int) $defaults['hide_heading'];
$heading  = isset( $input['heading_text'] ) ? sanitize_text_field( wp_unslash( $input['heading_text'] ) ) : (string) $defaults['heading_text'];

$show_title       = isset( $input['show_title'] ) ? (int) (bool) $input['show_title'] : (int) $defaults['show_title'];
$show_description = isset( $input['show_description'] ) ? (int) (bool) $input['show_description'] : (int) $defaults['show_description'];
$show_category    = isset( $input['show_category'] ) ? (int) (bool) $input['show_category'] : (int) $defaults['show_category'];
$show_image       = isset( $input['show_image'] ) ? (int) (bool) $input['show_image'] : (int) $defaults['show_image'];
$category_links   = isset( $input['category_links'] ) ? (int) (bool) $input['category_links'] : (int) $defaults['category_links'];
$click_action     = isset( $input['click_action'] ) ? self::sanitize_click_default( $input['click_action'] ) : $defaults['click_action'];
$link_target      = isset( $input['link_target'] ) ? self::sanitize_link_default( $input['link_target'] ) : $defaults['link_target'];
$category_target  = isset( $input['category_target'] ) ? self::sanitize_link_default( $input['category_target'] ) : $defaults['category_target'];

return array(
'carousel_visible'  => max( 1, $visible ),
'carousel_total'    => $total,
'carousel_autoplay' => $autoplay,
'carousel_interval' => $interval,
'hide_heading'      => $hide,
'heading_text'      => $heading,
'show_title'        => $show_title,
'show_description'  => $show_description,
'show_category'     => $show_category,
'show_image'        => $show_image,
'category_links'    => $category_links,
'click_action'      => $click_action,
'link_target'       => $link_target,
'category_target'   => $category_target,
);
}

/**
 * Sanitize a click action default allowing inherit.
 *
 * @param string $value Raw input value.
 * @return string
 */
protected static function sanitize_click_default( $value ) {
$value = sanitize_key( (string) $value );

if ( in_array( $value, array( 'inherit', '' ), true ) ) {
return 'inherit';
}

return self::sanitize_click_action( $value, 'link' );
}

/**
 * Sanitize a link target default allowing inherit.
 *
 * @param string $value Raw input value.
 * @return string
 */
protected static function sanitize_link_default( $value ) {
$value = sanitize_key( (string) $value );

if ( in_array( $value, array( 'inherit', '' ), true ) ) {
return 'inherit';
}

return self::sanitize_link_target( $value, '_self' );
}

/**
 * Persist display settings.
 *
 * @param array $input Raw settings.
 * @return void
 */
public static function update_display_settings( $input ) {
$sanitized = self::sanitize_display_settings( $input );
update_option( 'bhg_prize_display_settings', $sanitized );
}

/**
 * Retrieve default display toggles and behaviours.
 *
 * @return array
 */
public static function get_display_defaults() {
$settings = self::get_display_settings();

return array(
'show_title'       => isset( $settings['show_title'] ) ? (bool) $settings['show_title'] : true,
'show_description' => isset( $settings['show_description'] ) ? (bool) $settings['show_description'] : true,
'show_category'    => isset( $settings['show_category'] ) ? (bool) $settings['show_category'] : true,
'show_image'       => isset( $settings['show_image'] ) ? (bool) $settings['show_image'] : true,
'category_links'   => isset( $settings['category_links'] ) ? (bool) $settings['category_links'] : true,
'click_action'     => isset( $settings['click_action'] ) ? self::sanitize_click_default( $settings['click_action'] ) : 'inherit',
'link_target'      => isset( $settings['link_target'] ) ? self::sanitize_link_default( $settings['link_target'] ) : 'inherit',
'category_target'  => isset( $settings['category_target'] ) ? self::sanitize_link_default( $settings['category_target'] ) : 'inherit',
);
}

/**
 * Prepare normalized display overrides for prize cards.
 *
 * @param array $overrides Raw overrides.
 * @return array
 */
public static function prepare_display_overrides( $overrides = array() ) {
$overrides = is_array( $overrides ) ? $overrides : array();

return array(
'show_title'       => self::parse_yes_no_override( $overrides, 'show_title' ),
'show_description' => self::parse_yes_no_override( $overrides, 'show_description' ),
'show_category'    => self::parse_yes_no_override( $overrides, 'show_category' ),
'show_image'       => self::parse_yes_no_override( $overrides, 'show_image' ),
'category_links'   => self::parse_yes_no_override( $overrides, 'category_links' ),
'click_action'     => isset( $overrides['click_action'] ) ? self::sanitize_click_override( $overrides['click_action'] ) : 'inherit',
'link_target'      => isset( $overrides['link_target'] ) ? self::sanitize_link_override( $overrides['link_target'] ) : 'inherit',
'category_target'  => isset( $overrides['category_target'] ) ? self::sanitize_link_override( $overrides['category_target'] ) : 'inherit',
);
}

/**
 * Parse yes/no/inherit overrides.
 *
 * @param array  $source Array containing value.
 * @param string $key    Key to read.
 * @return bool|null
 */
protected static function parse_yes_no_override( $source, $key ) {
if ( ! isset( $source[ $key ] ) ) {
return null;
}

$value = strtolower( (string) $source[ $key ] );

if ( in_array( $value, array( 'inherit', 'default', '' ), true ) ) {
return null;
}

return in_array( $value, array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * Sanitize click override keyword allowing inherit.
 *
 * @param string $value Raw keyword.
 * @return string
 */
protected static function sanitize_click_override( $value ) {
$value = sanitize_key( (string) $value );

if ( in_array( $value, array( 'inherit', 'default', '' ), true ) ) {
return 'inherit';
}

return self::sanitize_click_action( $value, 'inherit' === $value ? 'link' : $value );
}

/**
 * Sanitize link override keyword allowing inherit.
 *
 * @param string $value Raw keyword.
 * @return string
 */
protected static function sanitize_link_override( $value ) {
$value = sanitize_key( (string) $value );

if ( in_array( $value, array( 'inherit', 'default', '' ), true ) ) {
return 'inherit';
}

return self::sanitize_link_target( $value, '_self' );
}

/**
 * Prepare section-level options for prize rendering.
 *
 * @param array $overrides Raw overrides.
 * @return array
 */
public static function prepare_section_options( $overrides = array() ) {
$settings  = self::get_display_settings();
$options   = array();
$overrides = is_array( $overrides ) ? $overrides : array();

$options['carousel_visible']  = max( 1, isset( $overrides['carousel_visible'] ) ? absint( $overrides['carousel_visible'] ) : (int) $settings['carousel_visible'] );
$options['carousel_total']    = isset( $overrides['carousel_total'] ) ? absint( $overrides['carousel_total'] ) : (int) $settings['carousel_total'];
$options['limit']             = isset( $overrides['limit'] ) ? absint( $overrides['limit'] ) : $options['carousel_total'];
$options['carousel_autoplay'] = isset( $overrides['carousel_autoplay'] ) ? ( ! empty( $overrides['carousel_autoplay'] ) ) : ( ! empty( $settings['carousel_autoplay'] ) );
$options['carousel_interval'] = isset( $overrides['carousel_interval'] ) ? max( 1000, absint( $overrides['carousel_interval'] ) ) : (int) max( 1000, $settings['carousel_interval'] );
$options['hide_heading']      = isset( $overrides['hide_heading'] ) ? ( ! empty( $overrides['hide_heading'] ) ) : ( ! empty( $settings['hide_heading'] ) );

$heading = '';
if ( isset( $overrides['heading_text'] ) ) {
$heading = sanitize_text_field( wp_unslash( $overrides['heading_text'] ) );
} elseif ( ! empty( $settings['heading_text'] ) ) {
$heading = sanitize_text_field( $settings['heading_text'] );
}

$options['heading_text'] = $heading;

return $options;
}

/**
 * Resolve a tri-state flag.
 *
 * @param bool|null $override     Override value.
 * @param bool       $prize_flag  Stored value on prize row.
 * @param bool|null $default_flag Default value from settings.
 * @return bool
 */
public static function resolve_display_flag( $override, $prize_flag, $default_flag = null ) {
if ( null !== $override ) {
return (bool) $override;
}

if ( null !== $default_flag ) {
return (bool) $default_flag;
}

return (bool) $prize_flag;
}

/**
 * Resolve click action using override and prize default.
 *
 * @param string $override_action Override keyword.
 * @param string $prize_action    Prize-level action keyword.
 * @param string $default_action  Default keyword from settings.
 * @return string
 */
public static function resolve_click_action( $override_action, $prize_action, $default_action = 'inherit' ) {
$override_action = self::sanitize_click_default( $override_action );
$prize_action    = self::sanitize_click_action( $prize_action, 'link' );
$default_action  = self::sanitize_click_default( $default_action );

if ( 'inherit' !== $override_action ) {
return $override_action;
}

if ( 'inherit' !== $default_action ) {
return $default_action;
}

return $prize_action;
}

/**
 * Resolve link target using override and stored value.
 *
 * @param string $override_target Override keyword.
 * @param string $prize_target    Prize-level target.
 * @param string $default_target  Default keyword from settings.
 * @return string
 */
public static function resolve_link_target( $override_target, $prize_target, $default_target = 'inherit' ) {
$override_target = self::sanitize_link_default( $override_target );
$prize_target    = self::sanitize_link_target( $prize_target, '_self' );
$default_target  = self::sanitize_link_default( $default_target );

if ( 'inherit' !== $override_target ) {
return $override_target;
}

if ( 'inherit' !== $default_target ) {
return $default_target;
}

return $prize_target;
}

/**
 * Retrieve the stored prize link URL.
 *
 * @param object $prize Prize row.
 * @return string
 */
public static function get_prize_link( $prize ) {
if ( ! $prize || ! is_object( $prize ) ) {
return '';
}

if ( empty( $prize->link_url ) ) {
return '';
}

return esc_url_raw( $prize->link_url );
}

/**
 * Retrieve the stored category link URL.
 *
 * @param object $prize Prize row.
 * @return string
 */
public static function get_category_link( $prize ) {
if ( ! $prize || ! is_object( $prize ) ) {
return '';
}

if ( empty( $prize->category_link_url ) ) {
return '';
}

return esc_url_raw( $prize->category_link_url );
}

/**
 * Determine the best-fit image ID to use for full-size displays.
 *
 * @param object $prize Prize row.
 * @return int
 */
protected static function get_full_image_id( $prize ) {
if ( ! $prize || ! is_object( $prize ) ) {
return 0;
}

$fields = array( 'image_large', 'image_medium', 'image_small' );

foreach ( $fields as $field ) {
if ( isset( $prize->$field ) && $prize->$field ) {
$id = absint( $prize->$field );
if ( $id > 0 ) {
return $id;
}
}
}

return 0;
}

/**
 * Retrieve the full-size image URL for a prize card.
 *
 * @param object $prize Prize row.
 * @return string
 */
public static function get_full_image_url( $prize ) {
$id = self::get_full_image_id( $prize );

if ( $id <= 0 ) {
return '';
}

$url = wp_get_attachment_url( $id );

return $url ? esc_url_raw( $url ) : '';
}

		/**
		 * Normalize a prize type string.
		 *
		 * @param string $type Raw prize type.
		 * @return string Normalized prize type (regular|premium).
		 */
	protected static function normalize_prize_type( $type ) {
			$type = sanitize_key( (string) $type );

		if ( 'premium' === $type ) {
				return 'premium';
		}

			return 'regular';
	}

		/**
		 * Default CSS settings for prize blocks.
		 *
		 * @return array
		 */
	public static function default_css_settings() {
			return array(
				'border'       => '',
				'border_color' => '',
				'padding'      => '',
				'margin'       => '',
				'background'   => '',
			);
	}

		/**
		 * Sanitize CSS settings array.
		 *
		 * @param array $input Raw input values.
		 * @return array
		 */
	public static function sanitize_css_settings( $input ) {
			$defaults = self::default_css_settings();
			$output   = array();

		foreach ( $defaults as $key => $default ) {
			if ( isset( $input[ $key ] ) && is_string( $input[ $key ] ) ) {
				$value = trim( wp_unslash( $input[ $key ] ) );
			} else {
					$value = $default;
			}

			if ( in_array( $key, array( 'border_color', 'background' ), true ) ) {
					$value = sanitize_hex_color_no_hash( ltrim( $value, '#' ) );
					$value = $value ? '#' . $value : '';
			} else {
					$value = sanitize_text_field( $value );
			}

				$output[ $key ] = $value;
		}

			return $output;
	}

		/**
		 * Extract CSS settings from a database row.
		 *
		 * @param object $prize Prize row from the database.
		 * @return array
		 */
	public static function get_css_settings_from_row( $prize ) {
			$defaults = self::default_css_settings();

		if ( ! $prize || ! is_object( $prize ) ) {
				return $defaults;
		}

			$map = array(
				'border'       => 'css_border',
				'border_color' => 'css_border_color',
				'padding'      => 'css_padding',
				'margin'       => 'css_margin',
				'background'   => 'css_background',
			);

			foreach ( $map as $key => $column ) {
				if ( isset( $prize->$column ) && is_string( $prize->$column ) ) {
						$defaults[ $key ] = sanitize_text_field( $prize->$column );
				}
			}

			return $defaults;
	}

		/**
		 * Return attachment IDs and preview URLs for the configured prize images.
		 *
		 * @param object $prize Prize row from the database.
		 * @return array
		 */
       public static function get_attachment_sources( $prize ) {
               $map     = array(
                       'small'  => 'image_small',
                       'medium' => 'image_medium',
                       'big'    => 'image_large',
               );
               $sources = array();

               foreach ( $map as $size => $column ) {
                       $id               = ( $prize && isset( $prize->$column ) ) ? absint( $prize->$column ) : 0;
                       $sources[ $size ] = array(
                               'id'  => $id,
                               'url' => $prize ? self::get_image_url( $prize, $size ) : '',
                       );
               }

               $full_id  = self::get_full_image_id( $prize );
               $full_url = $full_id ? wp_get_attachment_url( $full_id ) : '';

               $sources['full'] = array(
                       'id'  => $full_id,
                       'url' => $full_url ? esc_url( $full_url ) : '',
               );

               return $sources;
       }

		/**
		 * Prepare a prize row for JSON serialization (AJAX usage).
		 *
		 * @param object $prize Prize row from the database.
		 * @return array
		 */
       public static function format_prize_for_response( $prize ) {
               if ( ! $prize || ! is_object( $prize ) ) {
                       return array();
               }

               return array(
                       'id'                  => isset( $prize->id ) ? (int) $prize->id : 0,
                       'title'               => isset( $prize->title ) ? sanitize_text_field( $prize->title ) : '',
                       'description'         => isset( $prize->description ) ? wp_kses_post( $prize->description ) : '',
                       'category'            => isset( $prize->category ) ? sanitize_key( $prize->category ) : 'various',
                       'active'              => ! empty( $prize->active ) ? 1 : 0,
                       'type'                => isset( $prize->prize_type ) ? self::normalize_prize_type( $prize->prize_type ) : 'regular',
                       'link_url'            => isset( $prize->link_url ) ? esc_url( $prize->link_url ) : '',
                       'link_target'         => isset( $prize->link_target ) ? self::sanitize_link_target( $prize->link_target, '_self' ) : '_self',
                       'click_action'        => isset( $prize->click_action ) ? self::sanitize_click_action( $prize->click_action, 'link' ) : 'link',
                       'category_link_url'   => isset( $prize->category_link_url ) ? esc_url( $prize->category_link_url ) : '',
                       'category_link_target'=> isset( $prize->category_link_target ) ? self::sanitize_link_target( $prize->category_link_target, '_self' ) : '_self',
                       'show_title'          => isset( $prize->show_title ) ? (int) $prize->show_title : 1,
                       'show_description'    => isset( $prize->show_description ) ? (int) $prize->show_description : 1,
                       'show_category'       => isset( $prize->show_category ) ? (int) $prize->show_category : 1,
                       'show_image'          => isset( $prize->show_image ) ? (int) $prize->show_image : 1,
                       'css'                 => self::get_css_settings_from_row( $prize ),
                       'images'              => self::get_attachment_sources( $prize ),
               );
       }

		/**
		 * Retrieve a list of prizes.
		 *
		 * @param array $args Optional query args (category, active, search).
		 * @return array
		 */
	public static function get_prizes( $args = array() ) {
			global $wpdb;

			$table = $wpdb->prefix . 'bhg_prizes';

			$where  = array();
			$params = array();

		if ( isset( $args['category'] ) && $args['category'] ) {
				$category = sanitize_key( $args['category'] );
			if ( in_array( $category, self::get_categories(), true ) ) {
				$where[]  = 'category = %s';
				$params[] = $category;
			}
		}

		if ( isset( $args['active'] ) && '' !== $args['active'] ) {
				$active   = (int) $args['active'];
				$where[]  = 'active = %d';
				$params[] = $active ? 1 : 0;
		}

		if ( isset( $args['search'] ) && '' !== $args['search'] ) {
				$like     = '%' . $wpdb->esc_like( wp_unslash( $args['search'] ) ) . '%';
				$where[]  = '(title LIKE %s OR description LIKE %s)';
				$params[] = $like;
				$params[] = $like;
		}

			$sql = "SELECT * FROM {$table}";
		if ( ! empty( $where ) ) {
				$sql .= ' WHERE ' . implode( ' AND ', $where );
		}
			$sql .= ' ORDER BY title ASC';

		if ( ! empty( $params ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
				return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
		}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			return $wpdb->get_results( $sql );
	}

		/**
		 * Fetch a single prize.
		 *
		 * @param int $id Prize ID.
		 * @return object|null
		 */
	public static function get_prize( $id ) {
			global $wpdb;
			$table = $wpdb->prefix . 'bhg_prizes';

			$id = absint( $id );
		if ( $id <= 0 ) {
				return null;
		}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
	}

		/**
		 * Retrieve multiple prizes by ID.
		 *
		 * The resulting array preserves the order of the provided identifiers.
		 *
		 * @param array $ids Prize identifiers.
		 * @return array List of prize objects.
		 */
	public static function get_prizes_by_ids( $ids ) {
			global $wpdb;

			$clean_ids = array();
		foreach ( (array) $ids as $maybe_id ) {
				$maybe_id = absint( $maybe_id );
			if ( $maybe_id > 0 ) {
				$clean_ids[ $maybe_id ] = $maybe_id;
			}
		}

		if ( empty( $clean_ids ) ) {
				return array();
		}

			$table        = $wpdb->prefix . 'bhg_prizes';
			$placeholders = implode( ', ', array_fill( 0, count( $clean_ids ), '%d' ) );
			$sql          = "SELECT * FROM {$table} WHERE id IN ({$placeholders})";

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$results = $wpdb->get_results( $wpdb->prepare( $sql, array_values( $clean_ids ) ) );
		if ( empty( $results ) ) {
				return array();
		}

			$indexed = array();
		foreach ( $results as $row ) {
				$indexed[ (int) $row->id ] = $row;
		}

			$ordered = array();
		foreach ( $clean_ids as $id ) {
			if ( isset( $indexed[ $id ] ) ) {
					$ordered[] = $indexed[ $id ];
			}
		}

			return $ordered;
	}

		/**
		 * Insert or update a prize.
		 *
		 * @param array $data Prize data.
		 * @param int   $id   Optional existing ID.
		 * @return int|false Prize ID on success, false otherwise.
		 */
	public static function save_prize( $data, $id = 0 ) {
			global $wpdb;

			$table = $wpdb->prefix . 'bhg_prizes';

                        $defaults = array(
                                'title'               => '',
                                'description'         => '',
                                'category'            => 'various',
                                'link_url'            => '',
                                'link_target'         => '_self',
                                'click_action'        => 'link',
                                'category_link_url'   => '',
                                'category_link_target'=> '_self',
                                'image_small'         => 0,
                                'image_medium'        => 0,
                                'image_large'         => 0,
                                'show_title'          => 1,
                                'show_description'    => 1,
                                'show_category'       => 1,
                                'show_image'          => 1,
                                'css_settings'        => self::default_css_settings(),
                                'active'              => 1,
                        );

			$data = wp_parse_args( $data, $defaults );

			$category = sanitize_key( $data['category'] );
			if ( ! in_array( $category, self::get_categories(), true ) ) {
					$category = 'various';
			}

                        $row = array(
                                'title'               => sanitize_text_field( $data['title'] ),
                                'description'         => wp_kses_post( $data['description'] ),
                                'category'            => $category,
                                'link_url'            => esc_url_raw( $data['link_url'] ),
                                'link_target'         => self::sanitize_link_target( $data['link_target'], '_self' ),
                                'click_action'        => self::sanitize_click_action( $data['click_action'], 'link' ),
                                'category_link_url'   => esc_url_raw( $data['category_link_url'] ),
                                'category_link_target'=> self::sanitize_link_target( $data['category_link_target'], '_self' ),
                                'image_small'         => isset( $data['image_small'] ) ? absint( $data['image_small'] ) : 0,
                                'image_medium'        => isset( $data['image_medium'] ) ? absint( $data['image_medium'] ) : 0,
                                'image_large'         => isset( $data['image_large'] ) ? absint( $data['image_large'] ) : 0,
                                'show_title'          => ! empty( $data['show_title'] ) ? 1 : 0,
                                'show_description'    => ! empty( $data['show_description'] ) ? 1 : 0,
                                'show_category'       => ! empty( $data['show_category'] ) ? 1 : 0,
                                'show_image'          => ! empty( $data['show_image'] ) ? 1 : 0,
                                'active'              => ! empty( $data['active'] ) ? 1 : 0,
                        );

			$css_settings = isset( $data['css_settings'] ) ? $data['css_settings'] : array();
			$css_settings = self::sanitize_css_settings( $css_settings );

			$row['css_border']       = $css_settings['border'];
			$row['css_border_color'] = $css_settings['border_color'];
			$row['css_padding']      = $css_settings['padding'];
			$row['css_margin']       = $css_settings['margin'];
			$row['css_background']   = $css_settings['background'];

$formats = array(
'%s', // title.
'%s', // description.
'%s', // category.
'%s', // link_url.
'%s', // link_target.
'%s', // click_action.
'%s', // category_link_url.
'%s', // category_link_target.
'%d', // image_small.
'%d', // image_medium.
'%d', // image_large.
'%d', // show_title.
'%d', // show_description.
'%d', // show_category.
'%d', // show_image.
'%d', // active.
'%s', // css_border.
'%s', // css_border_color.
'%s', // css_padding.
'%s', // css_margin.
'%s', // css_background.
);

			if ( $id > 0 ) {
					$row['updated_at'] = current_time( 'mysql' );
					$formats[]         = '%s';
					$result            = $wpdb->update( $table, $row, array( 'id' => $id ), $formats, array( '%d' ) );
				if ( false === $result ) {
						return false;
				}
					return $id;
			}

			$row['created_at'] = current_time( 'mysql' );
			$row['updated_at'] = $row['created_at'];
			$formats[]         = '%s';
			$formats[]         = '%s';

			$inserted = $wpdb->insert( $table, $row, $formats );
			if ( false === $inserted ) {
					return false;
			}

			return (int) $wpdb->insert_id;
	}

		/**
		 * Delete a prize.
		 *
		 * @param int $id Prize ID.
		 * @return bool
		 */
	public static function delete_prize( $id ) {
			global $wpdb;
			$id    = absint( $id );
			$table = $wpdb->prefix . 'bhg_prizes';

		if ( $id <= 0 ) {
				return false;
		}

			$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
			$wpdb->delete( $wpdb->prefix . 'bhg_hunt_prizes', array( 'prize_id' => $id ), array( '%d' ) );

			return true;
	}

		/**
		 * Associate prizes with a hunt.
		 *
		 * @param int   $hunt_id   Hunt ID.
		 * @param int[] $prize_ids Prize IDs.
		 * @return void
		 */
	public static function set_hunt_prizes( $hunt_id, $prize_ids, $type = 'regular' ) {
			global $wpdb;

			$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
				return;
		}

			$table     = $wpdb->prefix . 'bhg_hunt_prizes';
			$type      = self::normalize_prize_type( $type );
			$current   = self::get_hunt_prize_ids( $hunt_id, $type );
			$new       = array_map( 'absint', (array) $prize_ids );
			$new       = array_filter( array_unique( $new ) );
			$to_add    = array_diff( $new, $current );
			$to_remove = array_diff( $current, $new );

		if ( ! empty( $to_remove ) ) {
				$placeholders = implode( ', ', array_fill( 0, count( $to_remove ), '%d' ) );
				$sql          = "DELETE FROM {$table} WHERE hunt_id = %d AND prize_type = %s AND prize_id IN ({$placeholders})";
				$prepared     = call_user_func_array(
					array( $wpdb, 'prepare' ),
					array_merge(
						array( $sql, $hunt_id, $type ),
						array_values( $to_remove )
					)
				);

				$wpdb->query( $prepared ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		if ( ! empty( $to_add ) ) {
				$now = current_time( 'mysql' );
			foreach ( $to_add as $pid ) {
					$wpdb->insert(
						$table,
						array(
							'hunt_id'    => $hunt_id,
							'prize_id'   => $pid,
							'prize_type' => $type,
							'created_at' => $now,
						),
						array( '%d', '%d', '%s', '%s' )
					);
			}
		}
	}

		/**
		 * Set the regular and premium prize sets for a hunt.
		 *
		 * @param int   $hunt_id Hunt ID.
		 * @param array $sets    Associative array of prize IDs keyed by type.
		 * @return void
		 */
	public static function set_hunt_prize_sets( $hunt_id, $sets ) {
			$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
				return;
		}

			$types = array( 'regular', 'premium' );

		foreach ( $types as $type ) {
				$ids = array();
			if ( isset( $sets[ $type ] ) ) {
					$ids = (array) $sets[ $type ];
			}

				self::set_hunt_prizes( $hunt_id, $ids, $type );
		}
	}

		/**
		 * Get prize IDs linked to a hunt.
		 *
		 * @param int $hunt_id Hunt ID.
		 * @return int[]
		 */
	public static function get_hunt_prize_ids( $hunt_id, $type = '' ) {
			global $wpdb;
			$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
				return array();
		}

			$table = $wpdb->prefix . 'bhg_hunt_prizes';

			$type   = ( '' === $type ) ? '' : self::normalize_prize_type( $type );
			$sql    = "SELECT prize_id FROM {$table} WHERE hunt_id = %d";
			$params = array( $hunt_id );

		if ( '' !== $type ) {
				$sql     .= ' AND prize_type = %s';
				$params[] = $type;
		}

			$sql     .= ' ORDER BY created_at ASC, id ASC';
			$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $params ) );

			$ids = $wpdb->get_col( $prepared ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			return array_map( 'intval', array_filter( array_unique( (array) $ids ) ) );
	}

		/**
		 * Retrieve detailed prize rows for a hunt.
		 *
		 * @param int $hunt_id Hunt ID.
		 * @return array
		 */
	public static function get_prizes_for_hunt( $hunt_id, $args = array() ) {
			global $wpdb;
			$hunt_id = absint( $hunt_id );
		if ( $hunt_id <= 0 ) {
				return array();
		}

			$table       = $wpdb->prefix . 'bhg_prizes';
			$relation    = $wpdb->prefix . 'bhg_hunt_prizes';
			$active_only = isset( $args['active_only'] ) ? (bool) $args['active_only'] : false;
			$grouped     = ! empty( $args['grouped'] );
			$type_filter = isset( $args['type'] ) ? sanitize_key( $args['type'] ) : '';

		if ( $grouped ) {
				$results = array();
			foreach ( array( 'regular', 'premium' ) as $set_type ) {
					$results[ $set_type ] = self::get_prizes_for_hunt(
						$hunt_id,
						array_merge(
							$args,
							array(
								'grouped' => false,
								'type'    => $set_type,
							)
						)
					);
			}

				return $results;
		}

			$where = '';
		if ( $active_only ) {
				$where = 'AND p.active = 1';
		}

			$type_sql = '';
			$bindings = array( $hunt_id );
		if ( '' !== $type_filter && 'all' !== $type_filter ) {
				$type_sql   = ' AND r.prize_type = %s';
				$bindings[] = self::normalize_prize_type( $type_filter );
		}

			$sql = "SELECT p.*, r.prize_type FROM {$table} p INNER JOIN {$relation} r ON r.prize_id = p.id WHERE r.hunt_id = %d {$where}{$type_sql} ORDER BY r.created_at ASC, r.id ASC";

			$prepared = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $bindings ) );

			return $wpdb->get_results( $prepared ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

		/**
		 * Format CSS inline style attribute based on prize settings.
		 *
		 * @param object $prize Prize row.
		 * @return string
		 */
	public static function build_style_attr( $prize ) {
			$styles = array();
		if ( ! empty( $prize->css_border ) ) {
				$styles[] = 'border:' . esc_attr( $prize->css_border );
		}
		if ( ! empty( $prize->css_border_color ) ) {
				$styles[] = 'border-color:' . esc_attr( $prize->css_border_color );
		}
		if ( ! empty( $prize->css_padding ) ) {
				$styles[] = 'padding:' . esc_attr( $prize->css_padding );
		}
		if ( ! empty( $prize->css_margin ) ) {
				$styles[] = 'margin:' . esc_attr( $prize->css_margin );
		}
		if ( ! empty( $prize->css_background ) ) {
				$styles[] = 'background-color:' . esc_attr( $prize->css_background );
		}

		if ( empty( $styles ) ) {
				return '';
		}

			return ' style="' . esc_attr( implode( ';', $styles ) ) . '"';
	}

/**
 * Retrieve image URL for a prize.
 *
 * @param object $prize Prize row.
 * @param string $size  Size key.
 * @return string
 */
public static function get_image_url( $prize, $size = 'medium' ) {
$size = sanitize_key( $size );
$map  = array(
'small'  => 'image_small',
'medium' => 'image_medium',
'big'    => 'image_large',
);

if ( ! isset( $map[ $size ] ) ) {
$size = 'medium';
}

$field = $map[ $size ];
$id    = isset( $prize->$field ) ? absint( $prize->$field ) : 0;

if ( $id <= 0 ) {
return '';
}

$wp_size = 'medium';
if ( 'small' === $size ) {
$wp_size = 'thumbnail';
} elseif ( 'big' === $size ) {
$wp_size = 'large';
} else {
$wp_size = $size;
}

$url = wp_get_attachment_image_url( $id, $wp_size );
if ( ! $url ) {
$url = wp_get_attachment_url( $id );
}

return $url ? esc_url( $url ) : '';
}

/**
 * Retrieve the attachment ID for a specific prize image size.
 *
 * @param object $prize Prize row.
 * @param string $size  Size key.
 * @return int
 */
public static function get_image_id_for_size( $prize, $size = 'medium' ) {
$map = array(
'small'  => 'image_small',
'medium' => 'image_medium',
'big'    => 'image_large',
);

$size = sanitize_key( $size );
if ( ! isset( $map[ $size ] ) ) {
$size = 'medium';
}

$field = $map[ $size ];

return ( $prize && isset( $prize->$field ) ) ? absint( $prize->$field ) : 0;
}
}
