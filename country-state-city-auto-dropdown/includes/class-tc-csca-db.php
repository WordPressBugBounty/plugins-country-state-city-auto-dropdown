<?php
/**
 * Database helpers — safe for existing installs.
 * Never renames tables. Data refresh uses explicit/versioned reseed.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TC_CSCA_DB {

	const DB_VERSION   = '2.8.1';
	const DATA_VERSION = '2026.07.1';

	/**
	 * Table names (unchanged for backward compatibility).
	 *
	 * @return array{countries:string,state:string,city:string}
	 */
	public static function tables() {
		global $wpdb;
		return array(
			'countries' => $wpdb->prefix . 'countries',
			'state'     => $wpdb->prefix . 'state',
			'city'      => $wpdb->prefix . 'city',
		);
	}

	/**
	 * Run on plugins_loaded for existing sites upgrading to 2.8+.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( 'tc_auto_plugin_version', '0' );
		if ( version_compare( (string) $installed, self::DB_VERSION, '<' ) ) {
			self::create_tables();
			self::ensure_indexes();

			$counts = self::get_counts();
			if ( 0 === (int) $counts['countries'] ) {
				self::seed_data();
				update_option( 'tc_csca_data_version', self::DATA_VERSION );
			}

			update_option( 'tc_auto_plugin_version', self::DB_VERSION );
			update_option( 'tc_auto_plugin', 'activated' );
		}

		self::create_tables();
		self::ensure_indexes();

		// Flag outdated geography packs (do not auto-wipe custom/live DBs).
		$data_ver = get_option( 'tc_csca_data_version', '' );
		if ( $data_ver !== self::DATA_VERSION && self::is_healthy() ) {
			update_option( 'tc_csca_data_update_available', '1' );
		}
	}

	/**
	 * Create tables if missing. Does not drop or alter existing columns.
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$t               = self::tables();

		$country_create = "CREATE TABLE {$t['countries']} (
			id mediumint(8) NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$state_create = "CREATE TABLE {$t['state']} (
			id mediumint(8) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			country_id mediumint(8) UNSIGNED NOT NULL,
			PRIMARY KEY  (id),
			KEY country_id (country_id)
		) $charset_collate;";

		$city_create = "CREATE TABLE {$t['city']} (
			id mediumint(8) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			state_id mediumint(8) UNSIGNED NOT NULL,
			PRIMARY KEY  (id),
			KEY state_id (state_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $country_create );
		dbDelta( $state_create );
		dbDelta( $city_create );
	}

	/**
	 * Add indexes on existing installs if missing (non-destructive).
	 */
	public static function ensure_indexes() {
		$t = self::tables();
		self::maybe_add_index( $t['state'], 'country_id', 'country_id' );
		self::maybe_add_index( $t['city'], 'state_id', 'state_id' );
	}

	/**
	 * @param string $table Full table name.
	 * @param string $index_name Index name.
	 * @param string $column Column name.
	 */
	private static function maybe_add_index( $table, $index_name, $column ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from our whitelist.
		$exists = $wpdb->get_results( $wpdb->prepare( "SHOW INDEX FROM `{$table}` WHERE Key_name = %s", $index_name ) );
		if ( ! empty( $exists ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE `{$table}` ADD INDEX `{$index_name}` (`{$column}`)" );
	}

	/**
	 * Whether geography pack is current.
	 */
	public static function is_data_current() {
		return get_option( 'tc_csca_data_version', '' ) === self::DATA_VERSION;
	}

	/**
	 * Seed location data only when countries table is empty.
	 *
	 * @return bool True if seed ran.
	 */
	public static function seed_data() {
		global $wpdb;
		$t = self::tables();

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['countries']}" );
		if ( $count > 0 ) {
			return false;
		}

		self::run_seed_inserts();
		update_option( 'tc_csca_data_version', self::DATA_VERSION );
		delete_option( 'tc_csca_data_update_available' );
		return true;
	}

	/**
	 * Wipe and reload full geography pack (admin-confirmed).
	 *
	 * @return bool
	 */
	public static function reseed_all() {
		global $wpdb;
		$t = self::tables();

		self::create_tables();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$t['city']}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$t['state']}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$t['countries']}" );

		self::ensure_indexes();
		self::run_seed_inserts();
		update_option( 'tc_csca_data_version', self::DATA_VERSION );
		delete_option( 'tc_csca_data_update_available' );
		return true;
	}

	/**
	 * Execute INSERT packs from includes/*-sql.php.
	 */
	private static function run_seed_inserts() {
		global $wpdb;
		$t = self::tables();

		$table_country = $t['countries'];
		$table_state   = $t['state'];
		$table_city    = $t['city'];

		include TC_CSCA_PATH . 'includes/countries-sql.php';
		include TC_CSCA_PATH . 'includes/states-sql.php';
		include TC_CSCA_PATH . 'includes/cities-sql.php';

		if ( ! empty( $country_insert ) ) {
			$wpdb->query( $country_insert ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		if ( ! empty( $state_insert ) ) {
			$wpdb->query( $state_insert ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		foreach ( array( 'city_insert', 'city_insert1', 'city_insert2', 'city_insert3', 'city_insert4', 'city_insert5', 'city_insert6' ) as $var ) {
			if ( ! empty( $$var ) ) {
				$wpdb->query( $$var ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}
	}

	/**
	 * @return array{countries:int,state:int,city:int,ok:bool,data_version:string,data_current:bool}
	 */
	public static function get_counts() {
		global $wpdb;
		$t = self::tables();

		$countries = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['countries']}" );
		$state     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['state']}" );
		$city      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['city']}" );
		$ok        = ( $countries > 0 && $state > 0 && $city > 0 );

		return array(
			'countries'    => $countries,
			'state'        => $state,
			'city'         => $city,
			'ok'           => $ok,
			'data_version' => (string) get_option( 'tc_csca_data_version', '' ),
			'data_current' => self::is_data_current(),
		);
	}

	/**
	 * Whether location tables look usable.
	 */
	public static function is_healthy() {
		$counts = self::get_counts();
		return ! empty( $counts['ok'] );
	}
}
