<?php
/**
 * Jackpots admin screen.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

$jackpots      = class_exists( 'BHG_Jackpots' ) ? BHG_Jackpots::instance() : null;
$jackpot_items = $jackpots ? $jackpots->get_jackpots( array( 'limit' => 10 ) ) : array();
$editing       = isset( $_GET['action'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['action'] ) );
$editing_id    = isset( $_GET['jackpot_id'] ) ? absint( wp_unslash( $_GET['jackpot_id'] ) ) : 0;
$current       = array();

if ( $editing && $jackpots && $editing_id ) {
		$current = $jackpots->get_jackpot( $editing_id );
	if ( ! $current ) {
			$editing = false;
	}
}

$hunts      = array();
$affiliates = array();

if ( class_exists( 'BHG_DB' ) ) {
		$db         = new BHG_DB();
		$affiliates = $db->get_affiliate_websites();
}

global $wpdb;
$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
$hunts       = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"SELECT id, title, status FROM {$hunts_table} ORDER BY id DESC LIMIT 100"
);

$messages = array(
	'jackpot_created' => bhg_t( 'jackpot_created', 'Jackpot created successfully.' ),
	'jackpot_updated' => bhg_t( 'jackpot_updated', 'Jackpot updated successfully.' ),
	'jackpot_deleted' => bhg_t( 'jackpot_deleted', 'Jackpot deleted.' ),
	'jackpot_reset'   => bhg_t( 'jackpot_reset', 'Jackpot reset to its starting amount.' ),
	'jackpot_error'   => bhg_t( 'jackpot_error', 'Unable to save jackpot. Please check the form and try again.' ),
	'nonce'           => bhg_t( 'nonce_error', 'Security check failed. Please try again.' ),
);

$current_msg = isset( $_GET['bhg_msg'] ) ? sanitize_key( wp_unslash( $_GET['bhg_msg'] ) ) : '';

if ( $current_msg && isset( $messages[ $current_msg ] ) ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $messages[ $current_msg ] ) . '</p></div>';
}

$default_link_mode = $current && isset( $current['link_mode'] ) ? sanitize_key( $current['link_mode'] ) : 'all';
$default_status    = $current && isset( $current['status'] ) ? sanitize_key( $current['status'] ) : 'active';
$linked_config     = array();

if ( $current && ! empty( $current['link_config'] ) ) {
		$decoded = json_decode( $current['link_config'], true );
	if ( is_array( $decoded ) ) {
			$linked_config = $decoded;
	}
}

$selected_hunts      = isset( $linked_config['hunts'] ) ? array_map( 'intval', (array) $linked_config['hunts'] ) : array();
$selected_affiliate  = isset( $linked_config['affiliates'] ) ? array_map( 'intval', (array) $linked_config['affiliates'] ) : array();
$selected_period     = isset( $linked_config['period'] ) ? sanitize_key( $linked_config['period'] ) : 'this_month';
$start_amount_value  = $current && isset( $current['start_amount'] ) ? (float) $current['start_amount'] : 0.0;
$increase_amount_val = $current && isset( $current['increase_amount'] ) ? (float) $current['increase_amount'] : 0.0;

$amount_formatter = function ( $amount ) {
	if ( function_exists( 'bhg_format_money' ) ) {
			return bhg_format_money( $amount );
	}

		return number_format_i18n( (float) $amount, 2 );
};
?>
<div class="wrap bhg-admin-jackpots">
		<h1><?php echo esc_html( bhg_t( 'menu_jackpots', 'Jackpots' ) ); ?></h1>

		<div class="bhg-jackpot-columns">
				<div class="bhg-jackpot-form">
						<h2>
								<?php
								echo esc_html(
									$editing
												? bhg_t( 'edit_jackpot', 'Edit Jackpot' )
												: bhg_t( 'add_jackpot', 'Add Jackpot' )
								);
								?>
						</h2>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'bhg_save_jackpot', 'bhg_save_jackpot_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_save_jackpot" />
								<input type="hidden" name="jackpot_id" value="<?php echo esc_attr( $editing ? (int) $current['id'] : 0 ); ?>" />

								<table class="form-table">
										<tbody>
												<tr>
														<th scope="row"><label for="bhg_jackpot_title"><?php echo esc_html( bhg_t( 'label_title', 'Title' ) ); ?></label></th>
														<td><input type="text" class="regular-text" name="title" id="bhg_jackpot_title" value="<?php echo esc_attr( $editing ? $current['title'] : '' ); ?>" required /></td>
												</tr>
												<tr>
														<th scope="row"><label for="bhg_jackpot_start_amount"><?php echo esc_html( bhg_t( 'label_start_amount', 'Start Amount' ) ); ?></label></th>
														<td><input type="text" name="start_amount" id="bhg_jackpot_start_amount" value="<?php echo esc_attr( number_format_i18n( $start_amount_value, 2 ) ); ?>" /></td>
												</tr>
												<tr>
														<th scope="row"><label for="bhg_jackpot_increase"><?php echo esc_html( bhg_t( 'label_increase_amount', 'Increase Per Miss' ) ); ?></label></th>
														<td><input type="text" name="increase_amount" id="bhg_jackpot_increase" value="<?php echo esc_attr( number_format_i18n( $increase_amount_val, 2 ) ); ?>" /></td>
												</tr>
												<tr>
														<th scope="row"><label for="bhg_jackpot_link_mode"><?php echo esc_html( bhg_t( 'label_link_mode', 'Linked Hunts' ) ); ?></label></th>
														<td>
																<select name="link_mode" id="bhg_jackpot_link_mode">
																		<?php
																		$modes = array(
																			'all'       => bhg_t( 'jackpot_link_all', 'All hunts' ),
																			'selected'  => bhg_t( 'jackpot_link_selected', 'Selected hunts' ),
																			'affiliate' => bhg_t( 'jackpot_link_affiliate', 'Hunts by affiliate' ),
																			'period'    => bhg_t( 'jackpot_link_period', 'Hunts by time period' ),
																		);
																		foreach ( $modes as $value => $label ) {
																				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $default_link_mode, $value, false ) . '>' . esc_html( $label ) . '</option>';
																		}
																		?>
																</select>
																<p class="description"><?php echo esc_html( bhg_t( 'jackpot_link_mode_help', 'Choose how hunts are attached to this jackpot.' ) ); ?></p>
														</td>
												</tr>
												<tr>
														<th scope="row"><?php echo esc_html( bhg_t( 'jackpot_selected_hunts', 'Selected Hunts' ) ); ?></th>
														<td>
																<select name="linked_hunts[]" id="bhg_jackpot_hunts" multiple="multiple" size="6">
																		<?php
																		if ( ! empty( $hunts ) ) {
																			foreach ( $hunts as $hunt ) {
																					$hunt_id    = isset( $hunt->id ) ? (int) $hunt->id : 0;
																					$hunt_title = isset( $hunt->title ) ? $hunt->title : '';
																				if ( $hunt_id <= 0 ) {
																						continue;
																				}
																					echo '<option value="' . esc_attr( $hunt_id ) . '" ' . selected( in_array( $hunt_id, $selected_hunts, true ), true, false ) . '>' . esc_html( $hunt_title ? $hunt_title : sprintf( '#%d', $hunt_id ) ) . '</option>';
																			}
																		}
																		?>
																</select>
																<p class="description"><?php echo esc_html( bhg_t( 'jackpot_selected_hunts_help', 'Only used when “Selected hunts” is chosen.' ) ); ?></p>
														</td>
												</tr>
												<tr>
														<th scope="row"><?php echo esc_html( bhg_t( 'jackpot_selected_affiliates', 'Affiliate Websites' ) ); ?></th>
														<td>
																<select name="linked_affiliates[]" id="bhg_jackpot_affiliates" multiple="multiple" size="5">
																		<?php
																		if ( ! empty( $affiliates ) ) {
																			foreach ( $affiliates as $affiliate ) {
																					$affiliate_id   = isset( $affiliate->id ) ? (int) $affiliate->id : 0;
																					$affiliate_name = isset( $affiliate->name ) ? $affiliate->name : '';
																				if ( $affiliate_id <= 0 ) {
																						continue;
																				}
																					echo '<option value="' . esc_attr( $affiliate_id ) . '" ' . selected( in_array( $affiliate_id, $selected_affiliate, true ), true, false ) . '>' . esc_html( $affiliate_name ? $affiliate_name : sprintf( '#%d', $affiliate_id ) ) . '</option>';
																			}
																		}
																		?>
																</select>
																<p class="description"><?php echo esc_html( bhg_t( 'jackpot_selected_affiliates_help', 'Used when “Hunts by affiliate” is selected.' ) ); ?></p>
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="bhg_jackpot_period"><?php echo esc_html( bhg_t( 'jackpot_period_label', 'Time Period' ) ); ?></label></th>
														<td>
																<select name="linked_period" id="bhg_jackpot_period">
																		<?php
																		$periods = array(
																			'this_month' => bhg_t( 'jackpot_period_month', 'This month' ),
																			'this_year'  => bhg_t( 'jackpot_period_year', 'This year' ),
																			'all_time'   => bhg_t( 'jackpot_period_all', 'All time' ),
																		);
																		foreach ( $periods as $value => $label ) {
																				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_period, $value, false ) . '>' . esc_html( $label ) . '</option>';
																		}
																		?>
																</select>
																<p class="description"><?php echo esc_html( bhg_t( 'jackpot_period_help', 'Used when “Hunts by time period” is selected.' ) ); ?></p>
														</td>
												</tr>
												<tr>
														<th scope="row"><label for="bhg_jackpot_status"><?php echo esc_html( bhg_t( 'label_status', 'Status' ) ); ?></label></th>
														<td>
																<select name="status" id="bhg_jackpot_status">
																		<?php
																		$statuses = array(
																			'active'   => bhg_t( 'status_active', 'Active' ),
																			'pending'  => bhg_t( 'status_pending', 'Pending' ),
																			'inactive' => bhg_t( 'status_inactive', 'Inactive' ),
																			'hit'      => bhg_t( 'status_hit', 'Hit' ),
																		);
																		foreach ( $statuses as $value => $label ) {
																				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $default_status, $value, false ) . '>' . esc_html( $label ) . '</option>';
																		}
																		?>
																</select>
														</td>
												</tr>
										</tbody>
								</table>

								<?php submit_button( $editing ? bhg_t( 'update_jackpot', 'Update Jackpot' ) : bhg_t( 'create_jackpot', 'Create Jackpot' ) ); ?>
						</form>

						<?php if ( $editing ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px;">
										<?php wp_nonce_field( 'bhg_reset_jackpot', 'bhg_reset_jackpot_nonce' ); ?>
										<input type="hidden" name="action" value="bhg_reset_jackpot" />
										<input type="hidden" name="jackpot_id" value="<?php echo esc_attr( (int) $current['id'] ); ?>" />
										<?php submit_button( bhg_t( 'reset_jackpot', 'Reset Jackpot' ), 'secondary' ); ?>
								</form>
						<?php endif; ?>
				</div>

				<div class="bhg-jackpot-list">
						<h2><?php echo esc_html( bhg_t( 'latest_jackpots', 'Latest Jackpots' ) ); ?></h2>
						<table class="widefat striped">
								<thead>
										<tr>
												<th><?php echo esc_html( bhg_t( 'label_title', 'Title' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_start_amount', 'Start Amount' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_current_amount', 'Current Amount' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_increase_amount', 'Increase' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_status', 'Status' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_updated', 'Updated' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
										</tr>
								</thead>
								<tbody>
										<?php if ( empty( $jackpot_items ) ) : ?>
												<tr>
														<td colspan="7"><?php echo esc_html( bhg_t( 'no_jackpots_found', 'No jackpots found yet.' ) ); ?></td>
												</tr>
										<?php else : ?>
												<?php foreach ( $jackpot_items as $item ) : ?>
														<?php
														$item_id      = isset( $item['id'] ) ? (int) $item['id'] : 0;
														$status       = isset( $item['status'] ) ? sanitize_key( $item['status'] ) : 'active';
														$start_amt    = isset( $item['start_amount'] ) ? (float) $item['start_amount'] : 0.0;
														$current_amt  = isset( $item['current_amount'] ) ? (float) $item['current_amount'] : 0.0;
														$increase_amt = isset( $item['increase_amount'] ) ? (float) $item['increase_amount'] : 0.0;
														$updated_at   = isset( $item['updated_at'] ) ? $item['updated_at'] : '';
														$hit_user     = isset( $item['hit_user_id'] ) ? (int) $item['hit_user_id'] : 0;
														$hit_at       = isset( $item['hit_at'] ) ? $item['hit_at'] : '';
														$status_label = bhg_t( 'status_' . $status, ucfirst( $status ) );

														if ( 'hit' === $status && $hit_user ) {
																$user = get_userdata( $hit_user );
															if ( $user ) {
																	/* translators: %1$s: user display name, %2$s: hit date. */
																	$status_label = sprintf( bhg_t( 'jackpot_hit_status', 'Hit by %1$s on %2$s' ), $user->display_name, $hit_at ? esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $hit_at ) ) : '' );
															}
														}
														?>
														<tr>
																<td><?php echo esc_html( $item['title'] ); ?></td>
																<td><?php echo esc_html( $amount_formatter( $start_amt ) ); ?></td>
																<td><?php echo esc_html( $amount_formatter( $current_amt ) ); ?></td>
																<td><?php echo esc_html( $amount_formatter( $increase_amt ) ); ?></td>
																<td><?php echo esc_html( $status_label ); ?></td>
																<td><?php echo esc_html( $updated_at ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $updated_at ) : '—' ); ?></td>
																<td class="bhg-jackpot-actions">
																		<a class="button" href="
																		<?php
																		echo esc_url(
																			add_query_arg(
																				array(
																					'page'       => 'bhg-jackpots',
																					'action'     => 'edit',
																					'jackpot_id' => $item_id,
																				),
																				admin_url( 'admin.php' )
																			)
																		);
																		?>
																								"><?php echo esc_html( bhg_t( 'edit', 'Edit' ) ); ?></a>
																		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
																				<?php wp_nonce_field( 'bhg_delete_jackpot', 'bhg_delete_jackpot_nonce' ); ?>
																				<input type="hidden" name="action" value="bhg_delete_jackpot" />
																				<input type="hidden" name="jackpot_id" value="<?php echo esc_attr( $item_id ); ?>" />
																				<button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'confirm_delete_jackpot', 'Delete this jackpot? This cannot be undone.' ) ); ?>');"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
																		</form>
																</td>
														</tr>
												<?php endforeach; ?>
										<?php endif; ?>
								</tbody>
						</table>
				</div>
		</div>
</div>
