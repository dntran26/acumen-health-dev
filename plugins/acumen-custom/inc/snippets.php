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
