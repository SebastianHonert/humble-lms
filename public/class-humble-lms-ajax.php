<?php
/**
 * The public-facing AJAX functionality.
 *
 * Creates the various functions used for AJAX on the front-end.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_Ajax' ) ) {

  class Humble_LMS_Public_Ajax {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $humble_lms       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;

    }
    
    /**
     * Mark lessons complete / open lesson
     *
     * @since 1.0.0
     * @return void
     */
    public function mark_lesson_complete() {

      $user_id = get_current_user_id();

      // Clear user data (testing)
      // delete_user_meta( $user_id, 'humble_lms_lessons_completed' );
      // delete_user_meta( $user_id, 'humble_lms_courses_completed' );
      // delete_user_meta( $user_id, 'humble_lms_tracks_completed' );

      // Dry this function a little bit.
      function default_redirect( $redirect_url, $course_id, $lesson_id, $completed = null ) {
        wp_die( json_encode( array(
          'status' => 200,
          'redirect_url' => $redirect_url,
          'course_id' => $course_id,
          'lesson_id' => $lesson_id,
          'completed' => $completed,
        ) ) );
      }

      // Check the nonce for permission.
      if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        die( 'Permission Denied' );
      }

      // Mark lesson complete and continue to the next lesson
      $course_id = isset( $_POST['courseId'] ) ? (int)$_POST['courseId'] : null;
      $lesson_id = isset( $_POST['lessonId'] ) ? (int)$_POST['lessonId'] : null;
      $lesson_completed = $_POST['lessonCompleted'] && $_POST['lessonCompleted'] === 'true';
      $mark_complete = filter_var( $_POST['markComplete'], FILTER_VALIDATE_BOOLEAN);

      // Neither course nor lesson ID is set: redirect accordingly.
      if( ! $course_id ) {
        if( ! $lesson_id ) {
          $redirect_url = esc_url( home_url() );
        } else {
          $redirect_url = esc_url( get_permalink( $lesson_id ) );
        }

        default_redirect( $redirect_url, $course_id, $lesson_id );
      }

      // Mark complete is not set: redirect to lessin with course ID set.
      if( ! $mark_complete ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      // Check if course exists
      $course = get_post( $course_id );

      if( ! $course ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      // User logged in
      if( is_user_logged_in() ) {
        $completed = $this->user->mark_lesson_complete( $lesson_id );
      }

      // Redirect to the next lesson
      $lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $lessons = ! empty( $lessons[0] ) ? json_decode( $lessons[0] ) : [];

      $key = array_search( $lesson_id, $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last ) {
        $next_lesson = get_post( $lessons[$key+1] );
        $redirect_url = esc_url( get_permalink( $next_lesson->ID ) );
      } else {
        // TODO: Check if all lessons complete => redirect accordingly
        $redirect_url = esc_url( get_permalink( $lessons[0] ) );
      }

      default_redirect( $redirect_url, $course_id, $next_lesson->ID, $completed );
    }
    
  }
  
}
