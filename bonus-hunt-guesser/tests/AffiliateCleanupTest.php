<?php

use PHPUnit\Framework\TestCase;

final class AffiliateCleanupTest extends TestCase {
	protected function setUp(): void {
		global $wpdb, $bhg_test_user_meta, $bhg_test_posts, $bhg_test_post_meta, $bhg_test_options, $bhg_test_cache, $bhg_test_next_post_id;

		$wpdb = new MockWPDB();
		$wpdb->set_table_exists( 'wp_usermeta' );
		$bhg_test_user_meta    = array();
		$bhg_test_posts        = array();
		$bhg_test_post_meta    = array();
		$bhg_test_options      = array();
		$bhg_test_cache        = array();
		$bhg_test_next_post_id = 1;
	}

	public function test_removed_affiliate_site_prunes_user_meta(): void {
		global $wpdb;

		update_user_meta( 10, 'bhg_affiliate_websites', array( 2, 5, 7 ) );
		update_user_meta( 11, 'bhg_affiliate_websites', array( 5 ) );
		update_user_meta( 12, 'bhg_affiliate_websites', array( 3 ) );

		bhg_remove_affiliate_site_from_users( 5 );

		$this->assertSame( array( 2, 7 ), get_user_meta( 10, 'bhg_affiliate_websites', true ) );
		$this->assertSame( array(), get_user_meta( 11, 'bhg_affiliate_websites', true ) );
		$this->assertSame( array( 3 ), get_user_meta( 12, 'bhg_affiliate_websites', true ) );
	}

	public function test_invalid_site_id_does_not_trigger_updates(): void {
		update_user_meta( 20, 'bhg_affiliate_websites', array( 4, 6 ) );

		bhg_remove_affiliate_site_from_users( 0 );

		$this->assertSame( array( 4, 6 ), get_user_meta( 20, 'bhg_affiliate_websites', true ) );
	}
}
