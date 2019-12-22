<?php
/**
 * This class provides front-end user data functionality.
 *
 * Creates the various functions used for user data management via front-end and AJAX interactions
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_User' ) ) {

	class Humble_LMS_Public_User {

    /**
     * Checks if a user has completed a single lesson.
     *
     * @since    0.0.1
     */
    public function completed_lesson( $lesson_id ) {
      if( ! is_user_logged_in() || ! $lesson_id )
        return;

      $user_id = get_current_user_id();
      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
      
      return $lessons_completed ? in_array( $lesson_id, $lessons_completed ) : '';
    }

    /**
     * Checks if a user has completed a course.
     *
     * @since    0.0.1
     */
    public function completed_course( $course_id ) {
      if( ! is_user_logged_in() || ! $course_id )
        return;

      $user_id = get_current_user_id();

      $course_lessons = json_decode( get_post_meta($course_id, 'humble_lms_course_lessons', true)[0] );
      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );

      if( ! $course_lessons || ! $lessons_completed ) {
        return;
      }

      sort( $course_lessons );
      sort( $lessons_completed );

      return ! array_diff( $course_lessons, $lessons_completed );
    }

    /**
     * Checks if a user has completed a track.
     *
     * @since    0.0.1
     */
    public function completed_track( $track_id ) {
      // TODO: This is not working => check all courses in tracks for completion
      // Why? Contect track_id not available!
      if( ! is_user_logged_in() || ! $track_id )
        return;

      $user_id = get_current_user_id();

      $track_courses = json_decode( get_post_meta($track_id, 'humble_lms_track_courses', true)[0] );
      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', true );

      if( ! $track_courses || ! $courses_completed ) {
        return;
      }

      sort( $track_courses );
      sort( $courses_completed );

      return ! array_diff( $track_courses, $courses_completed );
    }

    /**
     * Checks user privileges when accessing course contents.
     *
     * @since    0.0.1
     */
    public function can_access_lesson( $lesson_id ) {
      // Administrators can access all content
      if( current_user_can('manage_options') )
        return true;

      $levels = get_post_meta( $lesson_id, 'humble_lms_lesson_access_levels', false );
      $levels = is_array( $levels ) && ! empty( $levels[0] ) ? $levels[0] : [];

      // Public lesson
      if( empty( $levels ) )
        return true;

      if( ! is_user_logged_in() )
        return false;

      $user = wp_get_current_user();

      return ! empty( array_intersect( $user->roles, $levels ) );
    }
    
  }
  
}
