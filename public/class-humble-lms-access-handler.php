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
