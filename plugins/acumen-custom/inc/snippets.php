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

/**
 * ============================================
 * Team admin UI tweaks
 * ============================================
 */

/**
 * Admin – Team Thumbnail Column (60x60) in Team list table.
 */
function acumen_team_add_thumbnail_column( $columns ) {
    // Insert "Photo" before Title.
    $new = [];

    foreach ( $columns as $key => $label ) {
        if ( 'cb' === $key ) {
            $new[ $key ] = $label;
            continue;
        }

        if ( 'title' === $key ) {
            $new['team_thumb'] = 'Photo';
        }

        $new[ $key ] = $label;
    }

    if ( ! isset( $new['team_thumb'] ) ) {
        $new['team_thumb'] = 'Photo';
    }

    return $new;
}
add_filter( 'manage_edit-team_columns', 'acumen_team_add_thumbnail_column', 20 );

/**
 * Output thumbnail in Team "Photo" column.
 */
function acumen_team_thumbnail_column_content( $column, $post_id ) {
    if ( 'team_thumb' !== $column ) {
        return;
    }

    if ( has_post_thumbnail( $post_id ) ) {
        $thumb     = get_the_post_thumbnail(
            $post_id,
            [ 60, 60 ],
            [
                'style' => 'width:60px;height:60px;object-fit:cover;border-radius:50%;display:block;',
            ]
        );
        $edit_link = get_edit_post_link( $post_id );
        echo '<a href="' . esc_url( $edit_link ) . '">' . $thumb . '</a>';
    } else {
        echo '—';
    }
}
add_action( 'manage_team_posts_custom_column', 'acumen_team_thumbnail_column_content', 10, 2 );

/**
 * Tidy thumbnail column width in Team list table.
 */
function acumen_team_thumbnail_column_css() {
    $screen = get_current_screen();

    if ( $screen && 'team' === $screen->post_type ) {
        echo '<style>
            .column-team_thumb{width:80px;text-align:left;}
        </style>';
    }
}
add_action( 'admin_head-edit.php', 'acumen_team_thumbnail_column_css' );

/**
 * Enable manual ordering for Team CPT and show an Order column.
 */

/**
 * 1) Make sure the Team post type supports menu_order via "page-attributes".
 */
function acumen_team_add_order_support() {
    $post_type = 'team'; // adjust if CPT slug differs.

    $obj = get_post_type_object( $post_type );
    if ( $obj && ! in_array( 'page-attributes', (array) $obj->supports, true ) ) {
        add_post_type_support( $post_type, 'page-attributes' );
    }
}
add_action( 'init', 'acumen_team_add_order_support', 20 );

/**
 * 2) Add an "Order" column to the Team admin list.
 */
function acumen_team_add_order_column( $columns ) {
    $new = [];

    foreach ( $columns as $key => $label ) {
        if ( 'title' === $key ) {
            $new['menu_order'] = __( 'Order', 'acumen' );
        }

        $new[ $key ] = $label;
    }

    return $new;
}
add_filter( 'manage_edit-team_columns', 'acumen_team_add_order_column' );

/**
 * 3) Output the menu_order value in that column.
 */
function acumen_team_order_column_content( $column, $post_id ) {
    if ( 'menu_order' === $column ) {
        echo (int) get_post_field( 'menu_order', $post_id );
    }
}
add_action( 'manage_team_posts_custom_column', 'acumen_team_order_column_content', 10, 2 );

/**
 * 4) Make the Order column sortable.
 */
function acumen_team_order_sortable_columns( $columns ) {
    $columns['menu_order'] = 'menu_order';
    return $columns;
}
add_filter( 'manage_edit-team_sortable_columns', 'acumen_team_order_sortable_columns' );

/**
 * Narrow the Order column in Team list table.
 */
function acumen_team_order_column_css() {
    $screen = get_current_screen();
    if ( empty( $screen->post_type ) || 'team' !== $screen->post_type ) {
        return;
    }
    ?>
    <style>
        .wp-list-table .column-menu_order {
            width: 100px;
            max-width: 100px;
        }
    </style>
    <?php
}
add_action( 'admin_head-edit.php', 'acumen_team_order_column_css' );

/**
 * ============================================
 * Form helpers
 * ============================================
 */

/**
 * Output JS to handle province to city dropdown options on the contact page.
 */
function acumen_form_province_city_dropdown_script() {
    // Only run on the contact page.
    // Adjust the slug or use is_page(123) if needed.
    if ( ! is_page( 'contact-us' ) ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Update these IDs to match your Gravity Form.
      var provinceSelect = document.querySelector('#input_1_8'); // Province
      var citySelect     = document.querySelector('#input_1_9'); // City

      if (!provinceSelect || !citySelect) return;

      // Province to city map.
      var cityOptions = {
        'Alberta': [
          'Edmonton',
          'Calgary'
        ],
        'British Columbia': [
          'Vancouver',
          'Langley',
          'Kelowna'
        ]
      };

      function populateCities() {
        var province = provinceSelect.value;

        // Clear existing options.
        citySelect.innerHTML = '';

        // Placeholder option.
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select a city';
        citySelect.appendChild(placeholder);

        // Add cities for selected province.
        if (cityOptions[province]) {
          cityOptions[province].forEach(function (city) {
            var opt = document.createElement('option');
            opt.value = city;
            opt.textContent = city;
            citySelect.appendChild(opt);
          });
        }

        // Reset selection.
        citySelect.value = '';
      }

      provinceSelect.addEventListener('change', populateCities);

      // Run on load in case a province is preselected.
      populateCities();
    });
    </script>
    <?php
}
add_action( 'wp_footer', 'acumen_form_province_city_dropdown_script' );
