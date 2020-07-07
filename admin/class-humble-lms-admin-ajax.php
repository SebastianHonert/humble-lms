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
     */
    public function __construct() {

      $this->translator = new Humble_LMS_Translator;

    }
    
    /**
     * Send test emails from dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    public function send_test_email() {
      $format = sanitize_text_field( $_POST['format'] );
      $message = wp_kses_post( $_POST['message'] );
      // $message = $format = 'text/plain' ? nl2br( stripslashes( htmlspecialchars( $_POST['message'] ) ) ) : wp_kses_post( $_POST['message'] );
      $to = sanitize_email( $_POST['recipient'] );
      $subject = htmlspecialchars( $_POST['subject'] );
      $headers[] = 'Content-Type: ' . $format . '; charset=UTF-8';
      $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_option( 'admin_email' ) . '>';
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

      if( wp_mail( $to, $subject, $message, $headers ) ) {
        echo json_encode('success');
      } else  {
        echo json_encode('error');
      }

      die;
    }

    /**
     * Add content.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_content() {

      $allowed_post_types = array(
        'humble_lms_lesson',
        'humble_lms_course',
        'humble_lms_question'
      );
  
      if( ! isset( $_POST['title'] ) || empty( $_POST['title'] ) || ! isset( $_POST['post_type'] ) || empty( $_POST['post_type'] || empty( $_POST['lang'] ) ) || ! in_array( $_POST['post_type'], $allowed_post_types ) ) {
        echo json_encode('error');
        die;
      }

      $title = sanitize_text_field( $_POST['title'] );
      $lang = sanitize_text_field( $_POST['lang'] );
      $post_type = sanitize_text_field( $_POST['post_type'] );

      $post = array(
        'post_title' => $title,
        'post_type' => $post_type,
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_author' => get_current_user_id(),
      );

      $post_id = wp_insert_post( $post );

      if( $post_id ) {
        $this->translator->set_post_language( $post_id, $lang );

        echo json_encode(
          array(
            'post_id' => $post_id,
            'post_title' => $post['post_title'],
            'post_type' => $post_type,
            'post_edit_link' => esc_url( get_edit_post_link( $post_id ) )
          )
        );
      } else  {
        echo json_encode('error');
      }

      die;
    }
    
  }
  
}
