<?php

$labels = array(
  'name'                  => _x( 'Activities', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Activity', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Activities', 'humble-lms' ),
  'name_admin_bar'        => __( 'Activities', 'humble-lms' ),
  'archives'              => __( 'Activity Archives', 'humble-lms' ),
  'attributes'            => __( 'Activity Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Activity:', 'humble-lms' ),
  'all_items'             => __( 'All Activities', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Activity', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Activity', 'humble-lms' ),
  'edit_item'             => __( 'Edit Activity', 'humble-lms' ),
  'update_item'           => __( 'Update Activity', 'humble-lms' ),
  'view_item'             => __( 'View Activity', 'humble-lms' ),
  'view_items'            => __( 'View Activities', 'humble-lms' ),
  'search_items'          => __( 'Search Activity', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into activity', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this activity', 'humble-lms' ),
  'items_list'            => __( 'Activities list', 'humble-lms' ),
  'items_list_navigation' => __( 'Activities list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter activities list', 'humble-lms' ),
);

$rewrite = array(
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Activity', 'humble-lms' ),
  'description'           => __( 'Activity', 'humble-lms' ),
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
  'show_in_admin_bar'     => false,
  'show_in_nav_menus'     => false,
  'can_export'            => true,
  'has_archive'           => false,
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
    $selected = $type === 'user_completed_track' ? 'selected' : '';
    echo '<option value="user_completed_track" data-select="humble_lms_activity_trigger_track" ' . $selected . '>' . __('Student completed a track', 'humble-lms') . '</option>';
    $selected = $type === 'user_completed_course' ? 'selected' : '';
    echo '<option value="user_completed_course" data-select="humble_lms_activity_trigger_course" ' . $selected . '>' . __('Student completed a course', 'humble-lms') . '</option>';
    $selected = $type === 'user_completed_lesson' ? 'selected' : '';
    echo '<option value="user_completed_lesson" data-select="humble_lms_activity_trigger_lesson" ' . $selected . '>' . __('Student completed a lesson', 'humble-lms') . '</option>';
    $selected = $type === 'user_completed_all_tracks' ? 'selected' : '';
    echo '<option value="user_completed_all_tracks" ' . $selected . '>' . __('Student completed all tracks', 'humble-lms') . '</option>';
    $selected = $type === 'user_completed_all_courses' ? 'selected' : '';
    echo '<option value="user_completed_all_courses" ' . $selected . '>' . __('Student completed all courses', 'humble-lms') . '</option>';
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
  $action_email = (int)get_post_meta($post->ID, 'humble_lms_activity_action_email', true);
  $action_certificate = (int)get_post_meta($post->ID, 'humble_lms_activity_action_certificate', true);

  echo '<select class="widefat" name="humble_lms_activity_action" id="humble_lms_activity_action">';
    echo '<option disabled selected>' . __('Select an action following the activity', 'humble-lms') . '&hellip;</option>';
    $selected_award = $action === 'award' ? 'selected' : '';
    $selected_email = $action === 'email' ? 'selected' : '';
    $selected_certificate = $action === 'certificate' ? 'selected' : '';
    echo '<option value="award" data-select="humble_lms_activity_action_award" ' . $selected_award . '>' . __('Grant an award', 'humble-lms') . '</option>';
    echo '<option value="email" data-select="humble_lms_activity_action_email" ' . $selected_email . '>' . __('Send an email', 'humble-lms') . '</option>';
    echo '<option value="certificate" data-select="humble_lms_activity_action_certificate" ' . $selected_certificate . '>' . __('Issue a certificate', 'humble-lms') . '</option>';
  echo '</select>';

  $awards = get_posts( array(
    'post_type' => 'humble_lms_award',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  $emails = get_posts( array(
    'post_type' => 'humble_lms_email',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ) );

  $certificates = get_posts( array(
    'post_type' => 'humble_lms_cert',
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

  echo '<select class="widefat humble-lms-activity-action-select" name="humble_lms_activity_action_email" id="humble_lms_activity_action_email">';
    echo '<option disabled selected>' . __('Select an email', 'humble-lms') . '&hellip;</option>';
    foreach( $emails as $email ) {
      $selected = $action_email === $email->ID ? 'selected' : '';
      echo '<option value="' . $email->ID . '" ' . $selected . '>' . $email->post_title . '</option>';
    }
  echo '</select>';

  echo '<select class="widefat humble-lms-activity-action-select" name="humble_lms_activity_action_certificate" id="humble_lms_activity_action_certificate">';
    echo '<option disabled selected>' . __('Select a certificate', 'humble-lms') . '&hellip;</option>';
    foreach( $certificates as $certificate ) {
      $selected = $action_certificate === $certificate->ID ? 'selected' : '';
      echo '<option value="' . $certificate->ID . '" ' . $selected . '>' . $certificate->post_title . '</option>';
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
  $activity_meta['humble_lms_activity_action_email'] = isset( $_POST['humble_lms_activity_action_email'] ) ? (int)$_POST['humble_lms_activity_action_email'] : '';
  $activity_meta['humble_lms_activity_action_certificate'] = isset( $_POST['humble_lms_activity_action_certificate'] ) ? (int)$_POST['humble_lms_activity_action_certificate'] : '';

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
