<?php
/**
 ** [state_auto] and [state_auto*]
 **/

/* form_tag handler */
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
add_action('wpcf7_init', 'tc_csca_add_form_tag_stateauto');

function tc_csca_add_form_tag_stateauto()
{
    wpcf7_add_form_tag(
        array('state_auto', 'state_auto*'),
        'tc_csca_state_auto_form_tag_handler', array('name-attr' => true));
}

function tc_csca_state_auto_form_tag_handler($tag)
{
    if (empty($tag->name)) {
        return '';
    }
    // var_dump($tag);
    $options = $tag->options;
    $validation_error = wpcf7_get_validation_error($tag->name);
    $class = wpcf7_form_controls_class($tag->type, 'wpcf7-select state_auto');
    $atts = array();
    $atts['class'] = $tag->get_class_option($class);
    $atts['id'] = $tag->get_id_option();
    if ($tag->is_required()) {
        $atts['aria-required'] = 'true';
    }
    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $atts['name'] = $tag->name;
    $atts = wpcf7_format_atts($atts);
    $cnt_tag = wpcf7_scan_form_tags(array('type' => array('country_auto*', 'country_auto')));
    if ($cnt_tag) {
        $html = '<span class="wpcf7-form-control-wrap state_auto ' . $tag->name . '">';
        $html .= '<select ' . $atts . ' >';
        $html .= '<option value="0" data-id="0">Select State</option>';
        $html .= '</select></span>';
    } else {
        $html = 'Error: Country Field Must be available.';
    }
    return $html;
}

/* Validation filter */

add_filter('wpcf7_validate_state_auto', 'tc_csca_state_auto_validation_filter', 10, 2);
add_filter('wpcf7_validate_state_auto*', 'tc_csca_state_auto_validation_filter', 10, 2);

function tc_csca_state_auto_validation_filter($result, $tag)
{
    $type = $tag->type;
    $name = $tag->name;
    $value = sanitize_text_field($_POST[$name]);
    if ($tag->is_required() && '0' == $value) {
        $result->invalidate($tag, 'Please Select State.');
    }

    return $result;
}

/* Tag generator */

add_action('wpcf7_admin_init', 'tc_csca_add_tag_generator_State_auto', 20);

function tc_csca_add_tag_generator_State_auto()
{
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add('state_auto', __('state drop-down', 'tc_csca'),
        'tc_csca_tag_generator_state_auto');
}

function tc_csca_tag_generator_state_auto($contact_form, $args = '')
{
    $args = wp_parse_args($args, array());
    $type = 'state_auto';

    $description = __("Generate a form-tag for a country dorp list with flags icon text input field.", 'tc_csca');
    $desc_link = '';
    ?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf(esc_html($description), esc_html($desc_link)); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html(__('Field type', 'tc_csca')); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html(__('Field type', 'tc_csca')); ?></legend>
		<label><input type="checkbox" name="required" /> <?php echo esc_html(__('Required field', 'tc_csca')); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-name'); ?>"><?php echo esc_html(__('Name', 'tc_csca')); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'] . '-name'); ?>" /></td>
	</tr>


	<tr>
	<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-id'); ?>"><?php echo esc_html(__('Id attribute', 'tc_csca')); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr($args['content'] . '-id'); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr($args['content'] . '-class'); ?>"><?php echo esc_html(__('Class attribute', 'tc_csca')); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr($args['content'] . '-class'); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo esc_html($type); ?>" class="tag code" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', 'tc_csca')); ?>" />
	</div>

	<br class="clear" />
    <?php /* translators: %s is replaced with "string" */  ?>
	<p class="description mail-tag"><label for="<?php echo esc_attr($args['content'] . '-mailtag'); ?>"><?php echo sprintf(esc_html(__("To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'tc_csca')), '<strong><span class="mail-tag"></span></strong>'); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr($args['content'] . '-mailtag'); ?>" /></label></p>
</div>
<?php
}