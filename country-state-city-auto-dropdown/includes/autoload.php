<?php
// Block direct access to the main plugin file.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/*
check contact form 7 plugin is active.
 */
if (class_exists('WPCF7')) {
    require TC_CSCA_PATH . 'includes/country-dropdown.php';
    require TC_CSCA_PATH . 'includes/state-dropdown.php';
    require TC_CSCA_PATH . 'includes/city-dropdown.php';
    require TC_CSCA_PATH . 'includes/include-js-css.php';
    require TC_CSCA_PATH . 'includes/ajax-actions.php';
    require TC_CSCA_PATH . 'includes/patch-setting-page.php';
}
