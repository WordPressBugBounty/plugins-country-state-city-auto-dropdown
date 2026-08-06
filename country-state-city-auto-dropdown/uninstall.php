<?php
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
$option_name = 'tc_auto_plugin';
$version = 'tc_auto_plugin_version';
delete_option($option_name);
delete_option($version);
delete_option('tc_csca_data_version');
delete_option('tc_csca_data_update_available');
delete_option('tc_auto_plugin_admin_notices');
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}countries");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}state");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}city");