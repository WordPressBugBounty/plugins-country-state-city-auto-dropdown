<?php
/**
 * Settings: data health, optional reinstall, legacy India patches.
 * Existing users: patches remain; reinstall never wipes without Force.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tc_csca_register_patch_options_page() {
	add_options_page(
		__( 'Country State City Dropdown', 'tc_csca' ),
		__( 'Country State City Dropdown', 'tc_csca' ),
		'manage_options',
		'tc-csca-patch-setting',
		'tc_csca_patch_options_page'
	);
}
add_action( 'admin_menu', 'tc_csca_register_patch_options_page' );

function tc_csca_patch_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$counts = class_exists( 'TC_CSCA_DB' ) ? TC_CSCA_DB::get_counts() : array(
		'countries' => 0,
		'state'     => 0,
		'city'      => 0,
		'ok'        => false,
	);
	?>
	<div class="wrap tc-csca-settings">
		<h1><?php echo esc_html__( 'Country State City Dropdown', 'tc_csca' ); ?></h1>

		<div class="tc-patch-main">
			<h2><?php echo esc_html__( 'Data health', 'tc_csca' ); ?></h2>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: countries 2: states 3: cities */
						__( 'Countries: %1$d · States: %2$d · Cities: %3$d', 'tc_csca' ),
						(int) $counts['countries'],
						(int) $counts['state'],
						(int) $counts['city']
					)
				);
				?>
				<?php if ( ! empty( $counts['ok'] ) ) : ?>
					<span class="tc-csca-ok"><?php echo esc_html__( 'OK', 'tc_csca' ); ?></span>
				<?php else : ?>
					<span class="tc-csca-bad"><?php echo esc_html__( 'Incomplete — forms may show empty lists.', 'tc_csca' ); ?></span>
				<?php endif; ?>
			</p>
			<p class="description">
				<?php echo esc_html__( 'Updating the plugin never deletes your location tables. Use “Install missing data” only if counts are zero. “Force reinstall” wipes and reloads the default dataset — use only if lists are corrupted.', 'tc_csca' ); ?>
			</p>
			<p class="patch-button">
				<button type="button" id="tc-csca-install-data" class="button button-primary">
					<?php echo esc_html__( 'Install missing data', 'tc_csca' ); ?>
				</button>
				<button type="button" id="tc-csca-force-reinstall" class="button">
					<?php echo esc_html__( 'Force reinstall', 'tc_csca' ); ?>
				</button>
			</p>
			<div id="tc-csca-data-response" class="tc_response_update" style="display:none;"></div>
		</div>

		<div class="tc-patch-main">
			<h2><?php echo esc_html__( 'Regional patches', 'tc_csca' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Optional fixes for specific missing regions. Safe to run more than once.', 'tc_csca' ); ?>
			</p>
			<?php
			$obj = array(
				array(
					'tip'     => __( 'Updates India’s West Bengal state and cities.', 'tc_csca' ),
					'name'    => 'West Bengal',
					'country' => '101',
					'title'   => 'West Bengal (India)',
				),
				array(
					'tip'     => __( 'Updates India’s Ladakh state and cities.', 'tc_csca' ),
					'name'    => 'Ladakh',
					'country' => '101',
					'title'   => 'Ladakh (India)',
				),
			);
			echo '<div class="patch-container">';
			echo '<div class="patch-manage">';
			echo '<select id="patch">';
			foreach ( $obj as $option ) {
				printf(
					'<option value="%1$s" data-tip="%2$s" data-country="%3$s">%4$s</option>',
					esc_attr( $option['name'] ),
					esc_attr( $option['tip'] ),
					esc_attr( $option['country'] ),
					esc_html( $option['title'] )
				);
			}
			echo '</select>';
			echo '</div>';
			echo '<div class="select-info-tip"></div>';
			echo '<div class="patch-button">';
			echo '<input type="submit" name="submit" value="' . esc_attr__( 'Update patch', 'tc_csca' ) . '" id="update-patch" class="button button-primary"/>';
			echo '</div>';
			echo '</div>';
			?>
		</div>

		<div class="tc-patch-main">
			<h2><?php echo esc_html__( 'Pro', 'tc_csca' ); ?></h2>
			<p><?php echo esc_html__( 'Need default country by IP, country whitelist, Other State/City fields, or support for any form builder?', 'tc_csca' ); ?></p>
			<p><a class="button" href="https://trustyplugins.com" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'See Pro features', 'tc_csca' ); ?></a></p>
		</div>
	</div>
	<?php
}
