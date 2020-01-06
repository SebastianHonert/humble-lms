<?php

$labels = array(
  'name'                  => _x( 'Courses', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Course', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Courses', 'humble_lms' ),
  'name_admin_bar'        => __( 'Courses', 'humble_lms' ),
  'archives'              => __( 'Course Archives', 'humble_lms' ),
  'attributes'            => __( 'Course Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Course:', 'humble_lms' ),
  'all_items'             => __( 'All Courses', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Course', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Course', 'humble_lms' ),
  'edit_item'             => __( 'Edit Course', 'humble_lms' ),
  'update_item'           => __( 'Update Course', 'humble_lms' ),
  'view_item'             => __( 'View Course', 'humble_lms' ),
  'view_items'            => __( 'View Courses', 'humble_lms' ),
  'search_items'          => __( 'Search Course', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into course', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this course', 'humble_lms' ),
  'items_list'            => __( 'Courses list', 'humble_lms' ),
  'items_list_navigation' => __( 'Courses list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter courses list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('courses', 'humble-lms'),
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Course', 'humble_lms' ),
  'description'           => __( 'Course', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions', 'post-formats' ),
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

register_post_type( 'humble_lms_course', $args );

// Course meta boxes

function humble_lms_course_add_meta_boxes()
{
  add_meta_box( 'humble_lms_course_lessons_mb', __('Lesson(s) in this course', 'humble-lms'), 'humble_lms_course_lessons_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_duration_mb', __('Duration (approximately, e.g. 8 hours)', 'humble-lms'), 'humble_lms_course_duration_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_show_featured_image_mb', __('Display featured image', 'humble-lms'), 'humble_lms_course_show_featured_image_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_instructors_mb', __('Select instructor(s) for this course (optional)', 'humble-lms'), 'humble_lms_course_instructors_mb', 'humble_lms_course', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_course_add_meta_boxes' );

// Lessons meta box

function humble_lms_course_lessons_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $course_lessons = get_post_meta( $post->ID, 'humble_lms_course_lessons', true );
  $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

  $args = array(
    'post_type' => 'humble_lms_lesson',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'exclude' => $course_lessons
  );

  $lessons = get_posts( $args );

  $selected_lessons = [];
  foreach( $course_lessons as $id ) {
    if( get_post_status( $id ) ) {
      $lesson = get_post( $id );
      array_push( $selected_lessons, $lesson );
    }
  }

  if( $lessons || $selected_lessons ):

    echo '<div id="humble-lms-admin-course-lessons">';
      echo '<select class="humble-lms-searchable" data-content="course_lessons"  multiple="multiple">';
        foreach( $selected_lessons as $lesson ) {
          echo '<option data-id="' . $lesson->ID . '" value="' . $lesson->ID . '" ';
            if( is_array( $course_lessons ) && in_array( $lesson->ID, $course_lessons ) ) { echo 'selected'; }
          echo '>' . $lesson->post_title . ' (ID ' . $lesson->ID . ')</option>';
        }
        foreach( $lessons as $lesson ) {
          echo '<option data-id="' . $lesson->ID . '" value="' . $lesson->ID . '" ';
            if( is_array( $course_lessons ) && in_array( $lesson->ID, $course_lessons ) ) { echo 'selected'; }
          echo '>' . $lesson->post_title . ' (ID ' . $lesson->ID . ')</option>';
        }
      echo '</select>';
      echo '<input class="humble-lms-multiselect-value" id="humble_lms_course_lessons" name="humble_lms_course_lessons" type="hidden" value="' . implode(',', $course_lessons) . '">';
    echo '</div>';
  
  else:

    echo '<p>' . sprintf( __('No lessons found. Please %s first.', 'humble-lms'), '<a href="' . admin_url('/edit.php?post_type=humble_lms_lesson') . '">add one or more lessons</a>' ) . '</p>';

  endif;
}

// Duration meta box

function humble_lms_course_duration_mb()
{
  global $post;

  $duration = get_post_meta($post->ID, 'humble_lms_course_duration', true);

  echo '<input type="text" class="widefat" name="humble_lms_course_duration" id="humble_lms_course_duration" value="">';
  
}

// Featured image meta box

function humble_lms_course_show_featured_image_mb() {
  global $post;
  global $wp_roles;

  $show = get_post_meta($post->ID, 'humble_lms_course_show_featured_image', true);
  $checked = $show ? 'checked' : '';

  echo '<p><input type="checkbox" name="humble_lms_course_show_featured_image" id="humble_lms_course_show_featured_image" value="1" ' . $checked . '>' . __('Yes, display the featured image on the course page.', 'humble-lms') . '</p>';
}

// Course Instructor

function humble_lms_course_instructors_mb()
{
  global $post;

  $course_instructors = get_post_meta( $post->ID, 'humble_lms_course_instructors', true );
  $course_instructors = ! empty( $course_instructors[0] ) ? json_decode( $course_instructors[0] ) : [];

  $args = array(
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'exclude' => $course_instructors
  );

  $instructors = get_users( $args );

  $selected_instructors = [];

  foreach( $course_instructors as $key => $user_id ) {
    if( get_userdata( $user_id ) ) {
      $instructor = get_user_by( 'id', $user_id );
      array_push( $selected_instructors, $instructor );
    }
  }

  if( $instructors || $selected_instructors ):

    echo '<div id="humble-lms-admin-course-instructors humble_lms_multiselect_course_instructors">';
      echo '<select class="humble-lms-searchable" data-content="course_instructors" multiple="multiple">';
        foreach( $selected_instructors as $instructor ) {
          echo '<option data-id="' . $instructor->ID . '" value="' . $instructor->ID . '" ';
            if( is_array( $course_instructors ) && in_array( $instructor->ID, $course_instructors ) ) { echo 'selected'; }
          echo '>' . $instructor->display_name . ' (ID ' . $instructor->ID . ')</option>';
        }
        foreach( $instructors as $instructor ) {
          echo '<option data-id="' . $instructor->ID . '" value="' . $instructor->ID . '" ';
            if( is_array( $course_instructors ) && in_array( $instructor->ID, $course_instructors ) ) { echo 'selected'; }
          echo '>' . $instructor->display_name . ' (ID ' . $instructor->ID . ')</option>';
        }
      echo '</select>';
      echo '<input class="humble-lms-multiselect-value" id="humble_lms_course_instructors" name="humble_lms_course_instructors" type="hidden" value="' . implode(',', $course_instructors) . '">';
    echo '</div>';
  else:

    echo '<p>' . sprintf( __('No instructors found. Please %s first.', 'humble-lms'), '<a href="' . admin_url('/edit.php?post_type=humble_lms_lesson') . '">add one or more instructors</a>' ) . '</p>';

  endif;
}

// Save metabox data

function humble_lms_save_course_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_course' ) {
    return false;
  }

  // Let's save some data!
  $course_meta['humble_lms_course_lessons'] = isset( $_POST['humble_lms_course_lessons'] ) ? (array) $_POST['humble_lms_course_lessons'] : array();
  $course_meta['humble_lms_course_lessons'] = array_map( 'esc_attr', $course_meta['humble_lms_course_lessons'] );
  $course_meta['humble_lms_course_duration'] = sanitize_text_field( $_POST['humble_lms_course_duration'] );
  $course_meta['humble_lms_course_show_featured_image'] = (int)$_POST['humble_lms_course_show_featured_image'];
  $course_meta['humble_lms_course_instructors'] = isset( $_POST['humble_lms_course_instructors'] ) ? (array) $_POST['humble_lms_course_instructors'] : array();
  $course_meta['humble_lms_course_instructors'] = array_map( 'esc_attr', $course_meta['humble_lms_course_instructors'] );

  if( ! empty( $course_meta ) && sizeOf( $course_meta ) > 0 )
  {
    foreach ($course_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_course_meta_boxes', 1, 2);
