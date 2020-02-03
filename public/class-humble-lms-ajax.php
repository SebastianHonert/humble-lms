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
      $this->content_manager = new Humble_LMS_Content_Manager;

    }
    
    /**
     * Mark lesson complete button clicked / open lesson.
     *
     * @since 1.0.0
     * @return void
     */
    public function mark_lesson_complete() {

      $user_id = get_current_user_id();

      function default_redirect( $redirect_url, $course_id, $lesson_id, $completed = null ) {
        wp_die( json_encode( array(
          'status' => 200,
          'redirect_url' => $redirect_url,
          'course_id' => $course_id,
          'lesson_id' => $lesson_id,
          'completed' => $completed,
        ) ) );
      }

      if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        die( 'Permission Denied' );
      }

      $course_id = isset( $_POST['courseId'] ) ? (int)$_POST['courseId'] : null;
      $lesson_id = isset( $_POST['lessonId'] ) ? (int)$_POST['lessonId'] : null;
      $lesson_completed = $_POST['lessonCompleted'] && $_POST['lessonCompleted'] === 'true';
      $mark_complete = filter_var( $_POST['markComplete'], FILTER_VALIDATE_BOOLEAN);

      if( ! $course_id ) {
        if( ! $lesson_id ) {
          $redirect_url = esc_url( home_url() );
        } else {
          $redirect_url = esc_url( get_permalink( $lesson_id ) );
        }

        default_redirect( $redirect_url, $course_id, $lesson_id );
      }

      if( ! $mark_complete ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      $course = get_post( $course_id );

      if( ! $course ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      // This returns an array with the completed lesson, courses, track,
      // award, and certificate ids [[lessonIds], [courseIds], [trackIds] ...]
      if( is_user_logged_in() ) {
        $completed = $this->user->mark_lesson_complete( $lesson_id );
      }

      $lessons = $this->content_manager->get_course_lessons( $course_id );

      $key = array_search( $lesson_id, $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last ) {
        $next_lesson = get_post( $lessons[$key+1] );
        $redirect_url = esc_url( get_permalink( $next_lesson->ID ) );
      } else {
        $redirect_url = esc_url( get_permalink( $lessons[0] ) );
      }

      default_redirect( $redirect_url, $course_id, $next_lesson->ID, $completed );
    }

    /**
     * Save evaluated quiz for logged in user.
     *
     * @since 1.0.0
     * @return void
     */
    public function evaluate_quiz() {
      if( ! is_user_logged_in() || ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        die( 'Permission Denied' );
      }

      $evaluation = $_POST['evaluation'];
      $completed = (bool)$evaluation['completed'];
      foreach( $evaluation['quizIds'] as $key => $id ) {
        $evaluation['quizIds'][$key] = (int)$id;
      }

      if( $completed ) {
        $completed_quizzes = $this->user->completed_quizzes( get_current_user_ID() );
        foreach( $evaluation['quizIds'] as $id ) {
          if( ! in_array( $id, $completed_quizzes) ) {
            array_push( $completed_quizzes, $id );
          }
        }

        update_user_meta( get_current_user_ID(), 'humble_lms_quizzes_completed', $completed_quizzes );
      }

      die;
    }
    
  }
  
}
