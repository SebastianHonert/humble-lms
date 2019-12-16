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
		 * Mark lessons complete / open lesson
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function mark_lesson_complete() {
			// Check the nonce for permission.
			if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
				die( 'Permission Denied' );
      }

      $redirect_url = home_url();

      // Mark lesson complete and continue to the next lesson
      $course_id = isset( $_POST['courseId'] ) ? (int)$_POST['courseId'] : null;
      $lesson_id = isset( $_POST['lessonId'] ) ? (int)$_POST['lessonId'] : null;
      $mark_complete = filter_var( $_POST['markComplete'], FILTER_VALIDATE_BOOLEAN);

      if( ! $course_id ) {
        if( ! $lesson_id ) {
          $redirect_url = esc_url( home_url() );
        } else {
          $redirect_url = esc_url( get_permalink( $lesson_id ) );
        }

        wp_die( json_encode( array(
          'status' => 200,
          'redirect_url' => $redirect_url,
          'course_id' => $course_id,
          'lesson_id' => $lesson_id
        ) ) );
      }

      if( ! $mark_complete ) {
        wp_die( json_encode( array(
          'status' => 200,
          'redirect_url' =>  esc_url( get_permalink( $lesson_id ) ),
          'course_id' => $course_id,
          'lesson_id' => $lesson_id
        ) ) );
      }

      // Mark complete
      $course = get_post( $course_id );

      if( ! $course )
        return;

      $lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $lessons = explode(',', $lessons);

      $key = array_search( $lesson_id, $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last) {
        $next_lesson = get_post( $lessons[$key+1] );
        $redirect_url = esc_url( get_permalink( $next_lesson->ID ) );
      } else {
        // TODO: Check if all lessons complete => redirect accordingly
        $redirect_url = esc_url( get_permalink( $lessons[0] ) );
      }
      
			wp_die( json_encode( array(
				'status'  => 200,
        'redirect_url' => $redirect_url,
        'course_id' => $course_id,
        'lesson_id' => $next_lesson->ID
      ) ) );
    }
    
  }
  
}