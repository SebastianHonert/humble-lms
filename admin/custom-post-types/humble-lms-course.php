<?php

$labels = array(
  'name'                  => _x( 'Courses', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Course', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Courses', 'humble-lms' ),
  'name_admin_bar'        => __( 'Courses', 'humble-lms' ),
  'archives'              => __( 'Course Archives', 'humble-lms' ),
  'attributes'            => __( 'Course Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Course:', 'humble-lms' ),
  'all_items'             => __( 'All Courses', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Course', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Course', 'humble-lms' ),
  'edit_item'             => __( 'Edit Course', 'humble-lms' ),
  'update_item'           => __( 'Update Course', 'humble-lms' ),
  'view_item'             => __( 'View Course', 'humble-lms' ),
  'view_items'            => __( 'View Courses', 'humble-lms' ),
  'search_items'          => __( 'Search Course', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into course', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this course', 'humble-lms' ),
  'items_list'            => __( 'Courses list', 'humble-lms' ),
  'items_list_navigation' => __( 'Courses list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter courses list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => __('course', 'humble-lms'),
  'with_front'            => true,
);

$args = array(
  'label'                 => __( 'Course', 'humble-lms' ),
  'description'           => __( 'Course', 'humble-lms' ),
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
  add_meta_box( 'humble_lms_course_timestamps_mb', __('Timeframe', 'humble-lms'), 'humble_lms_course_timestamps_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_duration_mb', __('Duration (approximately, e.g. 8 hours)', 'humble-lms'), 'humble_lms_course_duration_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_sections_mb', __('Lesson(s) in this course', 'humble-lms'), 'humble_lms_course_sections_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_consecutive_order_mb', __('Order of completion', 'humble-lms'), 'humble_lms_course_consecutive_order_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_instructors_mb', __('Select instructor(s) for this course (optional)', 'humble-lms'), 'humble_lms_course_instructors_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_color_mb', __('Select a color for the course tile (optional)', 'humble-lms'), 'humble_lms_course_color_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_course_show_featured_image_mb', __('Display featured image', 'humble-lms'), 'humble_lms_course_show_featured_image_mb', 'humble_lms_course', 'normal', 'default' );
  add_meta_box( 'humble_lms_fixed_price_mb', __('Sell this course for a fixed price', 'humble-lms'), 'humble_lms_fixed_price_mb', 'humble_lms_course', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_course_add_meta_boxes' );

// Timeframe meta box

function humble_lms_course_timestamps_mb()
{
  global $post;
  
  $content_manager = new Humble_LMS_Content_Manager();
  $timestamps = get_post_meta( $post->ID, 'humble_lms_course_timestamps', false);
  $from = isset( $timestamps[0]['from'] ) && ! empty( $timestamps[0]['from'] ) ? date('Y-m-d', (int)$timestamps[0]['from'] ) : ''; 
  $to = isset( $timestamps[0]['to'] ) && ! empty( $timestamps[0]['to'] ) ? date('Y-m-d', (int)$timestamps[0]['to'] ) : ''; 
  $info = isset( $timestamps[0]['info'] ) ? $timestamps[0]['info'] : ''; 

  switch( $content_manager->course_is_open( $post->ID ) ) {
    case 1:
      echo '<p class="humble-lms-course-is-closed">' . __('This course has not started yet.', 'humble-lms') . '</p>';
      break;
    case 2:
      echo '<p class="humble-lms-course-is-closed">' . __('This course has been closed.', 'humble-lms') . '</p>';
      break;
    default:
      echo '<p class="humble-lms-course-is-open">' . __('This course is open.', 'humble-lms') . '</p>';
  }

  echo '<label class="humble-lms-label" for="humble_lms_course_timestamps_from">' . __('Course start', 'humble-lms') . '</label>';
  echo '<input type="text" class="widefat humble-lms-datepicker" name="humble_lms_course_timestamps[from]" id="humble_lms_course_timestamps_from" value="' . $from . '">';
  echo '<input type="hidden" name="humble_lms_course_timestamps[timestampFrom]" value="' . $from . '">';
  echo '<input type="button" class="button humble-lms-clear-datepicker-from" value="' . __('Remove', 'humble-lms') . '">';

  echo '<label class="humble-lms-label" for="humble_lms_course_timestamps_to">' . __('Course end', 'humble-lms') . '</label>';
  echo '<input type="text" class="widefat humble-lms-datepicker" name="humble_lms_course_timestamps[to]" id="humble_lms_course_timestamps_to" value="' . $to . '">';
  echo '<input type="hidden" name="humble_lms_course_timestamps[timestampTo]" value="' . $to . '">';
  echo '<input type="button" class="button humble-lms-clear-datepicker-to" value="' . __('Remove', 'humble-lms') . '">';

  echo '<label class="humble-lms-label" for="humble_lms_course_timestamps_info">' . __('Additional information on course timeframe', 'humble-lms') . '</label>';
  echo '<textarea class="widefat" type="humble-lms-timestamps-info" name="humble_lms_course_timestamps[info]" id="humble_lms_course_timestamps_info">' . $info . '</textarea>';
  echo '<p class="description">' . __('Allowed HTML tags', 'humble-lms') . ': strong, b, em, i, a' . '</p>';
}

// Duration meta box

function humble_lms_course_duration_mb()
{
  global $post;

  $duration = get_post_meta($post->ID, 'humble_lms_course_duration', true);

  echo '<input type="text" class="widefat" name="humble_lms_course_duration" id="humble_lms_course_duration" value="' . $duration . '">';
}

// Sections meta box

function humble_lms_course_sections_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $sections = Humble_LMS_Content_Manager::get_course_sections( $post->ID );
  $translator = new Humble_LMS_Translator;

  if( ! $sections ) {
    $sections = array(
      array(
        'title' => '',
        'lessons' => array(),
      )
    );
  }

  $args = array(
    'post_type' => 'humble_lms_lesson',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'lang' => $translator->current_language(),
  );

  $lessons = get_posts( $args );

  // Course sections wrapper
  echo '<div id="humble-lms-admin-course-sections" class="list-group">';

    // Add lesson lightbox
    echo '<div class="humble-lms-add-content-lightbox-wrapper">';
      echo '<div class="humble-lms-add-content-lightbox" data-post_type="humble_lms_lesson">';
        echo '<div class="humble-lms-add-content-lightbox-title">' . __('Add lesson', 'humble-lms') . '</div>';
        echo '<input type="text" class="widefat humble-lms-add-content-name" name="humble-lms-add-content-name" value="" placeholder="' . __('Lesson title', 'humble-lms') . '&hellip;">';
        echo '<p class="humble-lms-add-content-error" data-message="' . __('Please add a lesson title.', 'humble-lms') . '"></p>';
        echo '<a class="button button-primary humble-lms-add-content-submit">' . __('Create and add', 'humble-lms') . '</a> <a class="button humble-lms-add-content-cancel">' . __('Close') . '</a>';
        echo '<p class="humble-lms-add-content-success"><a target="_blank">' . __('Content added – click to edit.', 'humble-lms') . '</a></p>';
      echo '</div>';
    echo '</div>';

    // Sections
    foreach( $sections as $key => $section ) {
      $selected_lessons = [];
      $section_lessons = $section['lessons'];

      $args = array(
        'post_type' => 'humble_lms_lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'exclude' => $section_lessons,
        'lang' => $translator->current_language(),
      );
    
      $lessons = get_posts( $args );

      foreach( $section_lessons as $id ) {
        if( get_post_status( $id ) ) {
          $lesson = get_post( $id );
          array_push( $selected_lessons, $lesson );
        }
      }

      echo '<div class="humble-lms-course-section" class="list-group-item" data-id="' . ($key + 1) . '">';
        echo '<label for="humble_lms_course_section_title" class="humble-lms-course-section-title-label">' . __('Section', 'humble-lms') . ' <span class="humble-lms-course-section-number">' . ($key + 1) . '</span></label>';
        echo '<div class="humble-lms-section-toggle-wrapper">';  
          echo '<input type="text" name="humble_lms_course_section_title" class="widefat humble-lms-course-section-title" value="' . $section['title'] . '" placeholder="' . __('Section title (optional)', 'humble-lms') . '&hellip;">';
          echo '<label for="humble_lms_course_section_title" class="humble-lms-course-section-title-label">' . __('Lessons in this section', 'humble-lms') . '</label>';
          echo '<select class="humble-lms-searchable" data-content="course_lessons-' . ($key + 1) . '"  multiple="multiple">';
            foreach( $lessons as $lesson ) {
              echo '<option data-id="' . $lesson->ID . '" value="' . $lesson->ID . '" ';
                if( is_array( $section_lessons ) && in_array( $lesson->ID, $section_lessons ) ) { echo 'selected'; }
              echo '>' . $lesson->post_title . '</option>';
            }
            foreach( $selected_lessons as $lesson ) {
              echo '<option data-id="' . $lesson->ID . '" value="' . $lesson->ID . '" ';
                if( is_array( $section_lessons ) && in_array( $lesson->ID, $section_lessons ) ) { echo 'selected'; }
              echo '>' . $lesson->post_title . '</option>';
            }
          echo '</select>';
        echo '</div>';
        echo '<p class="humble-lms-course-section-remove-wrapper"><a class="button humble-lms-course-section-remove">' . __('Remove this section', 'humble-lms') . '</a> <a class="button humble-lms-toggle-section">' . __('Hide/Show', 'humble-lms') . '</a> <a class="humble-lms-open-admin-lightbox humble-lms-add-lesson-to-section button button-primary" data-id="' . ($key + 1) . '">' . __('Add lesson', 'humble-lms') . '</a></p>';
      echo '</div>';
    }

  echo '</div>';

  echo '<input type="hidden" name="humble_lms_course_sections" id="humble_lms_course_sections" value="">';

  echo '<p><a class="button button-primary humble-lms-repeater" data-element=".humble-lms-course-section" data-target="#humble-lms-admin-course-sections">' . __('Add section', 'humble-lms') . '</a></p>';
}

// Course Instructor

function humble_lms_course_instructors_mb()
{
  global $post;

  $course_instructors = Humble_LMS_Content_Manager::get_instructors( $post->ID );

  $args = array(
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'meta_key' => 'humble_lms_is_instructor',
    'meta_value' => 1,
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

    echo '<p>' . __('No instructors found. Please add one or more instructors first.', 'humble-lms') . '</p>';

  endif;
}

// Order of completion meta box

function humble_lms_course_consecutive_order_mb() {
  global $post;

  $chronological = get_post_meta($post->ID, 'humble_lms_course_consecutive_order', true);
  $checked = $chronological ? 'checked' : '';

  echo '<p><input type="checkbox" name="humble_lms_course_consecutive_order" id="humble_lms_course_consecutive_order" value="1" ' . $checked . '>' . __('Yes, the lessons in this course need to be completed in consecutive order.', 'humble-lms') . '</p>';
}

// Color meta box

function humble_lms_course_color_mb()
{
  global $post;

  $color = get_post_meta($post->ID, 'humble_lms_course_color', true);
  $color = ! $color ? '' : $color;

  echo '<input type="text" class="humble_lms_color_picker"" name="humble_lms_course_color" id="humble_lms_course_color" value="' . $color . '">';
}

// Featured image meta box

function humble_lms_course_show_featured_image_mb() {
  global $post;

  $show = get_post_meta($post->ID, 'humble_lms_course_show_featured_image', true);
  $checked = $show ? 'checked' : '';

  echo '<p><input type="checkbox" name="humble_lms_course_show_featured_image" id="humble_lms_course_show_featured_image" value="1" ' . $checked . '>' . __('Yes, display the featured image on the course page.', 'humble-lms') . '</p>';
}

// Sell for a fixed price meta box

function humble_lms_fixed_price_mb() {
  global $post;

  $sell = get_post_meta($post->ID, 'humble_lms_is_for_sale', true);
  $price = Humble_LMS_Content_Manager::get_price( $post->ID );

  $options = get_option('humble_lms_options');
  $currency = $options['currency'];
  $checked = $sell ? 'checked' : '';

  echo '<p><input type="checkbox" name="humble_lms_is_for_sale" id="humble_lms_is_for_sale" value="1" ' . $checked . '>' . __('Yes, sell this course for a fixed price.', 'humble-lms') . '</p>';
  echo '<p><label class="humble-lms-label">' . __('Price', 'humble-lms') . ' (' . $currency . ')</label><input type="number" min="0.00" max="9999999999.99" step="0.01" name="humble_lms_fixed_price" id="humble_lms_fixed_price" placeholder="0.00" value="' . $price . '"></p>';
  echo '<p class="description">' . __('Prices must be 2 digit decimals. Based on your browser language settings the saved value will sometimes be displayed with a comma instead of a dot.', 'humble-lms') . '</p>';
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
  $allowed_html = array(
    'a' => array(
      'href' => array(),
      'title' => array()
    ),
    'em' => array(),
    'strong' => array(),
  );

  $sections = get_post_meta( $post->ID, 'humble_lms_course_sections', true );
  $sections = json_decode( $sections, true );
  foreach( $sections as $key => $section ) {
    $sections[$key]['title'] = sanitize_text_field( $sections[$key]['title'] );
    $sections[$key]['lessons'] = sanitize_text_field( $sections[$key]['lessons'] );
  }

  $course_meta['humble_lms_course_duration'] = sanitize_text_field( $_POST['humble_lms_course_duration'] );
  $course_meta['humble_lms_course_timestamps']['from'] = isset( $_POST['humble_lms_course_timestamps']['timestampFrom'] ) ? (int)$_POST['humble_lms_course_timestamps']['timestampFrom'] : '';
  $course_meta['humble_lms_course_timestamps']['to'] = isset( $_POST['humble_lms_course_timestamps']['timestampTo'] ) ? (int)$_POST['humble_lms_course_timestamps']['timestampTo'] : '';
  $course_meta['humble_lms_course_timestamps']['info'] = wp_kses( $_POST['humble_lms_course_timestamps']['info'], $allowed_html );
  $course_meta['humble_lms_course_sections'] = json_encode( $sections );
  $course_meta['humble_lms_course_sections'] = $_POST['humble_lms_course_sections'];
  $course_meta['humble_lms_course_consecutive_order'] = (int)$_POST['humble_lms_course_consecutive_order'];
  $course_meta['humble_lms_instructors'] = isset( $_POST['humble_lms_course_instructors'] ) ? explode(',', $_POST['humble_lms_course_instructors']) : [];
  $course_meta['humble_lms_instructors'] = array_map( 'esc_attr', $course_meta['humble_lms_instructors'] );
  $course_meta['humble_lms_course_color'] = isset( $_POST['humble_lms_course_color'] ) ? sanitize_hex_color( $_POST['humble_lms_course_color'] ) : '';
  $course_meta['humble_lms_course_show_featured_image'] = (int)$_POST['humble_lms_course_show_featured_image'];
  $course_meta['humble_lms_is_for_sale'] = isset( $_POST['humble_lms_is_for_sale'] ) ? 1 : 0;
  $course_meta['humble_lms_fixed_price'] = isset( $_POST['humble_lms_fixed_price'] ) ? round( filter_var( $_POST['humble_lms_fixed_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ), 2 ) : 0.00;

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
