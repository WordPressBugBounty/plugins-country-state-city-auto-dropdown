<?php
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
add_action('wp_ajax_tc_csca_get_states', 'tc_csca_get_states');
add_action("wp_ajax_nopriv_tc_csca_get_states", "tc_csca_get_states");
function tc_csca_get_states()
{
    check_ajax_referer('tc_csca_ajax_nonce', 'nonce_ajax');
    // if ( ! current_user_can( 'manage_options' ) ) {
    //     // If the user doesn't have the required capability, return an error message
    //     $response = [
    //         'status' => 200,
    //         'message' => 'Not Allowed',
    //     ];
    //     echo wp_json_encode($response);
    // }
    // else {
    global $wpdb;
    if (isset($_POST["cnt"])) {
        $cid = sanitize_text_field($_POST["cnt"]);
    }
    $qry=$wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "state where country_id='%1s' order by name asc", $cid);
    $states = $wpdb->get_results($qry);

    echo wp_json_encode($states);
//}
    wp_die();
}

add_action('wp_ajax_tc_csca_get_cities', 'tc_csca_get_cities');
add_action("wp_ajax_nopriv_tc_csca_get_cities", "tc_csca_get_cities");
function tc_csca_get_cities()
{
    check_ajax_referer('tc_csca_ajax_nonce', 'nonce_ajax');
    // if ( ! current_user_can( 'manage_options' ) ) {
    //     // If the user doesn't have the required capability, return an error message
    //     $response = [
    //         'status' => 200,
    //         'message' => 'Not Allowed',
    //     ];
    //     echo wp_json_encode($response);
    // }
    // else {
    global $wpdb;
    if (isset($_POST["sid"])) {
        $sid = sanitize_text_field($_POST["sid"]);
    }
    $qry=$wpdb->prepare("SELECT * FROM " . $wpdb->base_prefix . "city where state_id='%1s' order by name asc", $sid);
    $cities = $wpdb->get_results($qry);
    echo wp_json_encode($cities);
//}
    wp_die();
}

/***** START Update Patch function *****/

add_action('wp_ajax_tc_csca_patch_settings', 'tc_csca_patch_settings');
//add_action("wp_ajax_nopriv_tc_csca_patch_settings", "tc_csca_patch_settings");

function get_items_array()
{
    return $items = array("west_bengal" => array('Alipurduar', 'Bankura', 'Cooch Behar', 'Dakshin Dinajpur (South Dinajpur)', 'Darjeeling',
        'Hooghly', 'Howrah', 'Jalpaiguri', 'Jhargram', 'Kalimpong', 'Kolkata', 'Malda', 'Murshidabad', 'Nadia', 'North 24 Parganas', 'Paschim Medinipur (West Medinipur)',
        'Paschim (West) Burdwan (Bardhaman)', 'Purba Burdwan (Bardhaman)', 'Purba Medinipur (East Medinipur)', 'Purulia', 'South 24 Parganas', 'Uttar Dinajpur (North Dinajpur)'),
        "ladakh" => array('Kargil', 'Leh', 'Chuglamsar', 'Spituk'),
    );
}
function get_state_by_name($name, $table_state)
{
    global $wpdb;
   $qry=$wpdb->prepare("SELECT * FROM  %i where LOWER(name)=%s",$table_state,$name);
    return $wpdb->get_results($qry);
}
function get_cities_by_state_id($st_id, $table_city)
{
    global $wpdb;
    $qry=$wpdb->prepare("SELECT name FROM  %i where state_id=%s",$table_city,$st_id);
    return $wpdb->get_results($qry);
}
function update_city_table($ct, $st_id, $table_city)
{
    global $wpdb;
    $qry=$wpdb->prepare("insert into %i (`name`, `state_id`) values(%s,%s)",$table_city,$ct,$st_id);
    return $wpdb->query($qry);
}
function build_qry($name, $st_id)
{
    global $wpdb;
    $table_city = $wpdb->prefix . 'city';
    $values = array();
    $name = str_replace(' ', '_', $name);
    $items = get_items_array();
    $items = $items[$name];
    foreach ($items as $key => $value) {
        $values[] = $wpdb->prepare("(%s,%s)", $value, $st_id);
    }
    $query = "INSERT INTO $table_city (`name`, `state_id`) VALUES ";
    return $query .= implode(",", $values);
}
function tc_csca_patch_settings()
{
    check_ajax_referer('tc_csca_ajax_nonce', 'nonce_ajax');
   
    if ( ! current_user_can( 'manage_options' ) ) {
        // If the user doesn't have the required capability, return an error message
        $response = [
            'status' => 200,
            'message' => 'Not Allowed',
        ];
    }
    else {

    global $wpdb;
    $response = [
        'status' => 200,
        'message' => 'Already Updated',
    ];
    $table_state = $wpdb->prefix . 'state';
    $table_cnt = $wpdb->prefix . 'countries';
    $table_city = $wpdb->prefix . 'city';
    $tables = array("countries", "state", "city");

    foreach ($tables as $table) {
        $tbl = $wpdb->prefix . $table;
        $qry=$wpdb->prepare("SHOW INDEXES FROM $tbl");
        $rs = $wpdb->get_results($qry);
        if (!$rs) {
           
            $wpdb->query("ALTER TABLE $tbl CHANGE id id mediumint(8)	AUTO_INCREMENT PRIMARY KEY");
        }
    }
   

    if (isset($_POST['value'])) {
        $name = sanitize_text_field(strtolower($_POST["value"]));
        $name1 = sanitize_text_field($_POST["value"]);
        $country = sanitize_text_field($_POST["country"]);
        $table_state = $wpdb->prefix . 'state';
        $table_city = $wpdb->prefix . 'city';
        $state_res = get_state_by_name($name, $table_state);
       
        if (count($state_res) > 0) {
            $st_id = $state_res[0]->id;
            $cities = get_cities_by_state_id($st_id, $table_city);
            if ($cities) {
                $ct = [];
                foreach ($cities as $city) {
                    $ct[] = $city->name;
                }
                $st_name = str_replace(' ', '_', $name);
                $items = get_items_array();
                $items_cities = $items[$st_name];
                foreach ($items_cities as $city) {
                    if (!in_array($city, $ct)) {
                        $res = update_city_table($city, $st_id, $table_city);
                        return;
                        $response = [
                            'status' => 200,
                            'message' => 'Cities updated successfully in ' . $name1,
                        ];
                    }
                }
            } else {
                $qry = build_qry($name, $st_id);
                if ($wpdb->query($qry)) {
                    $response = [
                        'status' => 200,
                        'message' => 'New Cities inserted successfully in ' . $name,
                    ];
                }
            }

        } else {
            $state_insert = $wpdb->insert($table_state, array(
                'name' => $name1,
                'country_id' => $country,
            ));
            $qry=$wpdb->prepare("SELECT id FROM  %i where LOWER(name)=%s",$table_state,$name);
            $state_res = $wpdb->get_results($qry);
            $st_id = $state_res[0]->id;
            $qry = build_qry($name, $st_id);
            if ($wpdb->query($qry)) {
                $response = [
                    'status' => 200,
                    'message' => 'New State (' . $name . ') and Cities inserted successfully',
                ];
            }

        }
    }
}
    die(wp_json_encode($response));
    // wp_die();
}

/***** END Update Patch function *****/
