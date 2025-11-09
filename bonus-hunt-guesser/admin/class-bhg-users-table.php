<?php
/**
 * Users list table for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once BHG_PLUGIN_DIR . 'includes/class-bhg-users.php';

/**
 * Admin list table for WordPress users and affiliate meta.
 */
class BHG_Users_Table extends WP_List_Table {
	/**
	 * Items per page.
	 *
	 * @var int
	 */
	private $per_page = 30;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'bhg_user',
				'plural'   => 'bhg_users',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Retrieve table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'username'     => bhg_t( 'label_username', 'Username' ),
			'display_name' => bhg_t( 'name', 'Name' ),
			'real_name'    => bhg_t( 'label_real_name', 'Real Name' ),
			'email'        => bhg_t( 'label_email', 'Email' ),
			'affiliate'    => bhg_t( 'affiliate_user', 'Affiliate' ),
			'actions'      => bhg_t( 'label_actions', 'Actions' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'username'  => array( 'username', true ),
			'email'     => array( 'email', false ),
			'affiliate' => array( 'affiliate', false ),
		);
	}

	/**
	 * Message to show when table is empty.
	 */
	public function no_items() {
		esc_html_e( 'No users found.', 'bonus-hunt-guesser' );
	}

	/**
	 * Render the username column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_username( $item ) {
		$user_id  = (int) $item['id'];
		$edit_url = esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) );
		$username = esc_html( $item['username'] );

		return sprintf( '<strong><a class="row-title" href="%1$s">%2$s</a></strong>', $edit_url, $username );
	}

	/**
	 * Render the display name column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_display_name( $item ) {
		return esc_html( $item['display_name'] );
	}

	/**
	 * Render the real name column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_real_name( $item ) {
		$form_id = esc_attr( $item['form_id'] );
		$value   = esc_attr( $item['real_name'] );

		return sprintf( '<input type="text" name="bhg_real_name" class="regular-text" form="%1$s" value="%2$s" />', $form_id, $value );
	}

	/**
	 * Render the email column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_email( $item ) {
		$email = sanitize_email( $item['email'] );
		if ( empty( $email ) ) {
			return '';
		}

		return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) );
	}

	/**
	 * Render the affiliate toggle column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_affiliate( $item ) {
		$form_id    = esc_attr( $item['form_id'] );
		$field_id   = 'bhg-affiliate-' . (int) $item['id'];
		$checked    = checked( (int) $item['affiliate'], 1, false );
		$label_text = esc_html( bhg_t( 'affiliate_user', 'Affiliate' ) );

		return sprintf(
			'<label class="screen-reader-text" for="%1$s">%2$s</label><input type="checkbox" id="%1$s" name="bhg_is_affiliate" form="%3$s" value="1" %4$s />',
			esc_attr( $field_id ),
			$label_text,
			$form_id,
			$checked
		);
	}

	/**
	 * Render actions column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_actions( $item ) {
		$form_id   = esc_attr( $item['form_id'] );
		$user_id   = (int) $item['id'];
		$action    = esc_url( admin_url( 'admin-post.php' ) );
		$nonce     = wp_nonce_field( 'bhg_save_user_meta', 'bhg_save_user_meta_nonce', true, false );
		$view_url  = esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) );
		$save_text = esc_html( bhg_t( 'button_save', 'Save' ) );
		$view_text = esc_html( bhg_t( 'view_edit', 'View / Edit' ) );

		return sprintf(
			'<form id="%1$s" method="post" action="%2$s"><input type="hidden" name="action" value="bhg_save_user_meta" /><input type="hidden" name="user_id" value="%3$d" />%4$s<button type="submit" class="button button-primary">%5$s</button></form><a class="button" href="%6$s">%7$s</a>',
			$form_id,
			$action,
			$user_id,
			$nonce,
			$save_text,
			$view_url,
			$view_text
		);
	}

	/**
	 * Fallback column rendering.
	 *
	 * @param array  $item        Current row.
	 * @param string $column_name Column name.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( isset( $item[ $column_name ] ) ) {
			return esc_html( $item[ $column_name ] );
		}

		return '';
	}

	/**
	 * Prepare table items.
	 */
	public function prepare_items() {
		$paged  = isset( $_REQUEST['paged'] ) ? max( 1, absint( wp_unslash( $_REQUEST['paged'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- viewing data.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- viewing data.

		$orderby_request = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'username'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- viewing data.
		$order_request   = isset( $_REQUEST['order'] ) ? sanitize_key( wp_unslash( $_REQUEST['order'] ) ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- viewing data.
		$order           = 'desc' === strtolower( $order_request ) ? 'DESC' : 'ASC';

		$results = BHG_Users::query(
			array(
				'number'  => $this->per_page,
				'offset'  => ( $paged - 1 ) * $this->per_page,
				'orderby' => $orderby_request,
				'order'   => $order,
				'search'  => $search,
			)
		);

		$users = isset( $results['users'] ) ? $results['users'] : array();
		$total = isset( $results['total'] ) ? (int) $results['total'] : 0;

		$items = array();

		foreach ( (array) $users as $user ) {
			if ( ! $user instanceof WP_User ) {
				continue;
			}

			$user_id   = (int) $user->ID;
			$form_id   = 'bhg-user-' . $user_id;
			$real_name = get_user_meta( $user_id, BHG_Users::META_REAL_NAME, true );
			$affiliate = (int) get_user_meta( $user_id, BHG_Users::META_AFFILIATE, true );

			$items[] = array(
				'id'           => $user_id,
				'username'     => $user->user_login,
				'display_name' => $user->display_name,
				'real_name'    => $real_name,
				'email'        => $user->user_email,
				'affiliate'    => $affiliate,
				'form_id'      => $form_id,
			);
		}

		$this->items = $items;

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $this->per_page,
				'total_pages' => max( 1, (int) ceil( $total / $this->per_page ) ),
			)
		);
	}

	/**
	 * Override table navigation rendering to allow manual pagination placement.
	 *
	 * @param string $which Location of the navigation (top|bottom).
	 */
	protected function display_tablenav( $which ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Navigation output handled in the view template to meet layout requirements.
	}
}
