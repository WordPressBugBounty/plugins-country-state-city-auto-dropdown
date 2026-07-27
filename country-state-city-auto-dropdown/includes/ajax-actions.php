<?php
/**
 * AJAX handlers — public endpoints stay the same for existing front-end JS.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_tc_csca_get_states', 'tc_csca_get_states' );
add_action( 'wp_ajax_nopriv_tc_csca_get_states', 'tc_csca_get_states' );

function tc_csca_get_states() {
	check_ajax_referer( 'tc_csca_ajax_nonce', 'nonce_ajax' );

	global $wpdb;
	$cid = isset( $_POST['cnt'] ) ? absint( $_POST['cnt'] ) : 0;
	if ( $cid < 1 ) {
		wp_send_json( array() );
	}

	$t      = TC_CSCA_DB::tables();
	$states = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, name FROM {$t['state']} WHERE country_id = %d ORDER BY name ASC",
			$cid
		)
	);

	wp_send_json( $states ? $states : array() );
}

add_action( 'wp_ajax_tc_csca_get_cities', 'tc_csca_get_cities' );
add_action( 'wp_ajax_nopriv_tc_csca_get_cities', 'tc_csca_get_cities' );

function tc_csca_get_cities() {
	check_ajax_referer( 'tc_csca_ajax_nonce', 'nonce_ajax' );

	global $wpdb;
	$sid = isset( $_POST['sid'] ) ? absint( $_POST['sid'] ) : 0;
	if ( $sid < 1 ) {
		wp_send_json( array() );
	}

	$t      = TC_CSCA_DB::tables();
	$cities = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, name FROM {$t['city']} WHERE state_id = %d ORDER BY name ASC",
			$sid
		)
	);

	wp_send_json( $cities ? $cities : array() );
}

/***** START Update Patch function *****/

add_action( 'wp_ajax_tc_csca_patch_settings', 'tc_csca_patch_settings' );
add_action( 'wp_ajax_tc_csca_reinstall_data', 'tc_csca_reinstall_data' );

/**
 * Re-seed only when tables are empty OR admin explicitly confirms wipe+reload.
 * Default path: seed only if unhealthy/empty — never wipe existing data.
 */
function tc_csca_reinstall_data() {
	check_ajax_referer( 'tc_csca_ajax_nonce', 'nonce_ajax' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json(
			array(
				'status'  => 403,
				'message' => __( 'Not allowed.', 'tc_csca' ),
			)
		);
	}

	$force = ! empty( $_POST['force'] );

	if ( ! $force && TC_CSCA_DB::is_healthy() ) {
		wp_send_json(
			array(
				'status'  => 200,
				'message' => __( 'Location data already looks complete. Nothing was changed. Use “Force reinstall” only if lists are wrong.', 'tc_csca' ),
			)
		);
	}

	if ( $force ) {
		global $wpdb;
		$t = TC_CSCA_DB::tables();
		// Truncate then reseed — only when admin opts in.
		$wpdb->query( "TRUNCATE TABLE {$t['city']}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$t['state']}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$t['countries']}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	TC_CSCA_DB::create_tables();
	TC_CSCA_DB::ensure_indexes();
	$ran = TC_CSCA_DB::seed_data();
	$counts = TC_CSCA_DB::get_counts();

	wp_send_json(
		array(
			'status'  => 200,
			'message' => $ran || $force
				? sprintf(
					/* translators: 1: countries 2: states 3: cities */
					__( 'Data installed. Countries: %1$d, States: %2$d, Cities: %3$d.', 'tc_csca' ),
					$counts['countries'],
					$counts['state'],
					$counts['city']
				)
				: __( 'Countries table was not empty, so existing data was kept. Use Force reinstall to replace it.', 'tc_csca' ),
			'counts'  => $counts,
		)
	);
}

function get_items_array() {
	return array(
		'west_bengal' => array(
			'Alipurduar',
			'Bankura',
			'Cooch Behar',
			'Dakshin Dinajpur (South Dinajpur)',
			'Darjeeling',
			'Hooghly',
			'Howrah',
			'Jalpaiguri',
			'Jhargram',
			'Kalimpong',
			'Kolkata',
			'Malda',
			'Murshidabad',
			'Nadia',
			'North 24 Parganas',
			'Paschim Medinipur (West Medinipur)',
			'Paschim (West) Burdwan (Bardhaman)',
			'Purba Burdwan (Bardhaman)',
			'Purba Medinipur (East Medinipur)',
			'Purulia',
			'South 24 Parganas',
			'Uttar Dinajpur (North Dinajpur)',
		),
		'ladakh'      => array( 'Kargil', 'Leh', 'Chuglamsar', 'Spituk' ),
	);
}

function get_state_by_name( $name, $table_state ) {
	global $wpdb;
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM `{$table_state}` WHERE LOWER(name) = %s",
			$name
		)
	);
}

function get_cities_by_state_id( $st_id, $table_city ) {
	global $wpdb;
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT name FROM `{$table_city}` WHERE state_id = %d",
			absint( $st_id )
		)
	);
}

function update_city_table( $ct, $st_id, $table_city ) {
	global $wpdb;
	return $wpdb->insert(
		$table_city,
		array(
			'name'     => $ct,
			'state_id' => absint( $st_id ),
		),
		array( '%s', '%d' )
	);
}

function build_qry( $name, $st_id ) {
	global $wpdb;
	$table_city = $wpdb->prefix . 'city';
	$values     = array();
	$name       = str_replace( ' ', '_', $name );
	$items      = get_items_array();
	if ( empty( $items[ $name ] ) ) {
		return '';
	}
	$items = $items[ $name ];
	foreach ( $items as $value ) {
		$values[] = $wpdb->prepare( '(%s,%d)', $value, absint( $st_id ) );
	}
	$query = "INSERT INTO {$table_city} (`name`, `state_id`) VALUES ";
	return $query . implode( ',', $values );
}

function tc_csca_patch_settings() {
	check_ajax_referer( 'tc_csca_ajax_nonce', 'nonce_ajax' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json(
			array(
				'status'  => 403,
				'message' => __( 'Not allowed.', 'tc_csca' ),
			)
		);
	}

	global $wpdb;
	$response = array(
		'status'  => 200,
		'message' => __( 'Already updated.', 'tc_csca' ),
	);

	$t = TC_CSCA_DB::tables();
	TC_CSCA_DB::ensure_indexes();

	// Repair missing PRIMARY KEY on legacy installs (non-destructive when already set).
	foreach ( $t as $tbl ) {
		$rs = $wpdb->get_results( "SHOW INDEXES FROM `{$tbl}` WHERE Key_name = 'PRIMARY'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $rs ) {
			$wpdb->query( "ALTER TABLE `{$tbl}` MODIFY id mediumint(8) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	if ( ! isset( $_POST['value'] ) ) {
		wp_send_json( $response );
	}

	$name1   = sanitize_text_field( wp_unslash( $_POST['value'] ) );
	$name    = strtolower( $name1 );
	$country = isset( $_POST['country'] ) ? absint( $_POST['country'] ) : 0;

	$table_state = $t['state'];
	$table_city  = $t['city'];
	$state_res   = get_state_by_name( $name, $table_state );

	if ( count( $state_res ) > 0 ) {
		$st_id  = $state_res[0]->id;
		$cities = get_cities_by_state_id( $st_id, $table_city );
		$ct     = array();
		if ( $cities ) {
			foreach ( $cities as $city ) {
				$ct[] = $city->name;
			}
		}
		$st_name      = str_replace( ' ', '_', $name );
		$items        = get_items_array();
		$items_cities = isset( $items[ $st_name ] ) ? $items[ $st_name ] : array();
		$added        = 0;
		foreach ( $items_cities as $city ) {
			if ( ! in_array( $city, $ct, true ) ) {
				update_city_table( $city, $st_id, $table_city );
				$added++;
			}
		}
		if ( $added > 0 ) {
			$response['message'] = sprintf(
				/* translators: %s: state name */
				__( 'Cities updated successfully in %s.', 'tc_csca' ),
				$name1
			);
		} elseif ( empty( $ct ) ) {
			$qry = build_qry( $name, $st_id );
			if ( $qry && $wpdb->query( $qry ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$response['message'] = sprintf(
					/* translators: %s: state name */
					__( 'New cities inserted successfully in %s.', 'tc_csca' ),
					$name1
				);
			}
		}
	} else {
		$wpdb->insert(
			$table_state,
			array(
				'name'       => $name1,
				'country_id' => $country,
			),
			array( '%s', '%d' )
		);
		$state_res = get_state_by_name( $name, $table_state );
		$st_id     = $state_res[0]->id;
		$qry       = build_qry( $name, $st_id );
		if ( $qry && $wpdb->query( $qry ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$response['message'] = sprintf(
				/* translators: %s: state name */
				__( 'New state (%s) and cities inserted successfully.', 'tc_csca' ),
				$name1
			);
		}
	}

	wp_send_json( $response );
}
