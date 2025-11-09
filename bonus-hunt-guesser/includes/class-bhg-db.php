<?php
/**
 * Database schema management for Bonus Hunt Guesser.
 *
 * @package BonusHuntGuesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles database schema creation and migrations for the plugin.
 */
class BHG_DB {

	/**
	 * Static wrapper to support legacy static calls.
	 *
	 * @return void
	 */
	public static function migrate() {
		$db = new self();
		$db->create_tables();

		global $wpdb;

		$tours_table = $wpdb->prefix . 'bhg_tournaments';

		// Drop legacy "period" column and related index if they exist.
		if ( $db->column_exists( $tours_table, 'period' ) ) {
			if ( $db->index_exists( $tours_table, 'type_period' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tours_table}` DROP INDEX type_period" );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tours_table}` DROP COLUMN period" );
		}

		if ( ! $db->column_exists( $tours_table, 'points_map' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tours_table}` ADD COLUMN points_map LONGTEXT NULL AFTER hunt_link_mode" );
		}

		if ( ! $db->column_exists( $tours_table, 'ranking_scope' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tours_table}` ADD COLUMN ranking_scope VARCHAR(20) NOT NULL DEFAULT 'all' AFTER points_map" );
		}

		$tres_table = $wpdb->prefix . 'bhg_tournament_results';

		if ( ! $db->column_exists( $tres_table, 'points' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tres_table}` ADD COLUMN points INT UNSIGNED NOT NULL DEFAULT 0 AFTER wins" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$tres_table}` ADD INDEX points (points)" );
		}
	}

	/**
	 * Create or update required database tables.
	 *
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Raw table names without backticks.
		$hunts_table        = $wpdb->prefix . 'bhg_bonus_hunts';
		$guesses_table      = $wpdb->prefix . 'bhg_guesses';
		$tours_table        = $wpdb->prefix . 'bhg_tournaments';
		$tres_table         = $wpdb->prefix . 'bhg_tournament_results';
		$ads_table          = $wpdb->prefix . 'bhg_ads';
		$trans_table        = $wpdb->prefix . 'bhg_translations';
		$aff_websites_table = $wpdb->prefix . 'bhg_affiliate_websites';
		$winners_table      = $wpdb->prefix . 'bhg_hunt_winners';
		$hunt_tours_table   = $wpdb->prefix . 'bhg_tournaments_hunts';
		$legacy_hunt_tours  = $wpdb->prefix . 'bhg_hunt_tournaments';
		$prizes_table       = $wpdb->prefix . 'bhg_prizes';
		$hunt_prizes_table  = $wpdb->prefix . 'bhg_hunt_prizes';

		if ( $this->table_exists( $legacy_hunt_tours ) && ! $this->table_exists( $hunt_tours_table ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "RENAME TABLE `{$legacy_hunt_tours}` TO `{$hunt_tours_table}`" );
		}

		$sql = array();

		// Bonus Hunts.
		$sql[] = "CREATE TABLE `{$hunts_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(190) NOT NULL,
	starting_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
	num_bonuses INT UNSIGNED NOT NULL DEFAULT 0,
	prizes TEXT NULL,
	affiliate_site_id BIGINT UNSIGNED NULL,
	affiliate_id BIGINT UNSIGNED NULL,
	tournament_id BIGINT UNSIGNED NULL,
	winners_count INT UNSIGNED NOT NULL DEFAULT 3,
	guessing_enabled TINYINT(1) NOT NULL DEFAULT 1,
	final_balance DECIMAL(12,2) NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'open',
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	closed_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY title (title),
	KEY status (status),
	KEY affiliate_id (affiliate_id),
	KEY tournament_id (tournament_id)
) {$charset_collate};";

		// Guesses table includes updated_at for tracking edits.
		$sql[] = "CREATE TABLE `{$guesses_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	hunt_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED NOT NULL,
	guess DECIMAL(12,2) NOT NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY hunt_id (hunt_id),
	KEY user_id (user_id)
) {$charset_collate};";

		// Tournaments.
		$sql[] = "CREATE TABLE `{$tours_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(190) NOT NULL,
	description TEXT NULL,
	type VARCHAR(20) NOT NULL DEFAULT 'monthly',
	participants_mode VARCHAR(20) NOT NULL DEFAULT 'winners',
	hunt_link_mode VARCHAR(20) NOT NULL DEFAULT 'manual',
	points_map LONGTEXT NULL,
	ranking_scope VARCHAR(20) NOT NULL DEFAULT 'all',
	prizes TEXT NULL,
	affiliate_site_id BIGINT UNSIGNED NULL,
	affiliate_website VARCHAR(255) NULL,
	affiliate_url_visible TINYINT(1) NOT NULL DEFAULT 1,
	start_date DATE NULL,
	end_date DATE NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'active',
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY title (title),
	KEY type (type),
	KEY affiliate_website (affiliate_website),
	KEY status (status)
) {$charset_collate};";

		// Tournament Results.
		$sql[] = "CREATE TABLE `{$tres_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	tournament_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED NOT NULL,
	wins INT UNSIGNED NOT NULL DEFAULT 0,
	points INT UNSIGNED NOT NULL DEFAULT 0,
	last_win_date DATETIME NULL,
	PRIMARY KEY  (id),
	KEY tournament_id (tournament_id),
	KEY user_id (user_id),
	KEY points (points)
) {$charset_collate};";

		// Ads.
		$sql[] = "CREATE TABLE `{$ads_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(190) NOT NULL,
	content TEXT NULL,
	link_url VARCHAR(255) NULL,
	placement VARCHAR(50) NOT NULL DEFAULT 'none',
	visible_to VARCHAR(30) NOT NULL DEFAULT 'all',
	target_pages TEXT NULL,
	active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY placement (placement),
	KEY visible_to (visible_to)
) {$charset_collate};";

		// Affiliate Websites.
		$sql[] = "CREATE TABLE `{$aff_websites_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(190) NOT NULL,
	slug VARCHAR(190) NOT NULL,
	url VARCHAR(255) NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'active',
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	UNIQUE KEY slug_unique (slug)
) {$charset_collate};";

		// Hunt Winners.
		$sql[] = "CREATE TABLE `{$winners_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	hunt_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED NOT NULL,
	position INT UNSIGNED NOT NULL,
	guess DECIMAL(12,2) NOT NULL,
	diff DECIMAL(12,2) NOT NULL,
	eligible TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY hunt_id (hunt_id),
	KEY user_id (user_id),
	KEY eligible (eligible)
) {$charset_collate};";

		// Prizes table.
$sql[] = "CREATE TABLE `{$prizes_table}` (
id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
title VARCHAR(190) NOT NULL,
description TEXT NULL,
category VARCHAR(40) NOT NULL DEFAULT 'various',
link_url VARCHAR(255) NULL,
link_target VARCHAR(20) NOT NULL DEFAULT '_self',
click_action VARCHAR(20) NOT NULL DEFAULT 'link',
category_link_url VARCHAR(255) NULL,
category_link_target VARCHAR(20) NOT NULL DEFAULT '_self',
image_small BIGINT UNSIGNED NULL,
image_medium BIGINT UNSIGNED NULL,
image_large BIGINT UNSIGNED NULL,
show_title TINYINT(1) NOT NULL DEFAULT 1,
show_description TINYINT(1) NOT NULL DEFAULT 1,
show_category TINYINT(1) NOT NULL DEFAULT 1,
show_image TINYINT(1) NOT NULL DEFAULT 1,
css_border VARCHAR(100) NULL,
css_border_color VARCHAR(40) NULL,
css_padding VARCHAR(60) NULL,
css_margin VARCHAR(60) NULL,
css_background VARCHAR(40) NULL,
	active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY category (category),
	KEY active (active)
) {$charset_collate};";

		// Hunt prize map.
		$sql[] = "CREATE TABLE `{$hunt_prizes_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	hunt_id BIGINT UNSIGNED NOT NULL,
	prize_id BIGINT UNSIGNED NOT NULL,
	prize_type VARCHAR(20) NOT NULL DEFAULT 'regular',
	created_at DATETIME NULL,
	PRIMARY KEY  (id),
	UNIQUE KEY hunt_prize (hunt_id, prize_id, prize_type),
	KEY hunt_id (hunt_id),
	KEY prize_id (prize_id),
	KEY prize_type (prize_type)
) {$charset_collate};";

		// Jackpots.
		$jackpots_table       = $wpdb->prefix . 'bhg_jackpots';
		$jackpot_events_table = $wpdb->prefix . 'bhg_jackpot_events';

		$sql[] = "CREATE TABLE `{$jackpots_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(190) NOT NULL,
	start_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
	increase_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
	current_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
	link_mode VARCHAR(20) NOT NULL DEFAULT 'all',
	link_config LONGTEXT NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'active',
	hit_user_id BIGINT UNSIGNED NULL,
	hit_hunt_id BIGINT UNSIGNED NULL,
	hit_guess_id BIGINT UNSIGNED NULL,
	hit_at DATETIME NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY status (status),
	KEY link_mode (link_mode),
	KEY hit_hunt_id (hit_hunt_id),
	KEY hit_user_id (hit_user_id)
) {$charset_collate};";

		$sql[] = "CREATE TABLE `{$jackpot_events_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	jackpot_id BIGINT UNSIGNED NOT NULL,
	event_type VARCHAR(20) NOT NULL,
	amount_before DECIMAL(14,2) NOT NULL DEFAULT 0.00,
	amount_after DECIMAL(14,2) NOT NULL DEFAULT 0.00,
	user_id BIGINT UNSIGNED NULL,
	hunt_id BIGINT UNSIGNED NULL,
	meta LONGTEXT NULL,
	created_at DATETIME NULL,
	PRIMARY KEY  (id),
	KEY jackpot_id (jackpot_id),
	KEY event_type (event_type),
	KEY created_at (created_at)
) {$charset_collate};";

		// Hunt to tournament mapping.
		$sql[] = "CREATE TABLE `{$hunt_tours_table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	hunt_id BIGINT UNSIGNED NOT NULL,
	tournament_id BIGINT UNSIGNED NOT NULL,
	created_at DATETIME NULL,
	PRIMARY KEY  (id),
	UNIQUE KEY tournament_hunt (tournament_id, hunt_id),
	KEY tournament_id (tournament_id),
	KEY hunt_id (hunt_id)
) {$charset_collate};";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}

		// Translations table handled separately.
		$this->create_table_translations();

		// Idempotent ensure for columns/indexes.
		try {
			// Hunts: winners_count, affiliate_site_id, tournament_id.
			$need = array(
				'winners_count'     => 'ADD COLUMN winners_count INT UNSIGNED NOT NULL DEFAULT 3',
				'affiliate_site_id' => 'ADD COLUMN affiliate_site_id BIGINT UNSIGNED NULL',
				'affiliate_id'      => 'ADD COLUMN affiliate_id BIGINT UNSIGNED NULL',
				'tournament_id'     => 'ADD COLUMN tournament_id BIGINT UNSIGNED NULL',
				'guessing_enabled'  => 'ADD COLUMN guessing_enabled TINYINT(1) NOT NULL DEFAULT 1',
				'final_balance'     => 'ADD COLUMN final_balance DECIMAL(12,2) NULL',
				'status'            => "ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'open'",
			);

			foreach ( $need as $column => $alter ) {
				if ( ! $this->column_exists( $hunts_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunts_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $hunts_table, 'title' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$hunts_table}` ADD KEY title (title)" );
			}

			if ( ! $this->index_exists( $hunts_table, 'affiliate_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$hunts_table}` ADD KEY affiliate_id (affiliate_id)" );
			}

			if ( ! $this->index_exists( $hunts_table, 'tournament_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$hunts_table}` ADD KEY tournament_id (tournament_id)" );
			}

			// Guesses columns.
			$gneed = array(
				'updated_at' => 'ADD COLUMN updated_at DATETIME NULL',
			);

			foreach ( $gneed as $column => $alter ) {
				if ( ! $this->column_exists( $guesses_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$guesses_table}` {$alter}" );
				}
			}

			// Tournaments: make sure common columns exist.
			$tneed = array(
				'title'                 => 'ADD COLUMN title VARCHAR(190) NOT NULL',
				'description'           => 'ADD COLUMN description TEXT NULL',
				'type'                  => "ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'monthly'",
				'participants_mode'     => "ADD COLUMN participants_mode VARCHAR(20) NOT NULL DEFAULT 'winners'",
				'hunt_link_mode'        => "ADD COLUMN hunt_link_mode VARCHAR(20) NOT NULL DEFAULT 'manual'",
				'prizes'                => 'ADD COLUMN prizes TEXT NULL',
				'affiliate_site_id'     => 'ADD COLUMN affiliate_site_id BIGINT UNSIGNED NULL',
				'affiliate_website'     => 'ADD COLUMN affiliate_website VARCHAR(255) NULL',
				'affiliate_url_visible' => 'ADD COLUMN affiliate_url_visible TINYINT(1) NOT NULL DEFAULT 1',
				'start_date'            => 'ADD COLUMN start_date DATE NULL',
				'end_date'              => 'ADD COLUMN end_date DATE NULL',
				'status'                => "ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'",
			);

			foreach ( $tneed as $column => $alter ) {
				if ( ! $this->column_exists( $tours_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$tours_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $tours_table, 'type' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tours_table}` ADD KEY type (type)" );
			}

			if ( ! $this->index_exists( $tours_table, 'title' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tours_table}` ADD KEY title (title)" );
			}

			if ( ! $this->index_exists( $tours_table, 'affiliate_website' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tours_table}` ADD KEY affiliate_website (affiliate_website)" );
			}

			// Hunt prize relation columns.
			$hpneed = array(
				'prize_type' => "ADD COLUMN prize_type VARCHAR(20) NOT NULL DEFAULT 'regular' AFTER prize_id",
			);

			foreach ( $hpneed as $column => $alter ) {
				if ( ! $this->column_exists( $hunt_prizes_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunt_prizes_table}` {$alter}" );

					if ( 'prize_type' === $column ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->query( "UPDATE `{$hunt_prizes_table}` SET prize_type = 'regular' WHERE prize_type = '' OR prize_type IS NULL" );
					}
				}
			}

			if ( $this->index_exists( $hunt_prizes_table, 'hunt_prize' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$hunt_prizes_table}` DROP INDEX hunt_prize" );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( "ALTER TABLE `{$hunt_prizes_table}` ADD UNIQUE KEY hunt_prize (hunt_id, prize_id, prize_type)" );

			if ( ! $this->index_exists( $hunt_prizes_table, 'prize_type' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$hunt_prizes_table}` ADD KEY prize_type (prize_type)" );
			}

			// Tournament results columns.
			$trrneed = array(
				'tournament_id' => 'ADD COLUMN tournament_id BIGINT UNSIGNED NOT NULL',
				'user_id'       => 'ADD COLUMN user_id BIGINT UNSIGNED NOT NULL',
				'wins'          => 'ADD COLUMN wins INT UNSIGNED NOT NULL DEFAULT 0',
				'last_win_date' => 'ADD COLUMN last_win_date DATETIME NULL',
			);

			foreach ( $trrneed as $column => $alter ) {
				if ( ! $this->column_exists( $tres_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$tres_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $tres_table, 'tournament_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tres_table}` ADD KEY tournament_id (tournament_id)" );
			}

			if ( ! $this->index_exists( $tres_table, 'user_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$tres_table}` ADD KEY user_id (user_id)" );
			}

			// Ads columns.
			$aneed = array(
				'title'        => 'ADD COLUMN title VARCHAR(190) NOT NULL',
				'content'      => 'ADD COLUMN content TEXT NULL',
				'link_url'     => 'ADD COLUMN link_url VARCHAR(255) NULL',
				'placement'    => "ADD COLUMN placement VARCHAR(50) NOT NULL DEFAULT 'none'",
				'visible_to'   => "ADD COLUMN visible_to VARCHAR(30) NOT NULL DEFAULT 'all'",
				'target_pages' => 'ADD COLUMN target_pages TEXT NULL',
				'active'       => 'ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1',
				'created_at'   => 'ADD COLUMN created_at DATETIME NULL',
				'updated_at'   => 'ADD COLUMN updated_at DATETIME NULL',
			);

			foreach ( $aneed as $column => $alter ) {
				if ( ! $this->column_exists( $ads_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$ads_table}` {$alter}" );
				}
			}

			// Translations columns.
			$trneed = array(
				'slug'         => 'ADD COLUMN slug VARCHAR(191) NOT NULL',
				'default_text' => 'ADD COLUMN default_text LONGTEXT NOT NULL',
				'text'         => 'ADD COLUMN `text` LONGTEXT NULL',
				'locale'       => 'ADD COLUMN locale VARCHAR(20) NOT NULL',
				'created_at'   => 'ADD COLUMN created_at DATETIME NULL',
				'updated_at'   => 'ADD COLUMN updated_at DATETIME NULL',
			);

			foreach ( $trneed as $column => $alter ) {
				if ( ! $this->column_exists( $trans_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$trans_table}` {$alter}" );
				}
			}

			// Ensure composite unique index on (slug, locale). Drop legacy single-column indexes if present first.
			if ( $this->index_exists( $trans_table, 'slug' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX slug" );
			}

			if ( $this->index_exists( $trans_table, 'slug_unique' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX slug_unique" );
			}

			if ( $this->index_exists( $trans_table, 'tkey_locale' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$trans_table}` DROP INDEX tkey_locale" );
			}

			if ( ! $this->index_exists( $trans_table, 'slug_locale' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$trans_table}` ADD UNIQUE KEY slug_locale (slug, locale)" );
			}

			// Affiliate websites columns / unique index.
			$afw_need = array(
				'name'       => 'ADD COLUMN name VARCHAR(190) NOT NULL',
				'slug'       => 'ADD COLUMN slug VARCHAR(190) NOT NULL',
				'url'        => 'ADD COLUMN url VARCHAR(255) NULL',
				'status'     => "ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'",
				'created_at' => 'ADD COLUMN created_at DATETIME NULL',
				'updated_at' => 'ADD COLUMN updated_at DATETIME NULL',
			);

			foreach ( $afw_need as $column => $alter ) {
				if ( ! $this->column_exists( $aff_websites_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$aff_websites_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $aff_websites_table, 'slug_unique' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$aff_websites_table}` ADD UNIQUE KEY slug_unique (slug)" );
			}

			// Hunt winners columns / indexes.
			$hwneed = array(
				'hunt_id'    => 'ADD COLUMN hunt_id BIGINT UNSIGNED NOT NULL',
				'user_id'    => 'ADD COLUMN user_id BIGINT UNSIGNED NOT NULL',
				'position'   => 'ADD COLUMN position INT UNSIGNED NOT NULL',
				'guess'      => 'ADD COLUMN guess DECIMAL(12,2) NOT NULL',
				'diff'       => 'ADD COLUMN diff DECIMAL(12,2) NOT NULL',
				'eligible'   => 'ADD COLUMN eligible TINYINT(1) NOT NULL DEFAULT 1',
				'created_at' => 'ADD COLUMN created_at DATETIME NULL',
			);

			foreach ( $hwneed as $column => $alter ) {
				if ( ! $this->column_exists( $winners_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$winners_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $winners_table, 'hunt_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$winners_table}` ADD KEY hunt_id (hunt_id)" );
			}

			if ( ! $this->index_exists( $winners_table, 'user_id' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$winners_table}` ADD KEY user_id (user_id)" );
			}

			if ( ! $this->index_exists( $winners_table, 'eligible' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$winners_table}` ADD KEY eligible (eligible)" );
			}

			// Prize columns and indexes.
$pneed = array(
'category'              => "ADD COLUMN category VARCHAR(40) NOT NULL DEFAULT 'various'",
'link_url'              => 'ADD COLUMN link_url VARCHAR(255) NULL',
'link_target'           => "ADD COLUMN link_target VARCHAR(20) NOT NULL DEFAULT '_self'",
'click_action'          => "ADD COLUMN click_action VARCHAR(20) NOT NULL DEFAULT 'link'",
'category_link_url'     => 'ADD COLUMN category_link_url VARCHAR(255) NULL',
'category_link_target'  => "ADD COLUMN category_link_target VARCHAR(20) NOT NULL DEFAULT '_self'",
'image_small'           => 'ADD COLUMN image_small BIGINT UNSIGNED NULL',
'image_medium'          => 'ADD COLUMN image_medium BIGINT UNSIGNED NULL',
'image_large'           => 'ADD COLUMN image_large BIGINT UNSIGNED NULL',
'show_title'            => 'ADD COLUMN show_title TINYINT(1) NOT NULL DEFAULT 1',
'show_description'      => 'ADD COLUMN show_description TINYINT(1) NOT NULL DEFAULT 1',
'show_category'         => 'ADD COLUMN show_category TINYINT(1) NOT NULL DEFAULT 1',
'show_image'            => 'ADD COLUMN show_image TINYINT(1) NOT NULL DEFAULT 1',
'css_border'            => 'ADD COLUMN css_border VARCHAR(100) NULL',
'css_border_color'      => 'ADD COLUMN css_border_color VARCHAR(40) NULL',
'css_padding'           => 'ADD COLUMN css_padding VARCHAR(60) NULL',
'css_margin'            => 'ADD COLUMN css_margin VARCHAR(60) NULL',
'css_background'        => 'ADD COLUMN css_background VARCHAR(40) NULL',
'active'                => 'ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1',
'created_at'            => 'ADD COLUMN created_at DATETIME NULL',
'updated_at'            => 'ADD COLUMN updated_at DATETIME NULL',
);

			foreach ( $pneed as $column => $alter ) {
				if ( ! $this->column_exists( $prizes_table, $column ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$prizes_table}` {$alter}" );
				}
			}

			if ( ! $this->index_exists( $prizes_table, 'category' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$prizes_table}` ADD KEY category (category)" );
			}

			if ( ! $this->index_exists( $prizes_table, 'active' ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( "ALTER TABLE `{$prizes_table}` ADD KEY active (active)" );
			}

			// Ensure hunt/tournament relation table structure.
			if ( $this->table_exists( $hunt_tours_table ) ) {
				$htneed = array(
					'hunt_id'       => 'ADD COLUMN hunt_id BIGINT UNSIGNED NOT NULL',
					'tournament_id' => 'ADD COLUMN tournament_id BIGINT UNSIGNED NOT NULL',
					'created_at'    => 'ADD COLUMN created_at DATETIME NULL',
				);

				foreach ( $htneed as $column => $alter ) {
					if ( ! $this->column_exists( $hunt_tours_table, $column ) ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->query( "ALTER TABLE `{$hunt_tours_table}` {$alter}" );
					}
				}

				if ( $this->index_exists( $hunt_tours_table, 'hunt_tournament' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunt_tours_table}` DROP INDEX hunt_tournament" );
				}

				if ( ! $this->index_exists( $hunt_tours_table, 'tournament_hunt' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunt_tours_table}` ADD UNIQUE KEY tournament_hunt (tournament_id, hunt_id)" );
				}

				if ( ! $this->index_exists( $hunt_tours_table, 'tournament_id' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunt_tours_table}` ADD KEY tournament_id (tournament_id)" );
				}

				if ( ! $this->index_exists( $hunt_tours_table, 'hunt_id' ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( "ALTER TABLE `{$hunt_tours_table}` ADD KEY hunt_id (hunt_id)" );
				}

				if ( $this->table_exists( $hunts_table ) ) {
					$now      = current_time( 'mysql' );
					$insert_q = $wpdb->prepare(
						"INSERT IGNORE INTO `{$hunt_tours_table}` (hunt_id, tournament_id, created_at)
SELECT id, tournament_id, %s FROM `{$hunts_table}`
WHERE tournament_id IS NOT NULL AND tournament_id > 0",
						$now
					);

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( $insert_q );
				}
			}
		} catch ( Throwable $e ) {
			if ( function_exists( 'error_log' ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( '[BHG] Schema ensure error: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Create or update the translations table.
	 *
	 * @return void
	 */
	private function create_table_translations() {
		global $wpdb;

		$table           = $wpdb->prefix . 'bhg_translations';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	slug VARCHAR(191) NOT NULL,
	default_text LONGTEXT NOT NULL,
	text LONGTEXT NULL,
	locale VARCHAR(20) NOT NULL,
	created_at DATETIME NULL,
	updated_at DATETIME NULL,
	PRIMARY KEY  (id),
	UNIQUE KEY slug_locale (slug, locale)
) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Retrieve all affiliate websites.
	 *
	 * @return array List of affiliate website objects.
	 */
	public function get_affiliate_websites() {
		global $wpdb;

		$table = $wpdb->prefix . 'bhg_affiliate_websites';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( "SELECT id, name, slug, url, status FROM `{$table}` ORDER BY name ASC" );
	}

	/**
	 * Check if a column exists, falling back when information_schema is not accessible.
	 *
	 * @param string $table  Table name.
	 * @param string $column Column to check.
	 * @return bool
	 */
	private function column_exists( $table, $column ) {
		global $wpdb;

		$wpdb->last_error = '';
		$sql              = $wpdb->prepare(
			'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME=%s',
			DB_NAME,
			$table,
			$column
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$exists = $wpdb->get_var( $sql );

		if ( $wpdb->last_error ) {
			$wpdb->last_error = '';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", $column ) );
		}

		return ! empty( $exists );
	}

	/**
	 * Check if an index exists, falling back when information_schema is not accessible.
	 *
	 * @param string $table Table name.
	 * @param string $index Index to check.
	 * @return bool
	 */
	private function index_exists( $table, $index ) {
		global $wpdb;

		$wpdb->last_error = '';
		$sql              = $wpdb->prepare(
			'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND INDEX_NAME=%s',
			DB_NAME,
			$table,
			$index
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$exists = $wpdb->get_var( $sql );

		if ( $wpdb->last_error ) {
			$wpdb->last_error = '';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `{$table}` WHERE Key_name=%s", $index ) );
		}

		return ! empty( $exists );
	}

	/**
	 * Check whether a table exists in the database.
	 *
	 * @param string $table Table name.
	 * @return bool
	 */
	private function table_exists( $table ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		return ! empty( $exists );
	}
}
