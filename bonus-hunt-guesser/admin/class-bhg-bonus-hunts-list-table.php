<?php
/**
 * Bonus hunts list table for the admin UI.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Provides a sortable, searchable table of bonus hunts in the admin area.
 */
class BHG_Bonus_Hunts_List_Table extends WP_List_Table {
	/**
	 * Cached search term.
	 *
	 * @var string
	 */
	protected $search_term = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'bonus_hunt',
				'plural'   => 'bonus_hunts',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Retrieve the current search term.
	 *
	 * @return string
	 */
	public function get_search_term() {
		return $this->search_term;
	}

	/**
	 * Get table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'id'               => bhg_t( 'id', 'ID' ),
			'title'            => bhg_t( 'sc_title', 'Title' ),
			'starting_balance' => bhg_t( 'sc_start_balance', 'Start Balance' ),
			'final_balance'    => bhg_t( 'sc_final_balance', 'Final Balance' ),
			'affiliate_name'   => bhg_t( 'affiliate', 'Affiliate' ),
			'winners_count'    => bhg_t( 'winners', 'Winners' ),
			'status'           => bhg_t( 'sc_status', 'Status' ),
			'actions'          => bhg_t( 'label_actions', 'Actions' ),
			'admin_action'     => bhg_t( 'admin_action', 'Admin Action' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'id'               => array( 'id', true ),
			'title'            => array( 'title', false ),
			'starting_balance' => array( 'starting_balance', false ),
			'final_balance'    => array( 'final_balance', false ),
			'affiliate_name'   => array( 'affiliate', false ),
			'winners_count'    => array( 'winners', false ),
			'status'           => array( 'status', false ),
		);
	}

	/**
	 * Message when no items are available.
	 */
	public function no_items() {
		esc_html_e( 'No hunts found.', 'bonus-hunt-guesser' );
	}

	/**
	 * Prepare table items.
	 */
	public function prepare_items() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		global $wpdb;

		$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$aff_table   = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->search_term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$search_like       = '%' . $wpdb->esc_like( $this->search_term ) . '%';

		$current_page = $this->get_pagenum();
		$per_page     = 30;
		$offset       = ( $current_page - 1 ) * $per_page;

		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$order   = isset( $_REQUEST['order'] ) ? strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : 'desc';

		$allowed_orderby = array(
			'id'               => 'h.id',
			'title'            => 'h.title',
			'starting_balance' => 'h.starting_balance',
			'final_balance'    => 'h.final_balance',
			'affiliate'        => 'a.name',
			'winners'          => 'h.winners_count',
			'status'           => 'h.status',
		);

		$allowed_order = array(
			'asc'  => 'ASC',
			'desc' => 'DESC',
		);

		$order_by_column = isset( $allowed_orderby[ $orderby ] ) ? $allowed_orderby[ $orderby ] : $allowed_orderby['id'];
		$order_direction = isset( $allowed_order[ $order ] ) ? $allowed_order[ $order ] : 'DESC';

		$query = "SELECT h.*, a.name AS affiliate_name
                FROM {$hunts_table} h
                LEFT JOIN {$aff_table} a ON a.id = h.affiliate_site_id
                WHERE h.title LIKE %s
                ORDER BY {$order_by_column} {$order_direction}
                LIMIT %d OFFSET %d";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$items = $wpdb->get_results( $wpdb->prepare( $query, $search_like, $per_page, $offset ), ARRAY_A );

		$count_query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$hunts_table} h WHERE h.title LIKE %s",
			$search_like
		);

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total_items = (int) $wpdb->get_var( $count_query );

		$this->items = array_map(
			static function ( $item ) {
				$item['id']               = (int) $item['id'];
				$item['starting_balance'] = (float) $item['starting_balance'];
				$item['final_balance']    = isset( $item['final_balance'] ) ? (float) $item['final_balance'] : null;
				$item['winners_count']    = isset( $item['winners_count'] ) ? (int) $item['winners_count'] : 0;
				$item['guessing_enabled'] = isset( $item['guessing_enabled'] ) ? (int) $item['guessing_enabled'] : 0;

				return $item;
			},
			(array) $items
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Default column rendering.
	 *
	 * @param array  $item        Current row.
	 * @param string $column_name Column key.
	 *
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'starting_balance':
				return esc_html( bhg_format_money( (float) $item['starting_balance'] ) );
			case 'final_balance':
				if ( 'closed' === $item['status'] && null !== $item['final_balance'] ) {
					return esc_html( bhg_format_money( (float) $item['final_balance'] ) );
				}

				return esc_html( bhg_t( 'label_en_dash', '–' ) );
			case 'affiliate_name':
				return $item['affiliate_name'] ? esc_html( $item['affiliate_name'] ) : esc_html( bhg_t( 'label_en_dash', '–' ) );
			case 'winners_count':
				return esc_html( (int) $item['winners_count'] );
			case 'status':
				return esc_html( bhg_t( $item['status'], ucfirst( $item['status'] ) ) );
		}

		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/**
	 * Column output for the ID column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_id( $item ) {
		return esc_html( (int) $item['id'] );
	}

	/**
	 * Render the title column including row actions.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_title( $item ) {
		$edit_url = wp_nonce_url(
			add_query_arg(
				array(
					'view' => 'edit',
					'id'   => (int) $item['id'],
				)
			),
			'bhg_edit_hunt'
		);

		$actions = array(
			'edit' => sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $edit_url ),
				esc_html( bhg_t( 'button_edit', 'Edit' ) )
			),
		);

		if ( 'open' === $item['status'] ) {
			$close_url = add_query_arg(
				array(
					'view' => 'close',
					'id'   => (int) $item['id'],
				)
			);

			$actions['close'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $close_url ),
				esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) )
			);
		}

		if ( 'closed' === $item['status'] && null !== $item['final_balance'] ) {
			$results_url = add_query_arg(
				array(
					'page'    => 'bhg-bonus-hunts-results',
					'hunt_id' => (int) $item['id'],
				),
				admin_url( 'admin.php' )
			);

			$actions['results'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $results_url ),
				esc_html( bhg_t( 'button_results', 'Results' ) )
			);
		}

		$title = sprintf(
			'<strong><a href="%1$s">%2$s</a></strong>',
			esc_url( $edit_url ),
			esc_html( $item['title'] )
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Render the actions column.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_actions( $item ) {
		$id        = (int) $item['id'];
		$status    = $item['status'];
		$final_set = null !== $item['final_balance'];
		$buttons   = array();
		$edit_url  = wp_nonce_url(
			add_query_arg(
				array(
					'view' => 'edit',
					'id'   => $id,
				)
			),
			'bhg_edit_hunt'
		);

		$buttons[] = sprintf(
			'<a class="button" href="%1$s">%2$s</a>',
			esc_url( $edit_url ),
			esc_html( bhg_t( 'button_edit', 'Edit' ) )
		);

		if ( 'open' === $status ) {
			$close_url = add_query_arg(
				array(
					'view' => 'close',
					'id'   => $id,
				)
			);

			$buttons[] = sprintf(
				'<a class="button" href="%1$s">%2$s</a>',
				esc_url( $close_url ),
				esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) )
			);
		} elseif ( $final_set ) {
			$results_url = add_query_arg(
				array(
					'page'    => 'bhg-bonus-hunts-results',
					'hunt_id' => $id,
				),
				admin_url( 'admin.php' )
			);

			$buttons[] = sprintf(
				'<a class="button button-primary" href="%1$s">%2$s</a>',
				esc_url( $results_url ),
				esc_html( bhg_t( 'button_results', 'Results' ) )
			);
		}

		$toggle_label = $item['guessing_enabled'] ? bhg_t( 'disable_guessing', 'Disable Guessing' ) : bhg_t( 'enable_guessing', 'Enable Guessing' );
		$toggle_value = $item['guessing_enabled'] ? 0 : 1;

		ob_start();
		foreach ( $buttons as $button ) {
			echo wp_kses_post( $button );
		}
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-inline-form">
			<?php wp_nonce_field( 'bhg_toggle_guessing', 'bhg_toggle_guessing_nonce' ); ?>
			<input type="hidden" name="action" value="bhg_toggle_guessing" />
			<input type="hidden" name="hunt_id" value="<?php echo esc_attr( $id ); ?>" />
			<input type="hidden" name="guessing_enabled" value="<?php echo esc_attr( $toggle_value ); ?>" />
			<button type="submit" class="button"><?php echo esc_html( $toggle_label ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the admin action column with the delete form.
	 *
	 * @param array $item Current row.
	 *
	 * @return string
	 */
	protected function column_admin_action( $item ) {
		$id = (int) $item['id'];

		ob_start();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-inline-form" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_hunt', 'Delete this hunt?' ) ); ?>');">
			<?php wp_nonce_field( 'bhg_delete_hunt', 'bhg_delete_hunt_nonce' ); ?>
			<input type="hidden" name="action" value="bhg_delete_hunt" />
			<input type="hidden" name="hunt_id" value="<?php echo esc_attr( $id ); ?>" />
			<button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}
}
