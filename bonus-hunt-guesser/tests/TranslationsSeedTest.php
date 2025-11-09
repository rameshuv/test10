<?php

use PHPUnit\Framework\TestCase;

final class TranslationsSeedTest extends TestCase {
	protected function setUp(): void {
		global $wpdb, $bhg_test_user_meta, $bhg_test_posts, $bhg_test_post_meta, $bhg_test_options, $bhg_test_cache, $bhg_test_next_post_id;

		$wpdb = new MockWPDB();
		$wpdb->set_table_exists( 'wp_bhg_translations' );
		$wpdb->set_table_exists( 'wp_usermeta' );
		$bhg_test_user_meta    = array();
		$bhg_test_posts        = array();
		$bhg_test_post_meta    = array();
		$bhg_test_options      = array();
		$bhg_test_cache        = array();
		$bhg_test_next_post_id = 1;
	}

	public function test_seed_default_translations_only_inserts_missing_entries(): void {
		global $wpdb;

		$this->assertEmpty( $wpdb->translations );

		bhg_seed_default_translations_if_empty();

		$this->assertNotEmpty( $wpdb->translations );
		$initial_count = count( $wpdb->translations );

		bhg_seed_default_translations_if_empty();

		$this->assertSame( $initial_count, count( $wpdb->translations ) );
	}
}
