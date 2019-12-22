<?php

$labels = array(
  'name'                  => _x( 'Activities', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Activity', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Activities', 'humble_lms' ),
  'name_admin_bar'        => __( 'Activities', 'humble_lms' ),
  'archives'              => __( 'Activity Archives', 'humble_lms' ),
  'attributes'            => __( 'Activity Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Activity:', 'humble_lms' ),
  'all_items'             => __( 'All Activities', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Activity', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Activity', 'humble_lms' ),
  'edit_item'             => __( 'Edit Activity', 'humble_lms' ),
  'update_item'           => __( 'Update Activity', 'humble_lms' ),
  'view_item'             => __( 'View Activity', 'humble_lms' ),
  'view_items'            => __( 'View Activities', 'humble_lms' ),
  'search_items'          => __( 'Search Activity', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into activity', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this activity', 'humble_lms' ),
  'items_list'            => __( 'Activities list', 'humble_lms' ),
  'items_list_navigation' => __( 'Activities list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter activities list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('activities', 'humble-lms'),
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Activity', 'humble_lms' ),
  'description'           => __( 'Activity', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-carrot',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => true,
  'can_export'            => true,
  'has_archive'           => true,
  'exclude_from_search'   => false,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_activity', $args );

// Activity meta boxes

function humble_lms_activity_add_meta_boxes()
{
  add_meta_box( 'humble_lms_activity_trigger_mb', __('Activity type', 'humble-lms'), 'humble_lms_activity_trigger_mb', 'humble_lms_activity', 'normal', 'default' );
  add_meta_box( 'humble_lms_activity_action_mb', __('Action following the activity', 'humble-lms'), 'humble_lms_activity_action_mb', 'humble_lms_activity', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_activity_add_meta_boxes' );

// Type meta box

function humble_lms_activity_trigger_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $type = get_post_meta($post->ID, 'humble_lms_activity_trigger', true);
  $type_track = (int)get_post_meta($post->ID, 'humble_lms_activity_trigger_track', true);
  $type_course = (int)get_post_meta($post->ID, 'humble_lms_activity_trigger_course', true);
  $type_lesson = (int)get_post_meta($post->ID, 'humble_lms_activity_trigger_lesson', true);

  $tracks = get_posts( array(
    'post_type' => 'humble_lms_track',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  $courses = get_posts( array(
    'post_type' => 'humble_lms_course',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  $lessons = get_posts( array(
    'post_type' => 'humble_lms_lesson',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  echo '<select class="widefat" name="humble_lms_activity_trigger" id="humble_lms_activity_trigger">';
    echo '<option disabled selected>' . __('Please select an activity trigger', 'humble-lms') . '&hellip;</option>';
    $selected = $type === 'user_completes_track' ? 'selected' : '';
    echo '<option value="user_completes_track" data-select="humble_lms_activity_trigger_track" ' . $selected . '>' . __('Student completes a track', 'humble-lms') . '</option>';
    $selected = $type === 'user_completes_course' ? 'selected' : '';
    echo '<option value="user_completes_course" data-select="humble_lms_activity_trigger_course" ' . $selected . '>' . __('Student completes a course', 'humble-lms') . '</option>';
    $selected = $type === 'user_completes_lesson' ? 'selected' : '';
    echo '<option value="user_completes_lesson" data-select="humble_lms_activity_trigger_lesson" ' . $selected . '>' . __('Student completes a lesson', 'humble-lms') . '</option>';
  echo '</select>';

  echo '<select class="widefat humble-lms-activity-trigger-select" name="humble_lms_activity_trigger_track" id="humble_lms_activity_trigger_track">';
    echo '<option disabled selected>' . __('Select a track', 'humble-lms') . '&hellip;</option>';
    foreach( $tracks as $track ) {
      $selected = $type_track === $track->ID ? 'selected' : '';
      echo '<option value="' . $track->ID . '" ' . $selected . '>' . $track->post_title . '</option>';
    }
  echo '</select>';

  echo '<select class="widefat humble-lms-activity-trigger-select" name="humble_lms_activity_trigger_course" id="humble_lms_activity_trigger_course">';
    echo '<option disabled selected>' . __('Select a course', 'humble-lms') . '&hellip;</option>';
    foreach( $courses as $course ) {
      $selected = $type_course === $course->ID ? 'selected' : '';
      echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
    }
  echo '</select>';

  echo '<select class="widefat humble-lms-activity-trigger-select" name="humble_lms_activity_trigger_lesson" id="humble_lms_activity_trigger_lesson">';
    echo '<option disabled selected>' . __('Select a lesson', 'humble-lms') . '&hellip;</option>';
    foreach( $lessons as $lesson ) {
      $selected = $type_lesson === $lesson->ID ? 'selected' : '';
      echo '<option value="' . $lesson->ID . '" ' . $selected . '>' . $lesson->post_title . '</option>';
    }
  echo '</select>';
}

// Action meta box

function humble_lms_activity_action_mb()
{
  global $post;

  $action = get_post_meta($post->ID, 'humble_lms_activity_action', true);
  $action_award = (int)get_post_meta($post->ID, 'humble_lms_activity_action_award', true);

  echo '<select class="widefat" name="humble_lms_activity_action" id="humble_lms_activity_action">';
    echo '<option disabled selected>' . __('Select an action following the activity', 'humble-lms') . '&hellip;</option>';
    $selected = $action === 'award' ? 'selected' : '';
    echo '<option value="award" data-select="humble_lms_activity_action_award" ' . $selected . '>' . __('Grant an award', 'humble-lms') . '</option>';
  echo '</select>';

  $awards = get_posts( array(
    'post_type' => 'humble_lms_award',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  echo '<select class="widefat humble-lms-activity-action-select" name="humble_lms_activity_action_award" id="humble_lms_activity_action_award">';
    echo '<option disabled selected>' . __('Select an award', 'humble-lms') . '&hellip;</option>';
    foreach( $awards as $award ) {
      $selected = $action_award === $award->ID ? 'selected' : '';
      echo '<option value="' . $award->ID . '" ' . $selected . '>' . $award->post_title . '</option>';
    }
  echo '</select>';
}

// Save metabox data

function humble_lms_save_activity_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_activity' ) {
    return false;
  }

  // Let's save some data!
  $activity_meta['humble_lms_activity_trigger'] = isset( $_POST['humble_lms_activity_trigger'] ) ? sanitize_text_field( $_POST['humble_lms_activity_trigger'] ) : '';
  $activity_meta['humble_lms_activity_trigger_track'] = isset( $_POST['humble_lms_activity_trigger_track'] ) ? (int)$_POST['humble_lms_activity_trigger_track'] : '';
  $activity_meta['humble_lms_activity_trigger_course'] = isset( $_POST['humble_lms_activity_trigger_course'] ) ? (int)$_POST['humble_lms_activity_trigger_course'] : '';
  $activity_meta['humble_lms_activity_trigger_lesson'] = isset( $_POST['humble_lms_activity_trigger_lesson'] ) ? (int)$_POST['humble_lms_activity_trigger_lesson'] : '';

  $activity_meta['humble_lms_activity_action'] = isset( $_POST['humble_lms_activity_action'] ) ? sanitize_text_field( $_POST['humble_lms_activity_action'] ) : '';
  $activity_meta['humble_lms_activity_action_award'] = isset( $_POST['humble_lms_activity_action_award'] ) ? (int)$_POST['humble_lms_activity_action_award'] : '';

  if( ! empty( $activity_meta ) && sizeOf( $activity_meta ) > 0 )
  {
    foreach ($activity_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_activity_meta_boxes', 1, 2);
