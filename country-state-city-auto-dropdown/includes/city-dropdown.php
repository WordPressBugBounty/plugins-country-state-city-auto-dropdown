<?php
/**
 * [city_auto] and [city_auto*]
 * Optional — forms may use country + state only.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wpcf7_init', 'tc_csca_add_form_tag_city_auto' );

function tc_csca_add_form_tag_city_auto() {
	wpcf7_add_form_tag(
		array( 'city_auto', 'city_auto*' ),
		'tc_csca_city_auto_form_tag_handler',
		array( 'name-attr' => true )
	);
}

function tc_csca_city_auto_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );
	$class            = wpcf7_form_controls_class( $tag->type, 'wpcf7-select city_auto' );
	$atts             = array();
	$atts['class']    = $tag->get_class_option( $class );
	$atts['id']       = $tag->get_id_option();
	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
	$atts['name']         = $tag->name;

	$group = tc_csca_get_tag_group( $tag );
	if ( $group ) {
		$atts['data-tc-group'] = $group;
	}

	$placeholder              = tc_csca_get_tag_placeholder( $tag, __( 'Select City', 'tc_csca' ) );
	$atts['data-placeholder'] = $placeholder;
	$atts['disabled']         = 'disabled';

	$atts    = wpcf7_format_atts( $atts );
	$cnt_tag = wpcf7_scan_form_tags( array( 'type' => array( 'country_auto*', 'country_auto' ) ) );
	$st_tag  = wpcf7_scan_form_tags( array( 'type' => array( 'state_auto*', 'state_auto' ) ) );

	if ( $cnt_tag && $st_tag ) {
		$html  = '<span class="wpcf7-form-control-wrap city_auto ' . esc_attr( $tag->name ) . '" data-name="' . esc_attr( $tag->name ) . '">';
		$html .= '<select ' . $atts . ' >';
		$html .= '<option value="0" data-id="0">' . esc_html( $placeholder ) . '</option>';
		$html .= '</select></span>';
	} else {
		$html = esc_html__( 'Error: Country and State fields must be available.', 'tc_csca' );
	}
	return $html;
}

add_filter( 'wpcf7_validate_city_auto', 'tc_csca_city_auto_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_city_auto*', 'tc_csca_city_auto_validation_filter', 10, 2 );

function tc_csca_city_auto_validation_filter( $result, $tag ) {
	$name  = $tag->name;
	$value = isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $tag->is_required() && ( '' === $value || '0' === $value ) ) {
		$result->invalidate( $tag, __( 'Please select a city.', 'tc_csca' ) );
	}

	return $result;
}

add_action( 'wpcf7_admin_init', 'tc_csca_add_tag_generator_city_auto', 25, 0 );

function tc_csca_add_tag_generator_city_auto() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add(
		'city_auto',
		__( 'city drop-down', 'tc_csca' ),
		'tc_csca_tag_generator_city_auto',
		array( 'version' => '2' )
	);
}

function tc_csca_tag_generator_city_auto( $contact_form, $options ) {
	$field_types = array(
		'city_auto' => array(
			'display_name' => __( 'City Dropdown', 'tc_csca' ),
			'heading'      => __( 'City Dropdown form-tag generator', 'tc_csca' ),
			'description'  => __( 'Optional. Generates a city dropdown. Skip this tag if you only need country and state.', 'tc_csca' ),
		),
	);

	$tgg       = new WPCF7_TagGeneratorGenerator( $options['content'] );
	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'header', array( 'class' => 'description-box' ) );
	$formatter->append_start_tag( 'h3' );
	$formatter->append_preformatted( esc_html( $field_types['city_auto']['heading'] ) );
	$formatter->end_tag( 'h3' );
	$formatter->append_start_tag( 'p' );
	$formatter->append_preformatted( esc_html( $field_types['city_auto']['description'] ) );
	$formatter->end_tag( 'header' );

	$formatter->append_start_tag( 'div', array( 'class' => 'control-box' ) );
	$formatter->call_user_func(
		static function () use ( $tgg, $field_types ) {
			$tgg->print(
				'field_type',
				array(
					'with_required'  => true,
					'select_options' => array(
						'city_auto' => $field_types['city_auto']['display_name'],
					),
				)
			);
			$tgg->print( 'field_name' );
			tc_csca_tag_generator_common_fields( $tgg, __( 'Select City', 'tc_csca' ) );
		}
	);
	$formatter->end_tag( 'div' );

	$formatter->append_start_tag( 'footer', array( 'class' => 'insert-box' ) );
	$formatter->call_user_func(
		static function () use ( $tgg ) {
			$tgg->print( 'insert_box_content' );
			$tgg->print( 'mail_tag_tip' );
		}
	);
	$formatter->print();
}
