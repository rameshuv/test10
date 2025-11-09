<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'you_do_not_have_sufficient_permissions_to_access_this_page', 'You do not have sufficient permissions to access this page.' ) ) );
}

/*
 * Verify nonce before processing request parameters.
 */
check_admin_referer( 'bhg_edit_hunt' );

global $wpdb;

$id   = absint( wp_unslash( $_GET['id'] ?? '' ) );
$hunt = bhg_get_hunt( $id );
if ( ! $hunt ) {
		echo '<div class="notice notice-error"><p>' . esc_html( bhg_t( 'invalid_hunt', 'Invalid hunt' ) ) . '</p></div>';
		return;
}

$aff_table = esc_sql( $wpdb->prefix . 'bhg_affiliate_websites' );
if ( isset( $allowed_tables ) && ! in_array( $aff_table, $allowed_tables, true ) ) {
				wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}
$affs = $wpdb->get_results(
	"SELECT id, name FROM {$aff_table} ORDER BY name ASC"
);
$sel  = isset( $hunt->affiliate_site_id ) ? (int) $hunt->affiliate_site_id : 0;

$selected_tournaments = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( (int) $hunt->id ) : array();
$selected_tournaments = array_values( array_filter( array_map( 'absint', (array) $selected_tournaments ) ) );
$t_table              = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
$active_sql           = $wpdb->prepare( "SELECT id, title FROM {$t_table} WHERE status = %s ORDER BY title ASC", 'active' );

if ( ! empty( $selected_tournaments ) ) {
		$ids_sql    = implode( ',', array_map( 'intval', $selected_tournaments ) );
		$active_sql = $wpdb->prepare(
			"SELECT id, title FROM {$t_table} WHERE status = %s OR id IN ({$ids_sql}) ORDER BY title ASC",
			'active'
		);
}

$tournaments = $wpdb->get_results( $active_sql );

$paged    = max( 1, absint( wp_unslash( $_GET['ppaged'] ?? '' ) ) );
$per_page = 30;
$data     = bhg_get_hunt_participants( $id, $paged, $per_page );
$rows     = $data['rows'];
$total    = (int) $data['total'];
$pages    = max( 1, (int) ceil( $total / $per_page ) );
$base     = remove_query_arg( 'ppaged' );
?>
<div class="wrap">
		<?php
		$message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
		if ( 'guess_deleted' === $message ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( bhg_t( 'notice_guess_removed_successfully', 'Guess removed successfully.' ) ) . '</p></div>';
		} elseif ( 'error' === $message ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( bhg_t( 'ajax_error', 'An error occurred. Please try again.' ) ) . '</p></div>';
		}
		?>
		<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'edit_bonus_hunt', 'Edit Bonus Hunt' ) ); ?> <?php echo esc_html( bhg_t( 'label_emdash', '—' ) ); ?> <?php echo esc_html( $hunt->title ); ?></h1>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="bhg-max-width-900 bhg-margin-top-small">
																<?php wp_nonce_field( 'bhg_save_hunt', 'bhg_save_hunt_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_save_hunt" />
				<input type="hidden" name="id" value="<?php echo (int) $hunt->id; ?>" />

				<?php
				$prize_rows      = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_prizes() : array();
$selected_prizes = array(
'regular' => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( $hunt->id, 'regular' ) : array(),
'premium' => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_hunt_prize_ids( $hunt->id, 'premium' ) : array(),
);
				?>

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
												<select id="bhg_affiliate" name="affiliate_site_id">
														<option value="0"><?php echo esc_html( bhg_t( 'none', 'None' ) ); ?></option>
														<?php foreach ( $affs as $a ) : ?>
																<option value="<?php echo (int) $a->id; ?>" <?php selected( $sel, (int) $a->id ); ?>>
																		<?php echo esc_html( $a->name ); ?>
																</option>
														<?php endforeach; ?>
												</select>
										</td>
								</tr>
								<tr>
										<th scope="row"><label for="bhg_tournament_select"><?php echo esc_html( bhg_t( 'tournament', 'Tournament' ) ); ?></label></th>
										<td>
												<select id="bhg_tournament_select" name="tournament_ids[]" multiple="multiple" size="5">
														<?php foreach ( $tournaments as $tournament ) : ?>
																<option value="<?php echo (int) $tournament->id; ?>" <?php selected( in_array( (int) $tournament->id, $selected_tournaments, true ) ); ?>><?php echo esc_html( $tournament->title ); ?></option>
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
                                        <td><label><input type="checkbox" id="bhg_guessing_enabled" name="guessing_enabled" value="1" <?php checked( ! empty( $hunt->guessing_enabled ) ); ?> /> <?php echo esc_html( bhg_t( 'enable_guessing_help', 'Allow users to submit or update guesses while the hunt is open.' ) ); ?></label></td>
                                </tr>
                                <tr>
                                        <th scope="row"><label for="bhg_final"><?php echo esc_html( bhg_t( 'sc_final_balance', 'Final Balance' ) ); ?></label></th>
                                                                                <td><input type="number" step="0.01" min="0" id="bhg_final" name="final_balance" value="<?php echo esc_attr( $hunt->final_balance ); ?>" placeholder="<?php echo esc_attr( esc_html( bhg_t( 'label_dash', '-' ) ) ); ?>"></td>
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

	<h2 class="bhg-margin-top-large">
	<?php
	echo esc_html( bhg_t( 'participants', 'Participants' ) );
	?>
</h2>
		<p>
		<?php
		/* translators: %s: number of participants */
		echo esc_html( sprintf( _n( '%s participant', '%s participants', $total, 'bonus-hunt-guesser' ), number_format_i18n( $total ) ) );
		?>
		</p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th>
				<?php
				echo esc_html( bhg_t( 'sc_user', 'User' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'sc_guess', 'Guess' ) );
				?>
</th>
				<th>
				<?php
				echo esc_html( bhg_t( 'date', 'Date' ) );
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
				<tr><td colspan="4">
				<?php
				echo esc_html( bhg_t( 'no_participants_yet', 'No participants yet.' ) );
				?>
</td></tr>
				<?php
			else :
				foreach ( $rows as $r ) :
					$u                        = get_userdata( (int) $r->user_id );
										$name = $u ? $u->display_name : sprintf( esc_html( bhg_t( 'label_user_hash', 'user#%d' ) ), (int) $r->user_id );
					?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . (int) $r->user_id ) ); ?>"><?php echo esc_html( $name ); ?></a></td>
					<td><?php echo esc_html( number_format_i18n( (float) $r->guess, 2 ) ); ?></td>
										<td><?php echo $r->created_at ? esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->created_at ) ) ) : esc_html( bhg_t( 'label_dash', '-' ) ); ?></td>
										<td>
												<?php
																								$delete_url = wp_nonce_url(
																									add_query_arg(
																										array(
																											'action'   => 'bhg_delete_guess',
																											'guess_id' => (int) $r->id,
																										),
																										admin_url( 'admin-post.php' )
																									),
																									'bhg_delete_guess',
																									'bhg_delete_guess_nonce'
																								);
												?>
												<a href="<?php echo esc_url( $delete_url ); ?>" class="button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'delete_this_guess', 'Delete this guess?' ) ); ?>');">
												<?php
												echo esc_html( bhg_t( 'remove', 'Remove' ) );
												?>
</a>
										</td>
								</tr>
							<?php
			endforeach;
endif;
			?>
		</tbody>
	</table>

	<?php if ( $pages > 1 ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo paginate_links(
					array(
						'base'      => add_query_arg( 'ppaged', '%#%', $base ),
						'format'    => '',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'total'     => $pages,
						'current'   => $paged,
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
