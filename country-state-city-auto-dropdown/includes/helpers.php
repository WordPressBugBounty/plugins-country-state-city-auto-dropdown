<?php
/**
 * Shared helpers for form-tags and tag generators.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optional group:xxx from tag options.
 *
 * @param WPCF7_FormTag $tag Tag.
 * @return string
 */
function tc_csca_get_tag_group( $tag ) {
	if ( empty( $tag->options ) || ! is_array( $tag->options ) ) {
		return '';
	}
	foreach ( $tag->options as $option ) {
		if ( strpos( $option, 'group:' ) === 0 ) {
			return sanitize_key( substr( $option, 6 ) );
		}
	}
	return '';
}

/**
 * Placeholder text: CF7-style `placeholder "Text"` / values, else default.
 *
 * @param WPCF7_FormTag $tag     Tag.
 * @param string        $default Default label.
 * @return string
 */
function tc_csca_get_tag_placeholder( $tag, $default ) {
	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$value = isset( $tag->values[0] ) ? (string) $tag->values[0] : '';
		if ( '' !== $value ) {
			return $value;
		}
	}

	// Manual option: placeholder:Select_Country (underscores → spaces).
	$opt = $tag->get_option( 'placeholder', '', true );
	if ( is_string( $opt ) && '' !== $opt ) {
		return str_replace( '_', ' ', $opt );
	}

	return $default;
}

/**
 * Extra controls for CF7 Tag Generator v2: id, placeholder, group.
 *
 * @param WPCF7_TagGeneratorGenerator $tgg Generator.
 * @param string                      $default_placeholder Default placeholder hint.
 */
function tc_csca_tag_generator_common_fields( $tgg, $default_placeholder = '' ) {
	$tgg->print( 'id_attr' );
	$tgg->print( 'class_attr' );
	$tgg->print(
		'default_value',
		array(
			'title'            => __( 'Placeholder', 'tc_csca' ),
			'with_placeholder' => true,
		)
	);
	?>
	<fieldset>
		<legend><?php echo esc_html__( 'Group (optional)', 'tc_csca' ); ?></legend>
		<input type="text" data-tag-part="option" data-tag-option="group:" pattern="[A-Za-z][A-Za-z0-9_\-]*" placeholder="billing" />
		<p class="description" style="margin:6px 0 0;">
			<?php echo esc_html__( 'Use the same group on country, state, and city when you have multiple location sets in one form.', 'tc_csca' ); ?>
		</p>
		<?php if ( $default_placeholder ) : ?>
			<p class="description" style="margin:6px 0 0;">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: example placeholder text */
						__( 'Tip: enter “%s”, then check “Use this text as the placeholder.”', 'tc_csca' ),
						$default_placeholder
					)
				);
				?>
			</p>
		<?php endif; ?>
	</fieldset>
	<?php
}
