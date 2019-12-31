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

      $course_lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );

      if( ( ! $course_lessons ) || ( ! $lessons_completed ) ) {
        return;
      }

      sort( $course_lessons );
      sort( $lessons_completed );

      return empty( array_diff( $course_lessons, $lessons_completed ) );
    }

    /**
     * Checks if a user has completed a track.
     *
     * @since    0.0.1
     */
    public function completed_track( $track_id ) {
      if( ! is_user_logged_in() || ! $track_id )
        return;

      $user_id = get_current_user_id();

      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', true );
      $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', true );

      if( ( ! $track_courses ) || ( ! $courses_completed ) ) {
        return;
      }

      sort( $track_courses );
      sort( $courses_completed );

      return empty( array_diff( $track_courses, $courses_completed ) );
    }

    /**
     * Returns an array of completed track IDs including the course ID or an empty array.
     *
     * @return  array
     * @param   int
     * @since   0.0.1
     */
    public function completed_tracks() {
      if( ! is_user_logged_in() )
        return [];

      $user_id = get_current_user_id();

      return get_user_meta( $user_id, 'humble_lms_tracks_completed', true );
    }

    /**
     * Updates the lessons, courses, and tracks completed by the current user.
     * Returns an array of completed IDs.
     *
     * @return  array
     * @param   int
     * @since   0.0.1
     */
    public function mark_lesson_complete( $lesson_id ) {
      if( ! is_user_logged_in() )
        return [];

      $user_id = get_current_user_id();

      $completed = array( [], [], [] ); // lesson, courses, tracks
      
      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
      if( ! is_array( $lessons_completed ) ) $lessons_completed = array();

      if( $lesson_id && ! in_array( $lesson_id, $lessons_completed ) ) {
        $lessons_completed[] = $lesson_id;
        array_push($completed[0], $lesson_id);
      } else {
        if( ( $key = array_search( $lesson_id, $lessons_completed ) ) !== false ) {
          unset( $lessons_completed[$key] );
        }
      }
      
      update_user_meta( $user_id, 'humble_lms_lessons_completed', $lessons_completed );

      // Complete courses that include the completed lesson
      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', true );
      if( ! is_array( $courses_completed ) ) $courses_completed = array();

      $args = array(
        'post_type' => 'humble_lms_course',
        'posts_per_page' => -1,
        'post_status' => 'publish',
      );

      $courses = get_posts( $args );

      foreach( $courses as $course ) {
        if( $this->completed_course( $course->ID ) ) {
          if( ! in_array( $course->ID, $courses_completed ) ) {
            $courses_completed[] = $course->ID;
            array_push($completed[1], $course->ID);
          }
        } else {
          if( ( $key = array_search( $course->ID, $courses_completed ) ) !== false ) {
            unset( $courses_completed[$key] );
          }
        }

        update_user_meta( $user_id, 'humble_lms_courses_completed', $courses_completed );

        // Complete tracks that include the completed course
        $tracks_completed = get_user_meta( $user_id, 'humble_lms_tracks_completed', true );
        if( ! is_array( $tracks_completed ) ) $tracks_completed = array();

        $args = array(
          'post_type' => 'humble_lms_track',
          'posts_per_page' => -1,
          'post_status' => 'publish',
        );

        $tracks = get_posts( $args );

        foreach( $tracks as $track ) {
          if( $this->completed_track( $track->ID ) ) {
            if( ! in_array( $track->ID, $tracks_completed ) ) {
              $tracks_completed[] = $track->ID;
              array_push($completed[2], $track->ID);
            }
          } else {
            if( ( $key = array_search( $track->ID, $tracks_completed ) ) !== false ) {
              unset( $tracks_completed[$key] );
            }
          }

          update_user_meta( $user_id, 'humble_lms_tracks_completed', $tracks_completed );
        }
      }

      return json_encode( $completed );
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
