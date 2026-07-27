<?php
/**
 * Database helpers — safe for existing installs.
 * Never renames tables or re-seeds when data already exists.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TC_CSCA_DB {

	const DB_VERSION = '2.8.0';

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
		if ( version_compare( (string) $installed, self::DB_VERSION, '>=' ) ) {
			return;
		}

		self::create_tables();
		self::ensure_indexes();

		// Never re-seed if rows already exist (protects live sites).
		$counts = self::get_counts();
		if ( 0 === (int) $counts['countries'] ) {
			self::seed_data();
		}

		update_option( 'tc_auto_plugin_version', self::DB_VERSION );
		update_option( 'tc_auto_plugin', 'activated' );
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
		global $wpdb;
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

		$table_country = $t['countries'];
		$table_state   = $t['state'];
		$table_city    = $t['city'];

		include TC_CSCA_PATH . 'includes/countries-sql.php';
		include TC_CSCA_PATH . 'includes/states-sql.php';
		include TC_CSCA_PATH . 'includes/cities-sql.php';

		// Use query() for INSERTs — dbDelta is for schema only.
		if ( ! empty( $country_insert ) ) {
			$wpdb->query( $country_insert ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		if ( ! empty( $state_insert ) ) {
			$wpdb->query( $state_insert ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		foreach ( array( 'city_insert', 'city_insert1', 'city_insert2', 'city_insert3', 'city_insert4' ) as $var ) {
			if ( ! empty( $$var ) ) {
				$wpdb->query( $$var ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}

		return true;
	}

	/**
	 * @return array{countries:int,state:int,city:int,ok:bool}
	 */
	public static function get_counts() {
		global $wpdb;
		$t = self::tables();

		$countries = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['countries']}" );
		$state     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['state']}" );
		$city      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['city']}" );

		// Healthy enough for cascading dropdowns.
		$ok = ( $countries > 0 && $state > 0 && $city > 0 );

		return array(
			'countries' => $countries,
			'state'     => $state,
			'city'      => $city,
			'ok'        => $ok,
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
