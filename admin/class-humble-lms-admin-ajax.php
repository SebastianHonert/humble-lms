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
     * @since 0.0.1
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
      $message = wpautop($message);

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
     * @since 0.0.1
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
        'post_status'   => 'draft',
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

    /**
     * Set membership access level for all lessons in a course.
     * 
     * @since 0.0.2
     */
    public function set_lesson_membership_level() {
      if( ! isset( $_POST['course_id'] ) || ! isset( $_POST['membership'] ) ) {
        echo json_encode( __('Course ID or membership type not set.', 'humble-lms' ) );
        die;
      }

      $course_id = (int)$_POST['course_id'];

      if( 'humble_lms_course' !== get_post_type( $course_id ) ) {
        echo json_encode( __('Course ID not found.', 'humble-lms' ) );
        die;
      }

      $memberships = Humble_LMS_Content_Manager::get_memberships();
      $membership = sanitize_text_field( $_POST['membership'] );
      
      if( 'free' !== $membership && ! in_array( $membership, $memberships ) ) {
        echo json_encode( __('Membership not found.', 'humble-lms' ) );
        die;
      }

      $lessons = Humble_LMS_Content_Manager::get_course_lessons( $course_id );

      if( empty( $lessons ) ) {
        echo json_encode( __('This course does not include any lessons.', 'humble-lms' ) );
        die;
      }

      foreach( $lessons as $lesson ) {
        update_post_meta( $lesson, 'humble_lms_membership', $membership );
      }

      $response_text = sprintf( __('Membership access for all lessons in this course set to %s', 'humble-lms'), '"' . ucfirst( $membership ) . '"' );
      echo json_encode( $response_text );
      die;
    }

    /**
     * Toggle user award/certificate by post ID.
     * 
     * @since 0.0.2
     */
    public function toggle_user_award_certificate() {
      if( ! isset( $_POST['post_id'] ) || ! $_POST['post_id'] ) {
        echo json_encode( __('Post ID not set.', 'humble-lms' ) );
        die;
      }

      if( ! isset( $_POST['user_id'] ) || ! $_POST['user_id'] ) {
        echo json_encode( __('User ID not set.', 'humble-lms' ) );
        die;
      }

      $post_id = (int)$_POST['post_id'];
      $user_id = (int)$_POST['user_id'];

      $post_types = array(
        'humble_lms_cert',
        'humble_lms_award'
      );

      $post_type = get_post_type( $post_id );

      if( ! in_array( $post_type, $post_types ) ) {
        echo json_encode( __('Post type not allowed.', 'humble-lms') );
        die;
      }

      $user = get_user_by( 'id', $user_id );

      if( ! $user ) {
        echo json_encode( __('User not found.', 'humble-lms') );
        die;
      }

      $user_manager = new Humble_LMS_Public_User;

      if( 'humble_lms_cert' === $post_type ) {
        $user_certificates = $user_manager->issued_certificates( $user_id );

        if( ! in_array( $post_id, $user_certificates ) ) {
          $user_manager->issue_certificate( $user->ID, $post_id );
        } else {
          $user_manager->revoke_certificate( $user->ID, $post_id );
        }
      }
      
      else if( 'humble_lms_award' === $post_type ) {
        $user_awards = $user_manager->granted_awards( $user_id );

        if( ! in_array( $post_id, $user_awards ) ) {
          $user_manager->grant_award( $user->ID, $post_id );
        } else {
          $user_manager->revoke_award( $user->ID, $post_id );
        }
      }

      echo json_encode( 'success' );

      die;
    }

  }
  
}
