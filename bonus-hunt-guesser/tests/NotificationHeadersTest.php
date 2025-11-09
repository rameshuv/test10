<?php

use PHPUnit\Framework\TestCase;

final class NotificationHeadersTest extends TestCase {
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

	public function test_prepare_notification_headers_filters_invalid_bcc(): void {
		$config = array(
			'bcc' => array(
				' valid@example.com ',
				'not-an-email',
				'second@example.org',
			),
		);

		$headers = bhg_prepare_notification_headers( $config );

		$this->assertContains( 'From: admin@example.com', $headers );
		$this->assertContains( 'Content-Type: text/html; charset=UTF-8', $headers );
		$this->assertContains( 'Bcc: valid@example.com, second@example.org', $headers );
	}

	public function test_prepare_notification_headers_handles_empty_bcc(): void {
		$headers = bhg_prepare_notification_headers( array() );

		$this->assertNotEmpty( $headers );
		$this->assertCount( 2, $headers );
	}
}
