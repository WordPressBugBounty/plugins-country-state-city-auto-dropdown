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
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}countries");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}state");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}city");