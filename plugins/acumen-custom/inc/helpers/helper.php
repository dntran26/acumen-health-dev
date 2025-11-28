<?php
/**
 * Helpers Module
 *
 * Generic helper functions shared across modules.
 * Keep this file small and focused on reusable utilities.
 *
 * @package AcumenCustom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Example: Safe debug logger (not yet wired up).
 *
 * @param mixed  $data   Data to log.
 * @param string $label  Optional label.
 * @return void
 */
function acumen_log( $data, $label = 'ACUMEN' ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[%s] %s', $label, print_r( $data, true ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}
}
