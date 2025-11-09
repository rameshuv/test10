<?php
/**
 * Admin functionality for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin screens and actions for the plugin.
 */
class BHG_Admin {

	/**
	 * Initialize admin hooks and actions.
	 */
	public function __construct() {
		// Menus.
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// Handlers.
				add_action( 'admin_post_bhg_delete_guess', array( $this, 'handle_delete_guess' ) );
				add_action( 'admin_post_bhg_save_hunt', array( $this, 'handle_save_hunt' ) );
				add_action( 'admin_post_bhg_close_hunt', array( $this, 'handle_close_hunt' ) );
				add_action( 'admin_post_bhg_delete_hunt', array( $this, 'handle_delete_hunt' ) );
				add_action( 'admin_post_bhg_toggle_guessing', array( $this, 'handle_toggle_guessing' ) );
				add_action( 'admin_post_bhg_save_ad', array( $this, 'handle_save_ad' ) );
				add_action( 'admin_post_bhg_delete_ad', array( $this, 'handle_delete_ad' ) );
				add_action( 'admin_post_bhg_save_prize', array( $this, 'handle_save_prize' ) );
				add_action( 'admin_post_bhg_save_prize_settings', array( $this, 'handle_save_prize_settings' ) );
				add_action( 'admin_post_bhg_prize_delete', array( $this, 'handle_delete_prize' ) );
				add_action( 'admin_post_bhg_save_jackpot', array( $this, 'handle_save_jackpot' ) );
				add_action( 'admin_post_bhg_delete_jackpot', array( $this, 'handle_delete_jackpot' ) );
				add_action( 'admin_post_bhg_reset_jackpot', array( $this, 'handle_reset_jackpot' ) );
								add_action( 'admin_post_bhg_tournament_save', array( $this, 'handle_save_tournament' ) );
								add_action( 'admin_post_bhg_tournament_delete', array( $this, 'handle_delete_tournament' ) );
				add_action( 'admin_post_bhg_tournament_close', array( $this, 'handle_close_tournament' ) );
				add_action( 'admin_post_bhg_save_affiliate', array( $this, 'handle_save_affiliate' ) );
				add_action( 'admin_post_bhg_delete_affiliate', array( $this, 'handle_delete_affiliate' ) );
				add_action( 'admin_post_bhg_save_user_meta', array( $this, 'handle_save_user_meta' ) );
	}

	/**
	 * Register admin menus and pages.
	 */
	public function menu() {
		$cap  = 'manage_options';
		$slug = 'bhg';

		add_menu_page(
			bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
			bhg_t( 'bonus_hunt', 'Bonus Hunt' ),
			$cap,
			$slug,
			array( $this, 'dashboard' ),
			'dashicons-awards',
			55
		);

		add_submenu_page( $slug, bhg_t( 'menu_dashboard', 'Dashboard' ), bhg_t( 'menu_dashboard', 'Dashboard' ), $cap, $slug, array( $this, 'dashboard' ) );
				add_submenu_page( $slug, bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ), bhg_t( 'label_bonus_hunts', 'Bonus Hunts' ), $cap, 'bhg-bonus-hunts', array( $this, 'bonus_hunts' ) );
				add_submenu_page( $slug, bhg_t( 'menu_prizes', 'Prizes' ), bhg_t( 'menu_prizes', 'Prizes' ), $cap, 'bhg-prizes', array( $this, 'prizes' ) );
				add_submenu_page( $slug, bhg_t( 'menu_jackpots', 'Jackpots' ), bhg_t( 'menu_jackpots', 'Jackpots' ), $cap, 'bhg-jackpots', array( $this, 'jackpots' ) );
				add_submenu_page( $slug, bhg_t( 'button_results', 'Results' ), bhg_t( 'button_results', 'Results' ), $cap, 'bhg-bonus-hunts-results', array( $this, 'bonus_hunts_results' ) );
		add_submenu_page( $slug, bhg_t( 'menu_tournaments', 'Tournaments' ), bhg_t( 'menu_tournaments', 'Tournaments' ), $cap, 'bhg-tournaments', array( $this, 'tournaments' ) );
		add_submenu_page( $slug, bhg_t( 'menu_users', 'Users' ), bhg_t( 'menu_users', 'Users' ), $cap, 'bhg-users', array( $this, 'users' ) );
		add_submenu_page( $slug, bhg_t( 'menu_affiliates', 'Affiliates' ), bhg_t( 'menu_affiliates', 'Affiliates' ), $cap, 'bhg-affiliates', array( $this, 'affiliates' ) );
		add_submenu_page( $slug, bhg_t( 'menu_advertising', 'Advertising' ), bhg_t( 'menu_advertising', 'Advertising' ), $cap, 'bhg-ads', array( $this, 'advertising' ) );
				add_submenu_page( $slug, bhg_t( 'menu_translations', 'Translations' ), bhg_t( 'menu_translations', 'Translations' ), $cap, 'bhg-translations', array( $this, 'translations' ) );
				add_submenu_page( $slug, bhg_t( 'menu_shortcodes', 'Shortcodes' ), bhg_t( 'menu_shortcodes', 'Shortcodes' ), $cap, 'bhg-shortcodes', array( $this, 'shortcodes' ) );
		add_submenu_page( $slug, bhg_t( 'database', 'Database' ), bhg_t( 'database', 'Database' ), $cap, 'bhg-database', array( $this, 'database' ) );
		add_submenu_page( $slug, bhg_t( 'settings', 'Settings' ), bhg_t( 'settings', 'Settings' ), $cap, 'bhg-settings', array( $this, 'settings' ) );
		add_submenu_page(
			$slug,
			bhg_t( 'bhg_tools', 'BHG Tools' ),
			bhg_t( 'bhg_tools', 'BHG Tools' ),
			$cap,
			'bhg-tools',
			array( $this, 'bhg_tools_page' )
		);

		if ( class_exists( 'BHG_Demo' ) ) {
			BHG_Demo::instance()->register_menu( $slug, $cap );
		}

				// NOTE: By default, WordPress adds a submenu item that duplicates the
				// top-level “Bonus Hunt” menu. The previous `remove_submenu_page()`
				// call removed this submenu, but it also inadvertently removed our
				// custom “Dashboard” submenu. Removing the call ensures the Dashboard
		// item remains visible under the "Bonus Hunt" menu.
	}

		/**
		 * Enqueue admin assets on BHG screens.
		 *
		 * @param string $hook Current admin page hook.
		 */
	public function assets( $hook ) {
		if ( false !== strpos( $hook, 'bhg' ) ) {
			wp_enqueue_style(
				'bhg-admin',
				BHG_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				defined( 'BHG_VERSION' ) ? BHG_VERSION : null
			);
						$script_path = BHG_PLUGIN_DIR . 'assets/js/admin.js';
			if ( file_exists( $script_path ) && filesize( $script_path ) > 0 ) {
							wp_enqueue_script(
								'bhg-admin',
								BHG_PLUGIN_URL . 'assets/js/admin.js',
								array( 'jquery' ),
								defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
								true
							);
			}

			if ( false !== strpos( $hook, 'bhg-prizes' ) ) {
					wp_enqueue_media();
					$prize_script = BHG_PLUGIN_DIR . 'assets/js/admin-prizes.js';
				if ( file_exists( $prize_script ) ) {
								wp_enqueue_script(
									'bhg-admin-prizes',
									BHG_PLUGIN_URL . 'assets/js/admin-prizes.js',
									array( 'jquery' ),
									defined( 'BHG_VERSION' ) ? BHG_VERSION : null,
									true
								);

								$css_defaults = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::default_css_settings() : array();

$display_defaults = class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_display_settings() : array();

wp_localize_script(
'bhg-admin-prizes',
'BHGPrizesL10n',
array(
'chooseImage'      => bhg_t( 'select_image', 'Select Image' ),
'noImage'          => bhg_t( 'no_image_selected', 'No image selected' ),
'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
'fetchNonce'       => wp_create_nonce( 'bhg_get_prize' ),
'cssDefaults'      => $css_defaults,
'displayDefaults'  => $display_defaults,
'clickActions'     => class_exists( 'BHG_Prizes' ) ? BHG_Prizes::get_click_actions() : array(),
'strings'          => array(
'saveLabel'    => bhg_t( 'add_prize', 'Add Prize' ),
'updateLabel'  => bhg_t( 'update_prize', 'Update Prize' ),
'modalAddTitle' => bhg_t( 'add_new_prize', 'Add New Prize' ),
'modalEditTitle' => bhg_t( 'edit_prize', 'Edit Prize' ),
'errorLoading' => bhg_t( 'prize_error_loading', 'Unable to load prize details.' ),
),
)
);
				}
			}

			if ( false !== strpos( $hook, 'bhg-bonus-hunts-results' ) ) {
							wp_enqueue_script(
								'bhg-admin-results',
								BHG_PLUGIN_URL . 'assets/js/admin-results.js',
								array(),
								BHG_VERSION,
								true
							);
							wp_localize_script(
								'bhg-admin-results',
								'bhgResults',
								array(
									'base_url' => admin_url( 'admin.php?page=bhg-bonus-hunts-results' ),
								)
							);
			}
		}
	}

	// -------------------- Views --------------------
	/**
	 * Render the dashboard page.
	 */
	public function dashboard() {
		require BHG_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render the bonus hunts page.
	 */
	public function bonus_hunts() {
			require BHG_PLUGIN_DIR . 'admin/views/bonus-hunts.php';
	}

		/**
		 * Render the prizes page.
		 */
	public function prizes() {
			require BHG_PLUGIN_DIR . 'admin/views/prizes.php';
	}

		/**
		 * Render the jackpots page.
		 */
	public function jackpots() {
			require BHG_PLUGIN_DIR . 'admin/views/jackpots.php';
	}

		/**
		 * Render the bonus hunts results page.
		 */
	public function bonus_hunts_results() {
			require BHG_PLUGIN_DIR . 'admin/views/bonus-hunts-results.php';
	}

	/**
	 * Render the tournaments page.
	 */
	public function tournaments() {
		require BHG_PLUGIN_DIR . 'admin/views/tournaments.php';
	}

	/**
	 * Render the users page.
	 */
	public function users() {
		require BHG_PLUGIN_DIR . 'admin/views/users.php';
	}

	/**
	 * Render the affiliates management page.
	 */
	public function affiliates() {
		$view = BHG_PLUGIN_DIR . 'admin/views/affiliate-websites.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_affiliates', 'Affiliates' ) ) . '</h1><p>' . esc_html( bhg_t( 'affiliate_management_ui_not_provided_yet', 'Affiliate management UI not provided yet.' ) ) . '</p></div>'; }
	}
		/**
		 * Render the advertising page.
		 */
	public function advertising() {
			require BHG_PLUGIN_DIR . 'admin/views/advertising.php';
	}

		/**
		 * Render the shortcodes reference page.
		 */
	public function shortcodes() {
			require BHG_PLUGIN_DIR . 'admin/views/shortcodes.php';
	}

		/**
		 * Render the translations page.
		 */
	public function translations() {
			$view = BHG_PLUGIN_DIR . 'admin/views/translations.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'menu_translations', 'Translations' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_translations_ui_found', 'No translations UI found.' ) ) . '</p></div>'; }
	}
		/**
		 * Render the database maintenance page.
		 */
	public function database() {
			require_once BHG_PLUGIN_DIR . 'includes/admin-database-tools.php';

			$view = BHG_PLUGIN_DIR . 'admin/views/database.php';
		if ( file_exists( $view ) ) {
				require $view;
		} else {
				echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'database', 'Database' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_database_ui_found', 'No database UI found.' ) ) . '</p></div>';
		}
	}
	/**
	 * Render the settings page.
	 */
	public function settings() {
		$view = BHG_PLUGIN_DIR . 'admin/views/settings.php';
		if ( file_exists( $view ) ) {
			require $view; } else {
			echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'settings', 'Settings' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_settings_ui_found', 'No settings UI found.' ) ) . '</p></div>'; }
	}
		/**
		 * Render the tools page.
		 */
	public function bhg_tools_page() {
			$view = BHG_PLUGIN_DIR . 'admin/views/tools.php';
		if ( file_exists( $view ) ) {
				require $view;
		} else {
					echo '<div class="wrap"><h1>' . esc_html( bhg_t( 'bhg_tools', 'BHG Tools' ) ) . '</h1><p>' . esc_html( bhg_t( 'no_tools_ui_found', 'No tools UI found.' ) ) . '</p></div>';
		}
	}

	// -------------------- Handlers --------------------

	/**
	 * Handle deletion of a guess from the admin screen.
	 */
	public function handle_delete_guess() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_delete_guess', 'bhg_delete_guess_nonce' );
				global $wpdb;

				$guesses_table     = $wpdb->prefix . 'bhg_guesses';
				$guesses_table_sql = esc_sql( $guesses_table );
		$guess_id                  = isset( $_POST['guess_id'] ) ? absint( wp_unslash( $_POST['guess_id'] ) ) : 0;
		if ( $guess_id ) {
				$wpdb->delete( $guesses_table, array( 'id' => $guess_id ), array( '%d' ) );
		}
				$referer = wp_get_referer();
								wp_safe_redirect( $referer ? $referer : BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
		exit;
	}

	/**
	 * Handle creation and updating of a bonus hunt.
	 */
	public function handle_save_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_save_hunt', 'bhg_save_hunt_nonce' );
				global $wpdb;

				$hunts_table     = $wpdb->prefix . 'bhg_bonus_hunts';
				$hunts_table_sql = esc_sql( $hunts_table );

		$id                   = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$title                = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
				$starting_raw = isset( $_POST['starting_balance'] ) ? sanitize_text_field( wp_unslash( $_POST['starting_balance'] ) ) : '';
				$starting     = 0.0;
				$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
				$redirect_url = BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' );
		}

		if ( '' !== trim( (string) $starting_raw ) ) {
				$starting_parsed = function_exists( 'bhg_parse_amount' ) ? bhg_parse_amount( $starting_raw ) : null;

			if ( null === $starting_parsed ) {
						wp_safe_redirect( add_query_arg( 'bhg_msg', 'invalid_starting_balance', $redirect_url ) );
						exit;
			}

				$starting = (float) $starting_parsed;
		}
		$num_bonuses                  = isset( $_POST['num_bonuses'] ) ? absint( wp_unslash( $_POST['num_bonuses'] ) ) : 0;
		$prizes                       = isset( $_POST['prizes'] ) ? wp_kses_post( wp_unslash( $_POST['prizes'] ) ) : '';
				$affiliate_site       = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;
				$tournament_ids_input = isset( $_POST['tournament_ids'] ) ? (array) wp_unslash( $_POST['tournament_ids'] ) : array();
				$tournament_ids       = bhg_sanitize_tournament_ids( $tournament_ids_input );
				$extract_prize_ids    = static function ( $field ) {
						$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : array();
						$ids = array();

					if ( is_array( $raw ) ) {
						foreach ( $raw as $maybe_id ) {
								$pid = absint( $maybe_id );
							if ( $pid > 0 ) {
								$ids[ $pid ] = $pid;
							}
						}
					} elseif ( '' !== $raw ) {
							$pid = absint( $raw );
						if ( $pid > 0 ) {
								$ids[ $pid ] = $pid;
						}
					}

						return array_values( $ids );
				};

				$prize_sets = array(
					'regular' => $extract_prize_ids( 'regular_prize_ids' ),
					'premium' => $extract_prize_ids( 'premium_prize_ids' ),
				);

				if ( empty( $prize_sets['regular'] ) && empty( $prize_sets['premium'] ) ) {
						$prize_sets['regular'] = $extract_prize_ids( 'prize_ids' );
				}
				if ( empty( $tournament_ids ) && isset( $_POST['tournament_id'] ) ) {
								$legacy = bhg_sanitize_tournament_id( sanitize_text_field( wp_unslash( $_POST['tournament_id'] ) ) );
					if ( $legacy > 0 ) {
							$tournament_ids = array( $legacy );
					}
				}
				$primary_tournament_id = ! empty( $tournament_ids ) ? (int) reset( $tournament_ids ) : 0;
				$winners_count         = isset( $_POST['winners_count'] ) ? max( 1, absint( wp_unslash( $_POST['winners_count'] ) ) ) : 3;
				$guessing_enabled      = isset( $_POST['guessing_enabled'] ) ? 1 : 0;
				$final_balance_raw     = isset( $_POST['final_balance'] ) ? sanitize_text_field( wp_unslash( $_POST['final_balance'] ) ) : '';
				$final_balance         = null;

				if ( '' !== trim( (string) $final_balance_raw ) ) {
						$final_parsed = function_exists( 'bhg_parse_amount' ) ? bhg_parse_amount( $final_balance_raw ) : null;

					if ( null === $final_parsed ) {
							wp_safe_redirect( add_query_arg( 'bhg_msg', 'invalid_final_balance', $redirect_url ) );
							exit;
					}

						$final_balance = (float) $final_parsed;
				}
				$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'open';
				if ( ! in_array( $status, array( 'open', 'closed' ), true ) ) {
					$status = 'open';
				}

				$data = array(
					'title'             => $title,
					'starting_balance'  => $starting,
					'num_bonuses'       => $num_bonuses,
					'prizes'            => $prizes,
					'affiliate_site_id' => $affiliate_site,
					'tournament_id'     => $primary_tournament_id,
					'winners_count'     => $winners_count,
					'guessing_enabled'  => $guessing_enabled,
				);

				$format = array( '%s', '%f', '%d', '%s', '%d', '%d', '%d', '%d' );

				if ( null !== $final_balance ) {
								$data['final_balance'] = $final_balance;
								// Use a float format to match the stored value.
								$format[] = '%f';
				}

				$data['status']                  = $status;
				$data['updated_at']              = current_time( 'mysql' );
				$format[]                        = '%s';
				$format[]                        = '%s';
								$previous_status = null;
				if ( $id ) {
					$existing_row = $wpdb->get_row(
						$wpdb->prepare(
							'SELECT status FROM ' . $hunts_table . ' WHERE id = %d',
							(int) $id
						)
					);

					if ( $existing_row && isset( $existing_row->status ) ) {
							$previous_status = (string) $existing_row->status;
					}

										$wpdb->update( $hunts_table, $data, array( 'id' => $id ), $format, array( '%d' ) );
				} else {
					$data['created_at'] = current_time( 'mysql' );
					$format[]           = '%s';
					$wpdb->insert( $hunts_table, $data, $format );
					$id = (int) $wpdb->insert_id;
				}

				if ( function_exists( 'bhg_set_hunt_tournaments' ) && $id > 0 ) {
						bhg_set_hunt_tournaments( $id, $tournament_ids );
				}

				if ( class_exists( 'BHG_Prizes' ) && $id > 0 ) {
						BHG_Prizes::set_hunt_prize_sets( $id, $prize_sets );
				}

								$should_close = (
										'closed' === $status
										&& null !== $final_balance
										&& ( null === $previous_status || 'closed' !== $previous_status )
								);

		if ( $should_close ) {
				$winners = BHG_Models::close_hunt( $id, $final_balance );

			$emails_enabled = (int) get_option( 'bhg_email_enabled', 1 );
			if ( $emails_enabled ) {
																														$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );

																														$rows = $wpdb->get_results(
																															$wpdb->prepare(
																																"SELECT DISTINCT user_id FROM {$guesses_table} WHERE hunt_id = %d",
																																$id
																															)
																														);

										$template = get_option(
											'bhg_email_template',
											'Hi {{username}},\nThe Bonus Hunt "{{hunt}}" is closed. Final balance: €{{final}}. Winners: {{winners}}. Thanks for playing!'
										);

																														$hunt_title = (string) $wpdb->get_var(
																															$wpdb->prepare(
																																"SELECT title FROM {$hunts_table} WHERE id = %d",
																																$id
																															)
																														);

										$winner_names = array();
				foreach ( (array) $winners as $winner_id ) {
					$wu = get_userdata( (int) $winner_id );
					if ( $wu ) {
										$winner_names[] = $wu->user_login;
					}
				}
								$winner_first = $winner_names ? $winner_names[0] : esc_html( bhg_t( 'label_emdash', '—' ) );
								$winner_list  = $winner_names ? implode( ', ', $winner_names ) : esc_html( bhg_t( 'label_emdash', '—' ) );

				foreach ( $rows as $r ) {
					$u = get_userdata( (int) $r->user_id );
					if ( ! $u ) {
						continue;
					}
					$username   = sanitize_text_field( $u->user_login );
					$hunt_title = sanitize_text_field( $hunt_title );

					$body = strtr(
						$template,
						array(
							'{{username}}' => esc_html( $username ),
							'{{hunt}}'     => esc_html( $hunt_title ),
							'{{final}}'    => number_format( $final_balance, 2 ),
							'{{winner}}'   => $winner_first,
							'{{winners}}'  => $winner_list,
						)
					);

								$headers = array( 'From: ' . BHG_Utils::get_email_from() );
								wp_mail(
									$u->user_email,
									sprintf(
									/* translators: %s: bonus hunt title. */
										bhg_t( 'results_for_s', 'Results for %s' ),
										$hunt_title ? $hunt_title : bhg_t( 'bonus_hunt', 'Bonus Hunt' )
									),
									$body,
									$headers
								);
				}
			}
		}

				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
				exit;
	}

	/**
	 * Close an active bonus hunt.
	 */
	public function handle_close_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
			check_admin_referer( 'bhg_close_hunt', 'bhg_close_hunt_nonce' );

				$hunt_id           = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
				$final_balance_raw = isset( $_POST['final_balance'] ) ? wp_unslash( $_POST['final_balance'] ) : '';

				$final_balance = function_exists( 'bhg_parse_amount' ) ? bhg_parse_amount( $final_balance_raw ) : null;
				$redirect_url  = BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' );

		if ( null === $final_balance || (float) $final_balance < 0 ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'invalid_final_balance', $redirect_url ) );
				exit;
		}

				$final_balance = (float) $final_balance;

		if ( $hunt_id ) {
										$result = BHG_Models::close_hunt( $hunt_id, $final_balance );
			if ( false === $result ) {
										wp_safe_redirect(
											add_query_arg(
												'bhg_msg',
												'close_failed',
												$redirect_url
											)
										);
										exit;
			}
		}

								$redirect_url = add_query_arg(
									'closed',
									1,
									BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' )
								);
								wp_safe_redirect( $redirect_url );
				exit;
	}

	/**
	 * Delete a bonus hunt and its guesses.
	 */
	public function handle_delete_hunt() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
		check_admin_referer( 'bhg_delete_hunt', 'bhg_delete_hunt_nonce' );

		global $wpdb;
		$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
		$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
		$winners_table = esc_sql( $wpdb->prefix . 'bhg_hunt_winners' );
		$results_table = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );
		$hunt_id       = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
		$winner_map    = array();

		if ( $hunt_id ) {
			$hunt_row       = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT tournament_id FROM ' . $hunts_table . ' WHERE id = %d',
					(int) $hunt_id
				)
			);
			$tournament_ids = function_exists( 'bhg_get_hunt_tournament_ids' ) ? bhg_get_hunt_tournament_ids( $hunt_id ) : array();
			if ( empty( $tournament_ids ) && $hunt_row && isset( $hunt_row->tournament_id ) ) {
				$legacy_id = (int) $hunt_row->tournament_id;
				if ( $legacy_id > 0 ) {
					$tournament_ids = array( $legacy_id );
				}
			}
			$tournament_ids = array_map( 'intval', array_unique( $tournament_ids ) );
			$winners        = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . $winners_table . ' WHERE hunt_id = %d',
					(int) $hunt_id
				)
			);

			if ( ! empty( $winners ) && ! empty( $tournament_ids ) ) {
				foreach ( $tournament_ids as $tournament_id ) {
					foreach ( $winners as $winner ) {
						$user_id = isset( $winner->user_id ) ? (int) $winner->user_id : 0;

						if ( $user_id <= 0 ) {
												continue;
						}

						if ( ! isset( $winner_map[ $tournament_id ] ) ) {
											$winner_map[ $tournament_id ] = array();
						}

								$winner_map[ $tournament_id ][] = $user_id;
					}
				}
			}

			$wpdb->delete( $hunts_table, array( 'id' => $hunt_id ), array( '%d' ) );
			$wpdb->delete( $guesses_table, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
			$wpdb->delete( $winners_table, array( 'hunt_id' => $hunt_id ), array( '%d' ) );
			$wpdb->delete( esc_sql( $wpdb->prefix . 'bhg_hunt_tournaments' ), array( 'hunt_id' => $hunt_id ), array( '%d' ) );

			if ( ! empty( $winner_map ) ) {
				foreach ( $winner_map as $tournament_id => $user_ids ) {
					foreach ( $user_ids as $user_id ) {
						$result_row = $wpdb->get_row(
							$wpdb->prepare(
								'SELECT id, wins FROM ' . $results_table . ' WHERE tournament_id = %d AND user_id = %d',
								(int) $tournament_id,
								(int) $user_id
							)
						);

						if ( ! $result_row ) {
							continue;
						}

						$new_wins = max( 0, (int) $result_row->wins - 1 );

						if ( $new_wins > 0 ) {
							$wpdb->update(
								$results_table,
								array( 'wins' => $new_wins ),
								array( 'id' => (int) $result_row->id ),
								array( '%d' ),
								array( '%d' )
							);
						} else {
							$wpdb->delete( $results_table, array( 'id' => (int) $result_row->id ), array( '%d' ) );
						}
					}
				}

				BHG_Models::recalculate_tournament_results( array_keys( $winner_map ) );
			}
		}

		wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts&bhg_msg=hunt_deleted' ) );
		exit;
	}

	/**
	 * Toggle guessing for a hunt.
	 */
	public function handle_toggle_guessing() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
		check_admin_referer( 'bhg_toggle_guessing', 'bhg_toggle_guessing_nonce' );

		global $wpdb;
				$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$hunt_id         = isset( $_POST['hunt_id'] ) ? absint( wp_unslash( $_POST['hunt_id'] ) ) : 0;
		$new_state           = isset( $_POST['guessing_enabled'] ) ? absint( wp_unslash( $_POST['guessing_enabled'] ) ) : 0;

		if ( $hunt_id ) {
			$wpdb->update(
				$hunts_table,
				array(
					'guessing_enabled' => $new_state,
					'updated_at'       => current_time( 'mysql' ),
				),
				array( 'id' => $hunt_id ),
				array( '%d', '%s' ),
				array( '%d' )
			);
		}

				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-bonus-hunts' ) );
		exit;
	}

		/**
		 * Handle deletion of advertising entries.
		 */
	public function handle_delete_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
			check_admin_referer( 'bhg_delete_ad', 'bhg_delete_ad_nonce' );
						global $wpdb;
						$ads_table = esc_sql( $wpdb->prefix . 'bhg_ads' );
			$ad_id                 = isset( $_POST['ad_id'] ) ? absint( wp_unslash( $_POST['ad_id'] ) ) : 0;
			$bulk_action           = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';
			$bulk_ad_ids           = isset( $_POST['ad_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ad_ids'] ) ) : array();

		if ( $ad_id ) {
						$wpdb->query(
							$wpdb->prepare(
								'DELETE FROM `' . $ads_table . '` WHERE id = %d',
								$ad_id
							)
						);
		} elseif ( 'delete' === $bulk_action && ! empty( $bulk_ad_ids ) ) {
						$placeholders = implode( ', ', array_fill( 0, count( $bulk_ad_ids ), '%d' ) );
						$wpdb->query(
							$wpdb->prepare(
								'DELETE FROM `' . $ads_table . '` WHERE id IN (' . $placeholders . ')',
								...$bulk_ad_ids
							)
						);
		}

			$referer = wp_get_referer();
						wp_safe_redirect( $referer ? $referer : BHG_Utils::admin_url( 'admin.php?page=bhg-ads' ) );
			exit;
	}

		/**
		 * Save or update an advertising entry.
		 */
	public function handle_save_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
						check_admin_referer( 'bhg_save_ad', 'bhg_save_ad_nonce' );
			global $wpdb;
			$table = $wpdb->prefix . 'bhg_ads';

			$id      = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
			$title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
			$link    = isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '';
			$place   = isset( $_POST['placement'] ) ? sanitize_text_field( wp_unslash( $_POST['placement'] ) ) : 'none';
			$visible = isset( $_POST['visible_to'] ) ? sanitize_text_field( wp_unslash( $_POST['visible_to'] ) ) : 'all';
			$targets = isset( $_POST['target_pages'] ) ? sanitize_text_field( wp_unslash( $_POST['target_pages'] ) ) : '';
			$active  = isset( $_POST['active'] ) ? 1 : 0;

			$data = array(
				'title'        => $title,
				'content'      => $content,
				'link_url'     => $link,
				'placement'    => $place,
				'visible_to'   => $visible,
				'target_pages' => $targets,
				'active'       => $active,
				'updated_at'   => current_time( 'mysql' ),
			);

			$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' );
			if ( $id ) {
				$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
			} else {
				$data['created_at'] = current_time( 'mysql' );
				$format[]           = '%s';
				$wpdb->insert( $table, $data, $format );
			}

							wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-ads' ) );
			exit;
	}

		/**
		 * Create or update a prize entry.
		 */
	public function handle_save_prize() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

		if ( ! check_admin_referer( 'bhg_save_prize', 'bhg_save_prize_nonce' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
				exit;
		}

			$redirect = BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' );

			$id          = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;
			$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
			$category    = isset( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : 'various';
			$images      = array(
				'image_small'  => isset( $_POST['image_small'] ) ? absint( wp_unslash( $_POST['image_small'] ) ) : 0,
				'image_medium' => isset( $_POST['image_medium'] ) ? absint( wp_unslash( $_POST['image_medium'] ) ) : 0,
				'image_large'  => isset( $_POST['image_large'] ) ? absint( wp_unslash( $_POST['image_large'] ) ) : 0,
			);

			$css_settings = array(
				'border'       => isset( $_POST['css_border'] ) ? wp_unslash( $_POST['css_border'] ) : '',
				'border_color' => isset( $_POST['css_border_color'] ) ? wp_unslash( $_POST['css_border_color'] ) : '',
				'padding'      => isset( $_POST['css_padding'] ) ? wp_unslash( $_POST['css_padding'] ) : '',
				'margin'       => isset( $_POST['css_margin'] ) ? wp_unslash( $_POST['css_margin'] ) : '',
				'background'   => isset( $_POST['css_background'] ) ? wp_unslash( $_POST['css_background'] ) : '',
			);

$link_url            = isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '';
$link_target         = isset( $_POST['link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['link_target'] ), '_self' ) : '_self';
$click_action        = isset( $_POST['click_action'] ) ? BHG_Prizes::sanitize_click_action( wp_unslash( $_POST['click_action'] ), 'link' ) : 'link';
$category_link_url   = isset( $_POST['category_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['category_link_url'] ) ) : '';
$category_link_target = isset( $_POST['category_link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['category_link_target'] ), '_self' ) : '_self';

$data = array(
'title'               => $title,
'description'         => $description,
'category'            => $category,
'link_url'            => $link_url,
'link_target'         => $link_target,
'click_action'        => $click_action,
'category_link_url'   => $category_link_url,
'category_link_target'=> $category_link_target,
'image_small'         => $images['image_small'],
'image_medium'        => $images['image_medium'],
'image_large'         => $images['image_large'],
'show_title'          => isset( $_POST['show_title'] ) ? 1 : 0,
'show_description'    => isset( $_POST['show_description'] ) ? 1 : 0,
'show_category'       => isset( $_POST['show_category'] ) ? 1 : 0,
'show_image'          => isset( $_POST['show_image'] ) ? 1 : 0,
'css_settings'        => $css_settings,
'active'              => isset( $_POST['active'] ) ? 1 : 0,
);

			$result = BHG_Prizes::save_prize( $data, $id );

			if ( false === $result ) {
					wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_error', $redirect ) );
					exit;
			}

			$msg = $id ? 'p_updated' : 'p_saved';

			if ( ! $id && $result ) {
					$redirect = add_query_arg(
						array(
							'action' => 'edit',
							'id'     => (int) $result,
						),
						$redirect
					);
			}

wp_safe_redirect( add_query_arg( 'bhg_msg', $msg, $redirect ) );
exit;
}

/**
 * Persist prize display settings submitted from admin UI.
 */
public function handle_save_prize_settings() {
if ( ! current_user_can( 'manage_options' ) ) {
wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
}

if ( ! check_admin_referer( 'bhg_prize_settings', 'bhg_prize_settings_nonce' ) ) {
wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
exit;
}

$settings = array(
'carousel_visible'  => isset( $_POST['carousel_visible'] ) ? absint( wp_unslash( $_POST['carousel_visible'] ) ) : 1,
'carousel_total'    => isset( $_POST['carousel_total'] ) ? absint( wp_unslash( $_POST['carousel_total'] ) ) : 0,
'carousel_autoplay' => isset( $_POST['carousel_autoplay'] ) ? 1 : 0,
'carousel_interval' => isset( $_POST['carousel_interval'] ) ? absint( wp_unslash( $_POST['carousel_interval'] ) ) : 5000,
'hide_heading'      => isset( $_POST['hide_heading'] ) ? 1 : 0,
'heading_text'      => isset( $_POST['heading_text'] ) ? sanitize_text_field( wp_unslash( $_POST['heading_text'] ) ) : '',
'show_title'        => isset( $_POST['show_title'] ) ? absint( wp_unslash( $_POST['show_title'] ) ) : 0,
'show_description'  => isset( $_POST['show_description'] ) ? absint( wp_unslash( $_POST['show_description'] ) ) : 0,
'show_category'     => isset( $_POST['show_category'] ) ? absint( wp_unslash( $_POST['show_category'] ) ) : 0,
'show_image'        => isset( $_POST['show_image'] ) ? absint( wp_unslash( $_POST['show_image'] ) ) : 0,
'category_links'    => isset( $_POST['category_links'] ) ? absint( wp_unslash( $_POST['category_links'] ) ) : 0,
'click_action'      => isset( $_POST['click_action'] ) ? sanitize_text_field( wp_unslash( $_POST['click_action'] ) ) : 'inherit',
'link_target'       => isset( $_POST['link_target'] ) ? sanitize_text_field( wp_unslash( $_POST['link_target'] ) ) : 'inherit',
'category_target'   => isset( $_POST['category_target'] ) ? sanitize_text_field( wp_unslash( $_POST['category_target'] ) ) : 'inherit',
);

BHG_Prizes::update_display_settings( $settings );

wp_safe_redirect( add_query_arg( 'bhg_msg', 'display_saved', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
exit;
}

		/**
		 * Delete a prize entry.
		 */
	public function handle_delete_prize() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

               if ( ! check_admin_referer( 'bhg_prize_delete', 'bhg_prize_delete_nonce' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
				exit;
		}

			$id = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;

		if ( $id ) {
				BHG_Prizes::delete_prize( $id );
		}

			wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
			exit;
	}

		/**
		 * Create or update a jackpot entry.
		 */
	public function handle_save_jackpot() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

		if ( ! check_admin_referer( 'bhg_save_jackpot', 'bhg_save_jackpot_nonce' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' ) ) );
				exit;
		}

			$redirect   = BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' );
			$jackpot_id = isset( $_POST['jackpot_id'] ) ? absint( wp_unslash( $_POST['jackpot_id'] ) ) : 0;
			$title      = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$link_mode  = isset( $_POST['link_mode'] ) ? sanitize_key( wp_unslash( $_POST['link_mode'] ) ) : 'all';
			$status     = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active';

			$start_amount    = isset( $_POST['start_amount'] ) ? wp_unslash( $_POST['start_amount'] ) : '';
			$increase_amount = isset( $_POST['increase_amount'] ) ? wp_unslash( $_POST['increase_amount'] ) : '';

		if ( function_exists( 'bhg_parse_amount' ) ) {
				$start_amount    = bhg_parse_amount( $start_amount );
				$increase_amount = bhg_parse_amount( $increase_amount );
		} else {
				$start_amount    = (float) $start_amount;
				$increase_amount = (float) $increase_amount;
		}

			$linked_hunts = isset( $_POST['linked_hunts'] ) ? wp_unslash( $_POST['linked_hunts'] ) : array();
			$linked_hunts = is_array( $linked_hunts ) ? array_map( 'absint', $linked_hunts ) : array();

			$linked_affiliates = isset( $_POST['linked_affiliates'] ) ? wp_unslash( $_POST['linked_affiliates'] ) : array();
			$linked_affiliates = is_array( $linked_affiliates ) ? array_map( 'absint', $linked_affiliates ) : array();

			$linked_period = isset( $_POST['linked_period'] ) ? sanitize_key( wp_unslash( $_POST['linked_period'] ) ) : '';

			$data = array(
				'id'                => $jackpot_id,
				'title'             => $title,
				'start_amount'      => $start_amount,
				'increase_amount'   => $increase_amount,
				'link_mode'         => $link_mode,
				'status'            => $status,
				'linked_hunts'      => $linked_hunts,
				'linked_affiliates' => $linked_affiliates,
				'linked_period'     => $linked_period,
			);

			$saved = BHG_Jackpots::instance()->save_jackpot( $data );

			if ( ! $saved ) {
					wp_safe_redirect( add_query_arg( 'bhg_msg', 'jackpot_error', $redirect ) );
					exit;
			}

			$args = array( 'bhg_msg' => $jackpot_id ? 'jackpot_updated' : 'jackpot_created' );

			if ( ! $jackpot_id && $saved ) {
					$args['action']     = 'edit';
					$args['jackpot_id'] = (int) $saved;
			} elseif ( $jackpot_id ) {
					$args['jackpot_id'] = (int) $jackpot_id;
			}

			wp_safe_redirect( add_query_arg( $args, $redirect ) );
			exit;
	}

		/**
		 * Delete a jackpot entry.
		 */
	public function handle_delete_jackpot() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

		if ( ! check_admin_referer( 'bhg_delete_jackpot', 'bhg_delete_jackpot_nonce' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' ) ) );
				exit;
		}

			$jackpot_id = isset( $_POST['jackpot_id'] ) ? absint( wp_unslash( $_POST['jackpot_id'] ) ) : 0;

		if ( $jackpot_id ) {
				BHG_Jackpots::instance()->delete_jackpot( $jackpot_id );
		}

			wp_safe_redirect( add_query_arg( 'bhg_msg', 'jackpot_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' ) ) );
			exit;
	}

		/**
		 * Reset a jackpot to its starting amount.
		 */
	public function handle_reset_jackpot() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

		if ( ! check_admin_referer( 'bhg_reset_jackpot', 'bhg_reset_jackpot_nonce' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' ) ) );
				exit;
		}

			$jackpot_id = isset( $_POST['jackpot_id'] ) ? absint( wp_unslash( $_POST['jackpot_id'] ) ) : 0;

		if ( $jackpot_id ) {
				BHG_Jackpots::instance()->reset_jackpot( $jackpot_id );
		}

			wp_safe_redirect(
				add_query_arg(
					array(
						'bhg_msg'    => 'jackpot_reset',
						'jackpot_id' => $jackpot_id,
					),
					BHG_Utils::admin_url( 'admin.php?page=bhg-jackpots' )
				)
			);
			exit;
	}

		/**
		 * Save a tournament record.
		 */
	public function handle_save_tournament() {
		if ( ! current_user_can( 'manage_options' ) ) {
				wp_safe_redirect( add_query_arg( 'bhg_msg', 'noaccess', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
			exit;
		}
		if ( ! check_admin_referer( 'bhg_tournament_save_action', 'bhg_tournament_save_nonce' ) ) {
					wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
			exit;
		}
			global $wpdb;
			$t              = $wpdb->prefix . 'bhg_tournaments';
			$id             = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
			$hunt_ids_input = isset( $_POST['hunt_ids'] ) ? wp_unslash( $_POST['hunt_ids'] ) : array();
			$hunt_ids       = array();
		if ( is_array( $hunt_ids_input ) ) {
			foreach ( $hunt_ids_input as $hunt_id ) {
					$hunt_id = absint( $hunt_id );
				if ( $hunt_id > 0 ) {
					$hunt_ids[ $hunt_id ] = $hunt_id;
				}
			}
		}
			$hunt_ids          = array_values( $hunt_ids );
			$participants_mode = isset( $_POST['participants_mode'] ) ? sanitize_key( wp_unslash( $_POST['participants_mode'] ) ) : 'winners';
		if ( ! in_array( $participants_mode, array( 'winners', 'all' ), true ) ) {
			$participants_mode = 'winners';
		}

			$hunt_link_mode = isset( $_POST['hunt_link_mode'] ) ? sanitize_key( wp_unslash( $_POST['hunt_link_mode'] ) ) : 'manual';
		if ( ! in_array( $hunt_link_mode, array( 'manual', 'auto' ), true ) ) {
			$hunt_link_mode = 'manual';
		}

			$points_map_input = isset( $_POST['points_map'] ) ? wp_unslash( $_POST['points_map'] ) : array();
		if ( function_exists( 'bhg_sanitize_points_map' ) ) {
				$points_map = bhg_sanitize_points_map( $points_map_input );
		} else {
				$points_map = array();
			if ( is_array( $points_map_input ) ) {
				foreach ( $points_map_input as $placement => $value ) {
					$placement                = absint( $placement );
					$points_map[ $placement ] = max( 0, (int) $value );
				}
			}
		}
		if ( empty( $points_map ) && function_exists( 'bhg_get_default_points_map' ) ) {
				$points_map = bhg_get_default_points_map();
		}
			$points_map_json = wp_json_encode( $points_map );
		if ( false === $points_map_json ) {
				$points_map_json = wp_json_encode( array() );
		}

			$ranking_scope  = isset( $_POST['ranking_scope'] ) ? sanitize_key( wp_unslash( $_POST['ranking_scope'] ) ) : 'all';
			$allowed_scopes = array( 'all', 'active', 'closed' );
		if ( ! in_array( $ranking_scope, $allowed_scopes, true ) ) {
				$ranking_scope = 'all';
		}

			$extract_prize_ids = static function ( $field ) {
					$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : array();
					$ids = array();

				if ( is_array( $raw ) ) {
					foreach ( $raw as $maybe_id ) {
						$maybe_id = absint( $maybe_id );
						if ( $maybe_id > 0 ) {
								$ids[ $maybe_id ] = $maybe_id;
						}
					}
				} elseif ( '' !== $raw ) {
						$maybe_id = absint( $raw );
					if ( $maybe_id > 0 ) {
							$ids[ $maybe_id ] = $maybe_id;
					}
				}

					return array_values( $ids );
			};

			$prize_sets = array(
				'regular' => $extract_prize_ids( 'regular_prize_ids' ),
				'premium' => $extract_prize_ids( 'premium_prize_ids' ),
			);

			if ( empty( $prize_sets['regular'] ) && empty( $prize_sets['premium'] ) ) {
					$prize_sets['regular'] = $extract_prize_ids( 'prize_ids' );
			}

			$prizes_json = wp_json_encode( $prize_sets['regular'] );
			if ( false === $prizes_json ) {
					$prizes_json = wp_json_encode( array() );
			}

			$affiliate_site_id = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;
			$affiliate_site_id = $affiliate_site_id > 0 ? $affiliate_site_id : 0;

			$affiliate_website = '';
			if ( isset( $_POST['affiliate_website'] ) ) {
					$affiliate_website = trim( (string) wp_unslash( $_POST['affiliate_website'] ) );
					$affiliate_website = $affiliate_website ? esc_url_raw( $affiliate_website ) : '';
				if ( strlen( $affiliate_website ) > 255 ) {
						$affiliate_website = substr( $affiliate_website, 0, 255 );
				}
			}

			$affiliate_url_visible = isset( $_POST['affiliate_url_visible'] ) ? 1 : 0;

			$allowed_types  = array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' );
			$raw_start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
			$raw_end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

			$start_date = '' !== $raw_start_date ? $raw_start_date : null;
			$end_date   = '' !== $raw_end_date ? $raw_end_date : null;

			$data                     = array(
				'title'                 => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
				'description'           => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
				'participants_mode'     => $participants_mode,
				'hunt_link_mode'        => $hunt_link_mode,
				'points_map'            => $points_map_json,
				'ranking_scope'         => $ranking_scope,
				'prizes'                => $prizes_json,
				'affiliate_site_id'     => $affiliate_site_id,
				'affiliate_website'     => $affiliate_website,
				'affiliate_url_visible' => $affiliate_url_visible,
				'start_date'            => $start_date,
				'end_date'              => $end_date,
				'status'                => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active',
				'updated_at'            => current_time( 'mysql' ),
			);
					$allowed_statuses = array( 'active', 'archived' );
			if ( ! in_array( $data['status'], $allowed_statuses, true ) ) {
				$data['status'] = 'active';
			}
					$existing_type = '';
			if ( $id > 0 ) {
							// db call ok; table name from prefix.
							$existing_row = $wpdb->get_row( $wpdb->prepare( "SELECT type FROM {$t} WHERE id = %d", $id ) );
				if ( $existing_row && isset( $existing_row->type ) ) {
					$existing_type = sanitize_key( (string) $existing_row->type );
				}
			}

					$posted_type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';

			if ( in_array( $posted_type, $allowed_types, true ) ) {
					$resolved_type = $posted_type;
			} elseif ( in_array( $existing_type, $allowed_types, true ) ) {
					$resolved_type = $existing_type;
			} else {
					$resolved_type = $this->infer_tournament_type( $start_date, $end_date );
			}

					$data['type'] = $resolved_type;

			if ( 'auto' === $hunt_link_mode ) {
				$hunt_ids = $this->get_hunt_ids_within_range( $start_date, $end_date );
			}

			try {
				$format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );
				if ( $id > 0 ) {
						$wpdb->update( $t, $data, array( 'id' => $id ), $format, array( '%d' ) );
						$saved_id = $id;
				} else {
					$data['created_at'] = current_time( 'mysql' );
					$format[]           = '%s';
					$wpdb->insert( $t, $data, $format );
					$saved_id = (int) $wpdb->insert_id;
				}

				if ( function_exists( 'bhg_set_tournament_hunts' ) && $saved_id > 0 ) {
					bhg_set_tournament_hunts( $saved_id, $hunt_ids );
				}

						wp_safe_redirect( add_query_arg( 'bhg_msg', 't_saved', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
						exit;
			} catch ( Throwable $e ) {
				if ( function_exists( 'error_log' ) ) {
						error_log( '[BHG] tournament save error: ' . $e->getMessage() );
				}
					wp_safe_redirect( add_query_arg( 'bhg_msg', 't_error', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
						exit;
			}
	}

		/**
		 * Delete a tournament.
		 */
	public function handle_delete_tournament() {
		if ( ! current_user_can( 'manage_options' ) ) {
												wp_safe_redirect( add_query_arg( 'bhg_msg', 'noaccess', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
						exit;
		}
		if ( ! isset( $_POST['bhg_tournament_delete_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['bhg_tournament_delete_nonce'] ), 'bhg_tournament_delete_action' ) ) {
												wp_safe_redirect( add_query_arg( 'bhg_msg', 'nonce', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
						exit;
		}
					global $wpdb;
					$table = $wpdb->prefix . 'bhg_tournaments';
					$id    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( $id ) {
			$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
			$wpdb->delete( esc_sql( $wpdb->prefix . 'bhg_hunt_tournaments' ), array( 'tournament_id' => $id ), array( '%d' ) );
			wp_safe_redirect( add_query_arg( 'bhg_msg', 't_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
			exit;
		}
											wp_safe_redirect( add_query_arg( 'bhg_msg', 't_error', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
					exit;
	}

		/**
		 * Close a tournament by setting its status to closed.
		 */
	public function handle_close_tournament() {
		if ( ! current_user_can( 'manage_options' ) ) {
						wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}

			check_admin_referer( 'bhg_tournament_close', 'bhg_tournament_close_nonce' );

			$id = isset( $_POST['tournament_id'] ) ? absint( wp_unslash( $_POST['tournament_id'] ) ) : 0;

		if ( $id ) {
				global $wpdb;
				$table = $wpdb->prefix . 'bhg_tournaments';
				$wpdb->update(
					$table,
					array(
						'status'     => 'closed',
						'updated_at' => current_time( 'mysql' ),
					),
					array( 'id' => $id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
		}

					wp_safe_redirect( add_query_arg( 'bhg_msg', 't_closed', BHG_Utils::admin_url( 'admin.php?page=bhg-tournaments' ) ) );
					exit;
	}

				/**
				 * Infer a tournament type based on provided dates or plugin defaults.
				 *
				 * @param string|null $start_date Start date in Y-m-d format or null.
				 * @param string|null $end_date   End date in Y-m-d format or null.
				 * @return string Resolved tournament type slug.
				 */
	private function infer_tournament_type( $start_date, $end_date ) {
					$allowed  = array( 'weekly', 'monthly', 'quarterly', 'yearly', 'alltime' );
					$settings = get_option( 'bhg_plugin_settings', array() );
					$default  = isset( $settings['default_tournament_period'] ) ? sanitize_key( $settings['default_tournament_period'] ) : 'monthly';
		if ( ! in_array( $default, $allowed, true ) ) {
			$default = 'monthly';
		}

					$start_date = is_string( $start_date ) ? trim( $start_date ) : ( is_null( $start_date ) ? '' : trim( (string) $start_date ) );
					$end_date   = is_string( $end_date ) ? trim( $end_date ) : ( is_null( $end_date ) ? '' : trim( (string) $end_date ) );

		if ( '' === $start_date || '' === $end_date ) {
			return $default;
		}

		try {
				$start = new DateTimeImmutable( $start_date );
				$end   = new DateTimeImmutable( $end_date );
		} catch ( Exception $e ) {
				return $default;
		}

		if ( $end < $start ) {
				$tmp   = $start;
				$start = $end;
				$end   = $tmp;
		}

					$days = (int) $end->diff( $start )->format( '%a' ) + 1;

		if ( $days <= 10 ) {
				return 'weekly';
		}

		if ( $days <= 45 ) {
				return 'monthly';
		}

		if ( $days <= 120 ) {
				return 'quarterly';
		}

		if ( $days <= 400 ) {
				return 'yearly';
		}

					return 'alltime';
	}

		/**
		 * Retrieve hunt IDs that fall within the provided tournament date range.
		 *
		 * Uses the hunt's closed, updated, or created timestamp (in that order) for comparisons.
		 *
		 * @param string|null $start_date Tournament start date (Y-m-d) or null.
		 * @param string|null $end_date   Tournament end date (Y-m-d) or null.
		 * @return array<int> Normalized hunt IDs.
		 */
	private function get_hunt_ids_within_range( $start_date, $end_date ) {
			global $wpdb;

			$table       = $wpdb->prefix . 'bhg_bonus_hunts';
			$date_column = 'COALESCE(closed_at, updated_at, created_at)';
			$where       = array();
			$params      = array();

		if ( is_string( $start_date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
				$where[]  = $date_column . ' >= %s';
				$params[] = $start_date . ' 00:00:00';
		}

		if ( is_string( $end_date ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
				$where[]  = $date_column . ' <= %s';
				$params[] = $end_date . ' 23:59:59';
		}

			$sql = "SELECT id FROM `{$table}`";
		if ( $where ) {
				$sql .= ' WHERE ' . implode( ' AND ', $where );
		}
			$sql .= " ORDER BY {$date_column} ASC, id ASC";

			$query = $sql;
		if ( ! empty( $params ) ) {
				$query = $wpdb->prepare( $sql, $params );
		}

			$ids = $wpdb->get_col( $query );

		if ( function_exists( 'bhg_normalize_int_list' ) ) {
				return bhg_normalize_int_list( $ids );
		}

			return array_map( 'absint', (array) $ids );
	}

				/**
				 * Save or update an affiliate website record.
				 */
	public function handle_save_affiliate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
			check_admin_referer( 'bhg_save_affiliate', 'bhg_save_affiliate_nonce' );
			global $wpdb;
			$table  = $wpdb->prefix . 'bhg_affiliate_websites';
			$id     = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
			$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			$slug   = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : sanitize_title( $name );
			$url    = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
			$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active';
		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

			$data   = array(
				'name'       => $name,
				'slug'       => $slug,
				'url'        => $url,
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			);
			$format = array( '%s', '%s', '%s', '%s', '%s' );
			if ( $id ) {
				$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
			} else {
				$data['created_at'] = current_time( 'mysql' );
				$format[]           = '%s';
				$wpdb->insert( $table, $data, $format );
			}
					wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-affiliates' ) );
			exit;
	}

		/**
		 * Delete an affiliate website.
		 */
	public function handle_delete_affiliate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_delete_affiliate', 'bhg_delete_affiliate_nonce' );
				global $wpdb;
				$table = $wpdb->prefix . 'bhg_affiliate_websites';
		$id            = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( $id ) {
				$wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
			if ( function_exists( 'bhg_remove_affiliate_site_from_users' ) ) {
						bhg_remove_affiliate_site_from_users( $id );
			}
		}
				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-affiliates' ) );
		exit;
	}

	/**
	 * Save custom user metadata from the admin screen.
	 */
	public function handle_save_user_meta() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( bhg_t( 'no_permission', 'No permission' ) ) );
		}
								check_admin_referer( 'bhg_save_user_meta', 'bhg_save_user_meta_nonce' );
		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
		if ( $user_id ) {
			$real_name    = isset( $_POST['bhg_real_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bhg_real_name'] ) ) : '';
			$is_affiliate = isset( $_POST['bhg_is_affiliate'] ) ? 1 : 0;
			update_user_meta( $user_id, 'bhg_real_name', $real_name );
			update_user_meta( $user_id, 'bhg_is_affiliate', $is_affiliate );
		}
				wp_safe_redirect( BHG_Utils::admin_url( 'admin.php?page=bhg-users' ) );
		exit;
	}

		/**
		 * Display admin notices for tournament actions.
		 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET['bhg_msg'] ) ) {
			return;
		}
		$msg   = sanitize_text_field( wp_unslash( $_GET['bhg_msg'] ) );
		$map   = array(
			't_saved'                  => bhg_t( 'tournament_saved', 'Tournament saved.' ),
			't_error'                  => bhg_t( 'could_not_save_tournament_check_logs', 'Could not save tournament. Check logs.' ),
			't_deleted'                => bhg_t( 'tournament_deleted', 'Tournament deleted.' ),
			't_closed'                 => bhg_t( 'tournament_closed', 'Tournament closed.' ),
			'invalid_starting_balance' => bhg_t(
				'invalid_starting_balance',
				'Starting balance could not be parsed. Please enter a numeric amount.'
			),
			'invalid_final_balance'    => bhg_t(
				'invalid_final_balance',
				'Final balance could not be parsed. Please enter a numeric amount.'
			),
			'nonce'                    => bhg_t( 'security_check_failed_please_retry', 'Security check failed. Please retry.' ),
			'noaccess'                 => bhg_t( 'you_do_not_have_permission_to_do_that', 'You do not have permission to do that.' ),
			'tools_success'            => bhg_t( 'tools_action_completed', 'Tools action completed.' ),
			'demo_reset_ok'            => bhg_t( 'demo_data_reset_complete', 'Demo data was reset and reseeded.' ),
			'demo_reset_error'         => bhg_t( 'demo_data_reset_failed', 'Demo data reset failed.' ),
			'p_saved'                  => bhg_t( 'prize_saved', 'Prize saved.' ),
			'p_updated'                => bhg_t( 'prize_updated', 'Prize updated.' ),
			'p_deleted'                => bhg_t( 'prize_deleted', 'Prize deleted.' ),
			'p_error'                  => bhg_t( 'prize_error', 'Unable to save prize.' ),
		);
		$class = ( strpos( $msg, 'error' ) !== false || 'nonce' === $msg || 'noaccess' === $msg ) ? 'notice notice-error' : 'notice notice-success';
		$text  = isset( $map[ $msg ] ) ? $map[ $msg ] : esc_html( $msg );
		echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $text ) . '</p></div>';
	}
}
