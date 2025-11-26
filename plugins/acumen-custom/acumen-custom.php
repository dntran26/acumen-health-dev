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

// Load separate snippet file.
require_once plugin_dir_path( __FILE__ ) . 'inc/snippets.php';

