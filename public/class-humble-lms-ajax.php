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
      $lesson_completed = isset( $_POST['lessonCompleted'] ) && $_POST['lessonCompleted'] === 'true' ? true : false;
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

      if( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
        
        if( ! is_array( $lessons_completed ) ) $lessons_completed = array();
        
        // Marked as complete => add lesson ID to user meta, else remove
        if( $lesson_id && ! in_array( $lesson_id, $lessons_completed ) ) {
          $lessons_completed[] = $lesson_id;
        } else {
          if( ( $key = array_search( $lesson_id, $lessons_completed ) ) !== false ) {
            unset( $lessons_completed[$key] );
          }
        }

        update_user_meta( $user_id, 'humble_lms_lessons_completed', $lessons_completed );
        
        // Clear data (testing)
        // delete_user_meta( $user_id, 'humble_lms_lessons_completed' );
      }

      $updated = update_user_meta( $user_id, 'some_meta_key', $new_value );

      // Redirect to the next lesson

      $lessons = json_decode( get_post_meta($course_id, 'humble_lms_course_lessons', true)[0] );

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