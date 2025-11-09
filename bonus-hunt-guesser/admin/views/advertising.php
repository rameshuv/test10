<?php
/**
 * Advertising management view.
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
$ads_table      = esc_sql( $wpdb->prefix . 'bhg_ads' );
$allowed_tables = array( $ads_table );
if ( ! in_array( $ads_table, $allowed_tables, true ) ) {
	wp_die( esc_html( bhg_t( 'notice_invalid_table', 'Invalid table.' ) ) );
}

$edit_id = 0;
if ( isset( $_GET['edit'] ) ) {
		$nonce = isset( $_GET['bhg_edit_ad_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['bhg_edit_ad_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'bhg_edit_ad' ) ) {
			wp_die( esc_html( bhg_t( 'notice_invalid_nonce', 'Invalid nonce.' ) ) );
	}
		$edit_id = absint( wp_unslash( $_GET['edit'] ) );
}

// Fetch ads.
// db call ok; no-cache ok.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic table name; query includes no user input.
$ads = $wpdb->get_results(
	"SELECT * FROM {$ads_table} ORDER BY id DESC"
);
?>
<div class="wrap bhg-wrap">
		<h1 class="wp-heading-inline"><?php echo esc_html( bhg_t( 'menu_advertising', 'Advertising' ) ); ?></h1>

		<div class="bhg-admin-sections">
				<div class="bhg-admin-card">
						<h2><?php echo esc_html( bhg_t( 'existing_ads', 'Existing Ads' ) ); ?></h2>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'bhg_delete_ad', 'bhg_delete_ad_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_delete_ad">
								<div class="tablenav top">
										<div class="alignleft actions bulkactions">
												<label for="bulk-action-selector-top" class="screen-reader-text"><?php echo esc_html( bhg_t( 'select_bulk_action', 'Select bulk action' ) ); ?></label>
												<select name="bulk_action" id="bulk-action-selector-top">
														<option value=""><?php echo esc_html( bhg_t( 'bulk_actions', 'Bulk actions' ) ); ?></option>
														<option value="delete"><?php echo esc_html( bhg_t( 'remove', 'Remove' ) ); ?></option>
												</select>
												<input type="submit" class="button action" value="<?php echo esc_attr( bhg_t( 'apply', 'Apply' ) ); ?>">
										</div>
								</div>

								<table class="widefat striped">
										<thead>
				<tr>
												<td id="cb" class="check-column"><input type="checkbox" onclick="document.querySelectorAll('.bhg-ad-checkbox').forEach(function(cb){cb.checked=this.checked;}.bind(this));" /></td>
												<th><?php echo esc_html( bhg_t( 'id', 'ID' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'titlecontent', 'Title/Content' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_visible_to', 'Visible To' ) ); ?></th>
												<th><?php echo esc_html( bhg_t( 'label_active', 'Active' ) ); ?></th>
												<th class="column-actions"><?php echo esc_html( bhg_t( 'label_actions', 'Actions' ) ); ?></th>
				</tr>
										</thead>
										<tbody>
						<?php if ( empty( $ads ) ) : ?>
		<tr><td colspan="7"><?php echo esc_html( bhg_t( 'notice_no_ads_yet', 'No ads yet.' ) ); ?></td></tr>
								<?php
												else :
														$placement_labels = array(
															'none'      => bhg_t( 'none', 'None' ),
															'footer'    => bhg_t( 'label_footer', 'Footer' ),
															'bottom'    => bhg_t( 'label_bottom', 'Bottom' ),
															'sidebar'   => bhg_t( 'label_sidebar', 'Sidebar' ),
															'shortcode' => bhg_t( 'label_shortcode', 'Shortcode' ),
														);
														$visible_labels   = array(
															'all'            => bhg_t( 'label_all', 'All' ),
															'guests'         => bhg_t( 'label_guests', 'Guests' ),
															'logged_in'      => bhg_t( 'label_logged_in', 'Logged In' ),
															'affiliates'     => bhg_t( 'label_affiliates', 'Affiliates' ),
															'non_affiliates' => bhg_t( 'label_non_affiliates', 'Non Affiliates' ),
														);
														foreach ( $ads as $ad ) :
																$placement  = isset( $ad->placement ) ? $ad->placement : 'none';
																$visible_to = isset( $ad->visible_to ) ? $ad->visible_to : 'all';
															?>
								<tr>
<th scope="row" class="check-column"><input type="checkbox" class="bhg-ad-checkbox" name="ad_ids[]" value="<?php echo esc_attr( (int) $ad->id ); ?>" /></th>
<td><?php echo esc_html( (int) $ad->id ); ?></td>
																																<td><?php echo isset( $ad->title ) && '' !== $ad->title ? esc_html( $ad->title ) : wp_kses_post( wp_trim_words( $ad->content, 12 ) ); ?></td>
																																<td><?php echo esc_html( $placement_labels[ $placement ] ?? $placement ); ?></td>
																																<td><?php echo esc_html( $visible_labels[ $visible_to ] ?? $visible_to ); ?></td>
																																<td><?php echo 1 === (int) $ad->active ? esc_html( bhg_t( 'yes', 'Yes' ) ) : esc_html( bhg_t( 'no', 'No' ) ); ?></td>
								<td class="column-actions" data-colname="<?php echo esc_attr( bhg_t( 'label_actions', 'Actions' ) ); ?>">
															<?php
															$edit_url = wp_nonce_url(
																add_query_arg(
																	array(
																		'page' => 'bhg-ads',
																		'edit' => (int) $ad->id,
																	),
																	BHG_Utils::admin_url( 'admin.php' )
																),
																'bhg_edit_ad',
																'bhg_edit_ad_nonce'
															);
															?>
										<div class="bhg-admin-actions">
												<a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( bhg_t( 'button_edit', 'Edit' ) ); ?></a>
												<button type="submit" name="ad_id" value="<?php echo esc_attr( (int) $ad->id ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js( bhg_t( 'delete_this_ad', 'Delete this ad?' ) ); ?>');"><?php echo esc_html( bhg_t( 'delete', 'Delete' ) ); ?></button>
										</div>
								</td>
								</tr>
																																<?php
																												endforeach;
																								endif;
												?>
																</tbody>
																</table>
						</form>
				</div>

				<?php
				$ad = null;
				if ( $edit_id ) {
						// db call ok; no-cache ok.
						$ad = $wpdb->get_row(
							$wpdb->prepare( "SELECT * FROM {$ads_table} WHERE id = %d", $edit_id )
						);
				}
				?>
				<div class="bhg-admin-card">
						<h2><?php echo $edit_id ? esc_html( bhg_t( 'edit_ad', 'Edit Ad' ) ) : esc_html( bhg_t( 'add_ad', 'Add Ad' ) ); ?></h2>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'bhg_save_ad', 'bhg_save_ad_nonce' ); ?>
								<input type="hidden" name="action" value="bhg_save_ad">
		<?php if ( $ad ) : ?>
								<input type="hidden" name="id" value="<?php echo esc_attr( (int) $ad->id ); ?>">
		<?php endif; ?>

		<table class="form-table" role="presentation">
				<tbody>
				<tr>
						<th scope="row"><label for="bhg_ad_title"><?php echo esc_html( bhg_t( 'sc_title', 'Title' ) ); ?></label></th>
						<td><input class="regular-text" id="bhg_ad_title" name="title" value="<?php echo esc_attr( $ad ? ( $ad->title ?? '' ) : '' ); ?>"></td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_content"><?php echo esc_html( bhg_t( 'content', 'Content' ) ); ?></label></th>
						<td><?php wp_editor( $ad ? $ad->content : '', 'bhg_ad_content', array( 'textarea_name' => 'content' ) ); ?></td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_link"><?php echo esc_html( bhg_t( 'link_url_optional', 'Link URL (optional)' ) ); ?></label></th>
						<td><input class="regular-text" id="bhg_ad_link" name="link_url" value="<?php echo esc_attr( $ad ? ( $ad->link_url ?? '' ) : '' ); ?>"></td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_place"><?php echo esc_html( bhg_t( 'placement', 'Placement' ) ); ?></label></th>
						<td>
						<select id="bhg_ad_place" name="placement">
								<?php
								$placement_opts   = array_values(
									array_unique(
										array_merge(
											array( 'none' ),
											BHG_Ads::get_allowed_placements()
										)
									)
								);
								$placement_labels = array(
									'none'      => bhg_t( 'none', 'None' ),
									'footer'    => bhg_t( 'label_footer', 'Footer' ),
									'bottom'    => bhg_t( 'label_bottom', 'Bottom' ),
									'sidebar'   => bhg_t( 'label_sidebar', 'Sidebar' ),
									'shortcode' => bhg_t( 'label_shortcode', 'Shortcode' ),
								);
								$sel              = $ad ? ( $ad->placement ?? 'none' ) : 'none';
								foreach ( $placement_opts as $o ) {
										$label = $placement_labels[ $o ] ?? $o;
										echo '<option value="' . esc_attr( $o ) . '" ' . selected( $sel, $o, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
						</select>
						</td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_vis"><?php echo esc_html( bhg_t( 'label_visible_to', 'Visible To' ) ); ?></label></th>
						<td>
						<select id="bhg_ad_vis" name="visible_to">
								<?php
								$visible_opts   = array( 'all', 'guests', 'logged_in', 'affiliates', 'non_affiliates' );
								$visible_labels = array(
									'all'            => bhg_t( 'label_all', 'All' ),
									'guests'         => bhg_t( 'label_guests', 'Guests' ),
									'logged_in'      => bhg_t( 'label_logged_in', 'Logged In' ),
									'affiliates'     => bhg_t( 'label_affiliates', 'Affiliates' ),
									'non_affiliates' => bhg_t( 'label_non_affiliates', 'Non Affiliates' ),
								);
								$sel            = $ad ? ( $ad->visible_to ?? 'all' ) : 'all';
								foreach ( $visible_opts as $o ) {
										$label = $visible_labels[ $o ] ?? $o;
										echo '<option value="' . esc_attr( $o ) . '" ' . selected( $sel, $o, false ) . '>' . esc_html( $label ) . '</option>';
								}
								?>
						</select>
						</td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_targets"><?php echo esc_html( bhg_t( 'target_page_slugs', 'Target Page Slugs' ) ); ?></label></th>
						<td><input class="regular-text" id="bhg_ad_targets" name="target_pages" value="<?php echo esc_attr( $ad ? ( $ad->target_pages ?? '' ) : '' ); ?>" placeholder="page-slug-1,page-slug-2"></td>
				</tr>
				<tr>
						<th scope="row"><label for="bhg_ad_active"><?php echo esc_html( bhg_t( 'label_active', 'Active' ) ); ?></label></th>
						<td><input type="checkbox" id="bhg_ad_active" name="active" value="1" <?php checked( $ad ? ( $ad->active ?? 1 ) : 1, 1 ); ?>></td>
				</tr>
				</tbody>
		</table>
		<?php submit_button( $ad ? bhg_t( 'update_ad', 'Update Ad' ) : bhg_t( 'create_ad', 'Create Ad' ) ); ?>
						</form>
				</div>
		</div>
</div>
