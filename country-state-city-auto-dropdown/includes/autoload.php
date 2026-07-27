<?php
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WPCF7' ) ) {
	require_once TC_CSCA_PATH . 'includes/class-tc-csca-db.php';
	require_once TC_CSCA_PATH . 'includes/helpers.php';
	require_once TC_CSCA_PATH . 'includes/country-dropdown.php';
	require_once TC_CSCA_PATH . 'includes/state-dropdown.php';
	require_once TC_CSCA_PATH . 'includes/city-dropdown.php';
	require_once TC_CSCA_PATH . 'includes/include-js-css.php';
	require_once TC_CSCA_PATH . 'includes/ajax-actions.php';
	require_once TC_CSCA_PATH . 'includes/patch-setting-page.php';
}
