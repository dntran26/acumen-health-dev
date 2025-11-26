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
