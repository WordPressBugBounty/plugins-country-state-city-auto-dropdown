<?php
/*
Plugin Name: Country State City Dropdown CF7
Description: Add country, state and city auto drop down for CONTACT FORM 7. State will auto populate in SELECT field according to selected country and city will auto populate according to selected state.
Version: 2.7.4
Author: Trusty Plugins
Author URI: https://trustyplugins.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Domain Path: /languages
Text Domain: tc_csca
 */
// Block direct access to the main plugin file.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TC_CSCA_Plugin
{

    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'tc_load_plugin_textdomain'));
        if (class_exists('WPCF7')) {
            $this->tc_plugin_constants();
            require_once TC_CSCA_PATH . 'includes/autoload.php';

            add_action('admin_enqueue_scripts', array($this, 'add_scripts_and_styles'));

        } else {
            add_action('admin_notices', array($this, 'tc_admin_error_notice'));
        }
        // if(!get_option('tc_auto_plugin_version')
        // {
        // }

    }

    public function tc_load_plugin_textdomain()
    {
        load_plugin_textdomain('tc_csca', false, basename(dirname(__FILE__)) . '/languages/');
    }

    /*
    register admin notice if contact form 7 is not active.
     */

    public function tc_admin_error_notice()
    {
        /* translators: %1$s is replaced with "string" */
        $message = sprintf(esc_html__('The %1$sCountry State City Dropdown CF7%2$s plugin requires %1$sContact form 7%2$s plugin active to run properly. Please install %1$scontact form 7%2$s and activate', 'tc_csca'), '<strong>', '</strong>');

        printf('<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post($message));

    }

    /*
    set plugin constants
     */
    public function tc_plugin_constants()
    {

        if (!defined('TC_CSCA_PATH')) {
            define('TC_CSCA_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('TC_CSCA_URL')) {
            define('TC_CSCA_URL', plugin_dir_url(__FILE__));
        }

    }
    /*
    Enqueue Scripts For ADMIN
     */
    public function add_scripts_and_styles($hook)
    {
        // Only load on ?page=wpcf7&post=??
        if (strpos($hook, 'wpcf7') !== false && isset($_GET['post'])) {
            $localize = array('csca_metabox' => tc_auto_plugin_add_post_metabox());
            wp_enqueue_script('tc_csca-country-auto-script-meta', TC_CSCA_URL . 'assets/js/script-meta.min.js', array('jquery'),'2.7.4', array(
                'in_footer'  => true,
            ));
            wp_localize_script('tc_csca-country-auto-script-meta', 'tc_csca_auto_ajax_meta', $localize);
        }
    }
}

// Instantiate the plugin class.
$tc_csca_plugin = new TC_CSCA_Plugin();
register_activation_hook(__FILE__, 'tc_create_db');
function tc_create_db()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_country = $wpdb->prefix . 'countries';
    $table_state = $wpdb->prefix . 'state';
    $table_city = $wpdb->prefix . 'city';
    $country_create = "CREATE TABLE IF NOT EXISTS $table_country (
  `id` mediumint(8) AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $state_create = "CREATE TABLE IF NOT EXISTS $table_state (
  `id` mediumint(8) AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `country_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;";
    $city_create = "CREATE TABLE IF NOT EXISTS $table_city (
  `id` mediumint(8) AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `state_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;";
    include_once 'includes/countries-sql.php';
    include_once 'includes/states-sql.php';
    include_once 'includes/cities-sql.php';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($country_create);
    dbDelta($state_create);
    dbDelta($city_create);
    if (!get_option('tc_auto_plugin')) {
        update_option('tc_auto_plugin', 'installed');
    }
    if (get_option('tc_auto_plugin') == 'installed') {
        dbDelta($country_insert);
        dbDelta($state_insert);
        dbDelta($city_insert);
        dbDelta($city_insert1);
        dbDelta($city_insert2);
        dbDelta($city_insert3);
        dbDelta($city_insert4);
    }
    update_option('tc_auto_plugin', 'activated');
    update_option('tc_auto_plugin_version', '2.7.4');
    $notices[] = "<b>Trusty Plugins</b> : <b style='color:#bb2c2c;'>CHEERS!!</b> Test and Rate ★★★★★ Our Plugin <b>Country State City Dropdown CF7</b> on <a href='https://wordpress.org/support/plugin/country-state-city-auto-dropdown/reviews/?filter=5#new-post' style='color:#bb2c2c;font-weight:bold;' target='_blank'>Wordpress.org</a> or <a href='http://trustyplugins.com#extend' style='color:#bb2c2c;font-weight:bold;' target='_blank'>BUY PRO</a> to extend its features.";
    update_option('tc_auto_plugin_admin_notices', $notices);
}

/* PROMO CODE */

add_action('admin_notices', 'tc_auto_plugin_admin_notices');
function tc_auto_plugin_admin_notices()
{
    if ($notices = get_option('tc_auto_plugin_admin_notices')) {
        foreach ($notices as $notice) {
            $esc_arr = array( 'a' => array('href'=>array(),'style'=>array()), 'b' => array('style'=>array()), 'strong' => array(),'style' => array() );
            echo "<div class='updated'><p>".wp_kses($notice,$esc_arr)."</p></div>";
        }
       delete_option('tc_auto_plugin_admin_notices');
    }
}
function tc_auto_plugin_add_post_metabox()
{
    ob_start();
    ?>
    <div id="tc_auto_plugin_meta_pro" class="tc_auto_plugin_meta_pro postbox">
      <h3 style='background-color:#f7f7f7'><?php echo esc_html('Country State City Dropdown CF7');?></h3>
      <div class="inside">
        <p>Like this plugin?<br><a href="https://wordpress.org/support/plugin/country-state-city-auto-dropdown/reviews/?filter=5#new-post" target="_blank" style='font-weight: bold;color: #bb2c2c;'>Rate it &rarr;</a></p>
        <p>Having Any Issue?<br><a href="https://wordpress.org/support/plugin/country-state-city-auto-dropdown/" target="_blank" style='font-weight: bold;color: #bb2c2c;'>Get support &rarr;</a></p>
          <p>You can check Live Demo of <a href="https://trustyplugins.com" target="_blank" style='font-weight: bold;color: #bb2c2c;'>PRO</a> Plugin too. You can extend the functionality of this plugin with these awesome features.<br>
		  <ol>
		  <li><b>Auto Set Default Country by user's IP.</b></li>
		  <li><b>Set Specific Default Country for your Contact Form.</b></li>
		  <li><b>Select only specific Countries to show in the dropdown.</b></li>
		  <li><b>Users can enter custom State/City if their State/City is not existed in the dropdown.</b></li>
		  <li><b>Custom placeholders and error message for Country/State/City Fields.</b></li>
		  </ol>
		  <a href="https://trustyplugins.com" target="_blank" style='font-weight: bold;background: #bb2c2c;color: #fff;padding: 8px 12px;display: inline-block;text-decoration: none;width: 90%;text-align: center;'>BUY PRO &rarr;</a></p>
      </div>
    </div>
    <?php
$output = ob_get_contents();
    ob_end_clean();
    return $output;
}
add_filter('plugin_row_meta', 'tc_csca_plugin_row_meta', 10, 2);
function tc_csca_plugin_row_meta($links, $file)
{
    if (plugin_basename(__FILE__) == $file) {
        $row_meta = array(
            'Buy PRO' => '<a href="' . esc_url('https://trustyplugins.com/') . '" target="_blank" aria-label="' . esc_attr__('Plugin Additional Links', 'tc_csca') . '" style="color:red;">' . esc_html__('Buy Pro', 'tc_csca') . '</a>',
        );
        return array_merge($links, $row_meta);
    }
    return (array) $links;
}
?>