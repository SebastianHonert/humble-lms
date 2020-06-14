<?php

$labels = array(
  'name'                  => _x( 'Tracks', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Track', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Tracks', 'humble-lms' ),
  'name_admin_bar'        => __( 'Tracks', 'humble-lms' ),
  'archives'              => __( 'Track Archives', 'humble-lms' ),
  'attributes'            => __( 'Track Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Track:', 'humble-lms' ),
  'all_items'             => __( 'All Tracks', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Track', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Track', 'humble-lms' ),
  'edit_item'             => __( 'Edit Track', 'humble-lms' ),
  'update_item'           => __( 'Update Track', 'humble-lms' ),
  'view_item'             => __( 'View Track', 'humble-lms' ),
  'view_items'            => __( 'View Tracks', 'humble-lms' ),
  'search_items'          => __( 'Search Track', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into track', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this track', 'humble-lms' ),
  'items_list'            => __( 'Tracks list', 'humble-lms' ),
  'items_list_navigation' => __( 'Tracks list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter tracks list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => __('track', 'humble-lms'),
  'with_front'            => true,
);

$args = array(
  'label'                 => __( 'Track', 'humble-lms' ),
  'description'           => __( 'Track', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
  'show_in_rest'          => true,
  'taxonomies'            => array( 'category', 'post_tag' ),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-welcome-learn-more',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => true,
  'can_export'            => true,
  'has_archive'           => true,
  'exclude_from_search'   => false,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_track', $args );

// Track meta boxes

function humble_lms_track_add_meta_boxes()
{
  add_meta_box( 'humble_lms_track_courses_mb', __('Courses in this track', 'humble-lms'), 'humble_lms_track_courses_mb', 'humble_lms_track', 'normal', 'default' );
  add_meta_box( 'humble_lms_track_duration_mb', __('Duration (approximately, e.g. 8 hours)', 'humble-lms'), 'humble_lms_track_duration_mb', 'humble_lms_track', 'normal', 'default' );
  add_meta_box( 'humble_lms_track_position_mb', __('Position on track archive page (low = first)', 'humble-lms'), 'humble_lms_track_position_mb', 'humble_lms_track', 'normal', 'default' );
  add_meta_box( 'humble_lms_track_color_mb', __('Select a color for the track tile (optional)', 'humble-lms'), 'humble_lms_track_color_mb', 'humble_lms_track', 'normal', 'default' );
  add_meta_box( 'humble_lms_track_fixed_price_mb', __('Sell this track for a fixed price', 'humble-lms'), 'humble_lms_track_fixed_price_mb', 'humble_lms_track', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_track_add_meta_boxes' );

// Courses meta box

function humble_lms_track_courses_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $translator = new Humble_LMS_Translator;
  $track_courses = Humble_LMS_Content_Manager::get_track_courses( $post->ID );

  $args = array(
    'post_type' => 'humble_lms_course',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'exclude' => $track_courses,
    'lang' => $translator->current_language(),
  );

  $courses = get_posts( $args );

  $selected_courses = [];

  foreach( $track_courses as $key => $id ) {
    if( get_post_status( $id ) ) {
      $course = get_post( $id );
      array_push( $selected_courses, $course );
    }
  }

  echo '<div id="humble-lms-admin-track-courses humble_lms_multiselect_track_courses">';
    echo '<select class="humble-lms-searchable" data-content="track_courses" multiple="multiple">';
      foreach( $selected_courses as $course ) {
        echo '<option data-id="' . $course->ID . '" value="' . $course->ID . '" ';
          if( is_array( $track_courses ) && in_array( $course->ID, $track_courses ) ) { echo 'selected'; }
        echo '>' . $course->post_title . '</option>';
      }
      foreach( $courses as $course ) {
        echo '<option data-id="' . $course->ID . '" value="' . $course->ID . '" ';
          if( is_array( $track_courses ) && in_array( $course->ID, $track_courses ) ) { echo 'selected'; }
        echo '>' . $course->post_title . '</option>';
      }
    echo '</select>';
    echo '<input class="humble-lms-multiselect-value" id="humble_lms_track_courses" name="humble_lms_track_courses" type="hidden" value="' . implode(',', $track_courses) . '">';
  echo '</div>';

  // Add course lightbox
  echo '<div class="humble-lms-add-content-lightbox-wrapper">';
    echo '<div class="humble-lms-add-content-lightbox" data-post_type="humble_lms_course" data-lang="' . $translator->current_language() . '">';
      echo '<div class="humble-lms-add-content-lightbox-title">' . __('Add course', 'humble-lms') . '</div>';
      echo '<input type="text" class="widefat humble-lms-add-content-name" name="humble-lms-add-content-name" value="" placeholder="' . __('Course title', 'humble-lms') . '&hellip;">';
      echo '<p class="humble-lms-add-content-error" data-message="' . __('Please add a course title.', 'humble-lms') . '"></p>';
      echo '<a class="button button-primary humble-lms-add-content-submit">' . __('Create and add', 'humble-lms') . '</a> <a class="button humble-lms-add-content-cancel">' . __('Close') . '</a>';
      echo '<p class="humble-lms-add-content-success"><a target="_blank">' . __('Content added – click to edit.', 'humble-lms') . '</a></p>';
    echo '</div>';
  echo '</div>';
  echo '<p class="humble-lms-add-course-to-section"><a class="humble-lms-open-admin-lightbox humble-lms-add-course-to-section button button-primary">' . __('Add course', 'humble-lms') . '</a>';
}

// Duration meta box

function humble_lms_track_duration_mb()
{
  global $post;

  $duration = get_post_meta($post->ID, 'humble_lms_track_duration', true);
  $duration = $duration ? $duration : '';

  echo '<input type="text" class="" name="humble_lms_track_duration" id="humble_lms_track_duration" value="' . $duration . '">';
  
}

// Position meta box

function humble_lms_track_position_mb()
{
  global $post;

  $position = get_post_meta($post->ID, 'humble_lms_track_position', true);
  $position = ! $position ? '1' : $position;

  echo '<input type="number" class="" name="humble_lms_track_position" id="humble_lms_track_position" min="0" max="99999" step="1" value="' . $position . '">';
  
}

// Color meta box

function humble_lms_track_color_mb()
{
  global $post;

  $color = get_post_meta($post->ID, 'humble_lms_track_color', true);
  $color = ! $color ? '' : $color;

  echo '<input type="text" class="humble_lms_color_picker"" name="humble_lms_track_color" id="humble_lms_track_color" value="' . $color . '">';
}

// Sell for a fixed price meta box

function humble_lms_track_fixed_price_mb() {
  global $post;

  $sell = get_post_meta($post->ID, 'humble_lms_is_for_sale', true);
  $price = Humble_LMS_Content_Manager::get_price( $post->ID );

  $options = get_option('humble_lms_options');
  $currency = $options['currency'];
  $checked = $sell ? 'checked' : '';

  echo '<p><input type="checkbox" name="humble_lms_is_for_sale" id="humble_lms_is_for_sale" value="1" ' . $checked . '>' . __('Yes, sell this track for a fixed price.', 'humble-lms') . '</p>';
  echo '<p><label class="humble-lms-label">' . __('Price', 'humble-lms') . ' (' . $currency . ')</label><input type="number" min="0.00" max="9999999999.99" step="0.01" name="humble_lms_fixed_price" id="humble_lms_fixed_price" placeholder="0.00" value="' . $price . '"></p>';
  echo '<p class="description">' . __('Prices must be 2 digit decimals. Based on your browser language settings the saved value will sometimes be displayed with a comma instead of a dot.', 'humble-lms') . '</p>';
}

// Save metabox data

function humble_lms_save_track_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_track' ) {
    return false;
  }

  // Let's save some data!
  $track_meta['humble_lms_track_courses'] = isset( $_POST['humble_lms_track_courses'] ) ? explode( ',', $_POST['humble_lms_track_courses'] ) : [];
  $track_meta['humble_lms_track_duration'] = sanitize_text_field( $_POST['humble_lms_track_duration'] );
  $track_meta['humble_lms_track_position'] = ! (int) $_POST['humble_lms_track_position'] ? '1' : (int) $_POST['humble_lms_track_position'];
  $track_meta['humble_lms_track_color'] = isset( $_POST['humble_lms_track_color'] ) ? sanitize_hex_color( $_POST['humble_lms_track_color'] ) : '';
  $track_meta['humble_lms_fixed_price'] = isset( $_POST['humble_lms_fixed_price'] ) ? abs( round( filter_var( $_POST['humble_lms_fixed_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ), 2 ) ) : 0.00;
  $track_meta['humble_lms_is_for_sale'] = $track_meta['humble_lms_fixed_price'] !== 0.00 && isset( $_POST['humble_lms_is_for_sale'] ) ? 1 : 0;

  if( ! empty( $track_meta ) && sizeOf( $track_meta ) > 0 )
  {
    foreach ($track_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_track_meta_boxes', 1, 2);
