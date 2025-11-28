<?php
/**
 * Plugin Name: Acumen Custom
 * Description: Custom functionality for the Acumen Health website.
 * Author: Nimble / Danny Tran
 * Version: 1.0.0
 *
 * @package AcumenCustom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin constants.
 */
define( 'ACUMEN_CUSTOM_VERSION', '1.0.0' );
define( 'ACUMEN_CUSTOM_DIR', plugin_dir_path( __FILE__ ) );
define( 'ACUMEN_CUSTOM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bootstrap the plugin.
 *
 * Loads all module files (admin, Elementor, forms, shortcodes, ACF, etc.).
 *
 * @return void
 */
function acumen_custom_bootstrap() {
	// Admin customizations.
	require_once ACUMEN_CUSTOM_DIR . 'inc/admin/admin.php';

	// Elementor custom queries / helpers.
	require_once ACUMEN_CUSTOM_DIR . 'inc/elementor/elementor.php';

	// Shortcodes.
	require_once ACUMEN_CUSTOM_DIR . 'inc/shortcodes/shortcodes.php';

	// Form logic (e.g. Gravity Forms).
	require_once ACUMEN_CUSTOM_DIR . 'inc/forms/forms.php';

	// ACF integration (JSON sync, helpers).
	require_once ACUMEN_CUSTOM_DIR . 'inc/acf/acf.php';

	// Generic helpers (currently optional).
	if ( file_exists( ACUMEN_CUSTOM_DIR . 'inc/helpers/helpers.php' ) ) {
		require_once ACUMEN_CUSTOM_DIR . 'inc/helpers/helpers.php';
	}
}
add_action( 'plugins_loaded', 'acumen_custom_bootstrap' );
