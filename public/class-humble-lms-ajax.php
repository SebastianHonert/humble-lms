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
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $humble_lms       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;
      $this->content_manager = new Humble_LMS_Content_Manager;

    }
    
    /**
     * Mark lesson complete button clicked / open lesson.
     *
     * @since 1.0.0
     * @return void
     */
    public function mark_lesson_complete() {

      $user_id = get_current_user_id();

      function default_redirect( $redirect_url, $course_id, $lesson_id, $completed = null ) {
        wp_die( json_encode( array(
          'status' => 200,
          'redirect_url' => $redirect_url,
          'course_id' => $course_id,
          'lesson_id' => $lesson_id,
          'completed' => $completed,
        ) ) );
      }

      if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        die( 'Permission Denied' );
      }

      $course_id = isset( $_POST['courseId'] ) ? (int)$_POST['courseId'] : null;
      $lesson_id = isset( $_POST['lessonId'] ) ? (int)$_POST['lessonId'] : null;
      $lesson_completed = $_POST['lessonCompleted'] && $_POST['lessonCompleted'] === 'true';
      $mark_complete = filter_var( $_POST['markComplete'], FILTER_VALIDATE_BOOLEAN );

      if( ! $course_id ) {
        if( ! $lesson_id ) {
          $redirect_url = esc_url( home_url() );
        } else {
          $redirect_url = esc_url( get_permalink( $lesson_id ) );
        }

        default_redirect( $redirect_url, $course_id, $lesson_id );
      }

      if( ! $mark_complete ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      $course = get_post( $course_id );

      if( ! $course ) {
        default_redirect( esc_url( get_permalink( $lesson_id ) ), $course_id, $lesson_id );
      }

      // This returns an array with the completed lesson, courses, track,
      // award, and certificate ids [[lessonIds], [courseIds], [trackIds] ...]
      if( is_user_logged_in() ) {
        $completed = $this->user->mark_lesson_complete( $lesson_id );
      }

      $lessons = $this->content_manager->get_course_lessons( $course_id );

      $key = array_search( $lesson_id, $lessons );
      $is_last = $key === array_key_last( $lessons );

      if( ! $is_last ) {
        $next_lesson = get_post( $lessons[$key+1] );
        $redirect_url = esc_url( get_permalink( $next_lesson->ID ) );
      } else {
        $redirect_url = esc_url( get_permalink( $lessons[0] ) );
      }

      default_redirect( $redirect_url, $course_id, $next_lesson->ID, $completed );
    }

    /**
     * Save evaluated quiz for logged in user.
     *
     * @since 1.0.0
     * @return void
     */
    public function evaluate_quiz() {
      if( ! is_user_logged_in() || ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        die( 'Permission Denied' );
      }

      $evaluation = $_POST['evaluation'];
      $completed = (bool)$evaluation['completed'];
      $tryAgain = (int)$evaluation['tryAgain'];
      foreach( $evaluation['quizIds'] as $key => $id ) {
        $evaluation['quizIds'][$key] = (int)$id;
      }

      if( $completed ) {
        $completed_quizzes = $this->user->completed_quizzes( get_current_user_ID() );

        if( $tryAgain === 0 ) {
          foreach( $evaluation['quizIds'] as $id ) {
            if( ! in_array( $id, $completed_quizzes) ) {
              array_push( $completed_quizzes, $id );
            }
          }
        } elseif( $tryAgain === 1 ) {
          foreach( $completed_quizzes as $key => $quiz_id ) {
            if( in_array( $quiz_id, $evaluation['quizIds'] ) ) {
              unset( $completed_quizzes[$key] );
            }
          }
        }

        update_user_meta( get_current_user_ID(), 'humble_lms_quizzes_completed', $completed_quizzes );
      }

      die;
    }

    /**
     * Reset user progress.
     *
     * @since 1.0.0
     * @return void
     */
    public function reset_user_progress( $user_id = null ) {
      if( ! $user_id && ! $_POST['userId'] )
        die;

      $user_id = $user_id ? $user_id : (int)$_POST['userId'];

      if( ! get_user_by( 'id', $user_id ) )
        die;

      $this->user->reset_user_progress( $user_id );
      die;
    }

    /**
     * Validate frontend price
     * 
     * @since   1.0.0
     * @return  boolean
     */
    public function validate_frontend_price() {
      $post_id = sanitize_text_field( $_POST['post_id'] );
      $is_for_sale = get_post_meta( $post_id, 'humble_lms_is_for_sale', true );

      if( (int)$is_for_sale !== 1 ) {
        echo json_encode('not for sale');
        die;
      }

      $price_frontend = sanitize_text_field( $_POST['price'] );
      $price_backend = Humble_LMS_Content_Manager::get_price( $post_id, true );

      if( $price_frontend !== $price_backend ) {
        echo json_encode('prices do not match');
        die;
      }

      echo json_encode('valid');
      die;
    }

    /**
     * Save PayPal transaction.
     *
     * @since 1.0.0
     * @return void
     */
    public function save_paypal_transaction() {
      if( ! is_user_logged_in() ) {
        die;
      }

      $user_id = get_current_user_id();
      $user = get_user_by( 'id', $user_id );

      if( ! $user ) {
        die;
      }

      $details = $_POST['details'];
      $context = sanitize_text_field( $_POST['context'] );

      // Create new transaction post
      $txn = array(
        'post_type' => 'humble_lms_txn',
        'post_title' => sanitize_text_field( $user->user_login ) . ' ' . date("Y-m-d h:i"),
        'post_status' => 'publish',
        'post_author' => 1,
      );

      $txn_id = wp_insert_post( $txn, $wp_error );
      update_post_meta( $txn_id, 'humble_lms_txn_user_id', (int)$user_id );

      // Update transaction meta
      $order_details = array (
        'order_id' => sanitize_text_field( $details['id'] ),
        'payer_id' => sanitize_text_field( $details['payer']['payer_id'] ),
        'status' => sanitize_text_field( $details['status'] ),
        'user_id' => (int)$user_id,
        'payment_service_provider' => 'PayPal',
        'email_address' => sanitize_email( $details['payer']['email_address'] ),
        'create_time' => sanitize_text_field( $details['create_time'] ),
        'update_time' => sanitize_text_field( $details['update_time'] ),
        'given_name' => sanitize_text_field( $details['payer']['name']['given_name'] ),
        'surname' => sanitize_text_field( $details['payer']['name']['surname'] ),
        'reference_id' => sanitize_text_field( $details['purchase_units'][0]['reference_id'] ),
        'currency_code' => sanitize_text_field( $details['purchase_units'][0]['amount']['currency_code'] ),
        'value' => sanitize_text_field( $details['purchase_units'][0]['amount']['value'] ),
      );

      add_post_meta( $txn_id, 'humble_lms_order_details', $order_details );

      // Update user meta
      switch( $context )
      {
        // Purchased membership
        case 'membership':
          update_user_meta( $order_details['user_id'], 'humble_lms_membership', $order_details['reference_id'] );
          break;
  
        // Purchased single item
        default:
          $post_id = (int)$order_details['reference_id'];
          $purchased = get_user_meta( $order_details['user_id'], 'humble_lms_purchased_content', false );
          $purchased = is_array( $purchased[0] ) ? $purchased[0] : [];
          $post_type = get_post_type( $post_id );

          // Course & track
          if( ! in_array( $post_id, $purchased ) ) {
            array_push( $purchased, $post_id );
          }

          // Tracks only
          if( $post_type === 'humble_lms_track' ) {
            $courses = Humble_LMS_Content_Manager::get_track_courses( $post_id );
            foreach( $courses as $course_id ) {
              if( ! in_array( $course_id, $purchased ) ) {
                array_push( $purchased, $course_id );
              }
            }
          }
          
          update_user_meta( $order_details['user_id'], 'humble_lms_purchased_content', $purchased );

          break;
      }

      // Send email notifications
      $this->send_email_notifications( $order_details, $context );

      // Done
      echo json_encode($details, true);
      die;
    }

    /**
     * Send email notifications for completed transactions.
     * 
     * @since   1.0.0
     * @return   void
     */
    public function send_email_notifications( $order_details, $context = '' ) {
      $options = get_option('humble_lms_options');
      $user_info = get_userdata( $order_details['user_id'] );

      $to = array( get_option( 'admin_email' ), $user_info->user_email );
      $subject = 'Your purchase (' . get_option( 'blogname' ) . ')';
      $body = '<p>Hi there,</p>';
      $body .= '<p>thank you very much for your purchase. Please find your transaction details below.</p>';
      
      $body .= '<p><strong>Account information</strong></p>';
      $body .= '<p>Login: ' . $user_info->user_login . '<br>';
      $body .= 'Display name: ' . $user_info->display_name . '<br>';
      $body .= 'First name: ' . $user_info->first_name . '<br>';
      $body .= 'Username: ' . $user_info->last_name . '<br>';
      $body .= 'Email: ' . $user_info->user_email . '<br>';

      $body .= '<p><strong>PayPal transaction details</strong></p>';
      $body .= 'Payer ID: ' . $order_details['payer_id'] . '<br>';
      $body .= 'Email: ' . $order_details['email_address'] . '<br>';
      $body .= 'Given name: ' . $order_details['given_name'] . '<br>';
      $body .= 'Surname: ' . $order_details['surname'] . '<br>';
      $body .= 'Order ID: ' . $order_details['order_id'] . '<br>';
      $body .= 'Create time: ' . $order_details['create_time'] . '<br>';
      $body .= 'Update time: ' . $order_details['update_time'] . '<br>';
      $body .= 'Reference ID: ' . $order_details['reference_id'] . '<br>';
      $body .= 'Value: ' . $order_details['currency_code'] . ' ' . $order_details['value'];

      if( $context !== 'membership' ) {
        $body .= '<br>Link to content: ' . esc_url( get_permalink( $order_details['reference_id'] ) );
      }

      $body .= '</p>';

      $account_page_html = ! empty( $options['custom_pages']['user_profile'] ) ? esc_url( get_permalink( $options['custom_pages']['user_profile'] ) ) : esc_url( site_url() );

      $body .= '<p>You can now access the purchased content. A list of all your transactions and purchases is available on your account page of our website:</p>';
      $body .= '<p><a href="' . $account_page_html . '">' . $account_page_html . '</a></p>';
      $body .= '<p>We will send you another email including the invoice shortly.</p>';
      $body .= '<p>Enjoy our courses!</p>';

      $headers = array('Content-Type: text/html; charset=UTF-8');
        
      wp_mail( $to, $subject, $body, $headers );
     }
    
  }
  
}
