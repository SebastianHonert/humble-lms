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

    }

    /**
     * Shortcode: track archive
     *
     * @since    0.0.1
     */
    public function track_archive( $atts = null ) {
      $options = $this->options_manager->options;
      $tile_width = $options['tile_width_track'] ? $options['tile_width_track'] : 'half';

      extract( shortcode_atts( array (
        'tile_width' => $tile_width,
        'style' => '',
        'class' => '',
      ), $atts ) );

      $tracks = new WP_Query( array(
        'post_type' => 'humble_lms_track',
        'post_status' => 'publish',
        'posts_per_page' => get_option( 'posts_per_page' ),
        'meta_key' => 'humble_lms_track_position',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
      ) );

      $html = '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';

      if ( $tracks->have_posts() ) {
        while ( $tracks->have_posts() ) {
          $tracks->the_post();
          $html .= do_shortcode('[humble_lms_track_tile tile_width="' . $tile_width . '" track_id="' . get_the_ID() . '"]');
        }
      }

      $html .= '</div>';

      $html .= $this->humble_lms_paginate_links( $tracks );

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
        'tile_width' => 'half'
      ), $atts ) );

      $track = get_post( $track_id );
      $completed = $this->user->completed_track( $track_id ) ? 'humble-lms-track-completed' : '';
      $featured_img_url = get_the_post_thumbnail_url( $track_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $track_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $track_id, 'humble_lms_track_duration', true );
      $duration = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->track_progress( $track_id );

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed . '">';
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
            $html .= $this->progress_bar( $progress );
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

      $options = $this->options_manager->options;
      $tile_width = $options['tile_width_course'] ? $options['tile_width_course'] : 'half';

      extract( shortcode_atts( array (
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

      $html = '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';

      if ( $courses->have_posts() ) {
        while ( $courses->have_posts() ) {
          $courses->the_post();
            $html .= do_shortcode('[humble_lms_course_tile tile_width="' . $tile_width . '" course_id="' . $post->ID . '"]');
        }
      }

      $html .= '</div>';

      $html .= $this->humble_lms_paginate_links( $courses );

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
        'tile_width' => 'half'
      ), $atts ) );

      $course = get_post( $course_id );
      $completed = $this->user->completed_course( $course_id ) ? 'humble-lms-course-completed' : '';
      $featured_img_url = get_the_post_thumbnail_url( $course_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $course_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
      $duration = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->course_progress( $course_id );

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed .'">';
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
            $html .= $this->progress_bar( $progress );
          }
        $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Track progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function track_progress( $track_id ) {
      if( ! $track_id )
        return 0;

      if( ! is_user_logged_in() ) {
        return 0;
      }
      
      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', true );
      $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];
      $courses_completed = get_user_meta( get_current_user_id(), 'humble_lms_courses_completed', false );
      $completed_track_courses = array_intersect( $courses_completed[0], $track_courses );

      if( ( empty( $track_courses ) ) || ( empty( $courses_completed[0] ) ) )
        return 0;

      $percent = count( $completed_track_courses ) * 100 / count( $track_courses );

      return round( $percent, 1 );
    }

    /**
     * Course progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function course_progress( $course_id ) {
      if( ! $course_id )
        return 0;

      if( ! is_user_logged_in() ) {
        return 0;
      }
      
      $course_lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];
      $lessons_completed = get_user_meta( get_current_user_id(), 'humble_lms_lessons_completed', false );
      $completed_course_lessons = array_intersect( $lessons_completed[0], $course_lessons );

      if( ( empty( $course_lessons ) ) || ( empty( $lessons_completed[0] ) ) )
        return 0;

      $percent = count( $completed_course_lessons ) * 100 / count( $course_lessons );

      return round( $percent, 1 );
    }

    /**
     * Course progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function progress_bar( $progress = 0 ) {
      if( ! is_user_logged_in() ) {
        return;
      }

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

      extract( shortcode_atts( array (
        'course_id' => $post->ID,
        'context' => 'course',
        'style' => '',
        'class' => '',
      ), $atts ) );

      if( $context === 'lesson' ) {
        $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
        $lesson_id = $post->ID;
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

      $html = '';

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
            $class_lesson_completed = $this->user->completed_lesson( $lesson->ID ) ? 'humble-lms-syllabus-lesson--completed' : '';
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
        $level = strip_tags( get_the_term_list( $lesson_id, 'humble_lms_course_level', '', ', ') );
        $level = $level ? '<strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level : '';
        $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
        $duration = $duration ? '<br><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration : '';

        $html .= '<p class="humble-lms-course-meta humble-lms-course-meta--lesson">';
          $html .= ! $course_id ? '<strong>' . __('Course', 'humble-lms') . ':</strong> ' . __('not selected', 'humble-lms') . '<br>' : '<strong>' . __('Course', 'humble-lms') . ':</strong> <a href="' . esc_url( get_permalink( $course_id ) ) . '">' . get_the_title( $course_id ) . '</a><br>';
          $html .= $level;
          $html .= $duration;
        $html .= '</p>';
      }

      // View course/lesson
      if( $context === 'course' ) {
        $html .= '<span class="humble-lms-btn-start-course humble-lms-open-lesson humble-lms-btn" data-lesson-id="' . $lessons[0]->ID  . '" data-course-id="' . $course_id . '">' . __('Start the course now', 'humble-lms') . '</span>';
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
      $html .= '<span class="humble-lms-instructors-title">' . __('Course instructor(s)', 'humble-lms') . '</span>';

      foreach( $instructors as $user_id ) {
        if( get_userdata( $user_id ) ) {
          $user = get_user_by( 'id', $user_id );
          $html .= '<a href="mailto:' . $user->user_email . '">' . $user->nickname . '</a>';
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
        $html .= '<input type="hidden" name="lesson-completed" id="lesson-completed" value="' . $this->user->completed_lesson( $post->ID ) . '">';
        
        if( $this->user->completed_lesson( $post->ID ) ) {
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
        $html .= '<h2 class="' . $class_completed . '">' . get_the_title( $track->ID ) . '</h2>';

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

      // $html .= '<hr>';
      // $html .= '<h2>Testing</h2>';
      // $html .= '<h5>' . __('Tracks', 'humble-lms') . '</h5>';
      // $html .= implode( ', ', $tracks_completed[0] );
      // $html .= '<h5>' . __('Courses', 'humble-lms') . '</h5>';
      // $html .= implode( ', ', $courses_completed[0] );
      // $html .= '<h5>' . __('Lessons', 'humble-lms') . '</h5>';
      // $html .= implode( ', ', $lessons_completed[0] );
      // $html .= '<h5>' . __('Awards', 'humble-lms') . '</h5>';
      // $html .= implode( ', ', $awards[0] );

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
     * Default login link text.
     * 
     * @return string
     * @since   0.0.1
     */
    public function display_login_text() {
      return sprintf( __('Please %s first.', 'humble-lms'), '<a href="' . $this->options_manager->login_url . '">log in</a>');
    }
    
  }
  
}
