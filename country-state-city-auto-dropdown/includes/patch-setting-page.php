<?php
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function tc_csca_register_patch_options_page()
{
    add_options_page('Page Title', 'Country State City Dropdown Patch', 'manage_options', 'tc-csca-patch-setting', 'tc_csca_patch_options_page');
}
add_action('admin_menu', 'tc_csca_register_patch_options_page');
?>
<?php function tc_csca_patch_options_page()
{
    ?>
  <div class="tc-patch-main">
  <h1><?php echo esc_html("Country State City Dropdown Patch"); ?>  </h1>
<?php
$obj = array(
        array("tip" => "This patch will update the country India's state West Bengal and their Cities.",
            "name" => "West Bengal",
            "country" => "101",
            "title" => "West Bengal (India)",
        ),
        array("tip" => "This patch will update the country India's state Ladakh and their Cities.",
            "name" => "Ladakh",
            "country" => "101",
            "title" => "Ladakh (India)",
        ),
    );
// echo "<pre>";
    // print_r($obj);
    // echo "</pre>";
    echo '<div class="patch-container">';
    echo '<div class="patch-manage">';
    echo '<select id="patch">';
    foreach ($obj as $key => $option) {
        echo '<option value="' . esc_attr($option['name']) . '" data-tip="' . esc_attr($option['tip']) . '" data-country="' . esc_attr($option['country']) . '">' . esc_attr($option['title']) . '</option>';
    }
    echo '</select>';
    echo "</div>";
    echo "<div class='select-info-tip'></div>";
    echo '<div class="patch-button">';
    echo '<input type="submit" name="submit" value="Update" id="update-patch" class= "button button-primary"/>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    ?>
    <!--<option id="west_bangal" name="west_bangal" value="4852" class="patch_opt">West Bangal</option>
    <option id="ladakh" name="ladakh" value="4853" class="patch_opt">Ladakh</option>-->

<?php
}?>
