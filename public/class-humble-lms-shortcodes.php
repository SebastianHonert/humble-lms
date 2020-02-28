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
      $this->quiz = new Humble_LMS_Quiz;

    }

    /**
     * Shortcode: track archive
     *
     * @since    0.0.1
     */
    public function track_archive( $atts = null ) {
      $html = '';
      $options = $this->options_manager->options;
      $tile_width = isset( $options['tile_width_track'] ) ? $options['tile_width_track'] : 'half';

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
      $level_str = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $track_id, 'humble_lms_track_duration', true );
      $duration_str = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->user->track_progress( $track_id, get_current_user_id() );
      $color = get_post_meta( $track_id, 'humble_lms_track_color', true );
      $overlay_color = $color !== '' ? 'background-color:' . $color : '';

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed . ' ' . $class . '" style="' . $style .'"">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $track_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer" style="' . $overlay_color . '"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $track->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= $level ? '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level_str . '</span>' : '';
          $html .= $duration ? '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration_str  . '</span>' : '';
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
      $tile_width = isset( $options['tile_width_course'] ) ? $options['tile_width_course'] : 'half';

      extract( shortcode_atts( array (
        'ids' => '',
        'track_id' => '',
        'tile_width' => $tile_width,
        'style' => '',
        'class' => '',
      ), $atts ) );

      $is_track = ! empty( $track_id ) && ( is_single() && $post->post_type === 'humble_lms_track' );
      $courses = $this->content_manager->get_track_courses( $track_id );

      $args = array(
        'post_type' => 'humble_lms_course',
        'post_status' => 'publish',
        'posts_per_page' => $is_track ? -1 : get_option( 'posts_per_page' ),
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        'orderby' => 'post__in',
        'order' => 'ASC',
        'post__in' => $courses
      );

      if( ! empty( $ids ) ) {
        $args['post__in'] = explode(',', str_replace(' ','', $ids));
      }

      if( $is_track) {
        $track_courses = $this->content_manager->get_track_courses( $track_id );

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
      $level_str = $level ? $level : __('Not specified', 'humble-lms');
      $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
      $duration_str = $duration ? $duration : __('Not specified', 'humble-lms');
      $progress = $this->user->course_progress( $course_id, get_current_user_id() );
      $color = get_post_meta( $course_id, 'humble_lms_course_color', true );
      $overlay_color = $color !== '' ? 'background-color:' . $color : '';

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . ' ' . $completed . ' ' . $class . '" style="' . $style .'">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $course_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer" style="' . $overlay_color . '"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $course->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= $level ? '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level_str . '</span>' : '';
          $html .= $duration ? '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration_str  . '</span>' : '';
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

      $lessons = $this->content_manager->get_course_lessons( $course_id );

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
            $locked = $this->access_handler->can_access_lesson( $lesson->ID, $course_id ) === 'allowed' ? '' : '<i class="ti-lock"></i>';
            $html .= '<li class="humble-lms-syllabus-lesson humble-lms-open-lesson ' . $class_lesson_current . ' ' . $class_lesson_completed . '" data-lesson-id="' . $lesson->ID  . '" data-course-id="' . $course_id . '">';
            $html .= '<span class="humble-lms-syllabus-title">' . $locked . (int)($key+1) . '. ' . $lesson->post_title . '</span>';
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
        $lesson_id = isset( $lessons[0]->ID ) ? $lessons[0]->ID : 0;
        $html .= '<span class="humble-lms-btn humble-lms-btn--success humble-lms-btn-start-course humble-lms-open-lesson" data-lesson-id="' . $lesson_id . '" data-course-id="' . $course_id . '">' . __('Start the course now', 'humble-lms') . '</span>';
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
    function instructors( $atts = null ) {
      global $post;

      extract( shortcode_atts( array (
        'widget' => '',
        'style' => '',
        'class' => '',
      ), $atts ) );
      
      $html = '';
      $widget = filter_var( $widget, FILTER_VALIDATE_BOOLEAN );
      $allowed_templates = array(
        'humble_lms_lesson',
        'humble_lms_course',
        'humble_lms_track',
      );

      // Inside a lesson?
      if( $post->post_type === 'humble_lms_lesson' ) {
        $instructors = $this->content_manager->get_instructors( $post->ID );
      }

      // Inside a course?
      if( empty( $instructors ) ) {
        if( $post->post_type === 'humble_lms_course' ) {
          $post_id = $post->ID;
        } else {
          $post_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
        }
        
        $instructors = $this->content_manager->get_instructors( $post_id );
      }

      $html .= '<div class="humble-lms-instructors ' . $class . '" style="' . $style . '">';

      if( isset( $instructors[0] ) && ! empty( $instructors ) ) {
        foreach( $instructors as $user_id ) {
          if( get_userdata( $user_id ) ) {
            $user = get_user_by( 'id', $user_id );
            $html .= '<a href="mailto:' . $user->user_email . '">' . $user->display_name . '</a>';
          }
        }
      } else {
        $html .= $widget ? '<p>' . __('No instructors available.', 'humble-lms'). '</p>' : '';
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

      $lessons = $this->content_manager->get_course_lessons( $course_id );
      $key = array_search( $post->ID, $lessons );
      $is_first = $key === array_key_first( $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last ) {
        $next_lesson = get_post( $lessons[$key+1] );
      }

      if( ! $is_first ) {
        $prev_lesson = get_post( $lessons[$key-1] );
      }
      
      $html = '';
      $quizzes = Humble_LMS_Content_Manager::get_lesson_quizzes( $post->ID );
      $lesson_has_quiz = isset( $quizzes ) && ! empty( $quizzes );
      $lesson_completed = $this->user->completed_lesson( get_current_user_id(), $post->ID );
      $quiz_class = $lesson_has_quiz ? 'humble-lms-has-quiz' : '';
      $quiz_ids_string = implode(',', $quizzes);
      $passing_required = false;
      $user_completed_quizzes = true;

      foreach( $quizzes as $id ) {
        if( $this->quiz->get_passing_required( $id ) ) {
          $passing_required = true;
        }
        if( ! $this->user->completed_quiz( $id ) ) {
          $user_completed_quizzes = false;
        }
      }
  
      // Evaluate quiz button
      $button_text = ! $user_completed_quizzes ? __('Check your answers', 'humble-lms') : __('Quiz passed. Try again?', 'humble-lms');
      $button_class = $user_completed_quizzes ? 'humble-lms-btn--success' : '';
      if( $lesson_has_quiz ) {
        $html .= '<form method="post" id="humble-lms-evaluate-quiz" class="' . $quiz_class . '">';
          $html .= '<input type="hidden" name="course-id" value="' . $course_id . '">';
          $html .= '<input type="hidden" name="lesson-id" value="' . $post->ID . '">';
          $html .= '<input type="hidden" name="quiz-ids" value="' . $quiz_ids_string . '">';
          $html .= '<input type="hidden" name="lesson-completed" value="' . $lesson_completed . '">';
          $html .= '<input type="hidden" name="try-again" value="' . ( $user_completed_quizzes ? 1 : 0 ) . '">';
          $html .= '<input type="submit" class="humble-lms-btn ' . $button_class . '" value="' . $button_text . '">';
        $html .= '</form>';
      }

      // Mark complete button
      $hidden_style = ! $user_completed_quizzes && ( $passing_required && $lesson_has_quiz && ! $lesson_completed ) ? 'display:none' : '';
      $html .= '<form method="post" id="humble-lms-mark-complete" class="' . $quiz_class . '" style="' . $hidden_style . '">';
        $html .= '<input type="hidden" name="course-id" id="course-id" value="' . $course_id . '">';
        $html .= '<input type="hidden" name="lesson-id" id="lesson-id" value="' . $post->ID . '">';
        $html .= '<input type="hidden" name="quiz-ids" id="quiz-ids" value="' . $quiz_ids_string . '">';
        $html .= '<input type="hidden" name="lesson-completed" id="lesson-completed" value="' . $lesson_completed . '">';

        if( $lesson_completed ) {
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
        'post_status' => 'publish',
        'meta_key' => 'humble_lms_track_position',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
      );

      $tracks = get_posts( $args );

      if( ! $tracks ) {
        $html .= '<p>' . __('No tracks available.', 'humble-lms') . '</p>';
      }

      foreach( $tracks as $key => $track ) {
        $counter = 0;
        $class_completed = $this->user->completed_track( $track->ID ) ? 'humble-lms-track-progress-track--completed' : '';
        $html .= '<p class="humble-lms-progress-track-title ' . $class_completed . '"><a href="' . esc_url( get_permalink( $track->ID ) ) . '">' . get_the_title( $track->ID ) . '</a></p>';

        $track_courses = $this->content_manager->get_track_courses( $track->ID );

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

      if( isset( $awards[0] ) && ! empty( $awards[0] ) ) {
        foreach( $awards[0] as $key => $id ) {
          if( get_post_status( $id ) !== 'publish' ) {
            unset( $awards[0][$key] );
          }
        }
      }

      $html = '';

      if( ! isset( $awards[0] ) || ! $awards[0] || empty( $awards[0] ) ) {
        $html .= '<p>' . __('You have not received any awards yet.', 'humble-lms') . '</p>';
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

      if( isset( $certificates[0] ) && ! empty( $certificates[0] ) ) {
        foreach( $certificates[0] as $key => $id ) {
          if( get_post_status( $id ) !== 'publish' ) {
            unset( $certificates[0][$key] );
          }
        }
      }

      $html = '';

      if( ! isset( $certificates[0] ) || ! $certificates[0] || empty( $certificates[0] ) ) {
        $html .= '<p>' . __('You have not been issued any certificates yet.', 'humble-lms') . '</p>';
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
          echo '<strong>' . __('Login failed.', 'humble-lms') . '</strong> ' . __('Username and password do not match.', 'humble-lms');
        echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'empty' ) {
        // echo '<div class="humble-lms-message humble-lms-message--error">';
        //   echo  '<strong>' . __('Login failed.', 'humble-lms') . '</strong> ' . __('Please enter your username and password.', 'humble-lms');
        // echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'false' ) {
        echo '<div class="humble-lms-message humble-lms-message--success">';
          echo  __('You have successfully been logged out.', 'humble-lms');
        echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'invalidkey' ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          echo sprintf( __('Invalid key. Please try to %s again.', 'humble-lms'), '<a href="' . home_url( 'lost-password' ) . '">' . __('reset your password', 'humble-lms') . '</a>' );
        echo '</div>';
      } else if( isset( $_GET['login'] ) && $_GET['login'] === 'expiredkey' ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          echo sprintf( __('Expired key. Please try to %s again.', 'humble-lms'), '<a href="' . home_url( 'lost-password' ) . '">' . __('reset your password', 'humble-lms') . '</a>' );
        echo '</div>';
      } else if( isset( $_GET['password'] ) && $_GET['password'] === 'changed' ) {
        echo '<div class="humble-lms-message humble-lms-message--success">';
          _e('Password changed successfully. Please sign in below.', 'humble-lms');
        echo '</div>';
      }

      if( isset( $_GET['checkemail'] ) && $_GET['checkemail'] === 'confirm' ) {
        echo '<div class="humble-lms-message humble-lms-message--success">' . __('Please check your email inbox for a link to reset your password.', 'humble-lms') . '</div>';
      }

      if ( ! is_user_logged_in() ) {
        $args = array(
            'redirect' => admin_url(), 
            'form_id' => 'humble-lms-custom-login-form',
            'label_username' => __( 'Username', 'humble-lms' ),
            'label_password' => __( 'Password', 'humble-lms' ),
            'label_remember' => __( 'Remember me', 'humble-lms' ),
            'label_log_in' => __( 'Login', 'humble-lms' ),
            'remember' => true
        );
        wp_login_form( $args );
        echo '<p><a href="' . site_url('/wp-login.php?action=lostpassword') . '">' . __('Lost your password?', 'humble-lms') . '</a> | <a href="' . site_url('/wp-login.php?action=register') . '">' . __('Register', 'humble-lms') . '</a></p>';
      } else {
          echo '<p>' . __('You are already signed in.', 'humble-lms') . '</p>';
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
        return '<p>' . __('You are already signed in.', 'humble-lms') . '</p>';
      }

      if( ! get_option( 'users_can_register' ) ) {
        return '<div class="humble-lms-message humble-lms-message--error">' . __('This site is currently not open for registration.', 'humble-lms') . '</div>';
      }

      ob_start();
      
      if( $codes = Humble_LMS_Admin::humble_lms_errors()->get_error_codes() ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          foreach( $codes as $code ) {
            $message = Humble_LMS_Admin::humble_lms_errors()->get_error_message( $code );
            echo '<strong>' . __('Error') . ':</strong> ' . $message . '<br>';
          }
        echo '</div>';
      }

      $registration_has_country = isset( $this->options_manager->options['registration_has_country'] ) && $this->options_manager->options['registration_has_country'] === 1;
      $countries = isset( $this->options_manager->options['registration_countries'] ) ? maybe_unserialize( $this->options_manager->options['registration_countries'] ) : $this->options_manager->countries;

      $post_user_login = isset( $_POST['humble-lms-user-login'] ) ? sanitize_text_field( $_POST['humble-lms-user-login'] ) : '';
      $post_user_first = isset( $_POST['humble-lms-user-first'] ) ? sanitize_text_field( $_POST['humble-lms-user-first'] ) : '';
      $post_user_last = isset( $_POST['humble-lms-user-last'] ) ? sanitize_text_field( $_POST['humble-lms-user-last'] ) : '';
      $post_user_country = $registration_has_country && isset( $_POST['humble-lms-user-country'] ) ? sanitize_text_field( $_POST['humble-lms-user-country'] ) : '';
      $post_user_email = isset( $_POST['humble-lms-user-email'] ) ? sanitize_email( $_POST['humble-lms-user-email'] ) : '';
      $post_user_email_confirm = isset( $_POST['humble-lms-user-email-confirm'] ) ? sanitize_email( $_POST['humble-lms-user-email-confirm'] ) : '';

      ?>
      
      <form id="humble-lms-registration-form" class="humble-lms-form" action="" method="post">
        <fieldset>
          <p>
            <label for="humble-lms-user-login" class="humble-lms-required"><?php _e('Username', 'humble-lms'); ?></label>
            <input name="humble-lms-user-login" id="humble-lms-user-login" class="humble-lms-required" type="text" value="<?php echo $post_user_login; ?>" />
            <input class="humble-lms-honeypot" type="text" name="humble-lms-honeypot" value="" />
          </p>
          <p>
            <label for="humble-lms-user-first" class="humble-lms-required"><?php _e('First Name', 'humble-lms'); ?><br><small><?php _e('Required for certification.', 'humble-lms'); ?></small></label>
            <input name="humble-lms-user-first" id="humble-lms-user-first" type="text" value="<?php echo $post_user_first; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-last" class="humble-lms-required"><?php _e('Last Name', 'humble-lms'); ?><br><small><?php _e('Required for certification.', 'humble-lms'); ?></small></label>
            <input name="humble-lms-user-last" id="humble-lms-user-last" type="text" value="<?php echo $post_user_last; ?>" />
          </p>
          <?php if( $registration_has_country ): ?>
            <p>
              <label for="humble-lms-user-country" class="humble-lms-required"><?php _e('Country', 'humble-lms'); ?></label>
              <select name="humble-lms-user-country" id="humble-lms-user-country">
                <option value=""><?php _e('Please select your country', 'humble-lms'); ?></option>

                <?php 
                foreach( $countries as $key => $country ) {
                  $selected = $country === $post_user_country ? 'selected' : '';
                  echo '<option value="' . $country . '" ' . $selected . '>' . $country . '</option>';
                }
                ?>

              </select>
            </p>
          <?php endif; ?>
          <p>
            <label for="humble-lms-user-email" class="humble-lms-required"><?php _e('Email address', 'humble-lms'); ?></label>
            <input name="humble-lms-user-email" id="humble-lms-user-email" class="humble-lms-required" type="email" value="<?php echo $post_user_email; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-email-confirm" class="humble-lms-required"><?php _e('Confirm email address', 'humble-lms'); ?></label>
            <input name="humble-lms-user-email-confirm" id="humble-lms-user-email-confirm" class="humble-lms-required" type="email" value="<?php echo $post_user_email_confirm; ?>" />
          </p>
          <p>
            <label for="password" class="humble-lms-required">
              <?php _e('Password'); ?><br>
              <small><?php _e('Min. 12 characters, at least 1 letter and 1 number', 'humble-lms'); ?></small>
            </label>
            <input name="humble-lms-user-pass" id="password" class="humble-lms-required" type="password" value="" />
          </p>
          <p>
            <label for="password-again" class="humble-lms-required"><?php _e('Password again', 'humble-lms'); ?></label>
            <input name="humble-lms-user-pass-confirm" id="password-again" class="humble-lms-required" type="password" value="" />
          </p>
          <p>
            <input type="hidden" name="humble-lms-register-nonce" value="<?php echo wp_create_nonce('humble-lms-register-nonce'); ?>" />
            <input type="submit" class="humble-lms-btn" value="<?php _e('Register Your Account', 'humble-lms'); ?>"/>
          </p>
        </fieldset>

        <input type="hidden" name="humble-lms-form" value="humble-lms-registration" />
      </form><?php 
      
      echo '<p><a href="' . site_url('/wp-login.php?action=lostpassword') . '">' . __('Lost your password?', 'humble-lms') . '</a> | <a href="' . site_url('/wp-login.php') . '">' . __('Login', 'humble-lms') . '</a></p>';
      
      return ob_get_clean();
    }

    /**
     * Custom lost password form.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_custom_lost_password_form() {
      if( is_user_logged_in() ) {
        return '<p>' . __('You are already signed in.', 'humble-lms') . '</p>';
      }

      if( isset( $_GET['lost_password_sent'] ) ) {
        echo '<p class="humble-lms-message humble-lms-message--success">' . __( 'Check your email for a link to reset your password.', 'personalize-login' ) . '</div>';
      } elseif( isset( $_GET['errors'] ) ) {
        switch( sanitize_text_field( $_GET['errors'] ) ) {
          case 'empty_username':
            $errors[] = __('Please enter a valid email address.', 'humble-lms');
            break;
          case 'invalid_email':
          case 'invalidcombo':
            $errors[] = __('There are no users registered with this email address.', 'humble-lms');
            break;
        }
      } 
  
      if( ! empty( $errors ) ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          foreach( $errors as $error ) {
            echo '<strong>' . __('Error') . ':</strong> ' . $error . '<br>';
          }
        echo '</div>';
      }
      
      ?>

      <div id="lostpasswordform" class="humble-lms-lost-password">
        <p><?php _e('Please enter your email address and we will send you a link you can use to pick a new password.', 'humble-lms'); ?></p>
    
        <form id="humble-lms-lost-password-form" action="<?php echo wp_lostpassword_url(); ?>" method="post">
          <p>
            <label for="user_login"><?php _e( 'Your email address', 'humble-lms' ); ?>
            <input type="text" name="user_login" id="user_login">
          </p>
    
          <p class="humble-lms-lost-password-submit">
            <input type="submit" name="submit" class="humble-lms-btn" value="<?php _e( 'Reset Password', 'humble-lms' ); ?>" />
          </p>
        </form>
      </div><?php

      echo '<p><a href="' . site_url('/wp-login.php') . '">' . __('Login', 'humble-lms') . '</a> | <a href="' . site_url('/wp-login.php?action=register') . '">' . __('Register', 'humble-lms') . '</a></p>';
    }

    /**
     * Custom reset password form.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_custom_reset_password_form() {
      if( is_user_logged_in() ) {
        return '<p>' . __('You are already signed in.', 'humble-lms') . '</p>';
      }
      
      ob_start();

      if( isset( $_REQUEST['error'] ) && $_REQUEST['error'] === 'password_reset_mismatch' ) {
        echo '<div class="humble-lms-message humble-lms-message--error"><strong>' . __('Error', 'humble-lms') . ':</strong> ' . __('The passwords you entered do not match.', 'humble-lms') . '</div>';
      }

      if( isset( $_REQUEST['error'] ) && $_REQUEST['error'] === 'password_reset_empty' ) {
        echo '<div class="humble-lms-message humble-lms-message--error"><strong>' . __('Error', 'humble-lms') . ':</strong> ' . __('Your new password should be at least 12 characters long and contain min. 1 letter and 1 number.', 'humble-lms') . '</div>';
      }
      
      ?>

      <form name="resetpassform" id="resetpassform" action="<?php echo site_url( 'wp-login.php?action=resetpass' ); ?>" method="post" autocomplete="off">
        <input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" />
        <input type="hidden" name="rp_key" value="<?php echo esc_attr( $_GET['key'] ); ?>" />
         
        <?php

        if( $codes = Humble_LMS_Admin::humble_lms_errors()->get_error_codes() ) {
          echo '<div class="humble-lms-message humble-lms-message--error">';
            foreach( $codes as $code ) {
              $message = Humble_LMS_Admin::humble_lms_errors()->get_error_message( $code );
              echo '<strong>' . __('Error') . ':</strong> ' . $message . '<br>';
            }
          echo '</div>';
        }

        ?>
 
        <p>
          <label for="pass1"><?php _e( 'New password', 'humble-lms' ) ?></label>
          <input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off" />
        </p>
        <p>
          <label for="pass2"><?php _e( 'Repeat new password', 'humble-lms' ) ?></label>
          <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off" />
        </p>
         
        <p class="description humble-lms-message"><?php echo wp_get_password_hint(); ?></p>
         
        <p class="resetpass-submit">
            <input type="submit" name="submit" id="resetpass-button" class="humble-lms-btn" value="<?php _e( 'Reset Password', 'humble-lms' ); ?>" />
        </p>
      </form><?php

      return ob_get_clean();
    }

    /**
     * Custom user profile.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_custom_user_profile() {
      if( ! is_user_logged_in() ) {
        return sprintf( __('Please %s first.', 'humble-lms'), '<a href="' . $this->options_manager->login_url . '">log in</a>');
      }

      $user_id = get_current_user_ID();
      $userdata = get_userdata( $user_id );
      
      ob_start();

      if( isset( $_GET['progress'] ) && esc_attr( $_GET['progress'] ) === 'reset' ) {
        echo '<div class="humble-lms-message humble-lms-message--success">' . __('You progress was reset successfully.', 'humble-lms') . '</div>';
      }
      
      if( $codes = Humble_LMS_Admin::humble_lms_errors()->get_error_codes() ) {
        echo '<div class="humble-lms-message humble-lms-message--error">';
          foreach( $codes as $code ) {
            $message = Humble_LMS_Admin::humble_lms_errors()->get_error_message( $code );
            echo '<strong>' . __('Error') . ':</strong> ' . $message . '<br>';
          }
        echo '</div>';
      } else {
        if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
          echo '<div class="humble-lms-message humble-lms-message--success">' . __('Profile update successful.', 'humble-lms') . '</div>';
        }
      }

      $registration_has_country = isset( $this->options_manager->options['registration_has_country'] ) && $this->options_manager->options['registration_has_country'] === 1;
      $countries = isset( $this->options_manager->options['registration_countries'] ) ? maybe_unserialize( $this->options_manager->options['registration_countries'] ) : $this->options_manager->countries;
      $user_login = $userdata->user_login;
      $user_first = $userdata->first_name;
      $user_last = $userdata->last_name;
      $user_country = get_user_meta( $user_id, 'humble_lms_country', true );
      $user_email = $userdata->user_email;
      $useremail_confirm = isset( $_POST['humble-lms-user-email'] ) ? sanitize_email( $_POST['humble-lms-user-email'] ) : '';
      $user_membership = get_user_meta( $user_id, 'humble_lms_membership', true );
      $options = get_option('humble_lms_options');
      $checkout_post_id = isset( $options['custom_pages']['checkout'] ) ? (int)$options['custom_pages']['checkout'] : null;

      ?>
      
      <form id="humble-lms-user-profile-form" class="humble-lms-form" action="" method="post">
        <fieldset>
          <?php if( $user_membership === 'premium'): ?>
            <label for="humble-lms-user-membership"><?php _e('Membership', 'humble-lms'); ?> <small>(<?php echo __('You can access all courses', 'humble-lms' ); ?>)</small></label>
          <p><strong><?php echo __('Premium', 'humble-lms'); ?></strong></p>
          <?php else: ?>
          <label for="humble-lms-user-membership"><?php _e('Membership', 'humble-lms'); ?> <small>(<?php echo __('Get access to all courses', 'humble-lms' ); ?>)</small></label>
          <p><?php _e('You are currently registered with a free account. In order to access all courses on this website please upgrade to a paid membership.', 'humble-lms' ); ?></p>
          <p><a class="humble-lms-btn humble-lms-btn--success" href="<?php echo esc_url( get_permalink( $checkout_post_id ) ); ?>"><?php _e('Upgrade membership', 'humble-lms'); ?></a></p>
          <?php endif; ?>
          <label for="humble-lms-user-login"><?php _e('Username', 'humble-lms'); ?> <small>(<?php echo __('Can\'t be changed', 'humble-lms' ); ?>)</small></label>
          <p><strong><?php echo $user_login; ?></strong></p>
          <input type="hidden" name="humble-lms-user-login" id="humble-lms-user-login" class="humble-lms-required" type="text" value="<?php echo $user_login; ?>" />
          
          <label for="humble-lms-user-first"><?php _e('First Name', 'humble-lms'); ?> <small>(<?php echo __('Can\'t be changed', 'humble-lms' ); ?>)</small></label>
          <p><strong><?php echo $user_first; ?></strong></p>
          <input type="hidden" name="humble-lms-user-first" id="humble-lms-user-first" type="text" value="<?php echo $user_first; ?>" />
          
          <label for="humble-lms-user-last"><?php _e('Last Name', 'humble-lms'); ?> <small>(<?php echo __('Can\'t be changed', 'humble-lms' ); ?>)</small></label>
          <p><strong><?php echo $user_last; ?></strong></p>
          <input type="hidden" name="humble-lms-user-last" id="humble-lms-user-last" type="text" value="<?php echo $user_last; ?>" />
          <?php if( $registration_has_country ): ?>
            <p>
              <label for="humble-lms-user-country" class="humble-lms-required"><?php _e('Country', 'humble-lms'); ?></label>
              <select name="humble-lms-user-country" id="humble-lms-user-country">
                <option value=""><?php _e('Please select your country', 'humble-lms'); ?></option>

                <?php 
                foreach( $countries as $key => $country ) {
                  $selected = $country === $user_country ? 'selected' : '';
                  echo '<option value="' . $country . '" ' . $selected . '>' . $country . '</option>';
                }
                ?>

              </select>
            </p>
          <?php endif; ?>
          <p>
            <label for="humble-lms-user-email" class="humble-lms-required"><?php _e('Email address', 'humble-lms'); ?></label>
            <input name="humble-lms-user-email" id="humble-lms-user-email" class="humble-lms-required" type="email" value="<?php echo $user_email; ?>" />
          </p>
          <p>
            <label for="humble-lms-user-email-confirm" class="humble-lms-required"><?php _e('Confirm email address', 'humble-lms'); ?></label>
            <input name="humble-lms-user-email-confirm" id="humble-lms-user-email-confirm" class="humble-lms-required" type="email" value="" />
          </p>
          <p>
            <label for="password" class="humble-lms-required">
              <?php _e('Password'); ?><br>
              <small><?php _e('Min. 12 characters, at least 1 letter and 1 number', 'humble-lms'); ?></small>
            </label>
            <input name="humble-lms-user-pass" id="password" class="humble-lms-required" type="password" value="" />
          </p>
          <p>
            <label for="password-again" class="humble-lms-required"><?php _e('Password again', 'humble-lms'); ?></label>
            <input name="humble-lms-user-pass-confirm" id="password-again" class="humble-lms-required" type="password" value="" />
          </p>
          <p>
            <input type="hidden" name="humble-lms-update-user-nonce" value="<?php echo wp_create_nonce('humble-lms-update-user-nonce'); ?>" />
            <input type="submit" class="humble-lms-btn" value="<?php _e('Save changes', 'humble-lms'); ?>"/>
          </p>
        </fieldset>

        <input type="hidden" name="humble-lms-form" value="humble-lms-update-user" />
      </form>
      
      <p><a id="humble-lms-reset-user-progress" data-user-id="<?php echo get_current_user_ID(); ?>" class="humble-lms-btn humble-lms-btn--error"><small><?php _e('Reset my learning progress', 'humble-lms'); ?></small></a></p>
      
      <?php 
       
      return ob_get_clean();
    }

    /**
     * Quizzes.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_quiz( $atts = null ) {
      extract( shortcode_atts( array (
        'ids' => '',
        'style' => '',
        'class' => '',
      ), $atts ) );

      if( ! $ids ) {
        $ids = [];
        return '<p>' . __('Please enter at least one valid quiz ID.', 'humble-lms') . '</p>';
      }

      $quizzes = $this->quiz->get( $ids );
      
      $html = '';
      $html .= '<div class="humble-lms-quiz-message"><div><div class="humble-lms-quiz-message-inner"><div>
        <div class="humble-lms-quiz-message-close" aria-label="Close quiz overlay">
          <i class="ti-close"></i>
        </div>
        <div class="humble-lms-message-quiz humble-lms-message-quiz--completed">
          <h3 class="humble-lms-quiz-message-title">' . __('Well done!', 'humble-lms') . '</h3>
          <p>' . __('You passed this quiz with a score of', 'humble-lms') . '</p><p><span class="humble-lms-quiz-score"></span></p>
        </div>
        <div class="humble-lms-message-quiz humble-lms-message-quiz--failed">
          <h3 class="humble-lms-quiz-message-title">' . __('Bummer', 'humble-lms') . '</h3>
          <p>' . __('You failed this quiz with a score of', 'humble-lms') . '</p>
          <p><span class="humble-lms-quiz-score"></span></p>
        </div>
        <div class="humble-lms-quiz-message-image humble-lms-bounce-in"></div>
      </div></div></div></div>';
      $html .= '<div class="humble-lms-quiz ' . $class . '" style="' . $style . '">';

      if ( $quizzes ) {
        
        foreach( $quizzes as $quiz ) {
          $questions = $this->quiz->questions( $quiz->ID );
          $passing_grade = $this->quiz->get_passing_grade( $quiz->ID );
          $passing_required = $this->quiz->get_passing_required( $quiz->ID ) ? '1' : '0';

          $html .= '<div class="humble-lms-quiz-single" data-passing-grade="' . $passing_grade . '" data-passing-required="' . $passing_required . '">';
  
          foreach( $questions as $question ) {
            $question_type = $this->quiz->question_type( $question->ID );
  
            $html .= '<div class="humble-lms-quiz-question ' . $question_type . '" data-id="' . $question->ID . '">';
              $title = get_post_meta( $question->ID, 'humble_lms_question', true );
              $html .= '<h3 class="humble-lms-quiz-question-title">' . $title . '</h3>';
              
              switch( $question_type ) {
                case 'single_choice':
                  $answers = $this->quiz->answers( $question->ID );
                  $html .= $this->quiz->single_choice( $quiz->ID, $answers );
                  break;

                case 'multiple_choice':
                  $answers = $this->quiz->answers( $question->ID );
                  $html .= $this->quiz->multiple_choice( $quiz->ID, $answers );
                  break;

                default:
                  break;
              }
            $html .= '</div>';
          }

          $html .= '</div>';

        }
      } else {
        $html .= '<p>' . __('No quizzes found.', 'humble-lms') . '</p>';
      }

      $html .= '</div>';

      return $html;
    }

    /**
     * PayPal Buttons.
     * 
     * @return false
     * @since   0.0.1
     */
    public function humble_lms_paypal_buttons() {
      if( ! is_user_logged_in() ) {
        return $this->display_login_text();
      }

      if( ! Humble_LMS_Admin_Options_Manager::has_paypal() ) {
        if( current_user_can('manage_options') ) {
          return '<p>' . __('Please provide your PayPal credentials first.', 'humble-lms') . '</p>';
        } else {
          return '';
        }
      }

      $membership = get_user_meta( get_current_user_id(), 'humble_lms_membership', true );
      
      if( $membership !== 'premium' ) {
        return '<div id="humble-lms-paypal-buttons"></div>';
      } else {
        return '<p>' . __('Your account has been upgraded to premium status. Enjoy the courses! 😊', 'humble-lms') . '</p><p><a class="humble-lms-btn" href="' . esc_url( site_url() ) . '">' . __('Back to home page', 'humble-lms') . '</a></p>';
      }
      
    }
    
  }
  
}
