<?php
/**
 * Admin controller for bonus hunt forms.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BHG_Bonus_Hunts_Controller' ) ) {
	/**
	 * Handles create, update and delete actions for bonus hunts.
	 */
	class BHG_Bonus_Hunts_Controller {
		/**
		 * Singleton instance.
		 *
		 * @var BHG_Bonus_Hunts_Controller|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return BHG_Bonus_Hunts_Controller
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {}

		/**
		 * Initialize hooks.
		 *
		 * @return void
		 */
		public function init() {
				add_action( 'admin_post_bhg_create_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_update_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_delete_bonus_hunt', array( $this, 'handle_form_submissions' ) );
				add_action( 'admin_post_bhg_delete_guess', array( $this, 'delete_guess' ) );
		}

		/**
		 * Retrieve data for bonus hunt admin views.
		 *
		 * @return array
		 */
		public function get_admin_view_vars() {
			$db = new BHG_DB();

			return array(
				'bonus_hunts'        => $db->get_all_bonus_hunts(),
				'affiliate_websites' => $db->get_affiliate_websites(),
			);
		}

		/**
		 * Retrieve the latest hunts with winner information for dashboard displays.
		 *
		 * @param int $limit Number of hunts to fetch.
		 * @return array
		 */
		public function get_latest_hunts( $limit = 3 ) {
			$limit = max( 1, (int) $limit );

			if ( ! class_exists( 'BHG_Bonus_Hunts' ) ) {
				$file = BHG_PLUGIN_DIR . 'includes/class-bhg-bonus-hunts.php';
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}

			if ( class_exists( 'BHG_Bonus_Hunts' ) && method_exists( 'BHG_Bonus_Hunts', 'get_latest_hunts_with_winners' ) ) {
				$hunts = BHG_Bonus_Hunts::get_latest_hunts_with_winners( $limit );
				if ( is_array( $hunts ) ) {
					return $hunts;
				}
			}

			$results = array();

			if ( function_exists( 'bhg_get_latest_closed_hunts' ) ) {
				$legacy_hunts = bhg_get_latest_closed_hunts( $limit );

				foreach ( (array) $legacy_hunts as $hunt ) {
					$hunt_id       = isset( $hunt->id ) ? (int) $hunt->id : 0;
					$winners_limit = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : 0;
					$winners_limit = $winners_limit > 0 ? $winners_limit : 25;
					$winners       = array();

					if ( $hunt_id && function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
						$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_limit );
					}

					$results[] = array(
						'hunt'    => $hunt,
						'winners' => $winners,
					);
				}
			}

			return $results;
		}

		/**
		 * Handle bonus hunt form submissions.
		 *
		 * @return void
		 */
		public function handle_form_submissions() {
			if ( empty( $_POST['action'] ) ) {
						return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
									wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
			}

							$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );

							check_admin_referer( $action, 'bhg_nonce' );

							$db      = new BHG_DB();
							$message = 'error';

			switch ( $action ) {
				case 'bhg_create_bonus_hunt':
						$title                         = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
						$starting_balance              = floatval( wp_unslash( $_POST['starting_balance'] ?? 0 ) );
						$num_bonuses                   = absint( wp_unslash( $_POST['num_bonuses'] ?? 0 ) );
									$prizes            = sanitize_textarea_field( wp_unslash( $_POST['prizes'] ?? '' ) );
									$status            = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
									$affiliate_site_id = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;

									$result = $db->create_bonus_hunt(
										array(
											'title'       => $title,
											'starting_balance' => $starting_balance,
											'num_bonuses' => $num_bonuses,
											'prizes'      => $prizes,
											'status'      => $status,
											'affiliate_site_id' => $affiliate_site_id,
											'created_by'  => get_current_user_id(),
											'created_at'  => current_time( 'mysql' ),
										)
									);

										$message = $result ? 'success' : 'error';
					break;

				case 'bhg_update_bonus_hunt':
						$id               = absint( wp_unslash( $_POST['id'] ?? 0 ) );
						$title            = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
						$starting_balance = floatval( wp_unslash( $_POST['starting_balance'] ?? 0 ) );
					$num_bonuses          = absint( wp_unslash( $_POST['num_bonuses'] ?? 0 ) );
					$prizes               = sanitize_textarea_field( wp_unslash( $_POST['prizes'] ?? '' ) );
					$status               = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
					$final_balance        = isset( $_POST['final_balance'] ) ? floatval( wp_unslash( $_POST['final_balance'] ) ) : null;
					$affiliate_site_id    = isset( $_POST['affiliate_site_id'] ) ? absint( wp_unslash( $_POST['affiliate_site_id'] ) ) : 0;

					$result = $db->update_bonus_hunt(
						$id,
						array(
							'title'             => $title,
							'starting_balance'  => $starting_balance,
							'num_bonuses'       => $num_bonuses,
							'prizes'            => $prizes,
							'status'            => $status,
							'final_balance'     => $final_balance,
							'affiliate_site_id' => $affiliate_site_id,
						)
					);

					if ( $result && 'closed' === $status && null !== $final_balance ) {
						if ( class_exists( 'BHG_Models' ) ) {
							$winner_ids = BHG_Models::close_hunt( $id, $final_balance );
							if ( function_exists( 'bhg_send_hunt_results_email' ) ) {
								bhg_send_hunt_results_email( $id, $winner_ids );
							}
						}
					}

						$message = $result ? 'updated' : 'error';
					break;

				case 'bhg_delete_bonus_hunt':
						$id      = absint( wp_unslash( $_POST['id'] ?? 0 ) );
						$result  = $db->delete_bonus_hunt( $id );
						$message = $result ? 'deleted' : 'error';
					break;
			}

							$url = esc_url_raw( add_query_arg( 'message', $message, wp_get_referer() ) );
							wp_safe_redirect( $url );
							exit;
		}

				/**
				 * Delete a guess submitted for a hunt.
				 *
				 * @return void
				 */
		public function delete_guess() {
			if ( ! current_user_can( 'manage_options' ) ) {
						wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
			}

																check_admin_referer( 'bhg_delete_guess', 'bhg_delete_guess_nonce' );

				$guess_id = isset( $_GET['guess_id'] ) ? absint( wp_unslash( $_GET['guess_id'] ) ) : 0;

				global $wpdb;
				$table   = $wpdb->prefix . 'bhg_guesses';
				$deleted = false;

			if ( $guess_id > 0 ) {
				$deleted = (bool) $wpdb->delete( $table, array( 'id' => $guess_id ), array( '%d' ) );
			}

							$message = $deleted ? 'guess_deleted' : 'error';
							$url     = esc_url_raw( add_query_arg( 'message', $message, wp_get_referer() ) );

							wp_safe_redirect( $url );
							exit;
		}
	}
}

if ( ! class_exists( 'BHG_Prizes_Controller' ) ) {
		/**
		 * Handles admin interactions for managing prizes.
		 */
	class BHG_Prizes_Controller {
			/**
			 * Singleton instance.
			 *
			 * @var BHG_Prizes_Controller|null
			 */
		private static $instance = null;

			/**
			 * Retrieve singleton instance.
			 *
			 * @return BHG_Prizes_Controller
			 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

				return self::$instance;
		}

			/**
			 * Constructor.
			 */
		private function __construct() {}

			/**
			 * Register hooks for prize CRUD.
			 *
			 * @return void
			 */
		public function init() {
				add_action( 'admin_post_bhg_prize_save', array( $this, 'save_prize' ) );
				add_action( 'admin_post_bhg_prize_delete', array( $this, 'delete_prize' ) );
				add_action( 'wp_ajax_bhg_get_prize', array( $this, 'ajax_get_prize' ) );
		}

			/**
			 * Determine if the current user can manage prizes.
			 *
			 * @return bool
			 */
		protected function current_user_can_manage() {
				$capability = apply_filters( 'bhg_manage_prizes_capability', 'manage_options' );

				return current_user_can( $capability );
		}

			/**
			 * Ensure the current user has permission to manage prizes.
			 *
			 * @return void
			 */
		protected function ensure_permission() {
			if ( $this->current_user_can_manage() ) {
					return;
			}

				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonus-hunt-guesser' ) );
		}

			/**
			 * Handle create/update submissions.
			 *
			 * @return void
			 */
		public function save_prize() {
				$this->ensure_permission();

				check_admin_referer( 'bhg_prize_save', 'bhg_prize_nonce' );

				$redirect = BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' );

				$id       = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;
				$category = isset( $_POST['category'] ) ? sanitize_key( wp_unslash( $_POST['category'] ) ) : 'various';

$data = array(
'title'        => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
'description'  => isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '',
'category'     => $category,
'link_url'     => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
'link_target'  => isset( $_POST['link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['link_target'] ), '_self' ) : '_self',
'click_action' => isset( $_POST['click_action'] ) ? BHG_Prizes::sanitize_click_action( wp_unslash( $_POST['click_action'] ), 'link' ) : 'link',
'category_link_url' => isset( $_POST['category_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['category_link_url'] ) ) : '',
'category_link_target' => isset( $_POST['category_link_target'] ) ? BHG_Prizes::sanitize_link_target( wp_unslash( $_POST['category_link_target'] ), '_self' ) : '_self',
'image_small'  => isset( $_POST['image_small'] ) ? absint( wp_unslash( $_POST['image_small'] ) ) : 0,
'image_medium' => isset( $_POST['image_medium'] ) ? absint( wp_unslash( $_POST['image_medium'] ) ) : 0,
'image_large'  => isset( $_POST['image_large'] ) ? absint( wp_unslash( $_POST['image_large'] ) ) : 0,
'show_title'   => isset( $_POST['show_title'] ) ? 1 : 0,
'show_description' => isset( $_POST['show_description'] ) ? 1 : 0,
'show_category' => isset( $_POST['show_category'] ) ? 1 : 0,
'show_image'   => isset( $_POST['show_image'] ) ? 1 : 0,
'css_settings' => array(
'border'       => isset( $_POST['css_border'] ) ? wp_unslash( $_POST['css_border'] ) : '',
'border_color' => isset( $_POST['css_border_color'] ) ? wp_unslash( $_POST['css_border_color'] ) : '',
'padding'      => isset( $_POST['css_padding'] ) ? wp_unslash( $_POST['css_padding'] ) : '',
'margin'       => isset( $_POST['css_margin'] ) ? wp_unslash( $_POST['css_margin'] ) : '',
'background'   => isset( $_POST['css_background'] ) ? wp_unslash( $_POST['css_background'] ) : '',
),
'active'       => isset( $_POST['active'] ) ? 1 : 0,
);

				$result = BHG_Prizes::save_prize( $data, $id );

				if ( false === $result ) {
						wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_error', $redirect ) );
						exit;
				}

				$message = $id ? 'p_updated' : 'p_saved';

				wp_safe_redirect( add_query_arg( 'bhg_msg', $message, $redirect ) );
				exit;
		}

			/**
			 * Handle prize deletions.
			 *
			 * @return void
			 */
		public function delete_prize() {
				$this->ensure_permission();

				check_admin_referer( 'bhg_prize_delete', 'bhg_prize_delete_nonce' );

				$id = isset( $_POST['prize_id'] ) ? absint( wp_unslash( $_POST['prize_id'] ) ) : 0;

			if ( $id ) {
					BHG_Prizes::delete_prize( $id );
			}

				wp_safe_redirect( add_query_arg( 'bhg_msg', 'p_deleted', BHG_Utils::admin_url( 'admin.php?page=bhg-prizes' ) ) );
				exit;
		}

			/**
			 * Provide prize data via AJAX.
			 *
			 * @return void
			 */
		public function ajax_get_prize() {
			if ( ! $this->current_user_can_manage() ) {
					wp_send_json_error( array( 'message' => __( 'You are not allowed to view this prize.', 'bonus-hunt-guesser' ) ), 403 );
			}

				check_ajax_referer( 'bhg_get_prize', 'nonce' );

				$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;

			if ( ! $id ) {
					wp_send_json_error( array( 'message' => __( 'Invalid prize ID supplied.', 'bonus-hunt-guesser' ) ), 400 );
			}

				$prize = BHG_Prizes::get_prize( $id );

			if ( ! $prize ) {
					wp_send_json_error( array( 'message' => __( 'Prize not found.', 'bonus-hunt-guesser' ) ), 404 );
			}

				wp_send_json_success( BHG_Prizes::format_prize_for_response( $prize ) );
		}
	}
}
