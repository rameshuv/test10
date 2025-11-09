<?php
/**
 * Core page provisioning helpers.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Retrieve definitions for the core pages required by the plugin.
 *
 * @return array[] List of page definitions (slug, title, content).
 */
function bhg_get_required_pages() {
		return array(
			array(
				'slug'    => 'active-bonus-hunt',
				'title'   => bhg_t( 'page_active_bonus_hunt', 'Active Bonus Hunt' ),
				'content' => "[bhg_active_hunt]\n[bhg_guess_form]",
			),
			array(
				'slug'    => 'all-bonus-hunts',
				'title'   => bhg_t( 'page_all_bonus_hunts', 'All Bonus Hunts' ),
				'content' => '[bhg_hunts]',
			),
			array(
				'slug'    => 'tournaments',
				'title'   => bhg_t( 'page_tournaments', 'Tournaments' ),
				'content' => '[bhg_tournaments]',
			),
			array(
				'slug'    => 'leaderboards',
				'title'   => bhg_t( 'page_leaderboards', 'Leaderboards' ),
				'content' => '[bhg_leaderboards]',
			),
			array(
				'slug'    => 'user-guesses',
				'title'   => bhg_t( 'page_user_guesses', 'User Guesses' ),
				'content' => '[bhg_user_guesses]',
			),
			array(
				'slug'    => 'my-profile',
				'title'   => bhg_t( 'page_my_profile', 'My Profile' ),
				'content' => "[bhg_user_profile]\n[my_bonushunts]\n[my_tournaments]\n[my_prizes]\n[my_rankings]",
			),
			array(
				'slug'    => 'prizes',
				'title'   => bhg_t( 'page_prizes', 'Prizes' ),
				'content' => '[bhg_prizes]',
			),
			array(
				'slug'    => 'advertising',
				'title'   => bhg_t( 'page_advertising', 'Advertising' ),
				'content' => '[bhg_advertising]',
			),
		);
}

/**
 * Ensure the plugin's required pages exist.
 *
 * @return void
 */
function bhg_ensure_required_pages() {
	if ( ! function_exists( 'wp_insert_post' ) || ! function_exists( 'get_page_by_path' ) ) {
			return;
	}

		$required = bhg_get_required_pages();
		$stored   = get_option( 'bhg_core_page_ids', array() );
	if ( ! is_array( $stored ) ) {
			$stored = array();
	}

		$updated_ids = $stored;

	foreach ( $required as $page ) {
			$slug    = $page['slug'];
			$page_id = 0;

		if ( isset( $stored[ $slug ] ) ) {
				$maybe_existing = get_post( (int) $stored[ $slug ] );
			if ( $maybe_existing && 'trash' === get_post_status( $maybe_existing->ID ) ) {
				wp_untrash_post( $maybe_existing->ID );
				$maybe_existing = get_post( $maybe_existing->ID );
			}

			if ( $maybe_existing && 'page' === $maybe_existing->post_type ) {
				if ( 'publish' !== $maybe_existing->post_status ) {
					wp_update_post(
						array(
							'ID'          => $maybe_existing->ID,
							'post_status' => 'publish',
						)
					);
				}

					$page_id = $maybe_existing->ID;
			}
		}

		if ( ! $page_id ) {
				$existing = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $existing ) {
				if ( 'trash' === get_post_status( $existing->ID ) ) {
					wp_untrash_post( $existing->ID );
				}

				if ( 'publish' !== get_post_status( $existing->ID ) ) {
						wp_update_post(
							array(
								'ID'          => $existing->ID,
								'post_status' => 'publish',
							)
						);
				}

					$page_id = $existing->ID;
			}
		}

		if ( ! $page_id ) {
				$page_id = wp_insert_post(
					array(
						'post_title'   => $page['title'],
						'post_name'    => $slug,
						'post_content' => $page['content'],
						'post_status'  => 'publish',
						'post_type'    => 'page',
						'post_author'  => get_current_user_id(),
					),
					true
				);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				continue;
			}
		}

		if ( $page_id ) {
			if ( ! metadata_exists( 'post', $page_id, '_bhg_core_page' ) ) {
					update_post_meta( $page_id, '_bhg_core_page', $slug );
			}

				$updated_ids[ $slug ] = (int) $page_id;
		}
	}

	if ( $updated_ids !== $stored ) {
			update_option( 'bhg_core_page_ids', $updated_ids );
	}
}

/**
 * Retrieve the permalink for a managed core page.
 *
 * @param string $slug Page slug from bhg_get_required_pages().
 * @return string Core page URL or empty string if unavailable.
 */
function bhg_get_core_page_url( $slug ) {
		$slug = sanitize_title( $slug );

	if ( '' === $slug ) {
			return '';
	}

		$page_ids = get_option( 'bhg_core_page_ids', array() );
	if ( is_array( $page_ids ) && isset( $page_ids[ $slug ] ) ) {
			$page_id   = (int) $page_ids[ $slug ];
			$permalink = get_permalink( $page_id );
		if ( $permalink ) {
				return $permalink;
		}
	}

		$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page && isset( $page->ID ) ) {
			$permalink = get_permalink( $page );
		if ( $permalink ) {
				return $permalink;
		}
	}

		return '';
}
