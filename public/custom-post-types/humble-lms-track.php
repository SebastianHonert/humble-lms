<?php

$labels = array(
  'name'                  => _x( 'Tracks', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Track', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Tracks', 'humble_lms' ),
  'name_admin_bar'        => __( 'Tracks', 'humble_lms' ),
  'archives'              => __( 'Track Archives', 'humble_lms' ),
  'attributes'            => __( 'Track Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Track:', 'humble_lms' ),
  'all_items'             => __( 'All Tracks', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Track', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Track', 'humble_lms' ),
  'edit_item'             => __( 'Edit Track', 'humble_lms' ),
  'update_item'           => __( 'Update Track', 'humble_lms' ),
  'view_item'             => __( 'View Track', 'humble_lms' ),
  'view_items'            => __( 'View Tracks', 'humble_lms' ),
  'search_items'          => __( 'Search Track', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into track', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this track', 'humble_lms' ),
  'items_list'            => __( 'Tracks list', 'humble_lms' ),
  'items_list_navigation' => __( 'Tracks list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter tracks list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('tracks', 'humble-lms'),
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Track', 'humble_lms' ),
  'description'           => __( 'Track', 'humble_lms' ),
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
}

add_action( 'add_meta_boxes', 'humble_lms_track_add_meta_boxes' );

// Courses meta box

function humble_lms_track_courses_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $track_courses = get_post_meta($post->ID, 'humble_lms_track_courses', true);
  $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

  $args = array(
    'post_type' => 'humble_lms_course',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'exclude' => $track_courses
  );

  $courses = get_posts( $args );

  $selected_courses = [];

  foreach( $track_courses as $key => $id ) {
    if( get_post_status( $id ) ) {
      $course = get_post( $id );
      array_push( $selected_courses, $course );
    }
  }

  if( $courses || $selected_courses ):

    echo '<select class="humble-lms-searchable" multiple="multiple">';
      foreach( $selected_courses as $course ) {
        echo '<option data-id="' . $course->ID . '" value="' . $course->ID . '" ';
          if( is_array( $track_courses ) && in_array( $course->ID, $track_courses ) ) { echo 'selected'; }
        echo '>' . $course->post_title . ' (ID ' . $course->ID . ')</option>';
      }
      foreach( $courses as $course ) {
        echo '<option data-id="' . $course->ID . '" value="' . $course->ID . '" ';
          if( is_array( $track_courses ) && in_array( $course->ID, $track_courses ) ) { echo 'selected'; }
        echo '>' . $course->post_title . ' (ID ' . $course->ID . ')</option>';
      }
    echo '</select>';
    echo '<input id="humble_lms_track_courses" name="humble_lms_track_courses" type="hidden" value="' . implode(',', $track_courses) . '">';

  else:

      echo '<p>' . sprintf( __('No courses found. Please %s first.', 'humble-lms'), '<a href="' . admin_url('/edit.php?post_type=humble_lms_lesson') . '">add one or more courses</a>' ) . '</p>';

  endif;
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

  echo '<input type="text" class="" name="humble_lms_track_position" id="humble_lms_track_position" value="' . $position . '">';
  
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
  $track_meta['humble_lms_track_courses'] = isset( $_POST['humble_lms_track_courses'] ) ? (array) $_POST['humble_lms_track_courses'] : array();
  $track_meta['humble_lms_track_courses'] = array_map( 'esc_attr', $track_meta['humble_lms_track_courses'] );
  $track_meta['humble_lms_track_duration'] = sanitize_text_field( $_POST['humble_lms_track_duration'] );
  $track_meta['humble_lms_track_position'] = ! (int) $_POST['humble_lms_track_position'] ? '1' : (int) $_POST['humble_lms_track_position'];

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
