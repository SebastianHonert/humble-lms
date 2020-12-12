<?php

/**
 * Register the coupon custom post type.
 * 
 * @since 0.0.5
 * @package Humble_LMS
 */

$labels = array(
  'name'                  => _x( 'Coupons', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Coupon', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Coupons', 'humble-lms' ),
  'name_admin_bar'        => __( 'Coupon', 'humble-lms' ),
  'archives'              => __( 'Coupon Archives', 'humble-lms' ),
  'attributes'            => __( 'Coupon Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Coupon:', 'humble-lms' ),
  'all_items'             => __( 'All Coupons', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Coupon', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Coupon', 'humble-lms' ),
  'edit_item'             => __( 'Edit Coupon', 'humble-lms' ),
  'update_item'           => __( 'Update Coupon', 'humble-lms' ),
  'view_item'             => __( 'View Coupon', 'humble-lms' ),
  'view_items'            => __( 'View Coupons', 'humble-lms' ),
  'search_items'          => __( 'Search Coupon', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into coupon', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this coupon', 'humble-lms' ),
  'items_list'            => __( 'Coupons list', 'humble-lms' ),
  'items_list_navigation' => __( 'Coupons list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter Coupons list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => 'coupon',
  'with_front'            => true,
);

$args = array(
  'label'                 => __( 'Coupon', 'humble-lms' ),
  'description'           => __( 'Coupon', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-tickets-alt',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => false,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => false,
  'publicly_queryable'    => false,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_coupon', $args );

// Coupon meta boxes

function humble_lms_coupon_add_meta_boxes()
{
  add_meta_box( 'humble_lms_coupon_code_mb', __('Code', 'humble-lms'), 'humble_lms_coupon_code_mb', 'humble_lms_coupon', 'normal', 'default' );
  add_meta_box( 'humble_lms_coupon_type_mb', __('Type', 'humble-lms'), 'humble_lms_coupon_type_mb', 'humble_lms_coupon', 'normal', 'default' );
  add_meta_box( 'humble_lms_coupon_value_mb', __('Discount', 'humble-lms'), 'humble_lms_coupon_value_mb', 'humble_lms_coupon', 'normal', 'default' );
  // add_meta_box( 'humble_lms_coupon_targets_mb', __('Coupon target', 'humble-lms'), 'humble_lms_coupon_targets_mb', 'humble_lms_coupon', 'normal', 'default' );
  add_meta_box( 'humble_lms_coupon_users_mb', __('Limit to specific users', 'humble-lms'), 'humble_lms_coupon_users_mb', 'humble_lms_coupon', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_coupon_add_meta_boxes' );

// Coupon code meta box

function humble_lms_coupon_code_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $coupon_code = get_post_meta( $post->ID, 'humble_lms_coupon_code', true );
  $coupon_code = $coupon_code ? sanitize_text_field( $coupon_code ) : '';

  echo '<p><input class="widefat" type="text" name="humble_lms_coupon_code" id="humble_lms_coupon_code" value="' . $coupon_code . '" maxlength="32" required="required"></p>';

  echo '<p class="description">' . __('The code that your customers will have to enter to get a discount on your website. Maximum length is 32 characters. Limited support for special characters.', 'humble-lms') . '</p>';
}

// Coupon type meta box

function humble_lms_coupon_type_mb()
{
  global $post;

  $coupon_type = get_post_meta( $post->ID, 'humble_lms_coupon_type', true );

  $selected_percent = $coupon_type === 'percent' ? 'selected' : '';
  $selected_fixed_amount = $coupon_type === 'fixed_amount' ? 'selected' : '';

  echo '<p><select name="humble_lms_coupon_type" id="humble_lms_coupon_type" class="humble_lms_coupon_type">
    <option value="percent" ' . $selected_percent . '>' . __('Percent', 'humble-lms') . '</option>
    <option value="fixed_amount" ' . $selected_fixed_amount . '>' . __('Fixed amount', 'humble-lms') . '</option>
  </select></p>';

  echo '<p class="description">' . __('Select either a percentage based or a fixed amount discount.', 'humble-lms') . '</p>';

  ?>

  <script>
  jQuery(document).ready(function($) {
    $('#humble_lms_coupon_type').on('change', function() {
      let coupon_type = $(this).val();

      if (coupon_type === 'percent') {
        $('#humble_lms_coupon_value').attr({'max': 100});
      } else {
        $('#humble_lms_coupon_value').attr({'max': 9999});
      }
    })
  })
</script>
  <?php
}

// Coupon value

function humble_lms_coupon_value_mb()
{
  global $post;

  $calculator = new Humble_LMS_Calculator;
  $currency = $calculator->currency();
  $coupon_value = get_post_meta( $post->ID, 'humble_lms_coupon_value', true );

  if( ! isset( $coupon_value ) ) {
    $coupon_value = 0;
  }

  echo '<p><input type="number" min="1" max="9999" step="0.01" name="humble_lms_coupon_value" id="humble_lms_coupon_value" value="' . $coupon_value . '"></p>';

  echo '<p class="description">' . __('Enter a discount value in percent or a fixed amount in your selected currency', 'humble-lms') . ' (' . $currency . ').</p>';
}

// Coupon target

// function humble_lms_coupon_targets_mb()
// {
//   global $post;

//   $coupon_targets = get_post_meta( $post->ID, 'humble_lms_coupon_targets', false );
//   $coupon_targets = ! isset( $coupon_targets[0] ) || ! is_array( $coupon_targets[0] ) || ! $coupon_targets[0] ? [] : $coupon_targets[0];

//   $coupon_valid_for_tracks = in_array( 'track', $coupon_targets ) ? 'checked' : '';
//   $coupon_valid_for_courses = in_array( 'course', $coupon_targets ) ? 'checked' : '';
//   $coupon_valid_for_memberships = in_array( 'membership', $coupon_targets ) ? 'checked' : '';

//   echo '<p><input type="checkbox" name="humble_lms_coupon_targets[]" id="humble_lms_coupon_targets_track" value="track" ' . $coupon_valid_for_tracks . '> ' . __('Tracks', 'humble-lms') . '<br>';
//   echo '<input type="checkbox" name="humble_lms_coupon_targets[]" id="humble_lms_coupon_targets_course" value="course" ' . $coupon_valid_for_courses . '> ' . __('Courses', 'humble-lms') . '<br>';
//   echo '<input type="checkbox" name="humble_lms_coupon_targets[]" id="humble_lms_coupon_targets_membership" value="membership" ' . $coupon_valid_for_memberships . '> ' . __('Memberships', 'humble-lms') . '</p>';
//   echo '<p class="description">' . __('Select the content types this coupon will be valid for.', 'humble-lms') . '</p>';
// }

// Coupon limit to specific users

function humble_lms_coupon_users_mb()
{
  global $post;

  $users = get_users();

  $coupon_users = get_post_meta( $post->ID, 'humble_lms_coupon_users', false );
  $coupon_users = ! isset( $coupon_users[0] ) || ! is_array( $coupon_users[0] ) || ! $coupon_users[0] ? [] : $coupon_users[0];

  echo '<select class="humble-lms-searchable" multiple="multiple" name="humble_lms_coupon_users[]" id="humble_lms_coupon_users">';
    foreach( $users as $user ) {
      $user_details = get_user_by( 'id', $user->ID );
      $selected = in_array( $user->ID, $coupon_users ) ? 'selected="selected"' : '';
      echo '<option value="' . $user->ID . '" ' . $selected . '">' . $user_details->user_login . ' | ' . $user_details->user_email . ' (' . $user_details->ID . ')</option>';
    }
  echo '</select>';

  echo '<p class="description">' . __('If you do not select any specific users this coupon will be available for all users.', 'humble-lms') . '</p>';
}

// Save metabox data

function humble_lms_save_coupon_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_coupon' ) {
    return false;
  }

  // Let's save some data!
  $calculator = new Humble_LMS_Calculator;

  $coupon_meta['humble_lms_coupon_code'] = isset( $_POST['humble_lms_coupon_code'] ) ? substr( sanitize_text_field( $_POST['humble_lms_coupon_code'] ), 0, 32 ) : '';
  $coupon_meta['humble_lms_coupon_type'] = isset( $_POST['humble_lms_coupon_type'] ) ? sanitize_text_field( $_POST['humble_lms_coupon_type'] ) : 'percent';
  $coupon_meta['humble_lms_coupon_value'] = isset( $_POST['humble_lms_coupon_value'] ) ? $calculator->format_price( $_POST['humble_lms_coupon_value'] ) : 0;

  if( $coupon_meta['humble_lms_coupon_value'] < 1 ) {
    $coupon_meta['humble_lms_coupon_value'] = 1.00;
  }

  if( $coupon_meta['humble_lms_coupon_type'] === 'percent' ) {
    if( $coupon_meta['humble_lms_coupon_value'] > 100 ) {
      $coupon_meta['humble_lms_coupon_value'] = 100.00;
    }
  }

  // $coupon_meta['humble_lms_coupon_targets'] = isset( $_POST['humble_lms_coupon_targets'] ) ? $_POST['humble_lms_coupon_targets'] : [];
  // $allowed_targets = array( 'track', 'course', 'membership' );

  // if( ! empty( $coupon_meta['humble_lms_coupon_targets'] ) ) {
  //   foreach( $coupon_meta['humble_lms_coupon_targets'] as $key => $target ) {
  //     if( ! in_array( $target, $allowed_targets ) ) {
  //       unset( $coupon_meta['humble_lms_coupon_targets'][$key] );
  //       continue;
  //     }

  //     $coupon_meta['humble_lms_coupon_targets'][$key] = sanitize_text_field( $target );
  //   }
  // }

  $coupon_meta['humble_lms_coupon_users'] = isset( $_POST['humble_lms_coupon_users'] ) ? $_POST['humble_lms_coupon_users'] : [];

  foreach( $coupon_meta['humble_lms_coupon_users'] as $key => $id ) {
    $coupon_meta['humble_lms_coupon_users'][$key] = absint( $id );
  }

  if( ! empty( $coupon_meta ) && sizeOf( $coupon_meta ) > 0 )
  {
    foreach ($coupon_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_coupon_meta_boxes', 1, 2);
