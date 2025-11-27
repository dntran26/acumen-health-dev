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

function acumen_custom_bootstrap() {
    // Custom actions and filters will go here.
}
add_action( 'init', 'acumen_custom_bootstrap' );

require_once __DIR__ . '/inc/shortcodes.php';
require_once __DIR__ . '/inc/elementor.php';
require_once __DIR__ . '/inc/admin.php';
require_once __DIR__ . '/inc/forms.php';
// Temporary landing zone if needed in future.
// require_once __DIR__ . '/inc/snippets.php';


