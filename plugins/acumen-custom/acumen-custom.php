<?php
/**
 * Plugin Name: Acumen Custom
 * Description: Custom functionality for Acumen Health
 * Author: Danny / Nimble
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
if ( ! defined( 'ACUMEN_CUSTOM_VERSION' ) ) {
    define( 'ACUMEN_CUSTOM_VERSION', '0.1.0' );
}

if ( ! defined( 'ACUMEN_CUSTOM_DIR' ) ) {
    define( 'ACUMEN_CUSTOM_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ACUMEN_CUSTOM_URL' ) ) {
    define( 'ACUMEN_CUSTOM_URL', plugin_dir_url( __FILE__ ) );
}


function acumen_custom_bootstrap() {
    // Custom actions and filters will go here.
}
add_action( 'init', 'acumen_custom_bootstrap' );

require_once ACUMEN_CUSTOM_DIR . 'inc/admin/admin.php';
require_once ACUMEN_CUSTOM_DIR . 'inc/elementor/elementor.php';
require_once ACUMEN_CUSTOM_DIR . 'inc/shortcodes/shortcodes.php';
require_once ACUMEN_CUSTOM_DIR . 'inc/forms/forms.php';
require_once ACUMEN_CUSTOM_DIR . 'inc/acf/acf.php';

// Temporary landing zone if needed in future.
// require_once __DIR__ . '/inc/snippets.php';