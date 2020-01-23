<?php
/**
 * This class provides the frontend plugin shortcodes.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_Shortcodes' ) ) {

  class Humble_LMS_Public_Shortcodes {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;
      $this->access_handler = new Humble_LMS_Public_Access_Handler;
      $this->options_manager = new Humble_LMS_Admin_Options_Manager;
      $this->content_manager = new Humble_LMS_Content_Manager;

    }

    /**
     * Shortcode: track archive
     *
     * @since    0.0.1
     */
    public function track_archive( $atts = null ) {
      $html = '';
      $options = $this->options_manager->options;
      $tile_width = $options['tile_width_track'] ? $options['tile_width_track'] : 'half';

      extract( shortcode_atts( array (
        'ids' => '',
        'tile_width' => $tile_width,
        'style' => '',
        'class' => '',
      ), $atts ) );

      $args = array(
        'post_type' => 'humble_lms_track',
        'post_status' => 'publish',
        'posts_per_page' => get_option( 'posts_per_page' ),
        'meta_key' => 'humble_lms_track_position',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
      );

      if( ! empty( $ids ) ) {
        $args['post__in'] = explode(',', str_replace(' ','', $ids));
      }

      $tracks = new WP_Query( $args );

      if ( $tracks->have_posts() ) {
        $html .= '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';
        while ( $tracks->have_posts() ) {
          $tracks->the_post();
          $html .= do_shortcode('[humble_lms_track_tile tile_width="' . $tile_width . '" track_id="' . get_the_ID() . '"]');
        }
        $html .= '</div>';
        $html .= $this->humble_lms_paginate_links( $tracks );
      } else {
        $html .= '<p>' . __('No tracks found.', 'humble-lms') . '</p>';
      }

      wp_reset_postdata();

      return $html;
    }

    /**
     * Shortcode: track tile
     *
     * @since    0.0.1
     */
    public function track_tile( $atts = null ) {
      extract( shortcode_atts( array (
        'track_id' => '',
        'tile_width' => 'half',
        'class' => '',
        'style' => '',
      ), $atts ) );

      if( ! $track_id || get_post_type( $track_id ) !== 'humble_lms_track' )
        return;

      $track = get_post( $track_id );

      $completed = $this->user->completed_track( $track_id ) ? 'humble-lms-track-completed' : '';
      $featured_img_url = get_the_post_thumbnail_url( $track_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $track_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $track_id, 'humble_lms_track_duration', true );
      $duration = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->user->track_progress( $track_id, get_current_user_id() );

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed . ' ' . $class . '" style="' . $style .'"">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $track_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $track->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level . '</span>';
          $html .= '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration  . '</span>';
          if( is_user_logged_in() ) {
            $html .= '<span class="humble-lms-progress"><strong>' . __('Progress', 'humble-lms') . ':</strong> ' . $progress  . '%</span>';
            $html .= do_shortcode('[humble_lms_progress_bar progress="' . $progress . '"]');
          }
        $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: course archive
     *
     * @since    0.0.1
     */
    public function course_archive( $atts = null ) {
      global $post;

      $html = '';
      $options = $this->options_manager->options;
      $tile_width = $options['tile_width_course'] ? $options['tile_width_course'] : 'half';

      extract( shortcode_atts( array (
        'ids' => '',
        'track_id' => '',
        'tile_width' => $tile_width,
        'style' => '',
        'class' => '',
      ), $atts ) );

      $is_track = ! empty( $track_id ) && ( is_single() && $post->post_type === 'humble_lms_track' );

      $args = array(
        'post_type' => 'humble_lms_course',
        'post_status' => 'publish',
        'posts_per_page' => $is_track ? -1 : get_option( 'posts_per_page' ),
        'orderby' => 'title',
        'order' => 'ASC',
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
      );

      if( ! empty( $ids ) ) {
        $args['post__in'] = explode(',', str_replace(' ','', $ids));
      }

      if( $is_track) {
        $track_courses = get_post_meta($track_id, 'humble_lms_track_courses', true);
        $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

        if( ! empty( $track_courses ) ) {
          $args['post__in'] = $track_courses;
        } else {
          return '<p>' . __('This track does not include any courses.', 'humble-lms') . '</p>';
        }
      }

      $courses = new WP_Query( $args );

      if ( $courses->have_posts() ) {
        $html .= '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';
        while ( $courses->have_posts() ) {
          $courses->the_post();
            $html .= do_shortcode('[humble_lms_course_tile tile_width="' . $tile_width . '" course_id="' . $post->ID . '"]');
        }
        $html .= '</div>';
        $html .= $this->humble_lms_paginate_links( $courses );
      } else {
        $html .= '<p>' . __('No courses found.', 'humble-lms') . '</p>';
      }

      wp_reset_postdata();

      return $html;
    }

    /**
     * Shortcode: course tile
     *
     * @since    0.0.1
     */
    public function course_tile( $atts = null ) {
      extract( shortcode_atts( array (
        'course_id' => '',
        'tile_width' => 'half',
        'class' => '',
        'style' => '',
      ), $atts ) );

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return;

      $course = get_post( $course_id );

      $completed = $this->user->completed_course( $course_id ) ? 'humble-lms-course-completed' : '';
      $featured_img_url = get_the_post_thumbnail_url( $course_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $course_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
      $duration = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->user->course_progress( $course_id, get_current_user_id() );

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed . ' ' . $class . '" style="' . $style .'">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $course_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $course->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level . '</span>';
          $html .= '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration  . '</span>';
          if( is_user_logged_in() ) {
            $html .= '<span class="humble-lms-progress"><strong>' . __('Progress', 'humble-lms') . ':</strong> ' . $progress  . '%</span>';
            $html .= do_shortcode('[humble_lms_progress_bar progress="' . $progress . '"]');
          }
        $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Course progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function progress_bar( $atts = null ) {
      extract( shortcode_atts( array (
        'progress' => 0
      ), $atts ) );

      $html = '<div class="humble-lms-progress-bar">';
      $html .= '<div class="humble-lms-progress-bar-inner" style="width:' . $progress . '%"></div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: syllabus
     *
     * @since    0.0.1
     */
    public function syllabus( $atts = null ) {
      global $post;
      $html = '';

      extract( shortcode_atts( array (
        'course_id' => $post->ID,
        'context' => 'course',
        'style' => '',
        'class' => '',
      ), $atts ) );

      if( is_single() && get_post_type() === 'humble_lms_lesson' )
        $context = 'lesson';

      if( $context === 'lesson' ) {
        $lesson_id = $post->ID;
        $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
        
        // Try to get course_id by checking if this lesson is
        // attached to only one course.
        if( ! $course_id ) {
          $course_ids = $this->content_manager->find_courses_by('lesson', $lesson_id );
          if( is_array( $course_ids ) && sizeOf( $course_ids ) === 1 ) {
              $course_id = $course_ids[0];
          }
        }
      } else {
        $lesson_id = null;
      }

      $lessons = json_decode( get_post_meta($course_id, 'humble_lms_course_lessons', true)[0] );

      if( is_single() && get_post_type() === 'humble_lms_course' && empty( $lessons ) ) {
        return '<p>' . __('There are no lessons attached to this course', 'humble-lms') . '</p>';
      }
      
      $lessons = get_posts( array(
        'post_type' => 'humble_lms_lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'post__in',
        'order' => 'ASC',
        'post__in' => $lessons
      ));

      // Course Syllabus
      $html .= '<nav class="humble-lms-syllabus ' . $class . '" style="' . $style . '">';
        $html .= $lesson_id ? '' : '<h2>' . __('Syllabus', 'humble-lms') . '</h2>';

        if( ! $course_id ) {
          $html .= '<p>' . __('Looking for the course syllabus? It seems that you have accessed this lesson directly so it is not related to a specific course. Please open the course and start your learning activities from there.', 'humble-lms') . '</p>';
        } else {
          $html .= '<ul class="humble-lms-syllabus-lessons">';

          foreach( $lessons as $key => $lesson ) {
            $description = $context === 'course' ? get_post_meta( $lesson->ID, 'humble_lms_lesson_description', true ) : '';
            $class_lesson_current = $lesson->ID === $lesson_id ? 'humble-lms-syllabus-lesson--current' : '';
            $class_lesson_completed = $this->user->completed_lesson( get_current_user_id(), $lesson->ID ) ? 'humble-lms-syllabus-lesson--completed' : '';
            $locked = $this->access_handler->can_access_lesson( $lesson->ID ) ? '' : '<i class="ti-lock"></i>';
            $html .= '<li class="humble-lms-syllabus-lesson humble-lms-open-lesson ' . $class_lesson_current . ' ' . $class_lesson_completed . '" data-lesson-id="' . $lesson->ID  . '" data-course-id="' . $course_id . '">';
            $html .= '<span class="humble-lms-syllabus-title">' . $locked . $lesson->post_title . '</span>';
            $html .= $description? '<span class="humble-lms-syllabus-description">' . $description . '</span>' : '';
            $html .= '</li>';
          }
          
          $html .= '</ul>';
        }

      $html .= '</nav>';

      // Meta information
      if( $lesson_id ) {
        $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
        $duration = $duration ? '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration . '</span>' : '';

        $html .= '<p class="humble-lms-course-meta humble-lms-course-meta--lesson">';
          $html .= ! $course_id ? '<strong>' . __('Course', 'humble-lms') . ':</strong> ' . __('not selected', 'humble-lms') . '<br>' : '<strong>' . __('Course', 'humble-lms') . ':</strong> <a href="' . esc_url( get_permalink( $course_id ) ) . '">' . get_the_title( $course_id ) . '</a><br>';
          $html .= $duration;
        $html .= '</p>';
      }

      // View course/lesson
      if( $context === 'course' ) {
        $html .= '<span class="humble-lms-btn humble-lms-btn--success humble-lms-btn-start-course humble-lms-open-lesson" data-lesson-id="' . $lessons[0]->ID  . '" data-course-id="' . $course_id . '">' . __('Start the course now', 'humble-lms') . '</span>';
      }

      return $html;
    }

    /**
     * Display track/course/lesson instructor(s).
     * Lesson > Course > Track
     * 
     * @return string
     * @since   0.0.1
     */
    function course_instructors() {
      global $post;
      
      $html = '';

      $allowed_templates = array(
        'humble_lms_lesson',
        'humble_lms_course',
      );

      if( is_single() && in_array( $post->post_type, $allowed_templates ) ) {
        $instructors = get_post_meta( $post->ID, 'humble_lms_lesson_instructors', true );
        $instructors = ! empty( $instructors[0] ) ? json_decode( $instructors[0] ) : [];
      }

      $course_id = $post->post_type === 'humble_lms_course' ? $post->ID : null;
      if( ! $course_id ) {
        $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
      }

      if( empty( $instructors ) && $course_id ) {
        $instructors = get_post_meta( $course_id, 'humble_lms_course_instructors', true );
        $instructors = ! empty( $instructors[0] ) ? json_decode( $instructors[0] ) : [];
      }

      if( empty( $instructors ) ) {
        return $html;
      }

      $html .= '<div class="humble-lms-instructors">';

      foreach( $instructors as $user_id ) {
        if( get_userdata( $user_id ) ) {
          $user = get_user_by( 'id', $user_id );
          $html .= '<a href="mailto:' . $user->user_email . '">' . $user->display_name . '</a>';
        }
      }

      $html .= '</div>';

      return $html;
      
    }

    /**
     * Shortcode: mark lesson complete button
     *
     * @since    0.0.1
     */
    public function mark_complete_button( $atts = null ) {
      global $post;

      $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
      
      // Try to find course_id based on current lesson.
      if( ! $course_id && get_post_type( $post->ID ) === 'humble_lms_lesson' ) {
        $course_ids = $this->content_manager->find_courses_by('lesson', $post->ID );
        if( is_array( $course_ids ) && sizeOf( $course_ids ) === 1 ) {
          $course_id = $course_ids[0];
        }
      }

      if( ! $course_id )
        return;

      $course = get_post( $course_id );

      if( ! $course )
        return;

      extract( shortcode_atts( array (
        'style' => '',
        'class' => '',
      ), $atts ) );

      $lessons = json_decode( get_post_meta($course_id, 'humble_lms_course_lessons', true)[0] );
      
      $key = array_search( $post->ID, $lessons );
      $is_first = $key === array_key_first( $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last ) {
        $next_lesson = get_post( $lessons[$key+1] );
      }

      if( ! $is_first ) {
        $prev_lesson = get_post( $lessons[$key-1] );
      }

      $html = '<form method="post" id="humble-lms-mark-complete">';
        $html .= '<input type="hidden" name="course-id" id="course-id" value="' . $course_id . '">';
        $html .= '<input type="hidden" name="lesson-id" id="lesson-id" value="' . $post->ID . '">';
        $html .= '<input type="hidden" name="lesson-completed" id="lesson-completed" value="' . $this->user->completed_lesson( get_current_user_id(), $post->ID ) . '">';
        
        if( $this->user->completed_lesson( get_current_user_id(), $post->ID ) ) {
          $html .= '<input type="submit" class="humble-lms-btn humble-lms-btn--success" value="' . __('Mark incomplete and continue', 'humble-lms') . '">';
        } else {
          $html .= '<input type="submit" class="humble-lms-btn humble-lms-btn--error" value="' . __('Mark complete and continue', 'humble-lms') . '">';
        }
      $html .= '</form>';

      $html .= '<div class="humble-lms-next-prev-lesson">';

      if( $is_first ) {
        $html .= '<a class="humble-lms-prev-lesson-link" href="' . esc_url( get_permalink( $course_id ) ) . '">' . __('Back to course overview', 'humble-lms') . '</a>';
      } else {
        $html .= '<a class="humble-lms-prev-lesson-link humble-lms-open-lesson" data-course-id="' . $course_id . '" data-lesson-id="' . $prev_lesson->ID . '">' . __('Previous lesson', 'humble-lms') . '</a>';
      }

      if( $is_last ) {
        $html .= '<a class="humble-lms-next-lesson-link humble-lms-open-lesson" data-course-id="' . $course_id . '" data-lesson-id="' . $lessons[0] . '">' . __('Back to first lesson', 'humble-lms') . '</a>';
      } else {
        $html .= '<a class="humble-lms-next-lesson-link humble-lms-open-lesson" data-course-id="' . $course_id . '" data-lesson-id="' . $next_lesson->ID . '">' . __('Next lesson', 'humble-lms') . '</a>';
      }
      
      $html .= '</div>';

      return $html;
    }

    /**
     * Pagination
     * 
     * @since   0.0.1
     */
    function humble_lms_paginate_links( $query ) {
      global $wp_query; if( ! $query ) $query = $wp_query;
  
      $big = 999999999;
      $html = paginate_links( array(
          'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
          'format' => '?paged=%#%',
          'current' => max( 1, get_query_var('paged') ),
          'total' => $query->max_num_pages,
          'mid_size' => 5,
          'prev_text' => '&laquo;',
          'next_text' => '&raquo;'
      ) );

      return $html;
    }

    /**
     * Display user progress (tracks and courses).
     * 
     * @return string
     * @since   0.0.1
     */
    function user_progress() {
      if( ! is_user_logged_in() )
        return $this->display_login_text();

      $html = '';
      $tracks_completed = get_user_meta( get_current_user_id(), 'humble_lms_tracks_completed', false );
      $courses_completed = get_user_meta( get_current_user_id(), 'humble_lms_courses_completed', false );

      $args = array(
        'post_type' => 'humble_lms_track',
        'posts_per_page' => -1,
        'post_status' => 'publish'
      );

      $tracks = get_posts( $args );

      if( ! $tracks ) {
        $html .= '<p>' . __('No tracks available.', 'humble-lms') . '</p>';
      }

      foreach( $tracks as $key => $track ) {
        $counter = 0;
        $class_completed = $this->user->completed_track( $track->ID ) ? 'humble-lms-track-progress-track--completed' : '';
        $html .= '<p class="humble-lms-progress-track-title ' . $class_completed . '"><a href="' . esc_url( get_permalink( $track->ID ) ) . '">' . get_the_title( $track->ID ) . '</a></p>';

        $track_courses = get_post_meta( $track->ID, 'humble_lms_track_courses', true );
        $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

        if( empty( $track_courses) )
          continue;

        $html .= '<div class="humble-lms-track-progress">';

        foreach( $track_courses as $key => $course ) {
          if( get_post_status( $course ) !== 'publish' )
            continue;

          $counter++;
          $class_completed = $this->user->completed_course( $course ) ? 'humble-lms-track-progress-course--completed' : '';
          $html .= '<a href="' . esc_url( get_permalink( $course ) ) . '" class="humble-lms-track-progress-course ' . $class_completed . '" title="' . get_the_title( $course ) . '">';
          $html .= $counter;
          $html .= '</a>';
          $html .= $key !== array_key_last( $track_courses ) ? '<i class="ti-angle-right humble-lms-track-progress-course-separator"></i>' : '';
        }

        $html .= '</div>';
      }

      return $html;
    }

    /**
     * Display user awards.
     * 
     * @return string
     * @since   0.0.1
     */
    function user_awards() {
      if( ! is_user_logged_in() )
        return $this->display_login_text();

      $awards = get_user_meta( get_current_user_id(), 'humble_lms_awards', false );
      $html = '';

      if( ! $awards ) {
        $html .= '<p>' . __('No have not received any awards yet.', 'humble-lms') . '</p>';
      } else {
        $html .= '<div class="humble-lms-awards-list">';
        foreach( $awards[0] as $award ) {
          $html .= '<div class="humble-lms-awards-list-item">';
          $html .= '<img src="' . get_the_post_thumbnail_url( $award ) . '" title="' . get_the_title( $award ) . '" alt="' . get_the_title( $award ) . '" />';
          $html .= '</div>';
        }
        $html .= '</div>';
      }

      return $html;
      
    }

    /**
     * Display user certificates.
     * 
     * @return string
     * @since   0.0.1
     */
    function user_certificates() {
      if( ! is_user_logged_in() )
        return $this->display_login_text();

      $certificates = get_user_meta( get_current_user_id(), 'humble_lms_certificates', false );
      $html = '';

      if( ! $certificates ) {
        $html .= '<p>' . __('No have not been issued any certificates yet.', 'humble-lms') . '</p>';
      } else {
        $html .= '<div class="humble-lms-certificates-list">';
        foreach( $certificates[0] as $certificate ) {
          $image = has_post_thumbnail( $certificate ) ? get_the_post_thumbnail_url( $certificate ) : plugins_url( 'humble-lms/public/assets/img/certificate.png' );
          $html .= '<div class="humble-lms-certificates-list-item">';
          $html .= '<a href="' . esc_url( get_permalink( $certificate ) ) . '"><img src="' . $image . '" alt="' . get_the_title( $certificate ) . '" title="' . get_the_title( $certificate ) . '" /></a>';
          $html .= '</div>';
        }
        $html .= '</div>';
      }

      return $html;
      
    }

    /**
     * Default login link text.
     * 
     * @return string
     * @since   0.0.1
     */
    public function display_login_text() {
      return sprintf( __('Please %s first.', 'humble-lms'), '<a href="' . $this->options_manager->login_url . '">log in</a>');
    }

    /**
     * Custom login form.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_custom_login_form() {
      ob_start();
  
      if( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          echo '<strong>' . __('Login failed.', 'humble-lms') . '</strong> ' . __('Username and password do not match. ', 'humble-lms');
        echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'empty' ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          echo  '<strong>' . __('Login failed.', 'humble-lms') . '</strong> ' . __('Please enter your username and password.', 'humble-lms');
        echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'false' ) {
        echo '<div class="humble-lms-message humble-lms-message--success">';
          echo  __('You have successfully been logged out.', 'humble-lms');
        echo '</div>';
      }

      if ( ! is_user_logged_in() ) {
        $args = array(
            'redirect' => admin_url(), 
            'form_id' => 'humble-lms-custom-login-form',
            'label_username' => __( 'Username', 'humble-lms' ),
            'label_password' => __( 'Password', 'humble-lms' ),
            'label_remember' => __( 'Remember me', 'humble-lms' ),
            'label_log_in' => __( 'Login' ),
            'remember' => true
        );
        wp_login_form( $args );
      } else {
          echo '<p>' . __('You are already logged in.', 'humble-lms') . '</p>';
      }

      return ob_get_clean();
    }

    /**
     * Custom registration form.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_custom_registration_form() {
      if( is_user_logged_in() ) {
        return '<p>' . __('You are already logged in.', 'humble-lms') . '</p>';
      }

      ob_start();
      
      if( $codes = Humble_LMS_Public::humble_lms_errors()->get_error_codes() ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          foreach( $codes as $code ) {
            $message = Humble_LMS_Public::humble_lms_errors()->get_error_message( $code );
            echo '<strong>' . __('Error') . ':</strong> ' . $message . '<br>';
          }
        echo '</div>';
      }

      $post_user_login = isset( $_POST['humble-lms-user-login'] ) ? sanitize_text_field( $_POST['humble-lms-user-login'] ) : '';
      $post_user_first = isset( $_POST['humble-lms-user-first'] ) ? sanitize_text_field( $_POST['humble-lms-user-first'] ) : '';
      $post_user_last = isset( $_POST['humble-lms-user-last'] ) ? sanitize_text_field( $_POST['humble-lms-user-last'] ) : '';
      $post_user_email = isset( $_POST['humble-lms-user-email'] ) ? sanitize_text_field( $_POST['humble-lms-user-email'] ) : '';

      ?>
      
      <form id="humble-lms-registration-form" class="humble-lms-form" action="" method="POST">
        <fieldset>
          <p>
            <label for="humble-lms-user-login" class="humble-lms-required"><?php _e('Username', 'humble-lms'); ?></label>
            <input name="humble-lms-user-login" id="humble-lms-user-login" class="humble-lms-required" type="text" value="<?php echo $post_user_login; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-first" class="humble-lms-required"><?php _e('First Name', 'humble-lms'); ?></label>
            <input name="humble-lms-user-first" id="humble-lms-user-first" type="text" value="<?php echo $post_user_first; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-last" class="humble-lms-required"><?php _e('Last Name', 'humble-lms'); ?></label>
            <input name="humble-lms-user-last" id="humble-lms-user-last" type="text" value="<?php echo $post_user_last; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-email" class="humble-lms-required"><?php _e('Email address', 'humble-lms'); ?></label>
            <input name="humble-lms-user-email" id="humble-lms-user-email" class="humble-lms-required" type="email" value="<?php echo $post_user_email; ?>" />
          </p>
          <p>
            <label for="password" class="humble-lms-required"><?php _e('Password'); ?></label>
            <input name="humble-lms-user-pass" id="password" class="humble-lms-required" type="password" />
          </p>
          <p>
            <label for="password-again" class="humble-lms-required"><?php _e('Password Again', 'humble-lms'); ?></label>
            <input name="humble-lms-user-pass-confirm" id="password-again" class="humble-lms-required" type="password" />
          </p>
          <p>
            <input type="hidden" name="humble-lms-register-nonce" value="<?php echo wp_create_nonce('humble-lms-register-nonce'); ?>" />
            <input type="submit" value="<?php _e('Register Your Account', 'humble-lms'); ?>"/>
          </p>
        </fieldset>
      </form><?php 
      
      return ob_get_clean();
    }
    
  }
  
}
