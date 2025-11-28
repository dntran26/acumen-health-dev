<?php
/**
 * Elementor Module
 *
 * Registers and handles all custom Elementor query filters and helpers:
 * - team_by_expertise: show team members linked via ACF relationship.
 * - service_featured_team: featured team query with preserved order.
 *
 * @package AcumenCustom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Query: team_by_expertise
 * Filters the Team Loop on a Service page to only show team members
 * connected via the ACF relationship field "expertise_services".
 */
function acumen_elementor_query_team_by_expertise( $query ) {
    // Ensure ACF is available.
    if ( ! function_exists( 'get_field' ) ) {
        return;
    }

    if ( is_admin() ) {
        return;
    }

    $service_id = get_queried_object_id();
    if ( ! $service_id ) {
        // not on a singular front-end request
        $query->set( 'post__in', [ 0 ] );
        return;
    }

    $ptype = get_post_type( $service_id );
    // accept either 'service' or 'services' as the CPT slug
    if ( ! in_array( $ptype, [ 'service', 'services' ], true ) ) {
        $query->set( 'post__in', [ 0 ] );
        return;
    }

    // ACF relationship field
    $relationship_field = 'expertise_services';

    // Force querying team posts
    $query->set( 'post_type', 'team' );

    // Only show team that reference THIS service in the relationship field
    $meta_query = [
        [
            'key'     => $relationship_field,
            'value'   => '"' . $service_id . '"', // ACF stores relationship as serialized array of IDs
            'compare' => 'LIKE',
        ],
    ];

    $query->set( 'meta_query', $meta_query );

    // Show all, adjust if needed
    $query->set( 'posts_per_page', -1 );
}
add_action( 'elementor/query/team_by_expertise', 'acumen_elementor_query_team_by_expertise' );
/**
 * Use ACF Relationship field "featured_team_members" to control
 * the Loop Grid with Query ID "service_featured_team"
 * and keep the same order as selected in ACF.
 */
function acumen_service_featured_team_query( $query ) {

    // Ensure ACF is available.
    if ( ! function_exists( 'get_field' ) ) {
        return;
    }

    // Current page ID
    $page_id = get_queried_object_id();
    if ( ! $page_id ) {
        return;
    }

    // Get Relationship field from this page
    $featured = get_field( 'featured_team_members', $page_id );
    if ( empty( $featured ) ) {
        return;
    }

    // Convert to array of IDs
    $post_ids = array_map(
        function( $item ) {
            return is_object( $item ) ? $item->ID : (int) $item;
        },
        $featured
    );

    // Override Elementor Loop Grid query
    $query->set( 'post__in', $post_ids );
    $query->set( 'orderby', 'post__in' );
    $query->set( 'posts_per_page', count( $post_ids ) );
}

add_action( 'elementor/query/service_featured_team', 'acumen_service_featured_team_query' );