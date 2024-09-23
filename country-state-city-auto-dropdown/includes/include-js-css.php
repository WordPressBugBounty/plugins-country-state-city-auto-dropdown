<?php
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/* Include all js and css files */
function tc_csca_embedCssJs()
{
    wp_enqueue_script('tc_csca-country-auto-script', TC_CSCA_URL . 'assets/js/script.js', array('jquery'),'2.7.3', array(
        'in_footer'  => true,
    ));
    wp_localize_script('tc_csca-country-auto-script', 'tc_csca_auto_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('tc_csca_ajax_nonce')));
}
add_action('wp_enqueue_scripts', 'tc_csca_embedCssJs');

function admin_tc_csca_embedCssJs()
{
    wp_enqueue_script('tc_csca-admin-country-auto-script', TC_CSCA_URL . 'assets/js/admin.js', array('jquery'),'2.7.3', array(
        'in_footer'  => true,
    ));
    wp_localize_script('tc_csca-admin-country-auto-script', 'tc_csca_auto_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('tc_csca_ajax_nonce')));
    wp_enqueue_style('tc_csca-admin-country-auto-css', TC_CSCA_URL . 'assets/css/admin-style.css', '','2.7.3');
}

add_action('admin_enqueue_scripts', 'admin_tc_csca_embedCssJs');
