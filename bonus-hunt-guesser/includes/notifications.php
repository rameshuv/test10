<?php
/**
 * Email notification helpers.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

if ( ! function_exists( 'bhg_get_notification_defaults' ) ) {
		/**
		 * Retrieve default notification templates.
		 *
		 * @return array
		 */
	function bhg_get_notification_defaults() {
			return array(
				'winner'     => array(
					'enabled' => 0,
					'title'   => bhg_t( 'default_winner_email_subject', 'Congratulations! You placed in {{hunt_title}}' ),
					'body'    => bhg_t( 'default_winner_email_body', 'Hi {{username}},<br><br>Congratulations on your {{position}} place finish in {{hunt_title}}!<br>Your guess: {{guess}} (difference {{difference}}).<br><br>Winners:<br>{{winner_summary}}<br><br>{{site_name}}' ),
					'bcc'     => array(),
				),
				'tournament' => array(
					'enabled' => 0,
					'title'   => bhg_t( 'default_tournament_email_subject', 'Tournament {{tournament_title}} results' ),
					'body'    => bhg_t( 'default_tournament_email_body', 'Hi {{username}},<br><br>{{tournament_title}} has concluded. You finished {{position}} with {{wins}} wins.<br><br>Final standings:<br>{{leaderboard}}<br><br>{{site_name}}' ),
					'bcc'     => array(),
				),
				'bonus_hunt' => array(
					'enabled' => 0,
					'title'   => bhg_t( 'default_bonus_email_subject', '{{hunt_title}} results' ),
					'body'    => bhg_t( 'default_bonus_email_body', 'Hi {{username}},<br><br>{{hunt_title}} finished with a final balance of {{final_balance}}.<br><br>Winners:<br>{{winner_summary}}<br><br>Thank you for playing,<br>{{site_name}}' ),
					'bcc'     => array(),
				),
			);
	}
}

if ( ! function_exists( 'bhg_get_notification_settings' ) ) {
		/**
		 * Retrieve stored notification settings merged with defaults.
		 *
		 * @return array
		 */
	function bhg_get_notification_settings() {
			$defaults = bhg_get_notification_defaults();
			$stored   = get_option( BHG_EMAIL_NOTIFICATIONS_OPTION, array() );

		if ( ! is_array( $stored ) ) {
				$stored = array();
		}

			$settings = array();

		foreach ( $defaults as $key => $default ) {
				$current = isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ? $stored[ $key ] : array();

				$settings[ $key ] = array(
					'enabled' => ! empty( $current['enabled'] ) ? 1 : 0,
					'title'   => isset( $current['title'] ) ? (string) $current['title'] : ( isset( $default['title'] ) ? $default['title'] : '' ),
					'body'    => isset( $current['body'] ) ? (string) $current['body'] : ( isset( $default['body'] ) ? $default['body'] : '' ),
					'bcc'     => bhg_normalize_notification_bcc( isset( $current['bcc'] ) ? $current['bcc'] : array() ),
				);
		}

			return $settings;
	}
}

if ( ! function_exists( 'bhg_normalize_notification_bcc' ) ) {
		/**
		 * Normalize BCC input into a sanitized array.
		 *
		 * @param string|array $value Raw BCC input.
		 * @return array
		 */
	function bhg_normalize_notification_bcc( $value ) {
			$addresses = array();

		if ( is_string( $value ) ) {
				$parts = preg_split( '/[\r\n,]+/', $value );
		} elseif ( is_array( $value ) ) {
				$parts = $value;
		} else {
				$parts = array();
		}

		foreach ( $parts as $part ) {
				$email = sanitize_email( trim( (string) $part ) );
			if ( $email && is_email( $email ) ) {
					$addresses[ $email ] = $email;
			}
		}

			return array_values( $addresses );
	}
}

if ( ! function_exists( 'bhg_prepare_notification_headers' ) ) {
		/**
		 * Build mail headers for a notification.
		 *
		 * @param array $config Notification configuration.
		 * @return array
		 */
	function bhg_prepare_notification_headers( array $config ) {
			$headers   = array();
			$from      = BHG_Utils::get_email_from();
			$headers[] = 'From: ' . $from;
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

		if ( ! empty( $config['bcc'] ) && is_array( $config['bcc'] ) ) {
				$bcc_addresses = array();

			foreach ( $config['bcc'] as $address ) {
				$email = sanitize_email( (string) $address );

				if ( $email && is_email( $email ) ) {
						$bcc_addresses[ $email ] = $email;
				}
			}

			if ( ! empty( $bcc_addresses ) ) {
					$headers[] = 'Bcc: ' . implode( ', ', $bcc_addresses );
			}
		}

			return $headers;
	}
}

if ( ! function_exists( 'bhg_render_notification_template' ) ) {
		/**
		 * Render a template string with replacements.
		 *
		 * @param string $template   Template text.
		 * @param array  $replacements Placeholder map.
		 * @param bool   $strip_tags  Whether to strip HTML tags.
		 * @return string
		 */
	function bhg_render_notification_template( $template, array $replacements, $strip_tags = false ) {
			$rendered = strtr( (string) $template, $replacements );

		if ( $strip_tags ) {
				$rendered = wp_strip_all_tags( $rendered );
		}

			return $rendered;
	}
}

if ( ! function_exists( 'bhg_dispatch_hunt_notifications' ) ) {
		/**
		 * Send bonus hunt related notifications.
		 *
		 * @param int   $hunt_id       Hunt identifier.
		 * @param float $final_balance Final balance.
		 * @param array $winner_ids    Winner user IDs.
		 * @return void
		 */
	function bhg_dispatch_hunt_notifications( $hunt_id, $final_balance, array $winner_ids = array() ) {
			$hunt_id       = (int) $hunt_id;
			$final_balance = (float) $final_balance;

		if ( $hunt_id <= 0 ) {
				return;
		}

			$settings = bhg_get_notification_settings();

		if ( empty( $settings ) ) {
				return;
		}

			$site_name = esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

			$hunt_title    = '';
			$winners_count = count( $winner_ids );

		if ( function_exists( 'bhg_get_hunt' ) ) {
				$hunt = bhg_get_hunt( $hunt_id );
			if ( $hunt ) {
					$hunt_title    = isset( $hunt->title ) ? wp_strip_all_tags( (string) $hunt->title ) : '';
					$winners_count = isset( $hunt->winners_count ) ? (int) $hunt->winners_count : $winners_count;
			}
		}

		if ( '' === $hunt_title ) {
			global $wpdb;
			$hunts_table = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$title_query = $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$hunts_table} WHERE id = %d", $hunt_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $title_query ) {
				$hunt_title = wp_strip_all_tags( (string) $title_query );
			}
		}

		if ( $winners_count <= 0 ) {
				$winners_count = max( 1, count( $winner_ids ) );
		}

			$winners = array();
		if ( function_exists( 'bhg_get_top_winners_for_hunt' ) ) {
				$winners = bhg_get_top_winners_for_hunt( $hunt_id, $winners_count );
		}

			$winner_lines     = array();
			$winner_usernames = array();
			$winner_lookup    = array();
			$position         = 1;

		if ( ! empty( $winners ) ) {
			foreach ( $winners as $winner ) {
					$user_id = isset( $winner->user_id ) ? (int) $winner->user_id : 0;
				if ( $user_id <= 0 ) {
					continue;
				}

					$user     = get_userdata( $user_id );
					$username = $user ? $user->user_login : sprintf( bhg_t( 'label_user_number', 'User #%d' ), $user_id );

					$winner_usernames[] = $username;

					$guess_value = isset( $winner->guess ) ? (float) $winner->guess : 0.0;
					$diff_value  = isset( $winner->diff ) ? abs( (float) $winner->diff ) : 0.0;

									$guess_display = bhg_format_money( $guess_value );
									$diff_display  = bhg_format_money( $diff_value );

					$winner_lines[] = sprintf(
						'%1$d. %2$s — %3$s (±%4$s)',
						$position,
						$username,
						$guess_display,
						$diff_display
					);

					$winner_lookup[ $user_id ] = array(
						'username' => esc_html( $username ),
						'position' => $position,
						'guess'    => esc_html( $guess_display ),
						'diff'     => esc_html( $diff_display ),
					);

					++$position;
			}
		}

			$winner_count      = count( $winner_lines );
			$winner_summary    = $winner_count ? implode( '<br>', array_map( 'esc_html', $winner_lines ) ) : '';
			$winner_names_line = $winner_usernames ? implode( ', ', array_map( 'esc_html', $winner_usernames ) ) : '';

			$hunt_title_safe            = '' !== $hunt_title ? esc_html( $hunt_title ) : esc_html( bhg_t( 'bonus_hunt', 'Bonus Hunt' ) );
					$final_balance_safe = esc_html( bhg_format_money( $final_balance ) );

			// Winner notifications.
		if ( ! empty( $settings['winner']['enabled'] ) && ! empty( $winner_lookup ) ) {
				$winner_config  = $settings['winner'];
				$winner_headers = bhg_prepare_notification_headers( $winner_config );

			foreach ( $winner_lookup as $user_id => $data ) {
					$user = get_userdata( $user_id );
				if ( ! $user || ! $user->user_email ) {
					continue;
				}

					$replacements = array(
						'{{username}}'         => $data['username'],
						'{{hunt_title}}'       => $hunt_title_safe,
						'{{final_balance}}'    => $final_balance_safe,
						'{{position}}'         => esc_html( (string) $data['position'] ),
						'{{guess}}'            => $data['guess'],
						'{{difference}}'       => $data['diff'],
						'{{winner_summary}}'   => $winner_summary,
						'{{winner_usernames}}' => $winner_names_line,
						'{{winner_count}}'     => esc_html( (string) $winner_count ),
						'{{site_name}}'        => $site_name,
					);

					$subject = bhg_render_notification_template( $winner_config['title'], $replacements, true );
					$body    = bhg_render_notification_template( $winner_config['body'], $replacements, false );

					if ( '' === trim( $subject ) ) {
							$subject = bhg_render_notification_template( bhg_t( 'default_winner_email_subject', 'Congratulations! You placed in {{hunt_title}}' ), $replacements, true );
					}

					wp_mail( $user->user_email, $subject, $body, $winner_headers );
			}
		}

			// Bonus hunt notifications (all participants).
		if ( ! empty( $settings['bonus_hunt']['enabled'] ) ) {
			global $wpdb;

			$guesses_table = esc_sql( $wpdb->prefix . 'bhg_guesses' );
			$participants  = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT user_id FROM {$guesses_table} WHERE hunt_id = %d", $hunt_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			if ( ! empty( $participants ) ) {
					$bonus_config  = $settings['bonus_hunt'];
					$bonus_headers = bhg_prepare_notification_headers( $bonus_config );

				foreach ( $participants as $participant_id ) {
					$user_id = (int) $participant_id;
					if ( $user_id <= 0 ) {
								continue;
					}

					$user = get_userdata( $user_id );
					if ( ! $user || ! $user->user_email ) {
									continue;
					}

								$username = esc_html( $user->user_login );

								$replacements = array(
									'{{username}}'         => $username,
									'{{hunt_title}}'       => $hunt_title_safe,
									'{{final_balance}}'    => $final_balance_safe,
									'{{winner_summary}}'   => $winner_summary,
									'{{winner_usernames}}' => $winner_names_line,
									'{{winner_count}}'     => esc_html( (string) $winner_count ),
									'{{site_name}}'        => $site_name,
								);

								$subject = bhg_render_notification_template( $bonus_config['title'], $replacements, true );
								$body    = bhg_render_notification_template( $bonus_config['body'], $replacements, false );

								if ( '' === trim( $subject ) ) {
									$subject = bhg_render_notification_template( bhg_t( 'default_bonus_email_subject', '{{hunt_title}} results' ), $replacements, true );
								}

								wp_mail( $user->user_email, $subject, $body, $bonus_headers );
				}
			}
		}
	}
}

if ( ! function_exists( 'bhg_dispatch_tournament_notifications' ) ) {
		/**
		 * Send tournament notifications when a tournament closes.
		 *
		 * @param int $tournament_id Tournament identifier.
		 * @return void
		 */
	function bhg_dispatch_tournament_notifications( $tournament_id ) {
			$tournament_id = (int) $tournament_id;

		if ( $tournament_id <= 0 ) {
				return;
		}

			$settings = bhg_get_notification_settings();

		if ( empty( $settings['tournament']['enabled'] ) ) {
				return;
		}

		global $wpdb;

		$tournaments_table = esc_sql( $wpdb->prefix . 'bhg_tournaments' );
		$results_table     = esc_sql( $wpdb->prefix . 'bhg_tournament_results' );

		$tournament_title     = $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$tournaments_table} WHERE id = %d", $tournament_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$tournament_title = $tournament_title ? wp_strip_all_tags( (string) $tournament_title ) : bhg_t( 'menu_tournaments', 'Tournaments' );
			$tournament_safe  = esc_html( $tournament_title );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, wins FROM {$results_table} WHERE tournament_id = %d ORDER BY wins DESC, user_id ASC", $tournament_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $results ) ) {
				return;
		}

			$site_name = esc_html( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

			$summary_limit  = 25;
			$summary_lines  = array();
			$summary_names  = array();
			$position_index = 1;

			$user_map = array();

		foreach ( $results as $row ) {
				$user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
			if ( $user_id <= 0 ) {
					++$position_index;
					continue;
			}

				$wins_count = isset( $row->wins ) ? (int) $row->wins : 0;
				$user       = get_userdata( $user_id );
				$username   = $user ? $user->user_login : sprintf( bhg_t( 'label_user_number', 'User #%d' ), $user_id );

			if ( $position_index <= $summary_limit ) {
					$summary_lines[] = sprintf(
						'%1$d. %2$s — %3$s',
						$position_index,
						$username,
						number_format_i18n( $wins_count )
					);
					$summary_names[] = $username;
			}

				$user_map[ $user_id ] = array(
					'position' => $position_index,
					'wins'     => esc_html( number_format_i18n( $wins_count ) ),
					'name'     => esc_html( $username ),
				);

				++$position_index;
		}

		if ( empty( $user_map ) ) {
				return;
		}

			$leaderboard_summary = $summary_lines ? implode( '<br>', array_map( 'esc_html', $summary_lines ) ) : '';
			$winner_names_line   = $summary_names ? implode( ', ', array_map( 'esc_html', $summary_names ) ) : '';
			$winner_count        = count( $summary_names );

			$config  = $settings['tournament'];
			$headers = bhg_prepare_notification_headers( $config );

		foreach ( $user_map as $user_id => $data ) {
				$user = get_userdata( $user_id );
			if ( ! $user || ! $user->user_email ) {
					continue;
			}

				$replacements = array(
					'{{username}}'         => $data['name'],
					'{{tournament_title}}' => $tournament_safe,
					'{{position}}'         => esc_html( (string) $data['position'] ),
					'{{wins}}'             => $data['wins'],
					'{{leaderboard}}'      => $leaderboard_summary,
					'{{winner_usernames}}' => $winner_names_line,
					'{{winner_count}}'     => esc_html( (string) $winner_count ),
					'{{site_name}}'        => $site_name,
				);

				$subject = bhg_render_notification_template( $config['title'], $replacements, true );
				$body    = bhg_render_notification_template( $config['body'], $replacements, false );

				if ( '' === trim( $subject ) ) {
						$subject = bhg_render_notification_template( bhg_t( 'default_tournament_email_subject', 'Tournament {{tournament_title}} results' ), $replacements, true );
				}

				wp_mail( $user->user_email, $subject, $body, $headers );
		}
	}
}

if ( ! function_exists( 'bhg_send_hunt_results_email' ) ) {
		/**
		 * Back-compat wrapper for sending hunt results.
		 *
		 * @param int        $hunt_id       Hunt identifier.
		 * @param array      $winner_ids    Winner user IDs.
		 * @param float|null $final_balance Final balance if available.
		 * @return void
		 */
	function bhg_send_hunt_results_email( $hunt_id, $winner_ids = array(), $final_balance = null ) {
			$hunt_id = (int) $hunt_id;

		if ( null === $final_balance ) {
			global $wpdb;
			$hunts_table   = esc_sql( $wpdb->prefix . 'bhg_bonus_hunts' );
			$final_balance = $wpdb->get_var( $wpdb->prepare( "SELECT final_balance FROM {$hunts_table} WHERE id = %d", $hunt_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		if ( null === $final_balance ) {
				$final_balance = 0.0;
		}

			bhg_dispatch_hunt_notifications( $hunt_id, (float) $final_balance, is_array( $winner_ids ) ? $winner_ids : array() );
	}
}
