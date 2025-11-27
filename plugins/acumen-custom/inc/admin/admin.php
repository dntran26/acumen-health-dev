<?php
/**
 * Admin UI tweaks for Acumen Custom plugin.
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
 * Admin UI tweaks
 * ============================================
 */

/**
 * Hide the "Add New" UI for the Card Types taxonomy.
 */
function acumen_hide_add_new_card_type() {
    ?>
    <style>
        /* Hide the "+ Add New Card Type" toggle */
        #taxonomy-card-type .taxonomy-add-new {
            display: none !important;
        }

        /* Hide the hidden input panel where new terms would be created */
        #taxonomy-card-type .category-add {
            display: none !important;
        }

        /* Hide the “Most Used” tab inside Card Types taxonomy */
        #taxonomy-card-type .category-tabs li.hide-if-no-js {
            display: none !important;
        }
    </style>
    <?php
}
add_action( 'admin_head', 'acumen_hide_add_new_card_type' );

/**
 * Add helper text below the Featured Image box on Service edit screens.
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
