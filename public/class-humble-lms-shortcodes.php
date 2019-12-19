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
     * @param      class    $user       Public user class
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;

    }

    /**
     * Shortcode: track archive
     *
     * @since    0.0.1
     */
    public function humble_lms_track_archive( $atts = null ) {
      extract( shortcode_atts( array (
        'tile_width' => 'half',
        'style' => '',
        'class' => '',
      ), $atts ) );

      $args = array(
        'post_type' => 'humble_lms_track',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
      );

      $tracks = get_posts( $args );

      $html = '';
      $html .= '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';

      foreach( $tracks as $track ) {
        $html .= do_shortcode('[track_tile tile_width="' . $tile_width . '" track_id="' . $track->ID . '"]');
      }

      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: track tile
     *
     * @since    0.0.1
     */
    public function humble_lms_track_tile( $atts = null ) {
      extract( shortcode_atts( array (
        'track_id' => '',
        'tile_width' => 'half'
      ), $atts ) );

      $track = get_post( $track_id );
      $featured_img_url = get_the_post_thumbnail_url( $track_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $track_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('not specified', 'humble-lms');
      $duration = get_post_meta( $track_id, 'humble_lms_track_duration', true );
      $duration = $duration ? $duration : __('not specified', 'humble-lms');

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . '">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $track_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $track->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level . '</span>';
          $html .= '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration  . '</span>';
        $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: course archive
     *
     * @since    0.0.1
     */
    public function humble_lms_course_archive( $atts = null ) {
      global $post;

      extract( shortcode_atts( array (
        'track_id' => '',
        'tile_width' => 'half',
        'style' => '',
        'class' => '',
      ), $atts ) );

      $args = array(
        'post_type' => 'humble_lms_course',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
      );

      if( ! empty( $track_id ) && ( is_single() && $post->post_type == 'humble_lms_track' ) ) {
        $track_courses = get_post_meta($track_id, 'humble_lms_track_courses', true);
        $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];
        
        if( ! empty( $track_courses ) ) {
          $args['include'] = $track_courses;
        } else {
          return '<p>' . __('This track does not include any courses.', 'humble-lms') . '</p>';
        }
      }

      $courses = get_posts( $args );

      $html = '';
      $html .= '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';

      foreach( $courses as $course ) {
        $html .= do_shortcode('[course_tile tile_width="' . $tile_width . '" course_id="' . $course->ID . '"]');
      }

      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: course tile
     *
     * @since    0.0.1
     */
    public function humble_lms_course_tile( $atts = null ) {
      extract( shortcode_atts( array (
        'course_id' => '',
        'tile_width' => 'half'
      ), $atts ) );

      $course = get_post( $course_id );
      $featured_img_url = get_the_post_thumbnail_url( $course_id, 'humble-lms-course-tile'); 
      $level = strip_tags( get_the_term_list( $course_id, 'humble_lms_course_level', '', ', ') );
      $level = $level ? $level : __('not specified', 'humble-lms');
      $duration = get_post_meta( $course_id, 'humble_lms_course_duration', true );
      $duration = $duration ? $duration : __('not specified', 'humble-lms');

      $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . '">';
        $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $course_id ) ) . '" class="humble-lms-course-tile">';
          $html .= '<div class="humble-lms-course-tile-layer"></div>';
          $html .= '<div class="humble-lms-16-9">';
            $html .= '<div class="humble-lms-course-title">' . $course->post_title . '</div>';
          $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="humble-lms-course-tile-meta">';
          $html .= '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level . '</span>';
          $html .= '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration  . '</span>';
        $html .= '</div>';
      $html .= '</div>';

      return $html;
    }

    /**
     * Shortcode: syllabus
     *
     * @since    0.0.1
     */
    public function humble_lms_syllabus( $atts = null ) {
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

      // Course Syllabus
      $html = '<nav class="humble-lms-syllabus ' . $class . '" style="' . $style . '">';
        $html .= '<h2>' . __('Course Syllabus', 'humble-lms') . '</h2>';
        if( ! $course_id ) {
          $html .= '<p>' . __('Looking for the course syllabus? It seems that you have accessed this lesson directly so it is not attached to a specific course. Please open the course and start your learning activities from there.', 'humble-lms') . '</p>';
        } else {
          $html .= '<ul class="humble-lms-syllabus-lessons">';

          foreach( $lessons as $key => $lesson ) {
            $description = $context === 'course' ? get_post_meta( $lesson->ID, 'humble_lms_lesson_description', true ) : '';
            $class_lesson_current = $lesson->ID === $lesson_id ? 'humble-lms-syllabus-lesson--current' : '';
            $class_lesson_completed = $this->user->completed_lesson( $lesson->ID ) ? 'humble-lms-syllabus-lesson--completed' : '';
            $locked = $this->user->can_access_lesson( $lesson->ID ) ? '' : '<i class="ti-lock"></i>';
            $html .= '<li class="humble-lms-syllabus-lesson humble-lms-open-lesson ' . $class_lesson_current . ' ' . $class_lesson_completed . '" data-lesson-id="' . $lesson->ID  . '" data-course-id="' . $course_id . '">';
            $html .= '<span class="humble-lms-syllabus-title">' . $locked . $lesson->post_title . '</span>';
            $html .= $description? '<span class="humble-lms-syllabus-description">' . $description . '</span>' : '';
            $html .= '</li>';
          }
          
          $html .= '</ul>';
        }

      $html .= '</nav>';

      // View course/lesson
      if( $context === 'course' ) {
        $html .= '<span class="humble-lms-open-lesson humble-lms-btn humble-lms-btn--success" data-lesson-id="' . $lessons[0]->ID  . '" data-course-id="' . $course_id . '">' . __('Start the course now', 'humble-lms') . '</span>';
      } else {
        if( $course_id ) {
          $html .= '<a class="humble-lms-prev-lesson-link" href="' . esc_url( get_permalink( $course_id ) ) . '">' . __('Back to course overview', 'humble-lms') . '</a>';
        }
      }

      return $html;
    }

    /**
     * Shortcode: mark lesson complete
     *
     * @since    0.0.1
     */
    public function humble_lms_mark_complete( $atts = null ) {
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

      if( ! $is_last) {
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
    
  }
  
}
