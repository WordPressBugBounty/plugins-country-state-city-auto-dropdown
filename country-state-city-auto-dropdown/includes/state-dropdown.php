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
        $html = '<span class="wpcf7-form-control-wrap state_auto ' . $tag->name . '" data-name="'.$tag->name.'">';
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

add_action('wpcf7_admin_init', 'tc_csca_add_tag_generator_State_auto', 25,0);

function tc_csca_add_tag_generator_State_auto()
{
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add('state_auto', __('state drop-down', 'tc_csca'),
        'tc_csca_tag_generator_state_auto',array('version' => '2'));
}

function tc_csca_tag_generator_state_auto($contact_form, $options)
{
    $field_types = array(
		'state_auto' => array(
			'display_name' => __( 'State Dropdown', 'tc_csca' ),
			'heading' => __( 'State Dropdown form-tag generator', 'tc_csca' ),
			'description' => __( 'Generates a form-tag for a <a href="https://trustyplugins.com/">state dropdown</a>.', 'tc_csca' ),
		),
	);

	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );

	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'header', array(
		'class' => 'description-box',
	) );

	$formatter->append_start_tag( 'h3' );

	$formatter->append_preformatted(
		esc_html( $field_types['state_auto']['heading'] )
	);

	$formatter->end_tag( 'h3' );

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		wp_kses_data( $field_types['state_auto']['description'] )
	);

	$formatter->end_tag( 'header' );

	$formatter->append_start_tag( 'div', array(
		'class' => 'control-box',
	) );

	$formatter->call_user_func( static function () use ( $tgg, $field_types ) {
		$tgg->print( 'field_type', array(
			'with_required' => true,
			'select_options' => array(
				'state_auto' => $field_types['state_auto']['display_name'],
			),
		) );

		$tgg->print( 'field_name' );

		$tgg->print( 'class_attr' );

	
	} );

	$formatter->end_tag( 'div' );

	$formatter->append_start_tag( 'footer', array(
		'class' => 'insert-box',
	) );

	$formatter->call_user_func( static function () use ( $tgg, $field_types ) {
		$tgg->print( 'insert_box_content' );

		$tgg->print( 'mail_tag_tip' );
	} );

	$formatter->print();
}