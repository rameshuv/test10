<?php
/**
 * Admin view for managing bonus hunts.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

global $wpdb;
			$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$guesses_table           = esc_sql( $wpdb->prefix . 'bhg_guesses' );
$tours_table             = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$users_table             = esc_sql( $wpdb->users );
			$aff_table   = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
$allowed_tables          = array( $hunts_table, $guesses_table, $aff_table, $tours_table, $users_table );
if (
								! in_array( $hunts_table, $allowed_tables, true ) ||
								! in_array( $guesses_table, $allowed_tables, true ) ||
								! in_array( $users_table, $allowed_tables, true ) ||
								! in_array( $tours_table, $allowed_tables, true )
) {
				wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}


$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'list';
if ( ! in_array( $view, array( 'list', 'add', 'edit', 'close' ), true ) ) {
		$view = 'list';
}

/** LIST VIEW */
if ( 'list' === $view ) :
		$current_page = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
	$per_page         = 30;
		$offset       = ( $current_page - 1 ) * $per_page;
	$search_term      = '';
	if ( isset( $_GET['s'] ) ) {
			check_admin_referer( 'bhg_hunts_search', 'bhg_hunts_search_nonce' );
		$search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}
		$orderby_param = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'id';
		$order_param   = isset( $_GET['order'] ) ? strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'desc';

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

		$order_by_column = isset( $allowed_orderby[ $orderby_param ] ) ? $allowed_orderby[ $orderby_param ] : $allowed_orderby['id'];
		$order_direction = isset( $allowed_order[ $order_param ] ) ? $allowed_order[ $order_param ] : 'DESC';
		$order_by_clause = sprintf( '%s %s', $order_by_column, $order_direction );
		$search_like     = '%' . $wpdb->esc_like( $search_term ) . '%';

		$hunts_query = $wpdb->prepare(
			"SELECT h.*, a.name AS affiliate_name FROM {$hunts_table} h LEFT JOIN {$aff_table} a ON a.id = h.affiliate_site_id WHERE h.title LIKE %s ORDER BY {$order_by_clause} LIMIT %d OFFSET %d",
			$search_like,
			$per_page,
			$offset
		);

	// db call ok; no-cache ok.
	$hunts = $wpdb->get_results( $hunts_query );

		$count_query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$hunts_table} h WHERE h.title LIKE %s",
			$search_like
		);
		// db call ok; no-cache ok.
		$total = (int) $wpdb->get_var( $count_query );
	$base_url  = remove_query_arg( array( 'paged' ) );
	$sort_base = remove_query_arg( array( 'paged', 'orderby', 'order' ) );
	?>
<div class="wrap bhg-wrap">
<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ) ); ?></h1>
<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php echo esc_html( bhg_t( 'add_new', 'Add New' ) ); ?></a>

<form method="get" class="search-form">
<input type="hidden" name="page" value="bhg-bonus-hunts" />
		<?php wp_nonce_field( 'bhg_hunts_search', 'bhg_hunts_search_nonce' ); ?>
<p class="search-box">
<input type="search" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
	<?php if ( $orderby_param ) : ?>
<input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby_param ); ?>" />
<?php endif; ?>
	<?php if ( $order_param ) : ?>
<input type="hidden" name="order" value="<?php echo esc_attr( strtolower( $order_param ) ); ?>" />
<?php endif; ?>
<input type="submit" class="button" value="<?php echo esc_attr( bhg_t( 'search_hunts', 'Search Hunts' ) ); ?>" />
</p>
</form>

	<?php if ( isset( $_GET['bhg_msg'] ) && 'invalid_final_balance' === sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) ) : ?>
<div class="notice notice-error notice-large is-dismissible">
<p><strong><?php echo esc_html( bhg_t( 'hunt_not_closed_invalid_final_balance', 'Hunt not closed. Please enter a non-negative final balance.' ) ); ?></strong></p>
</div>
<?php endif; ?>

		<?php if ( isset( $_GET['closed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['closed'] ) ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_closed_successfully', 'Hunt closed successfully.' ) ); ?></p></div>
		<?php endif; ?>

		<?php if ( isset( $_GET['bhg_msg'] ) && 'close_failed' === sanitize_text_field( wp_unslash( $_GET['bhg_msg'] ) ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( bhg_t( 'hunt_close_failed', 'Failed to close the hunt.' ) ); ?></p></div>
		<?php endif; ?>

<table class="widefat striped bhg-margin-top-small">
<thead>
<tr>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'id',
				'order'   => ( 'id' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'id', 'ID' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'title',
				'order'   => ( 'title' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'starting_balance',
				'order'   => ( 'starting_balance' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_start_balance', 'Start Balance' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'final_balance',
				'order'   => ( 'final_balance' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'affiliate',
				'order'   => ( 'affiliate' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'affiliate', 'Affiliate' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'winners',
				'order'   => ( 'winners' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'winners', 'Winners' ) ); ?></a></th>
<th><a href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'orderby' => 'status',
				'order'   => ( 'status' === $orderby_param && 'asc' === $order_param ) ? 'desc' : 'asc',
			),
			$sort_base
		)
	);
	?>
				"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></a></th>
<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
<th><?php echo esc_html( bhg_t( 'admin_action', 'Admin Action' ) ); ?></th>
</tr>
</thead>
				<tbody>
								<?php if ( empty( $hunts ) ) : ?>
<tr><td colspan="9"><?php echo esc_html( bhg_t( 'notice_no_hunts_found', 'No hunts found.' ) ); ?></td></tr>
								<?php else : ?>
										<?php foreach ( $hunts as $h ) : ?>
												<?php
												$edit_url    = wp_nonce_url(
													add_query_arg(
														array(
															'view' => 'edit',
															'id'   => (int) $h->id,
														)
													),
													'bhg_edit_hunt'
												);
												$results_url = add_query_arg(
													array(
														'page'    => 'bhg-bonus-hunts-results',
														'hunt_id' => (int) $h->id,
														'id'      => (int) $h->id,
													),
													admin_url( 'admin.php' )
												);
												?>
				<tr>
<td><?php echo esc_html( (int) $h->id ); ?></td>
						<td><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $h->title ); ?></a></td>
<td><?php echo esc_html( bhg_format_money( (float) $h->starting_balance ) ); ?></td>
<td><?php echo null !== $h->final_balance ? esc_html( bhg_format_money( (float) $h->final_balance ) ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
<td><?php echo $h->affiliate_name ? esc_html( $h->affiliate_name ) : esc_html( bhg_t( 'label_emdash', '—' ) ); ?></td>
<td><?php echo esc_html( (int) ( $h->winners_count ?? 3 ) ); ?></td>
<td><?php echo esc_html( bhg_t( $h->status, ucfirst( $h->status ) ) ); ?></td>
<td>
<a class="button" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
												<?php if ( 'open' === $h->status ) : ?>
<a class="button" href="
													<?php
													echo esc_url(
														add_query_arg(
															array(
																'view' => 'close',
																'id'   => (int) $h->id,
															)
														)
													);
													?>
"><?php echo esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) ); ?></a>
<?php elseif ( null !== $h->final_balance ) : ?>
<a class="button button-primary" href="<?php echo esc_url( $results_url ); ?>"><?php echo esc_html( bhg_t( 'button_results', 'Results' ) ); ?></a>

<?php endif; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-inline-form">
											<?php wp_nonce_field( 'bhg_toggle_guessing', 'bhg_toggle_guessing_nonce' ); ?>
<input type="hidden" name="action" value="bhg_toggle_guessing" />
<input type="hidden" name="hunt_id" value="<?php echo esc_attr( (int) $h->id ); ?>" />
<input type="hidden" name="guessing_enabled" value="<?php echo esc_attr( $h->guessing_enabled ? 0 : 1 ); ?>" />
<button type="submit" class="button"><?php echo esc_html( $h->guessing_enabled ? bhg_t( 'disable_guessing', 'Disable Guessing' ) : bhg_t( 'enable_guessing', 'Enable Guessing' ) ); ?></button>
</form>
</td>
<td>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_hunt', 'Delete this hunt?' ) ); ?>');" class="bhg-inline-form">
											<?php wp_nonce_field( 'bhg_delete_hunt', 'bhg_delete_hunt_nonce' ); ?>
<input type="hidden" name="action" value="bhg_delete_hunt" />
<input type="hidden" name="hunt_id" value="<?php echo esc_attr( (int) $h->id ); ?>" />
<button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
</form>
</td>
</tr>
														<?php endforeach; ?>
								<?php endif; ?>
				</tbody>
		</table>

	<?php
		$total_pages = (int) ceil( $total / $per_page );
	if ( $total_pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo paginate_links(
				array(
					'base'      => add_query_arg( 'paged', '%#%', $base_url ),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $current_page,
				)
			);
			echo '</div></div>';
	}
	?>
</div>
<?php endif; ?>

<?php
/** CLOSE VIEW */
if ( 'close' === $view ) :
								$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
				// db call ok; no-cache ok.
								$hunt = $wpdb->get_row(
									$wpdb->prepare(
										"SELECT * FROM {$hunts_table} WHERE id = %d",
										$id
									)
								);
	if ( ! $hunt || 'open' !== $hunt->status ) :
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt_2', 'Invalid hunt.' ) ) . '</p></div>';
	else :
		?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'close_bonus_hunt', 'Close Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-400 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_close_hunt', 'bhg_close_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_close_hunt" />
<input type="hidden" name="hunt_id" value="<?php echo esc_attr( (int) $hunt->id ); ?>" />
	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_final_balance"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
						<td><input type="text" id="bhg_final_balance" name="final_balance" value="" required></td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'close_hunt', 'Close Hunt' ) ) ); ?>
	</form>
</div>
				<?php
		endif;
endif;
?>

<?php
/** ADD VIEW */
if ( 'add' === $view ) :
	?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'add_new_bonus_hunt', 'Add New Bonus Hunt' ) ); ?></h1>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_hunt" />

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
			<td><input required class="regular-text" id="bhg_title" name="title" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_starting"><?php echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_starting" name="starting_balance" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_num"><?php echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ); ?></label></th>
			<td><input type="number" min="0" id="bhg_num" name="num_bonuses" value=""></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_prizes"><?php echo esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="bhg_prizes" name="prizes"></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
			<td>
						<?php
												$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
										wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
																								// db call ok; no-cache ok.
																								$affs = $wpdb->get_results(
																									"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
																								);
						$sel = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
								<?php foreach ( $affs as $a ) : ?>
								<option value="<?php echo esc_attr( (int) $a->id ); ?>" <?php selected( $sel, (int) $a->id ); ?>><?php echo esc_html( $a->name ); ?></option>
								<?php endforeach; ?>
			</select>
			</td>
				</tr>
								<tr>
												<th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
												<td>
																								<?php
																								$t_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
																								if ( ! in_array( $t_table, $allowed_tables, true ) ) {
																																wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
																								}
																								$selected_tournaments = array();
																								$active_sql           = $wpdb->prepare(
																									"SELECT id, title FROM {$t_table} WHERE status = %s ORDER BY title ASC",
																									'active'
																								);
																								$tours                = $wpdb->get_results( $active_sql );
																								?>
												<select id="bhg_tournament" name="tournament_ids[]" multiple="multiple" size="5">
																<?php foreach ( $tours as $t ) : ?>
																<option value="<?php echo esc_attr( (int) $t->id ); ?>" <?php selected( in_array( (int) $t->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $t->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'select_multiple_tournaments_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.' ) ); ?></p>
												</td>
								</tr>
								<tr>
												<th scope="row"><label for="bhg_regular_prize_ids"><?php echo esc_html( bhg_t( 'label_regular_prize_set', 'Regular Prize Set' ) ); ?></label></th>
												<td>
																								<?php
																								$prize_rows = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
																								?>
												<select id="bhg_regular_prize_ids" name="regular_prize_ids[]" multiple="multiple" size="5">
																<?php foreach ( $prize_rows as $prize_row ) : ?>
																<option value="<?php echo esc_attr( (int) $prize_row->id ); ?>"><?php echo esc_html( $prize_row->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'regular_prize_set_help', 'Select prizes awarded to non-affiliate winners.' ) ); ?></p>
												</td>
								</tr>
								<tr>
												<th scope="row"><label for="bhg_premium_prize_ids"><?php echo esc_html( bhg_t( 'label_premium_prize_set', 'Premium Prize Set' ) ); ?></label></th>
												<td>
												<select id="bhg_premium_prize_ids" name="premium_prize_ids[]" multiple="multiple" size="5">
																<?php foreach ( $prize_rows as $prize_row ) : ?>
																<option value="<?php echo esc_attr( (int) $prize_row->id ); ?>"><?php echo esc_html( $prize_row->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'premium_prize_set_help', 'Select additional prizes shown to affiliate winners.' ) ); ?></p>
												</td>
								</tr>
<tr>
<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="3"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_guessing_enabled"><?php echo esc_html( bhg_t( 'guessing_enabled', 'Guessing Enabled' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" checked></td>
</tr>
<tr>
<th scope="row"><label for="bhg_status"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></label></th>
			<td>
			<select id="bhg_status" name="status">
				<option value="open"><?php echo esc_html( bhg_t( 'open', 'Open' ) ); ?></option>
				<option value="closed"><?php echo esc_html( bhg_t( 'label_closed', 'Closed' ) ); ?></option>
			</select>
			</td>
		</tr>
		<tr>
						<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'final_balance_optional', 'Final Balance (optional)' ) ); ?></label></th>
						<td><input type="text" id="bhg_final" name="final_balance" value=""></td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'create_bonus_hunt', 'Create Bonus Hunt' ) ) ); ?>
	</form>
</div>
<?php endif; ?>

<?php
/** EDIT VIEW */
if ( 'edit' === $view ) :
			$id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		// db call ok; no-cache ok.
								$hunt = $wpdb->get_row(
									$wpdb->prepare(
										"SELECT * FROM {$hunts_table} WHERE id = %d",
										$id
									)
								);
	if ( ! $hunt ) {
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
		return;
	}
		$users_table_local = $users_table;
	if ( ! in_array( $users_table_local, $allowed_tables, true ) ) {
			wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
	}
		$users_table_local = esc_sql( $users_table_local );
								// db call ok; no-cache ok.
								$guesses = $wpdb->get_results(
									$wpdb->prepare(
										"SELECT g.*, u.display_name FROM {$guesses_table} g LEFT JOIN {$users_table_local} u ON u.ID = g.user_id WHERE g.hunt_id = %d ORDER BY g.id ASC",
										$id
									)
								);
	?>
<div class="wrap bhg-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'edit_bonus_hunt', 'Edit Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
								<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_hunt" />
<input type="hidden" name="id" value="<?php echo esc_attr( (int) $hunt->id ); ?>" />

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><label for="bhg_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
			<td><input required class="regular-text" id="bhg_title" name="title" value="<?php echo esc_attr( $hunt->title ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_starting"><?php echo esc_html( bhg_t( 'label_start_balance', 'Starting Balance' ) ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="bhg_starting" name="starting_balance" value="<?php echo esc_attr( $hunt->starting_balance ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_num"><?php echo esc_html( bhg_t( 'label_number_bonuses', 'Number of Bonuses' ) ); ?></label></th>
			<td><input type="number" min="0" id="bhg_num" name="num_bonuses" value="<?php echo esc_attr( $hunt->num_bonuses ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_prizes"><?php echo esc_html( bhg_t( 'sc_prizes', 'Prizes' ) ); ?></label></th>
			<td><textarea class="large-text" rows="3" id="bhg_prizes" name="prizes"><?php echo esc_textarea( $hunt->prizes ); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="bhg_affiliate"><?php echo esc_html( bhg_t( 'affiliate_site', 'Affiliate Site' ) ); ?></label></th>
			<td>
						<?php
												$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
						if ( ! in_array( $aff_table, $allowed_tables, true ) ) {
										wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
						}
																								// db call ok; no-cache ok.
																								$affs = $wpdb->get_results(
																									"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
																								);
						$sel = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;
						?>
			<select id="bhg_affiliate" name="affiliate_site_id">
				<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
								<?php foreach ( $affs as $a ) : ?>
								<option value="<?php echo esc_attr( (int) $a->id ); ?>" <?php selected( $sel, (int) $a->id ); ?>><?php echo esc_html( $a->name ); ?></option>
								<?php endforeach; ?>
			</select>
						</td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_tournament"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
						<td>
												<?php
												$t_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
												if ( ! in_array( $t_table, $allowed_tables, true ) ) {
																		wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
												}
																								$selected_tournaments = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( (int) $hunt->id ) : array();
																								$selected_tournaments = array_values( array_filter( array_map( 'absint', (array) $selected_tournaments ) ) );
																								$active_sql           = $wpdb->prepare(
																									"SELECT id, title FROM {$t_table} WHERE status = %s ORDER BY title ASC",
																									'active'
																								);

												if ( ! empty( $selected_tournaments ) ) {
														$ids_sql    = implode( ',', array_map( 'intval', $selected_tournaments ) );
														$active_sql = $wpdb->prepare(
															"SELECT id, title FROM {$t_table} WHERE status = %s OR id IN ({$ids_sql}) ORDER BY title ASC",
															'active'
														);
												}

																								$tours           = $wpdb->get_results( $active_sql );
																								$prize_rows      = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
																								$selected_prizes = array(
																									'regular' => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( (int) $hunt->id, 'regular' ) : array(),
																									'premium' => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( (int) $hunt->id, 'premium' ) : array(),
																								);
																								?>
												<select id="bhg_tournament" name="tournament_ids[]" multiple="multiple" size="5">
																<?php foreach ( $tours as $t ) : ?>
																<option value="<?php echo esc_attr( (int) $t->id ); ?>" <?php selected( in_array( (int) $t->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $t->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'select_multiple_tournaments_hint', 'Hold Ctrl (Windows) or Command (Mac) to select multiple tournaments.' ) ); ?></p>
												</td>
								</tr>
								<tr>
												<th scope="row"><label for="bhg_prize_ids_edit_regular"><?php echo esc_html( bhg_t( 'label_regular_prize_set', 'Regular Prize Set' ) ); ?></label></th>
												<td>
												<select id="bhg_prize_ids_edit_regular" name="regular_prize_ids[]" multiple="multiple" size="5">
																<?php foreach ( $prize_rows as $prize_row ) : ?>
																<option value="<?php echo esc_attr( (int) $prize_row->id ); ?>" <?php selected( in_array( (int) $prize_row->id, $selected_prizes['regular'], true ) ); ?>><?php echo esc_html( $prize_row->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'regular_prize_set_help', 'Select prizes awarded to non-affiliate winners.' ) ); ?></p>
												</td>
								</tr>
								<tr>
												<th scope="row"><label for="bhg_prize_ids_edit_premium"><?php echo esc_html( bhg_t( 'label_premium_prize_set', 'Premium Prize Set' ) ); ?></label></th>
												<td>
												<select id="bhg_prize_ids_edit_premium" name="premium_prize_ids[]" multiple="multiple" size="5">
																<?php foreach ( $prize_rows as $prize_row ) : ?>
																<option value="<?php echo esc_attr( (int) $prize_row->id ); ?>" <?php selected( in_array( (int) $prize_row->id, $selected_prizes['premium'], true ) ); ?>><?php echo esc_html( $prize_row->title ); ?></option>
																<?php endforeach; ?>
												</select>
												<p class="description"><?php echo esc_html( bhg_t( 'premium_prize_set_help', 'Select additional prizes shown to affiliate winners.' ) ); ?></p>
												</td>
								</tr>
<tr>
<th scope="row"><label for="bhg_winners"><?php echo esc_html( bhg_t( 'number_of_winners', 'Number of Winners' ) ); ?></label></th>
<td><input type="number" min="1" max="25" id="bhg_winners" name="winners_count" value="<?php echo esc_attr( $hunt->winners_count ? $hunt->winners_count : 3 ); ?>"></td>
</tr>
<tr>
<th scope="row"><label for="bhg_guessing_enabled"><?php echo esc_html( bhg_t( 'guessing_enabled', 'Guessing Enabled' ) ); ?></label></th>
<td><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" <?php checked( $hunt->guessing_enabled, 1 ); ?>></td>
</tr>
<tr>
<th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
<td><input type="text" id="bhg_final" name="final_balance" value="<?php echo esc_attr( $hunt->final_balance ); ?>" placeholder="<?php echo esc_attr( esc_html( bhg_t( 'label_emdash', '—' ) ) ); ?>"></td>
</tr>
		<tr>
			<th scope="row"><label for="bhg_status"><?php echo esc_html( bhg_t( 'sc_status', 'Status' ) ); ?></label></th>
			<td>
			<select id="bhg_status" name="status">
				<option value="open" <?php selected( $hunt->status, 'open' ); ?>><?php echo esc_html( bhg_t( 'open', 'Open' ) ); ?></option>
				<option value="closed" <?php selected( $hunt->status, 'closed' ); ?>><?php echo esc_html( bhg_t( 'label_closed', 'Closed' ) ); ?></option>
			</select>
			</td>
		</tr>
		</tbody>
	</table>
		<?php submit_button( esc_html( bhg_t( 'save_hunt', 'Save Hunt' ) ) ); ?>
	</form>

	<h2 class="bhg-margin-top-large"><?php echo esc_html( bhg_t( 'participants', 'Participants' ) ); ?></h2>
	<table class="widefat striped">
	<thead>
		<tr>
		<th><?php echo esc_html( bhg_t( 'sc_user', 'User' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'sc_guess', 'Guess' ) ); ?></th>
		<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $guesses ) ) : ?>
		<tr><td colspan="3"><?php echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) ); ?></td></tr>
			<?php
		else :
			foreach ( $guesses as $g ) :
				?>
		<tr>
			<td>
							<?php
										/* translators: %d: user ID. */
										$name = $g->display_name ? $g->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $g->user_id );
							$url              = admin_url( 'user-edit.php?user_id=' . (int) $g->user_id );
							echo '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
							?>
			</td>
											<td><?php echo esc_html( bhg_format_money( (float) ( $g->guess ?? 0 ) ) ); ?></td>
			<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_guess', 'Delete this guess?' ) ); ?>');" class="bhg-inline-form">
																<?php wp_nonce_field( 'bhg_delete_guess', 'bhg_delete_guess_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_delete_guess">
<input type="hidden" name="guess_id" value="<?php echo esc_attr( (int) $g->id ); ?>">
				<button type="submit" class="button-link-delete"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></button>
			</form>
			</td>
		</tr>
					<?php
		endforeach;
endif;
		?>
	</tbody>
	</table>
</div>
<?php endif; ?>
