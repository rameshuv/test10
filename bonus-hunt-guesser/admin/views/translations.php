<?php
/**
 * Admin view for managing plugin translations.
 *
 * Provides search, pagination and context-based grouping for translation keys.
 * Custom translations are highlighted and each row uses a nonce for security.
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

$table_name                  = $wpdb->prefix . 'bhg_translations';
$table_like                  = $wpdb->esc_like( $table_name );
$translations_table_exists   = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_like ) ) === $table_name );
$translations_notice_message = '';

if ( $translations_table_exists && function_exists( 'bhg_seed_default_translations_if_empty' ) ) {
		bhg_seed_default_translations_if_empty();
}

$table = $table_name;

if ( ! $translations_table_exists ) {
		$translations_notice_message = sprintf(
			bhg_t( 'translations_missing_table_message', 'Translations cannot be listed because the database table is missing. Run the database upgrade from the <a href="%s">Database tools</a> screen to create it.' ),
			esc_url( admin_url( 'admin.php?page=bhg-database' ) )
		);
}

// Pagination variables.
$allowed_per_page = array( 10, 20, 50 );
$items_per_page   = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 20;
if ( ! in_array( $items_per_page, $allowed_per_page, true ) ) {
		$items_per_page = 20;
}
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
$offset       = ( $current_page - 1 ) * $items_per_page;

// Current search term.
$search_term = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

// Handle form submission.
if ( $translations_table_exists && isset( $_POST['bhg_save_translation'] ) && check_admin_referer( 'bhg_save_translation_action', 'bhg_nonce' ) ) {
		$slug   = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$locale = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : get_locale();
		$text   = isset( $_POST['text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) : '';

	if ( '' === $slug ) {
			$form_error = bhg_t( 'key_field_is_required', 'Key field is required.' );
	} else {
						$exists = (int) $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(*) FROM {$table} WHERE slug = %s AND locale = %s",
								$slug,
								$locale
							)
						);

		if ( $exists ) {
				$wpdb->update(
					$table,
					array( 'text' => $text ),
					array(
						'slug'   => $slug,
						'locale' => $locale,
					),
					array( '%s' ),
					array( '%s', '%s' )
				);
		} else {
					$wpdb->insert(
						$table,
						array(
							'slug'   => $slug,
							'text'   => $text,
							'locale' => $locale,
						),
						array( '%s', '%s', '%s' )
					);
		}

						// Invalidate cached values so updates appear immediately.
						bhg_clear_translation_cache();
						$notice = bhg_t( 'translation_saved', 'Translation saved.' );
	}
}

// Fetch rows with pagination and optional search.
$rows       = array();
$total      = 0;
$grouped    = array();
$pagination = '';

if ( $translations_table_exists ) {
	if ( $search_term ) {
			$like  = '%' . $wpdb->esc_like( $search_term ) . '%';
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE slug LIKE %s OR text LIKE %s OR default_text LIKE %s",
					$like,
					$like,
					$like
				)
			);
			$rows  = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT slug, default_text, text, locale FROM {$table} WHERE slug LIKE %s OR text LIKE %s OR default_text LIKE %s ORDER BY slug ASC LIMIT %d OFFSET %d",
					$like,
					$like,
					$like,
					$items_per_page,
					$offset
				)
			);
	} else {
			$total = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$table}"
			);
			$rows  = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT slug, default_text, text, locale FROM {$table} ORDER BY slug ASC LIMIT %d OFFSET %d",
					$items_per_page,
					$offset
				)
			);
	}

		// Pagination links.
		$total_pages = max( 1, ceil( $total / $items_per_page ) );
		$pagination  = paginate_links(
			array(
				'base'     => add_query_arg( 'paged', '%#%' ),
				'format'   => '',
				'current'  => $current_page,
				'total'    => $total_pages,
				'add_args' => array(
					'per_page' => $items_per_page,
					's'        => $search_term,
				),
			)
		);

		// Group rows by context (prefix before the first underscore).
	if ( $rows ) {
		foreach ( $rows as $r ) {
				list( $context )       = array_pad( explode( '_', $r->slug, 2 ), 2, 'misc' );
				$grouped[ $context ][] = $r;
		}
			ksort( $grouped );
		foreach ( $grouped as &$items ) {
				usort(
					$items,
					static function ( $a, $b ) {
								return strcmp( $a->slug, $b->slug );
					}
				);
		}
			unset( $items );
	}
}
?>
<div class="wrap">
		<h1><?php echo esc_html( bhg_t( 'menu_translations', 'Translations' ) ); ?></h1>

		<?php if ( $translations_notice_message ) : ?>
				<div class="notice notice-warning"><p><?php echo wp_kses_post( $translations_notice_message ); ?></p></div>
		<?php endif; ?>

		<?php if ( $translations_table_exists ) : ?>
				<?php if ( ! empty( $notice ) ) : ?>
						<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
				<?php endif; ?>

				<?php if ( ! empty( $form_error ) ) : ?>
						<div class="notice notice-error"><p><?php echo esc_html( $form_error ); ?></p></div>
				<?php endif; ?>

				<form method="post">
						<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
						<input type="hidden" name="locale" value="<?php echo esc_attr( get_locale() ); ?>" />
						<table class="form-table" role="presentation">
								<tbody>
										<tr>
												<th scope="row"><label for="slug"><?php echo esc_html( bhg_t( 'slug', 'Slug' ) ); ?></label></th>
												<td>
														<input name="slug" id="slug" type="text" class="regular-text code" required aria-describedby="bhg-translation-slug-help" />
														<p class="description" id="bhg-translation-slug-help"><?php echo esc_html( bhg_t( 'translations_slug_description', 'Use the identifier from the plugin source (for example, sc_title).' ) ); ?></p>
												</td>
										</tr>
										<tr>
												<th scope="row"><label for="text"><?php echo esc_html( bhg_t( 'value', 'Value' ) ); ?></label></th>
												<td>
														<textarea name="text" id="text" class="large-text code" rows="4" aria-describedby="bhg-translation-value-help"></textarea>
														<p class="description" id="bhg-translation-value-help"><?php echo esc_html( bhg_t( 'translations_value_description', 'Provide the custom wording you want to display.' ) ); ?> <?php echo esc_html( bhg_t( 'translations_help_leave_blank', 'Leave blank to use the default text.' ) ); ?></p>
												</td>
										</tr>
								</tbody>
						</table>
						<p class="submit"><button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_save', 'Save' ) ); ?></button></p>
				</form>

				<form method="get" class="bhg-translations-search">
						<input type="hidden" name="page" value="bhg-translations" />
						<p class="search-box">
								<label class="screen-reader-text" for="bhg-translation-search-input"><?php echo esc_html( bhg_t( 'label_search_translations', 'Search translations' ) ); ?></label>
								<input type="search" id="bhg-translation-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" />
								<label class="screen-reader-text" for="bhg-per-page"><?php echo esc_html( bhg_t( 'label_items_per_page', 'Items per page' ) ); ?></label>
								<select id="bhg-per-page" name="per_page">
										<option value="10" <?php selected( $items_per_page, 10 ); ?>>10</option>
										<option value="20" <?php selected( $items_per_page, 20 ); ?>>20</option>
										<option value="50" <?php selected( $items_per_page, 50 ); ?>>50</option>
								</select>
								<button class="button"><?php echo esc_html( bhg_t( 'button_filter', 'Filter' ) ); ?></button>
						</p>
				</form>

				<h2><?php echo esc_html( bhg_t( 'existing_keys', 'Existing keys' ) ); ?></h2>
				<p class="description"><?php echo esc_html( bhg_t( 'custom_translations_highlighted', 'Custom translations are highlighted.' ) ); ?></p>
				<?php if ( $pagination ) : ?>
						<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
				<?php endif; ?>

				<?php if ( ! empty( $grouped ) ) : ?>
					<?php
					$context_meta = array(
						'label'  => array(
							'title'       => bhg_t( 'translations_group_frontend_labels', 'Frontend Labels' ),
							'description' => bhg_t( 'translations_group_frontend_labels_help', 'Form labels and user interface text on the public site.' ),
							'section'     => 'frontend',
						),
						'button' => array(
							'title'       => bhg_t( 'translations_group_frontend_buttons', 'Frontend Buttons' ),
							'description' => bhg_t( 'translations_group_frontend_buttons_help', 'Call-to-action buttons available on the public interface.' ),
							'section'     => 'frontend',
						),
						'notice' => array(
							'title'       => bhg_t( 'translations_group_frontend_notices', 'Frontend Notices' ),
							'description' => bhg_t( 'translations_group_frontend_notices_help', 'Alerts and validation messages shown to visitors.' ),
							'section'     => 'frontend',
						),
						'guess'  => array(
							'title'       => bhg_t( 'translations_group_guessing', 'Guessing Workflow' ),
							'description' => bhg_t( 'translations_group_guessing_help', 'Messages and hints presented while visitors submit their guesses.' ),
							'section'     => 'frontend',
						),
						'sc'     => array(
							'title'       => bhg_t( 'translations_group_shortcodes', 'Shortcodes & Leaderboards' ),
							'description' => bhg_t( 'translations_group_shortcodes_help', 'Column headers and text shown in shortcode tables.' ),
							'section'     => 'frontend',
						),
						'menu'   => array(
							'title'       => bhg_t( 'translations_group_admin', 'Admin Interface' ),
							'description' => bhg_t( 'translations_group_admin_help', 'Settings and dashboard copy for administrators.' ),
							'section'     => 'admin',
						),
					);

					$frontend_contexts = array();
					foreach ( $context_meta as $key => $meta ) {
						if ( isset( $meta['section'] ) && 'frontend' === $meta['section'] ) {
								$frontend_contexts[] = $key;
						}
					}

					$sections = array(
						'frontend' => array(
							'title'       => bhg_t( 'translations_section_frontend', 'Frontend Interface' ),
							'description' => bhg_t( 'translations_section_frontend_help', 'Strings displayed to visitors across forms, leaderboards, and shortcodes.' ),
							'contexts'    => array(),
						),
						'admin'    => array(
							'title'       => bhg_t( 'translations_section_admin', 'WordPress Admin' ),
							'description' => bhg_t( 'translations_section_admin_help', 'Internal controls for site managers.' ),
							'contexts'    => array(),
						),
					);

					foreach ( $grouped as $context => $items ) {
						$section_key                                      = in_array( $context, $frontend_contexts, true ) ? 'frontend' : 'admin';
						$sections[ $section_key ]['contexts'][ $context ] = $items;
					}

					$frontend_order = array( 'label', 'button', 'notice', 'guess', 'sc' );
					$admin_order    = array( 'menu', 'translations', 'tools', 'bonus', 'tournaments', 'ads' );

					foreach ( $sections as $section_key => &$section ) {
						if ( empty( $section['contexts'] ) ) {
								continue;
						}

						if ( 'frontend' === $section_key ) {
								uksort(
									$section['contexts'],
									static function ( $a, $b ) use ( $frontend_order ) {
												$pos_a = array_search( $a, $frontend_order, true );
												$pos_b = array_search( $b, $frontend_order, true );

												$pos_a = ( false === $pos_a ) ? PHP_INT_MAX : $pos_a;
												$pos_b = ( false === $pos_b ) ? PHP_INT_MAX : $pos_b;

										if ( $pos_a === $pos_b ) {
												return strcmp( $a, $b );
										}

												return ( $pos_a < $pos_b ) ? -1 : 1;
									}
								);
						} else {
							uksort(
								$section['contexts'],
								static function ( $a, $b ) use ( $admin_order ) {
										$pos_a = array_search( $a, $admin_order, true );
										$pos_b = array_search( $b, $admin_order, true );

										$pos_a = ( false === $pos_a ) ? PHP_INT_MAX : $pos_a;
										$pos_b = ( false === $pos_b ) ? PHP_INT_MAX : $pos_b;

									if ( $pos_a === $pos_b ) {
											return strcmp( $a, $b );
									}

										return ( $pos_a < $pos_b ) ? -1 : 1;
								}
							);
						}
					}
					unset( $section );
					?>

					<?php foreach ( $sections as $section_key => $section ) : ?>
						<?php if ( empty( $section['contexts'] ) ) : ?>
								<?php continue; ?>
						<?php endif; ?>
						<section class="bhg-translation-section" data-section="<?php echo esc_attr( $section_key ); ?>">
								<header class="bhg-translation-section__header">
										<h2><?php echo esc_html( $section['title'] ); ?></h2>
										<?php if ( ! empty( $section['description'] ) ) : ?>
												<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
										<?php endif; ?>
								</header>

								<?php foreach ( $section['contexts'] as $context => $items ) : ?>
										<?php
										$meta          = isset( $context_meta[ $context ] ) ? $context_meta[ $context ] : array();
										$context_title = isset( $meta['title'] ) ? $meta['title'] : ucwords( str_replace( '_', ' ', $context ) );
										$context_desc  = isset( $meta['description'] ) ? $meta['description'] : '';
										?>
										<div class="bhg-translation-group" data-context="<?php echo esc_attr( $context ); ?>">
												<h3>
														<?php echo esc_html( $context_title ); ?>
														<?php if ( $context_desc ) : ?>
																<span class="dashicons dashicons-editor-help" tabindex="0" role="img" aria-label="<?php echo esc_attr( $context_desc ); ?>" title="<?php echo esc_attr( $context_desc ); ?>"></span>
														<?php endif; ?>
												</h3>
												<?php if ( $context_desc ) : ?>
														<p class="description"><?php echo esc_html( $context_desc ); ?></p>
												<?php endif; ?>
												<table class="widefat striped bhg-translations-table">
														<thead>
																<tr>
																		<th><?php echo esc_html( bhg_t( 'label_key', 'Key' ) ); ?></th>
																		<th><?php echo esc_html( bhg_t( 'label_default', 'Default' ) ); ?></th>
																		<th><?php echo esc_html( bhg_t( 'label_custom', 'Custom' ) ); ?></th>
																</tr>
														</thead>
														<tbody>
																<?php foreach ( $items as $r ) : ?>
																		<?php
																		$row_class      = ( '' === $r->text || $r->text === $r->default_text ) ? 'bhg-default-row' : 'bhg-custom-row';
																		$input_id       = 'bhg-translation-' . sanitize_html_class( $r->slug );
																		$description_id = $input_id . '-help';
																		$help_text      = ( 'frontend' === $section_key ) ? bhg_t( 'translations_help_frontend', 'Visible on the public site. Update to match your branding.' ) : bhg_t( 'translations_help_admin', 'Displayed inside the WordPress admin screens.' );
																		$help_text     .= ' ' . bhg_t( 'translations_help_leave_blank', 'Leave blank to use the default text.' );
																		$help_text      = trim( $help_text );
																		?>
																		<tr class="<?php echo esc_attr( $row_class ); ?>">
																				<td><code><?php echo esc_html( $r->slug ); ?></code></td>
																				<td><span class="bhg-default-text"><?php echo esc_html( $r->default_text ); ?></span></td>
																				<td>
																						<form method="post" class="bhg-inline-form">
																								<?php wp_nonce_field( 'bhg_save_translation_action', 'bhg_nonce' ); ?>
																								<input type="hidden" name="slug" value="<?php echo esc_attr( $r->slug ); ?>" />
																								<input type="hidden" name="locale" value="<?php echo esc_attr( $r->locale ); ?>" />
																								<input type="text" name="text" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $r->text ); ?>" class="regular-text bhg-translation-input" data-original="<?php echo esc_attr( $r->text ); ?>" placeholder="<?php echo esc_attr( bhg_t( 'placeholder_custom_value', 'Custom value' ) ); ?>" aria-describedby="<?php echo esc_attr( $description_id ); ?>" />
																								<p id="<?php echo esc_attr( $description_id ); ?>" class="description"><?php echo esc_html( $help_text ); ?></p>
																								<button type="submit" name="bhg_save_translation" class="button button-primary"><?php echo esc_html( bhg_t( 'button_update', 'Update' ) ); ?></button>
																						</form>
																				</td>
																		</tr>
																<?php endforeach; ?>
														</tbody>
												</table>
										</div>
								<?php endforeach; ?>
						</section>
				<?php endforeach; ?>
				<?php else : ?>
						<p><?php echo esc_html( bhg_t( 'no_translations_yet', 'No translations yet.' ) ); ?></p>
				<?php endif; ?>

				<?php if ( $pagination ) : ?>
						<div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( $pagination ); ?></div></div>
				<?php endif; ?>
		<?php else : ?>
				<p><?php echo esc_html( bhg_t( 'translations_missing_table_placeholder', 'Translation entries will appear here after the database tables are installed.' ) ); ?></p>
		<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.bhg-inline-form input[type="text"]').forEach(function (input) {
		var original = input.getAttribute('data-original');
		var row      = input.closest('tr');
		input.addEventListener('input', function () {
			if (input.value !== original) {
				row.classList.add('bhg-modified-row');
			} else {
				row.classList.remove('bhg-modified-row');
			}
		});
	});
});
</script>

<style>
.bhg-translation-section {
				margin-top: 32px;
}
.bhg-translation-section__header h2 {
				margin-bottom: 4px;
}
.bhg-translation-section__header .description {
				margin-top: 0;
				max-width: 720px;
}
.bhg-translation-group {
				margin-top: 24px;
}
.bhg-translation-group h3 {
				align-items: center;
				display: flex;
				gap: 6px;
				margin-bottom: 4px;
}
.bhg-translation-group .dashicons {
				color: #646970;
				cursor: help;
}
.bhg-translation-group .dashicons:focus {
				color: #2271b1;
				outline: 2px solid #2271b1;
				outline-offset: 2px;
}
.bhg-translation-group .description {
				margin: 0 0 12px;
				max-width: 720px;
}
.bhg-default-text {
				display: inline-block;
				max-width: 320px;
}
.bhg-translation-input {
				max-width: 100%;
}
.bhg-inline-form .description {
				margin: 6px 0 10px;
}
.bhg-inline-form .button {
				margin-top: 4px;
}
@media (min-width: 782px) {
				.bhg-translation-input {
								min-width: 260px;
				}
}
.bhg-modified-row {
				background-color: #fff3cd;
				border-left: 4px solid #d97706;
}
.bhg-custom-row {
				background-color: #e6ffed;
				border-left: 4px solid #2f855a;
}
</style>

