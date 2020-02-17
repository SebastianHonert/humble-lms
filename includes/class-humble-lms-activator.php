<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sebastianhonert.com
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS_Activator {

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    0.0.1
   */
  public function activate() {
    $this->add_custom_pages();
    $this->init_options();
  }

  /**
   * Add custom pages for login, registration and password reset.
   * 
   * @since   0.0.1
   */
  public function add_custom_pages() {
    $custom_page_course_archive = array(
      'post_title' => 'Humble LMS Course Archive',
      'post_name' => 'courses',
      'post_content' => '[humble_lms_course_archive]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_track_archive = array(
      'post_title' => 'Humble LMS Track Archive',
      'post_name' => 'tracks',
      'post_content' => '[humble_lms_track_archive]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_login = array(
      'post_title' => 'Humble LMS Login',
      'post_name' => 'login',
      'post_content' => '[humble_lms_login_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );
    
    $custom_page_registration = array(
      'post_title' => 'Humble LMS Registration',
      'post_name' => 'registration',
      'post_content' => '[humble_lms_registration_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_lost_password = array(
      'post_title' => 'Humble LMS Lost Password',
      'post_name' => 'lost-password',
      'post_content' => '[humble_lms_lost_password_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_reset_password = array(
      'post_title' => 'Humble LMS Reset Password',
      'post_name' => 'reset-password',
      'post_content' => '[humble_lms_reset_password_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_user_profile = array(
      'post_title' => 'Humble LMS User Profile',
      'post_name' => 'account',
      'post_content' => '[humble_lms_user_profile]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    if( ! get_page_by_title('Humble LMS Course Archive', OBJECT, 'page') )
      wp_insert_post( $custom_page_course_archive );
    
    if( ! get_page_by_title('Humble LMS Track Archive', OBJECT, 'page') )
      wp_insert_post( $custom_page_track_archive );

    if( ! get_page_by_title('Humble LMS Login', OBJECT, 'page') )
      wp_insert_post( $custom_page_login );
    
    if( ! get_page_by_title('Humble LMS Registration', OBJECT, 'page') )
      wp_insert_post( $custom_page_registration );

    if( ! get_page_by_title('Humble LMS Lost Password', OBJECT, 'page') )
      wp_insert_post( $custom_page_lost_password );

    if( ! get_page_by_title('Humble LMS Reset Password', OBJECT, 'page') )
      wp_insert_post( $custom_page_reset_password );

    if( ! get_page_by_title('Humble LMS User Profile', OBJECT, 'page') )
      wp_insert_post( $custom_page_user_profile );
  }

  /**
   * Initialize plugin options.
   * 
   * @since   0.0.1
   */
  public function init_options() {
    $custom_page_login = get_page_by_title('Humble LMS Login', OBJECT, 'page');
    $custom_page_registration = get_page_by_title('Humble LMS Registration', OBJECT, 'page');
    $custom_page_lost_password = get_page_by_title('Humble LMS Lost Password', OBJECT, 'page');
    $custom_page_reset_password = get_page_by_title('Humble LMS Reset Password', OBJECT, 'page');
    $custom_page_user_profile = get_page_by_title('Humble LMS User Profile', OBJECT, 'page');

    update_option('humble_lms_options', array(
      'messages' => array('lesson', 'course', 'track', 'award', 'certificate'),
      'custom_pages' => array(
        'login' => $custom_page_login->ID,
        'registration' => $custom_page_registration->ID,
        'lost_password' => $custom_page_lost_password->ID,
        'reset_password' => $custom_page_reset_password->ID,
        'user_profile' => $custom_page_user_profile->ID,
      ),
    ));
  }

}
