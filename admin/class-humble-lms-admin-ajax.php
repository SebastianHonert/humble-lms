<?php
/**
 * Admin AJAX functionality.
 *
 * Creates the various functions used for AJAX in the admin dashboard.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Admin_Ajax' ) ) {

  class Humble_LMS_Admin_Ajax {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $humble_lms       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {

      // ...

    }
    
    /**
     * Send test emails from dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    public function send_test_email() {
      $message = nl2br( stripslashes( htmlspecialchars( $_POST['message'] ) ) );
      $to = sanitize_email( $_POST['recipient'] );
      $subject = htmlspecialchars( $_POST['subject'] );
      $headers = 'From: '. get_option('admin_email') . "\r\n" . 'Reply-To: ' . get_option('admin_email') . "\r\n";
      $user = wp_get_current_user();
      $date_format = 'F j, Y';
      $date = current_time( $date_format );
      $message = str_replace( 'USER_NAME', $user->user_login, $message );
      $message = str_replace( 'USER_EMAIL', $user->user_email, $message );
      $message = str_replace( 'CURRENT_DATE', $date, $message );
      $message = str_replace( 'WEBSITE_NAME', get_bloginfo('name'), $message );
      $message = str_replace( 'WEBSITE_URL', get_bloginfo('url'), $message );
      $message = str_replace( 'LOGIN_URL', wp_login_url(), $message );
      $message = str_replace( 'ADMIN_EMAIL', get_option('admin_email'), $message );

      if( wp_mail( $to, $subject, strip_tags( $message ), $headers ) ) {
        echo json_encode('success');
      } else  {
        echo json_encode('error');
      }

      die;
    }
    
  }
  
}
