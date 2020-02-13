<?php

$labels = array(
  'name'                  => _x( 'Lessons', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Lesson', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Lessons', 'humble-lms' ),
  'name_admin_bar'        => __( 'Lessons', 'humble-lms' ),
  'archives'              => __( 'Lesson Archives', 'humble-lms' ),
  'attributes'            => __( 'Lesson Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Lesson:', 'humble-lms' ),
  'all_items'             => __( 'All Lessons', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Lesson', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Lesson', 'humble-lms' ),
  'edit_item'             => __( 'Edit Lesson', 'humble-lms' ),
  'update_item'           => __( 'Update Lesson', 'humble-lms' ),
  'view_item'             => __( 'View Lesson', 'humble-lms' ),
  'view_items'            => __( 'View Lessons', 'humble-lms' ),
  'search_items'          => __( 'Search Lesson', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into lesson', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this lesson', 'humble-lms' ),
  'items_list'            => __( 'Lessons list', 'humble-lms' ),
  'items_list_navigation' => __( 'Lessons list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter lessons list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => 'lesson',
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Lesson', 'humble-lms' ),
  'description'           => __( 'Lesson', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'editor', 'revisions', 'post-formats' ),
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

register_post_type( 'humble_lms_lesson', $args );

// Lesson meta boxes

function humble_lms_lesson_add_meta_boxes() {
  add_meta_box( 'humble_lms_lesson_description_mb', __('What is this lesson about?', 'humble-lms'), 'humble_lms_lesson_description_mb', 'humble_lms_lesson', 'normal', 'default' );
  add_meta_box( 'humble_lms_lesson_access_levels_mb', __('Who can access this lesson?', 'humble-lms'), 'humble_lms_lesson_access_levels_mb', 'humble_lms_lesson', 'normal', 'default' );
  add_meta_box( 'humble_lms_lesson_instructors_mb', __('Select instructor(s) for this lesson (optional)', 'humble-lms'), 'humble_lms_lesson_instructors_mb', 'humble_lms_lesson', 'normal', 'default' );
  add_meta_box( 'humble_lms_lesson_quizzes_mb', __('Quizzes attached to this lesson', 'humble-lms'), 'humble_lms_lesson_quizzes_mb', 'humble_lms_lesson', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_lesson_add_meta_boxes' );

// Description meta box

function humble_lms_lesson_description_mb() {
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $description = get_post_meta( $post->ID, 'humble_lms_lesson_description', true );

  echo '<p>' . __('Describe the content of this lesson in a few words. Allowed HTML-tags: strong, em, b, i.', 'humble-lms') . '</p>';
  echo '<textarea rows="5" class="widefat" name="humble_lms_lesson_description" id="humble_lms_lesson_description">' . $description . '</textarea>';
}

// Access level meta box

function humble_lms_lesson_access_levels_mb() {
  global $post;
  global $wp_roles;

  $roles = $wp_roles->roles;
  $levels = get_post_meta( $post->ID, 'humble_lms_lesson_access_levels', false );
  $levels = is_array( $levels ) && ! empty( $levels[0] ) ? $levels[0] : [];

  echo '<p>' . __('Select the user roles that can access this lesson. If you do not select any specific role(s), the lesson will be publicly available.', 'humble-lms') . '</p>';
  echo '<input type="checkbox" checked disabled>Administrator<br>';
  foreach( $roles as $key => $role ) {
    if( $key === 'administrator' ) continue;
    $checked = in_array( $key, $levels ) ? 'checked' : '';
    echo '<input type="checkbox" name="humble_lms_lesson_access_levels[]" value="' . $key . '" ' . $checked . '> ' . $role['name'] . '<br>';
  }
  echo '<p><a class="button humble-lms-access-levels-check-all">' . __('Select/deselect all', 'humble-lms') . '</a></p>';
}

// Lesson Instructor

function humble_lms_lesson_instructors_mb()
{
  global $post;

  $lesson_instructors = get_post_meta( $post->ID, 'humble_lms_instructors', true );
  $lesson_instructors = ! empty( $lesson_instructors[0] ) ? json_decode( $lesson_instructors[0] ) : [];
  if( $lesson_instructors === null ) $lesson_instructors = [];

  $args = array(
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'meta_key' => 'humble_lms_is_instructor',
    'meta_value' => 1,
    'exclude' => $lesson_instructors
  );

  $instructors = get_users( $args );

  $selected_instructors = [];

  foreach( $lesson_instructors as $key => $user_id ) {
    if( get_userdata( $user_id ) ) {
      $instructor = get_user_by( 'id', $user_id );
      array_push( $selected_instructors, $instructor );
    }
  }

  if( $instructors || $selected_instructors ):

    echo '<div id="humble-lms-admin-lesson-instructors humble_lms_multiselect_lesson_instructors">';
      echo '<select class="humble-lms-searchable" data-content="lesson_instructors" multiple="multiple">';
        foreach( $selected_instructors as $instructor ) {
          echo '<option data-id="' . $instructor->ID . '" value="' . $instructor->ID . '" ';
            if( is_array( $lesson_instructors ) && in_array( $instructor->ID, $lesson_instructors ) ) { echo 'selected'; }
          echo '>' . $instructor->display_name . ' (ID ' . $instructor->ID . ')</option>';
        }
        foreach( $instructors as $instructor ) {
          echo '<option data-id="' . $instructor->ID . '" value="' . $instructor->ID . '" ';
            if( is_array( $lesson_instructors ) && in_array( $instructor->ID, $lesson_instructors ) ) { echo 'selected'; }
          echo '>' . $instructor->display_name . ' (ID ' . $instructor->ID . ')</option>';
        }
      echo '</select>';
      echo '<input class="humble-lms-multiselect-value" id="humble_lms_lesson_instructors" name="humble_lms_lesson_instructors" type="hidden" value="' . implode(',', $lesson_instructors) . '">';
    echo '</div>';
  else:

    echo '<p>' . sprintf( __('No instructors found. Please %s first.', 'humble-lms'), '<a href="' . admin_url('/users.php') . '">add one or more instructors</a>' ) . '</p>';

  endif;
}

// Quizzes meta box

function humble_lms_lesson_quizzes_mb()
{
  global $post;

  $lesson_quizzes = Humble_LMS_Content_Manager::get_lesson_quizzes( $post->ID );

  $args = array(
    'post_type' => 'humble_lms_quiz',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'exclude' => $lesson_quizzes
  );

  $quizzes = get_posts( $args );

  $selected_quizzes = [];
  foreach( $lesson_quizzes as $id ) {
    if( get_post_status( $id ) ) {
      $lesson = get_post( $id );
      array_push( $selected_quizzes, $lesson );
    }
  }

  if( $quizzes || $selected_quizzes ):

    echo '<div id="humble-lms-admin-lesson-quizzes">';
      echo '<select class="humble-lms-searchable" data-content="lesson_quizzes"  multiple="multiple">';
        foreach( $selected_quizzes as $quiz ) {
          echo '<option data-id="' . $quiz->ID . '" value="' . $quiz->ID . '" ';
            if( is_array( $lesson_quizzes ) && in_array( $quiz->ID, $lesson_quizzes ) ) { echo 'selected'; }
          echo '>' . $quiz->post_title . ' (ID ' . $quiz->ID . ')</option>';
        }
        foreach( $quizzes as $quiz ) {
          echo '<option data-id="' . $quiz->ID . '" value="' . $quiz->ID . '" ';
            if( is_array( $lesson_quizzes ) && in_array( $quiz->ID, $lesson_quizzes ) ) { echo 'selected'; }
          echo '>' . $quiz->post_title . ' (ID ' . $quiz->ID . ')</option>';
        }
      echo '</select>';
      echo '<input class="humble-lms-multiselect-value" id="humble_lms_lesson_quizzes" name="humble_lms_lesson_quizzes" type="hidden" value="' . implode(',', $lesson_quizzes) . '">';
    echo '</div>';
  
  else:

    echo '<p>' . sprintf( __('No lessons found. Please %s first.', 'humble-lms'), '<a href="' . admin_url('/edit.php?post_type=humble_lms_lesson') . '">add one or more lessons</a>' ) . '</p>';

  endif;
}

// Save metabox data

function humble_lms_save_lesson_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_lesson' ) {
    return false;
  }

  // Let's save some data!
  $allowed_tags = array(
    'strong' => array(),
    'em' => array(),
    'b' => array(),
    'i' => array()
  );

  $lesson_meta['humble_lms_lesson_description'] = wp_kses( $_POST['humble_lms_lesson_description'], $allowed_tags );
  $lesson_meta['humble_lms_lesson_access_levels'] = isset( $_POST['humble_lms_lesson_access_levels'] ) ? (array) $_POST['humble_lms_lesson_access_levels'] : array();
  $lesson_meta['humble_lms_lesson_access_levels'] = array_map( 'esc_attr', $lesson_meta['humble_lms_lesson_access_levels'] );
  $lesson_meta['humble_lms_instructors'] = isset( $_POST['humble_lms_lesson_instructors'] ) ? (array) $_POST['humble_lms_lesson_instructors'] : array();
  $lesson_meta['humble_lms_instructors'] = array_map( 'esc_attr', $lesson_meta['humble_lms_instructors'] );
  $lesson_meta['humble_lms_quizzes'] = isset( $_POST['humble_lms_lesson_quizzes'] ) ? (array) $_POST['humble_lms_lesson_quizzes'] : array();
  $lesson_meta['humble_lms_quizzes'] = array_map( 'esc_attr', $lesson_meta['humble_lms_quizzes'] );

  if( ! empty( $lesson_meta ) && sizeOf( $lesson_meta ) > 0 )
  {
    foreach ($lesson_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_lesson_meta_boxes', 1, 2);
