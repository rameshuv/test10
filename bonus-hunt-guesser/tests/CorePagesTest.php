<?php

use PHPUnit\Framework\TestCase;

final class CorePagesTest extends TestCase {
	protected function setUp(): void {
		global $wpdb, $bhg_test_user_meta, $bhg_test_posts, $bhg_test_post_meta, $bhg_test_options, $bhg_test_cache, $bhg_test_next_post_id;

		$wpdb = new MockWPDB();
		$wpdb->set_table_exists( 'wp_usermeta' );
		$wpdb->set_table_exists( 'wp_bhg_translations' );
		$bhg_test_user_meta    = array();
		$bhg_test_posts        = array();
		$bhg_test_post_meta    = array();
		$bhg_test_options      = array();
		$bhg_test_cache        = array();
		$bhg_test_next_post_id = 1;
	}

	public function test_creates_all_required_pages_when_missing(): void {
		bhg_ensure_required_pages();

		$page_ids    = get_option( 'bhg_core_page_ids', array() );
		$definitions = bhg_get_required_pages();

		$this->assertCount( count( $definitions ), $page_ids );

		foreach ( $definitions as $definition ) {
			$this->assertArrayHasKey( $definition['slug'], $page_ids );

			$page_id = $page_ids[ $definition['slug'] ];
			$post    = get_post( $page_id );

			$this->assertNotNull( $post );
			$this->assertSame( 'publish', get_post_status( $page_id ) );

			$expected_snippets = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $definition['content'] ) ) );
			foreach ( $expected_snippets as $shortcode ) {
				$this->assertStringContainsString( $shortcode, $post->post_content );
			}
		}
	}

	public function test_existing_core_page_is_reused(): void {
		$existing_id = wp_insert_post(
			array(
				'post_title'   => 'Leaderboards',
				'post_name'    => 'leaderboards',
				'post_content' => 'Custom Content',
				'post_status'  => 'draft',
				'post_type'    => 'page',
			)
		);
		update_post_meta( $existing_id, '_bhg_core_page', 'leaderboards' );

		bhg_ensure_required_pages();

		$page_ids = get_option( 'bhg_core_page_ids', array() );

		$this->assertSame( $existing_id, $page_ids['leaderboards'] );
		$this->assertSame( 'publish', get_post_status( $existing_id ) );

		global $bhg_test_posts;
		$leaderboard_count = 0;
		foreach ( $bhg_test_posts as $post ) {
			if ( 'leaderboards' === $post['post_name'] ) {
				++$leaderboard_count;
			}
		}

		$this->assertSame( 1, $leaderboard_count );
	}

	public function test_untrashes_existing_page_before_publishing(): void {
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Prizes',
				'post_name'   => 'prizes',
				'post_status' => 'trash',
				'post_type'   => 'page',
			)
		);

		bhg_ensure_required_pages();

		$this->assertSame( 'publish', get_post_status( $page_id ) );
	}
}
