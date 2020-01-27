<?php

/**
 * Fired during plugin activation
 *
 * @link       https://minimalwordpress.com/humble-lms
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
  }

  /**
   * Add custom pages for login, registration and password reset.
   */
  public function add_custom_pages() {
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

    wp_insert_post( $custom_page_login );
    wp_insert_post( $custom_page_registration );
    wp_insert_post( $custom_page_lost_password );
    wp_insert_post( $custom_page_reset_password );
  }

}
