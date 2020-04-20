<?php

$labels = array(
  'name'                  => _x( 'Memberships', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Membership', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Memberships', 'humble-lms' ),
  'name_admin_bar'        => __( 'Memberships', 'humble-lms' ),
  'archives'              => __( 'Membership Archives', 'humble-lms' ),
  'attributes'            => __( 'Membership Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Membership:', 'humble-lms' ),
  'all_items'             => __( 'All Memberships', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Membership', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Membership', 'humble-lms' ),
  'edit_item'             => __( 'Edit Membership', 'humble-lms' ),
  'update_item'           => __( 'Update Membership', 'humble-lms' ),
  'view_item'             => __( 'View Membership', 'humble-lms' ),
  'view_items'            => __( 'View Memberships', 'humble-lms' ),
  'search_items'          => __( 'Search Membership', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into membership', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this membership', 'humble-lms' ),
  'items_list'            => __( 'Memberships list', 'humble-lms' ),
  'items_list_navigation' => __( 'Memberships list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter memberships list', 'humble-lms' ),
);

$rewrite = array(
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Membership', 'humble-lms' ),
  'description'           => __( 'Membership', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-groups',
  'show_in_admin_bar'     => false,
  'show_in_nav_menus'     => false,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => false,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_mbship', $args );

// Membership meta boxes
function humble_lms_memberhsip_add_meta_boxes()
{
  add_meta_box( 'humble_lms_mbship_price_mb', __('Price', 'humble-lms'), 'humble_lms_mbship_price_mb', 'humble_lms_mbship', 'normal', 'default' );
  add_meta_box( 'humble_lms_mbship_description_mb', __('Description', 'humble-lms'), 'humble_lms_mbship_description_mb', 'humble_lms_mbship', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_memberhsip_add_meta_boxes' );

// Price
function humble_lms_mbship_price_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $price = get_post_meta( $post->ID, 'humble_lms_mbship_price', true );
  $price = number_format((float)$price, 2, '.', '');

  if( $price ) {
    $price = filter_var( $price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
  }

  echo '<input lang="en" class="widefat" type="number" min="0.00" max="9999999999.99" step="0.01" name="humble_lms_mbship_price" id="humble_lms_mbship_price" value="' . $price . '">';
  echo '<p class="description">' . __('Prices must be 2 digit decimals, e.g. "14.99", "79.00", "239.49" etc. Based on your browser language settings the saved value will sometimes be displayed with a comma instead of a dot. Don\'t worry, that\'s fine.', 'humble-lms') . '</p>';
}

// Description
function humble_lms_mbship_description_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $description = get_post_meta( $post->ID, 'humble_lms_mbship_description', true );

  echo '<p>' . __('Describe the benefits of this membership in a few words. Allowed HTML-tags: strong, em, b, i.', 'humble-lms') . '</p>';
  echo '<textarea rows="5" class="widefat" name="humble_lms_mbship_description" id="humble_lms_mbship_description">' . $description . '</textarea>';
}

// Save metabox data

function humble_lms_save_mbship_meta_boxes( $post_id, $post )
{
  $nonce = ! empty( $_POST['humble_lms_meta_nonce'] ) ? $_POST['humble_lms_meta_nonce'] : '';

  if( ! wp_verify_nonce( $nonce, 'humble_lms_meta_nonce' ) ) {
    return $post_id;
  }
  
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }

  if( ! is_admin() ) {
    return false;
  }
  
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return $post_id;
  }
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_mbship' ) {
    return false;
  }

  $allowed_tags = array(
    'strong' => array(),
    'em' => array(),
    'b' => array(),
    'i' => array()
  );

  $mbship_meta['humble_lms_mbship_description'] = wp_kses( $_POST['humble_lms_mbship_description'], $allowed_tags );
  $mbship_meta['humble_lms_mbship_price'] = isset( $_POST['humble_lms_mbship_price'] ) ? round( filter_var( $_POST['humble_lms_mbship_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ), 2 ) : 0.00; 

  if( ! empty( $mbship_meta ) && sizeOf( $mbship_meta ) > 0 )
  {
    foreach ($mbship_meta as $key => $value)
    {
      if( $post->post_type == 'revision' ) return; // Don't store custom data twice

      if( get_post_meta( $post->ID, $key, FALSE ) ) {
        update_post_meta( $post->ID, $key, $value );
      } else {
        add_post_meta( $post->ID, $key, $value );
      }

      if( ! $value ) delete_post_meta( $post->ID, $key ); // Delete if blank
    }
  }
}

add_action('save_post', 'humble_lms_save_mbship_meta_boxes', 1, 2);
