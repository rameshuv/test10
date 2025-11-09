<?php
/**
 * Admin view for affiliate websites.
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
$table          = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
$allowed_tables = array( $table );
if ( ! in_array( $table, $allowed_tables, true ) ) {
		wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}

// Load for edit.
$edit_id = isset( $_GET['edit'] ) ? absint( wp_unslash( $_GET['edit'] ) ) : 0;
if ( $edit_id ) {
		$nonce = isset( $_GET['bhg_edit_affiliate_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_edit_affiliate_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'bhg_edit_affiliate' ) ) {
			$edit_id = 0;
	}
}
$row = $edit_id ? $wpdb->get_row(
	$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $edit_id )
) : null;

// List
// db call ok; no-cache ok.
$rows = // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic table name; static query.
$wpdb->get_results(
	"SELECT * FROM {$table} ORDER BY id DESC"
);
?>
<div class="wrap bhg-wrap">
		<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_affiliates', 'Affiliate Websites' ) ); ?></h1>

	<h2 style="margin-top:1em"><?php echo esc_html( bhg_t( 'all_affiliate_websites', 'All Affiliate Websites' ) ); ?></h2>
	<table class="widefat striped">
	<thead>
		<tr>
		<th>
		<?php
		echo esc_html( bhg_t( 'id', 'ID' ) );
		?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'name', 'Name' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'slug', 'Slug' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'url', 'URL' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'sc_status', 'Status' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'label_actions', 'Actions' ) );
				?>
</th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="6"><em>
				<?php
				echo esc_html( bhg_t( 'no_affiliates_yet', 'No affiliate websites yet.' ) );
				?>
</em></td></tr>
			<?php
		else :
			foreach ( $rows as $r ) :
				?>
		<tr>
			<td><?php echo (int) $r->id; ?></td>
						<td><?php echo esc_html( $r->name ); ?></td>
						<td><?php echo esc_html( $r->slug ); ?></td>
						<td><?php echo esc_html( $r->url ); ?></td>
												<td><?php echo esc_html( bhg_t( $r->status, ucfirst( $r->status ) ) ); ?></td>
						<td>
						<?php
						$edit_url = wp_nonce_url(
							add_query_arg( array( 'edit' => (int) $r->id ) ),
							'bhg_edit_affiliate',
							'bhg_edit_affiliate_nonce'
						);
						?>
						<a class="button" href="<?php echo esc_url( $edit_url ); ?>">
								<?php
								echo esc_html( bhg_t( 'button_edit', 'Edit' ) );
								?>
						</a>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('<?php echo esc_js( bhg_t( 'delete_this_affiliate', 'Delete this affiliate website?' ) ); ?>');">
														<?php wp_nonce_field( 'bhg_delete_affiliate', 'bhg_delete_affiliate_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_delete_affiliate">
								<input type="hidden" name="id" value="<?php echo (int) $r->id; ?>">
								<button class="button-link-delete" type="submit">
								<?php
								echo esc_html( bhg_t( 'remove', 'Remove' ) );
								?>
								</button>
						</form>
						</td>
		</tr>
					<?php
					endforeach;
endif;
		?>
	</tbody>
	</table>

		<h2 style="margin-top:2em"><?php echo $row ? esc_html( bhg_t( 'edit_affiliate', 'Edit Affiliate Website' ) ) : esc_html( bhg_t( 'add_affiliate', 'Add Affiliate Website' ) ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:800px">
				<?php wp_nonce_field( 'bhg_save_affiliate', 'bhg_save_affiliate_nonce' ); ?>
		<input type="hidden" name="action" value="bhg_save_affiliate">
	<?php
	if ( $row ) :
		?>
		<input type="hidden" name="id" value="<?php echo (int) $row->id; ?>"><?php endif; ?>
	<table class="form-table">
		<tr>
				<th><label for="aff_name">
				<?php
				echo esc_html( bhg_t( 'name', 'Name' ) );
				?>
</label></th>
				<td><input class="regular-text" id="aff_name" name="name" value="<?php echo esc_attr( $row->name ?? '' ); ?>" required></td>
				</tr>
				<tr>
				<th><label for="aff_slug">
				<?php
				echo esc_html( bhg_t( 'slug', 'Slug' ) );
				?>
</label></th>
				<td><input class="regular-text" id="aff_slug" name="slug" value="<?php echo esc_attr( $row->slug ?? '' ); ?>" required></td>
				</tr>
		<tr>
		<th><label for="aff_url">
		<?php
		echo esc_html( bhg_t( 'url', 'URL' ) );
		?>
</label></th>
		<td><input class="regular-text" id="aff_url" name="url" value="<?php echo esc_attr( $row->url ?? '' ); ?>" placeholder="https://example.com"></td>
		</tr>
		<tr>
		<th><label for="aff_status">
		<?php
		echo esc_html( bhg_t( 'sc_status', 'Status' ) );
		?>
</label></th>
		<td>
			<select id="aff_status" name="status">
			<?php
			$opts = array( 'active', 'inactive' );
			$cur  = $row->status ?? 'active'; foreach ( $opts as $o ) :
				?>
				<option value="<?php echo esc_attr( $o ); ?>" <?php selected( $cur, $o ); ?>><?php echo esc_html( ucfirst( $o ) ); ?></option>
						<?php endforeach; ?>
			</select>
		</td>
		</tr>
	</table>
		<?php submit_button( $row ? bhg_t( 'update_affiliate', 'Update Affiliate Website' ) : bhg_t( 'create_affiliate', 'Create Affiliate Website' ) ); ?>
	</form>
</div>
