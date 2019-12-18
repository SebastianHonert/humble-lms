<?php
/**
 * This class provides user data functionality
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
     * Checks if user has completed a single lesson.
     *
     * @since    0.0.1
     */
    public function humble_lms_user_completed_lesson( $lesson_id ) {
      if( ! is_user_logged_in() || ! $lesson_id )
        return;

      $user_id = get_current_user_id();
      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
      
      return $lessons_completed ? in_array( $lesson_id, $lessons_completed ) : '';
    }
    
  }
  
}