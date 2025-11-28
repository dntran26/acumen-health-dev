<?php
/**
 * ACF Module
 *
 * Handles Advanced Custom Fields integration:
 * - JSON save / load paths.
 * - Any future ACF helper functions.
 *
 * @package AcumenCustom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail early if ACF is not available.
if ( ! function_exists( 'acf_update_setting' ) && ! function_exists( 'get_field' ) ) {
    return;
}

/**
 * Change the ACF JSON save path to the repo's /acf-json directory.
 *
 * @param string $path Default save path.
 * @return string
 */
function acumen_acf_json_save_point( $path ) {
    // __DIR__ is .../plugins/acumen-custom/inc
    // Go up three levels to get to the repo root: .../acumen-health.
    $root = dirname( dirname( dirname( __DIR__ ) ) );

    return $root . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'acumen_acf_json_save_point' );

/**
 * Add the repo's /acf-json directory to the ACF JSON load paths.
 *
 * @param array $paths Default load paths.
 * @return array
 */
function acumen_acf_json_load_points( $paths ) {
    $root = dirname( dirname( dirname( __DIR__ ) ) );
    $paths[] = $root . '/acf-json';

    return $paths;
}
add_filter( 'acf/settings/load_json', 'acumen_acf_json_load_points' );
