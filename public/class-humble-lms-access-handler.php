<?php
/**
 * This class provides access management functionality.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_Access_Handler' ) ) {

  class Humble_LMS_Public_Access_Handler {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      class    $access       Public access class
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;
      $this->content_manager = new Humble_LMS_Content_Manager;

    }

    /**
     * Checks user privileges when accessing course contents.
     *
     * @since    0.0.1
     * @return   string
     */
    public function can_access_lesson( $lesson_id = null, $course_id = null ) {
      // Administrators can access all content
      if( current_user_can('manage_options') )
        return 'allowed';

      if( get_post_type( $lesson_id ) !== 'humble_lms_lesson' && get_post_type( $course_id ) !== 'humble_lms_course' )
        return 'allowed';

      // Check membership access level
      $lesson_membership = get_post_meta( $lesson_id, 'humble_lms_membership', true );
      $user_membership = get_user_meta( get_current_user_id(), 'humble_lms_membership', true );

      if( $lesson_membership !== 'free' && ( $user_membership !== $lesson_membership ) ) {
        return 'membership';
      }

      // Check for consecutive order of lessons
      if( ! $this->lesson_reached( $lesson_id, $course_id ) ) {
        return 'order';
      }

      // Public lesson
      $levels = get_post_meta( $lesson_id, 'humble_lms_lesson_access_levels', false );
      $levels = is_array( $levels ) && ! empty( $levels[0] ) ? $levels[0] : [];

      if( empty( $levels ) )
        return 'allowed';

      if( ! is_user_logged_in() )
        return 'denied';

      $user = wp_get_current_user();

      return empty( array_intersect( $user->roles, $levels ) ) ? 'denied' : 'allowed';
    }

    /**
     * Checks if lessons have to be completed in a consecutive order and
     * whether or not a user has completed the previous lessons.
     *
     * @since    0.0.1
     */
    public function lesson_reached( $lesson_id = null, $course_id = null ) {
      if( ! $lesson_id || ! $course_id ) {
        return true;
      }

      if( get_post_type( $course_id ) !== 'humble_lms_course' ) {
        return true;
      }

      $courseHasConsecutiveOrder = get_post_meta( $course_id, 'humble_lms_course_consecutive_order', true );
      if( ! $courseHasConsecutiveOrder ) {
        return true;
      }

      $lessons = $this->content_manager->get_course_lessons( $course_id );
      $current_lesson_index = array_search( $lesson_id, $lessons );

      if( $current_lesson_index === 0 )
        return true;

      for( $i = 0; $i < $current_lesson_index; $i++ ) {
        if( ! $this->user->completed_lesson( get_current_user_id(), $lessons[$i] ) ) {
          return false;
        }
      }

      return true;
    }
    
  }

}
