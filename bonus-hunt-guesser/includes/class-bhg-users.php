<?php
/**
 * User data helpers for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper utilities for retrieving WordPress users with plugin meta.
 */
class BHG_Users {
	/**
	 * Meta key storing the real name field.
	 */
	const META_REAL_NAME = 'bhg_real_name';

	/**
	 * Meta key storing the affiliate flag.
	 */
	const META_AFFILIATE = 'bhg_is_affiliate';

	/**
	 * Query WordPress users with plugin-specific ordering and search helpers.
	 *
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 *     @type int    $number  Number of results to return. Default 30.
	 *     @type int    $offset  Query offset. Default 0.
	 *     @type string $search  Search term. Default empty.
	 *     @type string $orderby Sort column: username|email|affiliate|display_name. Default username.
	 *     @type string $order   Sort direction: ASC|DESC. Default ASC.
	 * }
	 *
	 * @return array {
	 *     Array containing query results.
	 *
	 *     @type WP_User[] $users Retrieved users.
	 *     @type int       $total Total matched users.
	 * }
	 */
	public static function query( $args = array() ) {
		$defaults = array(
			'number'  => 30,
			'offset'  => 0,
			'search'  => '',
			'orderby' => 'username',
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		$number  = max( 1, (int) $args['number'] );
		$offset  = max( 0, (int) $args['offset'] );
		$search  = is_string( $args['search'] ) ? sanitize_text_field( $args['search'] ) : '';
		$orderby = sanitize_key( $args['orderby'] );
		$order   = strtoupper( is_string( $args['order'] ) ? $args['order'] : 'ASC' );

		if ( 'DESC' !== $order ) {
			$order = 'ASC';
		}

		$allowed_orderby = array( 'username', 'email', 'affiliate', 'display_name' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'username';
		}

		$query_args = array(
			'number' => $number,
			'offset' => $offset,
			'order'  => $order,
			'fields' => 'all_with_meta',
		);

		switch ( $orderby ) {
			case 'email':
				$query_args['orderby'] = 'email';
				break;
			case 'display_name':
				$query_args['orderby'] = 'display_name';
				break;
			case 'affiliate':
				$query_args['orderby']  = 'meta_value_num';
				$query_args['meta_key'] = self::META_AFFILIATE;
				break;
			case 'username':
			default:
				$query_args['orderby'] = 'login';
				break;
		}

		if ( '' !== $search ) {
			$query_args['search']         = '*' . $search . '*';
			$query_args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}

		$user_query = new WP_User_Query( $query_args );

		return array(
			'users' => $user_query->get_results(),
			'total' => (int) $user_query->get_total(),
		);
	}
}
