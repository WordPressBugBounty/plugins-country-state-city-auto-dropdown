<?php
/**
 * Front-end / admin assets — load only where needed (existing sites keep working).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localized strings + ajax config shared by front script.
 *
 * @return array
 */
function tc_csca_script_localize() {
	return array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'tc_csca_ajax_nonce' ),
		'i18n'     => array(
			'select_state'   => __( 'Select State', 'tc_csca' ),
			'select_city'    => __( 'Select City', 'tc_csca' ),
			'loading'        => __( 'Loading...', 'tc_csca' ),
			'state_not_found'=> __( 'State list not found', 'tc_csca' ),
			'city_not_found' => __( 'City list not found', 'tc_csca' ),
		),
	);
}

/**
 * Enqueue front script when CF7 enqueues its assets (forms on page).
 * Filter `tc_csca_enqueue_frontend` to force on/off.
 */
function tc_csca_embedCssJs() {
	/**
	 * Allow themes/plugins to force-load or skip front assets.
	 *
	 * @param bool $enqueue Default true when hooked from CF7.
	 */
	if ( ! apply_filters( 'tc_csca_enqueue_frontend', true ) ) {
		return;
	}

	wp_enqueue_script(
		'tc_csca-country-auto-script',
		TC_CSCA_URL . 'assets/js/script.js',
		array( 'jquery' ),
		TC_CSCA_VERSION,
		true
	);
	wp_localize_script( 'tc_csca-country-auto-script', 'tc_csca_auto_ajax', tc_csca_script_localize() );
	wp_enqueue_style(
		'tc_csca-country-auto-style',
		TC_CSCA_URL . 'assets/css/frontend.css',
		array(),
		TC_CSCA_VERSION
	);
}

// CF7 fires this when a form is present — preferred (not every site page).
add_action( 'wpcf7_enqueue_scripts', 'tc_csca_embedCssJs' );

/**
 * Fallback for builders / odd setups where wpcf7_enqueue_scripts may not run.
 * Loads if CF7 assets are present, or post content references a form.
 * Filter `tc_csca_force_frontend_assets` to always load (legacy behavior).
 */
function tc_csca_maybe_enqueue_fallback() {
	if ( wp_script_is( 'tc_csca-country-auto-script', 'enqueued' ) ) {
		return;
	}
	if ( is_admin() ) {
		return;
	}

	$force = apply_filters( 'tc_csca_force_frontend_assets', false );
	if ( $force ) {
		tc_csca_embedCssJs();
		return;
	}

	// Most reliable: CF7 already decided a form is on this page.
	if ( wp_script_is( 'contact-form-7', 'enqueued' ) || wp_script_is( 'wpcf7-recaptcha', 'enqueued' ) ) {
		tc_csca_embedCssJs();
		return;
	}

	global $post;
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$content = $post->post_content;
	if (
		has_shortcode( $content, 'contact-form-7' )
		|| has_shortcode( $content, 'contact-form' )
		|| false !== strpos( $content, 'country_auto' )
		|| false !== strpos( $content, 'wpcf7' )
	) {
		tc_csca_embedCssJs();
	}
}
add_action( 'wp_enqueue_scripts', 'tc_csca_maybe_enqueue_fallback', 30 );

function admin_tc_csca_embedCssJs( $hook ) {
	// Settings patch page + CF7 screens only (not every admin page).
	$on_patch = ( 'settings_page_tc-csca-patch-setting' === $hook );
	$on_cf7   = ( false !== strpos( (string) $hook, 'wpcf7' ) );

	if ( ! $on_patch && ! $on_cf7 ) {
		return;
	}

	wp_enqueue_script(
		'tc_csca-admin-country-auto-script',
		TC_CSCA_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		TC_CSCA_VERSION,
		true
	);
	wp_localize_script( 'tc_csca-admin-country-auto-script', 'tc_csca_auto_ajax', tc_csca_script_localize() );
	wp_enqueue_style(
		'tc_csca-admin-country-auto-css',
		TC_CSCA_URL . 'assets/css/admin-style.css',
		array(),
		TC_CSCA_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'admin_tc_csca_embedCssJs' );
