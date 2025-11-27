<?php
/**
 * Shortcodes for Acumen Custom plugin.
 */

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
    // Ensure ACF is available.
    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

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
 * Shortcode: [service_treatments columns="4" gap="24"]
 * Renders the Treatment Options section on single Service pages.
 */
function acumen_service_treatments_shortcode( $atts ) {
    // Ensure ACF is available.
    if ( ! function_exists( 'get_field' ) || ! function_exists( 'have_rows' ) ) {
        return '';
    }
    
    if ( ! is_singular( 'service' ) ) {
        return '';
    }

    $a = shortcode_atts(
        [
            'enable'  => 'treat_enable',
            'heading' => 'treat_heading',
            'intro'   => 'treat_intro',
            'field'   => 'treat_items',
            'columns' => '4',
            'gap'     => '24',
            'class'   => 'treatments-section',
        ],
        $atts
    );

    if ( ! get_field( $a['enable'] ) ) {
        return '';
    }

    if ( ! have_rows( $a['field'] ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="<?php echo esc_attr( $a['class'] ); ?>"
             style="--t-cols:<?php echo (int) $a['columns']; ?>;--t-gap:<?php echo (int) $a['gap']; ?>px;">
        <?php if ( $h = trim( (string) get_field( $a['heading'] ) ) ) : ?>
            <h2 class="treat-heading"><?php echo esc_html( $h ); ?></h2>
        <?php endif; ?>

        <?php if ( $intro = get_field( $a['intro'] ) ) : ?>
            <div class="treat-intro"><?php echo wp_kses_post( $intro ); ?></div>
        <?php endif; ?>

        <div class="treat-grid">
            <?php
            while ( have_rows( $a['field'] ) ) :
                the_row();
                $title = (string) get_sub_field( 't_title' );
                $blurb = (string) get_sub_field( 't_blurb' );
                $link  = get_sub_field( 't_link' ); // URL string or array.

                $url = is_array( $link ) ? ( $link['url'] ?? '' ) : ( is_string( $link ) ? $link : '' );

                if ( ! $title ) {
                    continue;
                }
                ?>
                <article class="treat-card">
                    <?php if ( $url ) : ?>
                        <a class="treat-link" href="<?php echo esc_url( $url ); ?>">
                    <?php endif; ?>

                        <h3 class="treat-title"><?php echo esc_html( $title ); ?></h3>
                        <?php if ( $blurb ) : ?>
                            <p class="treat-blurb"><?php echo esc_html( $blurb ); ?></p>
                        <?php endif; ?>

                    <?php if ( $url ) : ?>
                        </a>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode( 'service_treatments', 'acumen_service_treatments_shortcode' );
