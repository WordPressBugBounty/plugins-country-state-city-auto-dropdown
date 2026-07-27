<?php
/*
Plugin Name: Country State City Dropdown CF7
Description: Add country, state and city auto drop down for CONTACT FORM 7. State will auto populate in SELECT field according to selected country and city will auto populate according to selected state.
Version: 2.8.0
Author: Trusty Plugins
Author URI: https://trustyplugins.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Domain Path: /languages
Text Domain: tc_csca
 */
// Block direct access to the main plugin file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TC_CSCA_VERSION' ) ) {
	define( 'TC_CSCA_VERSION', '2.8.0' );
}

class TC_CSCA_Plugin {

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'tc_load_plugin_textdomain' ) );
		if ( class_exists( 'WPCF7' ) ) {
			$this->tc_plugin_constants();
			require_once TC_CSCA_PATH . 'includes/autoload.php';

			add_action( 'plugins_loaded', array( 'TC_CSCA_DB', 'maybe_upgrade' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts_and_styles' ) );
			add_action( 'admin_notices', array( $this, 'health_notice' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'tc_admin_error_notice' ) );
		}
	}

	public function tc_load_plugin_textdomain() {
		load_plugin_textdomain( 'tc_csca', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	public function tc_admin_error_notice() {
		$message = sprintf(
			/* translators: %1$s and %2$s are HTML strong tags */
			esc_html__( 'The %1$sCountry State City Dropdown CF7%2$s plugin requires %1$sContact Form 7%2$s plugin active to run properly. Please install %1$sContact Form 7%2$s and activate', 'tc_csca' ),
			'<strong>',
			'</strong>'
		);
		printf( '<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * One-time notice if location tables look empty (broken / incomplete install).
	 */
	public function health_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! class_exists( 'TC_CSCA_DB' ) ) {
			return;
		}
		if ( TC_CSCA_DB::is_healthy() ) {
			return;
		}

		$counts = TC_CSCA_DB::get_counts();
		$url    = admin_url( 'options-general.php?page=tc-csca-patch-setting' );
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post(
				sprintf(
					/* translators: 1: countries count 2: states count 3: cities count 4: settings URL */
					__( '<strong>Country State City Dropdown CF7:</strong> Location data looks incomplete (Countries: %1$d, States: %2$d, Cities: %3$d). Existing forms may show empty lists. You can reinstall data from <a href="%4$s">Settings → Country State City Dropdown</a> without changing your form tags.', 'tc_csca' ),
					(int) $counts['countries'],
					(int) $counts['state'],
					(int) $counts['city'],
					esc_url( $url )
				)
			)
		);
	}

	public function tc_plugin_constants() {
		if ( ! defined( 'TC_CSCA_PATH' ) ) {
			define( 'TC_CSCA_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'TC_CSCA_URL' ) ) {
			define( 'TC_CSCA_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	public function add_scripts_and_styles( $hook ) {
		// Only on CF7 form edit screens.
		if ( false === strpos( $hook, 'wpcf7' ) || ! isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$localize = array( 'csca_metabox' => tc_auto_plugin_add_post_metabox() );
		wp_enqueue_script(
			'tc_csca-country-auto-script-meta',
			TC_CSCA_URL . 'assets/js/script-meta.min.js',
			array( 'jquery' ),
			TC_CSCA_VERSION,
			true
		);
		wp_localize_script( 'tc_csca-country-auto-script-meta', 'tc_csca_auto_ajax_meta', $localize );
	}
}

$tc_csca_plugin = new TC_CSCA_Plugin();

register_activation_hook( __FILE__, 'tc_create_db' );

/**
 * Activation: create schema + seed only if empty (safe for re-activation).
 */
function tc_create_db() {
	if ( ! defined( 'TC_CSCA_PATH' ) ) {
		define( 'TC_CSCA_PATH', plugin_dir_path( __FILE__ ) );
	}
	if ( ! defined( 'TC_CSCA_VERSION' ) ) {
		define( 'TC_CSCA_VERSION', '2.8.0' );
	}

	require_once TC_CSCA_PATH . 'includes/class-tc-csca-db.php';

	TC_CSCA_DB::create_tables();
	TC_CSCA_DB::ensure_indexes();

	$seeded = false;
	if ( ! TC_CSCA_DB::is_healthy() ) {
		$seeded = TC_CSCA_DB::seed_data();
	}

	update_option( 'tc_auto_plugin', 'activated' );
	update_option( 'tc_auto_plugin_version', TC_CSCA_VERSION );

	// Soft one-time notice — dismissible via delete_option after display.
	$notices   = get_option( 'tc_auto_plugin_admin_notices', array() );
	$notices   = is_array( $notices ) ? $notices : array();
	$notices[] = $seeded
		? __( 'Country State City Dropdown CF7 is ready. Add the Country, State, and City tags in your Contact Form 7 form.', 'tc_csca' )
		: __( 'Country State City Dropdown CF7 updated. Your existing location data and form tags were kept as-is.', 'tc_csca' );
	update_option( 'tc_auto_plugin_admin_notices', $notices );
}

add_action( 'admin_notices', 'tc_auto_plugin_admin_notices' );
function tc_auto_plugin_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$notices = get_option( 'tc_auto_plugin_admin_notices' );
	if ( ! $notices || ! is_array( $notices ) ) {
		return;
	}
	foreach ( $notices as $notice ) {
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			wp_kses_post( $notice )
		);
	}
	delete_option( 'tc_auto_plugin_admin_notices' );
}

/**
 * Compact help / Pro tip on CF7 form editor (less aggressive than before).
 */
function tc_auto_plugin_add_post_metabox() {
	ob_start();
	?>
	<div id="tc_auto_plugin_meta_pro" class="tc_auto_plugin_meta_pro postbox">
		<h3><?php echo esc_html__( 'Country State City Dropdown', 'tc_csca' ); ?></h3>
		<div class="inside">
			<p><?php echo esc_html__( 'Add country + state (city is optional). Use the tag generator for placeholder and group options. Same group:name on paired fields if you need multiple location sets.', 'tc_csca' ); ?></p>
			<p>
				<a href="https://wordpress.org/support/plugin/country-state-city-auto-dropdown/" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Support', 'tc_csca' ); ?></a>
				&nbsp;|&nbsp;
				<a href="https://trustyplugins.com" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Pro features', 'tc_csca' ); ?></a>
			</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

add_filter( 'plugin_row_meta', 'tc_csca_plugin_row_meta', 10, 2 );
function tc_csca_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) !== $file ) {
		return (array) $links;
	}
	$row_meta = array(
		'docs' => '<a href="' . esc_url( 'https://wordpress.org/plugins/country-state-city-auto-dropdown/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Docs', 'tc_csca' ) . '</a>',
		'pro'  => '<a href="' . esc_url( 'https://trustyplugins.com/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Pro', 'tc_csca' ) . '</a>',
	);
	return array_merge( $links, $row_meta );
}
