<?php
/**
 * Snippets moved from Code Snippets plugin.
 */

/**
 * Adds helper text below the Featured Image box on Service edit screens.
 */
function acumen_service_featured_image_note() {
    $screen = get_current_screen();

    if ( $screen && $screen->post_type === 'service' ) {
        ?>
        <script>
        jQuery(function($){
            var $box = $("#postimagediv .inside");
            if ($box.length && !$box.find(".featured-image-note").length) {
                $box.append(
                    "<p class='featured-image-note' style='margin:8px 0 0;'>"+
                    "✔ Used as the main thumbnail for this Service (featured image).<br>"+
                    "✔ Also appears in the Service Options section and other displays."+
                    "</p>"
                );
            }
        });
        </script>
        <?php
    }
}
add_action( 'admin_head', 'acumen_service_featured_image_note' );

/**
 * Shortcode: [team_firstname]
 * Outputs the team member's first name, preserving "Dr." if present.
 */
function acumen_team_firstname_shortcode() {
    if ( ! is_singular( 'team' ) ) {
        return '';
    }

    $title = trim( get_the_title() );

    // Detect if the title starts with Dr or Doctor
    $has_dr = preg_match( '/^(Dr\.?|Doctor)\s+/i', $title );

    // Remove any prefix (Dr, Mr, etc.) for name extraction
    $clean_title = preg_replace( '/^(Dr\.?|Doctor|Mr\.?|Mrs\.?|Ms\.?)\s+/i', '', $title );

    // Extract first name
    $parts = preg_split( '/\s+/', $clean_title );
    $first = ucfirst( strtolower( preg_replace( '/[^a-zA-Z]/', '', $parts[0] ?? '' ) ) );

    // If it originally had Dr., prepend it again
    if ( $has_dr ) {
        $output = 'Dr. ' . $first;
    } else {
        $output = $first;
    }

    return esc_html( $output );
}
add_shortcode( 'team_firstname', 'acumen_team_firstname_shortcode' );

/**
 * Shortcode: [team_expertise field="expertise_services" class="expertise-list" icon="URL"]
 * Outputs a list of expertise items from an ACF relationship field.
 */
function acumen_team_expertise_shortcode( $atts ) {
    $a = shortcode_atts(
        [
            'field' => 'expertise_services', // ACF relationship field name
            'class' => 'expertise-list',     // wrapper class
            'icon'  => 'https://acumen-health-local.local/wp-content/uploads/icon-open-link.svg',
            'post'  => get_the_ID(),         // current post by default
        ],
        $atts
    );

    // Fetch relationship value (Post Objects expected)
    $items = get_field( $a['field'], (int) $a['post'] );

    if ( empty( $items ) || ! is_array( $items ) ) {
        return ''; // nothing to show
    }

    ob_start();
    ?>
    <ul class="<?php echo esc_attr( $a['class'] ); ?>">
        <?php
        foreach ( $items as $item ) :
            // If the field is ever switched to "Post ID", normalize.
            if ( is_numeric( $item ) ) {
                $item = get_post( (int) $item );
            }
            if ( ! $item || 'publish' !== $item->post_status ) {
                continue;
            }

            $url   = get_permalink( $item );
            $title = get_the_title( $item );
            ?>
            <li>
                <a class="expertise-link" href="<?php echo esc_url( $url ); ?>">
                    <span class="expertise-title"><?php echo esc_html( $title ); ?></span>
                    <img class="icon-arrow" src="<?php echo esc_url( $a['icon'] ); ?>" alt="" loading="lazy">
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}
add_shortcode( 'team_expertise', 'acumen_team_expertise_shortcode' );

/**
 * Elementor Query: team_by_expertise
 * Filters the Team Loop on a Service page to only show team members
 * connected via the ACF relationship field "expertise_services".
 */
function acumen_elementor_query_team_by_expertise( $query ) {
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
