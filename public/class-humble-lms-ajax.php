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
      $this->quiz = new Humble_LMS_Quiz;
      $this->translator = new Humble_LMS_Translator;

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

      // Get translated post IDs
      $course_id = $this->translator->get_translated_post_id( $course_id );
      $lesson_id = $this->translator->get_translated_post_id( $lesson_id );

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
        $next_lesson_id = $this->translator->get_translated_post_id( $next_lesson->ID );
        $redirect_url = esc_url( get_permalink( $next_lesson_id ) );
      } else {
        $next_lesson_id = $this->translator->get_translated_post_id( $lessons[0] );
        $redirect_url = esc_url( get_permalink( $next_lesson_id  ) );
      }

      default_redirect( $redirect_url, $course_id, $next_lesson_id, $completed );
    }

    /**
     * Save evaluated quiz for logged in user.
     *
     * @since 1.0.0
     * @return void
     */
    public function evaluate_quiz() {
      if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
        echo json_encode( 'nonce' );
        die;
      }

      $options = get_option('humble_lms_options');
      $evaluation = $_POST['evaluation'];
      $tryAgain = (int)$evaluation['tryAgain'];
      $completed = (int)$evaluation['completed'];
      
      foreach( $evaluation['quizIds'] as $key => $id ) {
        $evaluation['quizIds'][$key] = (int)$id;
      }

      // Max. attempts exceeded
      $evaluation['max_attempts_exceeded'] = $this->quiz->max_attempts_exceeded( $evaluation['quizIds'] );
      if( $evaluation['max_attempts_exceeded'] ) {
        echo json_encode( $evaluation );
        die;
      }

      // Updated completed quizzes
      $completed_quizzes = $this->user->completed_quizzes( get_current_user_ID() );

      if( $completed === 1 ) {
        foreach( $evaluation['quizIds'] as $id ) {
          if( ! in_array( $id, $completed_quizzes ) ) {
            array_push( $completed_quizzes, $id );
          }
        }
      }
        
      if( $tryAgain === 1 ) {
        foreach( $completed_quizzes as $key => $quiz_id ) {
          if( in_array( $quiz_id, $evaluation['quizIds'] ) ) {
            unset( $completed_quizzes[$key] );
          }
        }
      }

      update_user_meta( get_current_user_ID(), 'humble_lms_quizzes_completed', $completed_quizzes );

      // Reset quiz
      if( $tryAgain === 1 ) {
        $evaluation['tryAgain'] = 1;
        echo json_encode($evaluation);
        die;
      }

      // Add quiz evaluation to user meta
      $evaluations = $this->user->evaluations();
      $evaluation['datetime'] = round(microtime(true) * 1000);
      $evaluation['completed'] = $completed;
      $evaluation['completed_quizzes'] = $completed_quizzes;

      if( count( $evaluations ) < $options['max_evaluations'] ) {
        array_push( $evaluations, $evaluation );
      } else {
        array_shift( $evaluations );
        array_push( $evaluations, $evaluation );
      }

      update_user_meta( get_current_user_ID(), 'humble_lms_quiz_evaluations', $evaluations );

      // Perform activities => array( [], [], [], [], [], [] ) => lesson, courses, tracks, awards, certificates, quizzes
      $evaluation['activities'] = $this->user->perform_activities( array( [], [], [], [], [], $completed_quizzes ), $evaluation['percent'], $evaluation['quizIds'] );

      // Awards
      if( ! empty( $evaluation['activities'][3] ) ) {
        $evaluation['awards'] = array();
        $awards = $evaluation['activities'][3];

        foreach( $awards as $award_id ) {
          $tmp = array(
            'title' => '',
            'name' => '',
            'image_url' => '',
            'icon' => ''
          );

          $tmp['title'] = __('You received an award', 'humble-lms');
          $tmp['name'] = get_the_title( $award_id );
          $tmp['image_url'] = get_the_post_thumbnail_url( $award_id );
          $tmp['icon'] = 'ti-medall';

          array_push( $evaluation['awards'], $tmp );
        }
      }

      // Certificates
      if( ! empty( $evaluation['activities'][4] ) ) {
        $evaluation['certificates'] = array();
        $certificates = $evaluation['activities'][4];

        foreach( $certificates as $certificate_id ) {
          $tmp = array(
            'title' => '',
            'name' => '',
            'image_url' => '',
            'icon' => ''
          );

          $tmp['title'] = __('You have been issued a certificate', 'humble-lms');
          $tmp['name'] = get_the_title( $certificate_id );
          $tmp['image_url'] = get_the_post_thumbnail_url( $certificate_id );
          $tmp['icon'] = 'ti-clipboard';

          array_push( $evaluation['certificates'], $tmp );
        }
      }

      // Update remaining attempts
      $evaluation['max_attempts_exceeded'] = $this->quiz->max_attempts_exceeded( $evaluation['quizIds'] );
      $evaluation['remaining_attempts'] = $this->quiz->remaining_attempts( $evaluation['quizIds'] );

      // Done.
      echo json_encode( $evaluation );
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
      $this->send_checkout_email( $order_details, $context );

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
    public function send_checkout_email( $order_details, $context = '' ) {
      $options = get_option('humble_lms_options');
      $user_info = get_userdata( $order_details['user_id'] );

      $to = $user_info->user_email;
      $subject = __('Order details', 'humble-lms') . ' (' . get_option( 'blogname' ) . ')';
      $headers[] = 'Content-Type: text/html; charset=UTF-8';
      $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_option( 'admin_email' ) . '>';
      $headers[] = 'Cc: ' . get_bloginfo('name') . ' <' . get_option( 'admin_email' ) . '>';

      // Order details
      $order_html = '<p><strong>' . __('Account information', 'humble-lms') . '</strong></p>';
      $order_html .= '<p>' . __('Login', 'humble-lms') . ': ' . $user_info->user_login . '<br>';
      $order_html .= __('Display name', 'humble-lms') . ': ' . $user_info->display_name . '<br>';
      $order_html .= __('First name', 'humble-lms') . ': ' . $user_info->first_name . '<br>';
      $order_html .= __('Username', 'humble-lms') . ': ' . $user_info->last_name . '<br>';
      $order_html .= __('Email', 'humble-lms') . ': ' . $user_info->user_email . '</p>';

      $order_html .= '<p><strong>' . __('PayPal transaction details', 'humble-lms') . '</strong></p>';
      $order_html .= __('Payer ID', 'humble-lms') . ': ' . $order_details['payer_id'] . '<br>';
      $order_html .= __('Email', 'humble-lms') . ': ' . $order_details['email_address'] . '<br>';
      $order_html .= __('Given name', 'humble-lms') . ': ' . $order_details['given_name'] . '<br>';
      $order_html .= __('Surname', 'humble-lms') . ': ' . $order_details['surname'] . '<br>';
      $order_html .= __('Order ID', 'humble-lms') . ': ' . $order_details['order_id'] . '<br>';
      $order_html .= __('Create time', 'humble-lms') . ': ' . $order_details['create_time'] . '<br>';
      $order_html .= __('Update time', 'humble-lms') . ': ' . $order_details['update_time'] . '<br>';
      $order_html .= __('Reference ID', 'humble-lms') . ': ' . $order_details['reference_id'] . '<br>';
      $order_html .= __('Value', 'humble-lms') . ': ' . $order_details['currency_code'] . ' ' . $order_details['value'];

      if( $context !== 'membership' ) {
        $order_html .= '<br>' . __('Link to content', 'humble-lms') . ': ' . esc_url( get_permalink( $order_details['reference_id'] ) );
      }

      $order_html .= '</p>';

      // Use default content if option is not set
      if( ! isset( $options['email_checkout'] ) || empty( $options['email_checkout'] ) ) {
        $body = '<p>' . sprintf( __('Hello %s!', 'humble-lms'), $user_info->user_login ) . '</p>';
        $body .= '<p>' . __('Thank you very much for your purchase. Please find the details of your order below.', 'humble-lms') . '</p>';
        $body .= $order_html;

        $account_page_html = ! empty( $options['custom_pages']['user_profile'] ) ? esc_url( get_permalink( $options['custom_pages']['user_profile'] ) ) : esc_url( site_url() );

        $body .= '<p>' . __('You can now access the purchased content. A list of all your transactions and purchases is available on your account page of our website:', 'humble-lms') . '</p>';
        $body .= '<p><a href="' . $account_page_html . '">' . $account_page_html . '</a></p>';
        $body .= '<p>' . __('We will send you another email including the invoice shortly.', 'humble-lms') . '</p>';
        $body .= '<p>' . __('Enjoy our courses!', 'humble-lms') . '</p>';
      }

      else {
        $body = wp_kses_post( $options['email_checkout'] );
        $body = str_replace( 'ORDER_DETAILS', $order_html, $body );
        $body = str_replace( 'USER_NAME', $user_info->display_name, $body );
        $body = str_replace( 'ADMIN_EMAIL', get_option( 'admin_email' ), $body );
        $body = str_replace( 'CURRENT_DATE', $date, $body );
        $body = str_replace( 'WEBSITE_NAME', get_bloginfo('name'), $body );
        $body = str_replace( 'WEBSITE_URL', get_bloginfo('url'), $body );
      }

      wp_mail( $to, $subject, $body, $headers );
     }

    /**
     * Toggle height of syllabus in lesson view.
     * 
     * @since   1.0.0
     * @return   void
     */
    public function toggle_syllabus_height() {
      if( ! is_user_logged_in() ) {
        echo json_encode('expanded');
        die;
      }

      $syllabus_state = sanitize_text_field( $_POST['syllabusState'] );
      $new_syllabus_state = $syllabus_state === 'expanded' ? 'closed' : 'expanded';

      update_user_meta( get_current_user_id(), 'humble_lms_syllabus_state', $new_syllabus_state );

      echo json_encode( $new_syllabus_state );

      die;
    }
    
  }
  
}
