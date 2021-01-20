<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sebastianhonert.com
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS_Public {

  /**
   * The ID of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $humble_lms    The ID of this plugin.
   */
  private $humble_lms;

  /**
   * The version of this plugin.
   *
   * @since    0.0.1
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    0.0.1
   * @param      string    $humble_lms       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $humble_lms, $version ) {

    $this->humble_lms = $humble_lms;
    $this->version = $version;
    $this->user = new Humble_LMS_Public_User;
    $this->access_handler = new Humble_LMS_Public_Access_Handler;
    $this->options_manager = new Humble_LMS_Admin_Options_Manager;
    $this->translator = new Humble_LMS_Translator;

  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    0.0.1
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Humble_LMS_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Humble_LMS_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    $options = get_option('humble_lms_options');

    $tippy_theme = isset( $options['tippy_theme'] ) ? sanitize_text_field( $options['tippy_theme'] ) : 'default';

    // Tippy
    if( $tippy_theme !== 'default' ) {
      wp_enqueue_style( 'tippy-theme-' . $tippy_theme, plugin_dir_url( __FILE__ ) . 'js/tippy/themes/' . $tippy_theme . '.css', array(), $this->version, 'all' );
    }

    wp_enqueue_style( 'tippy-scale', plugin_dir_url( __FILE__ ) . 'js/tippy/animations/scale.css', array(), $this->version, 'all' );
    
    // Humble LMS
    wp_enqueue_style( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'css/humble-lms-public.css', array(), $this->version, 'all' );
    wp_enqueue_style( 'themify-icons', plugin_dir_url( __FILE__ ) . 'font/themify-icons/themify-icons.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    0.0.1
   */
  public function enqueue_scripts() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Humble_LMS_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Humble_LMS_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    global $post;
    
    $options = get_option('humble_lms_options');

    // PayPal
    if( Humble_LMS_Admin_Options_Manager::has_sales() ) {
      $client_id = $options['paypal_client_id'];
      $currency = $this->options_manager->get_currency();
      
      if( ! $client_id )
        $client_id = 'sb';

      wp_enqueue_script( 'humble-lms-paypal' , 'https://www.paypal.com/sdk/js?client-id=' . $client_id . '&currency=' . $currency, false, NULL, true );
    }

    // reCAPTChA
    if( Humble_LMS_Admin_Options_Manager::has_recaptcha() ) {
      $website_key = isset( $options['recaptcha_website_key'] ) ? $options['recaptcha_website_key'] : false;

      if( isset( $post->post_content ) && $website_key && ( has_shortcode( $post->post_content, 'humble_lms_registration_form' ) ) ) {
        wp_enqueue_script( 'humble-lms-recaptcha' , 'https://www.google.com/recaptcha/api.js', false, NULL, true );
      }
    }

     // Marked
     wp_enqueue_script( 'humble-lms-marked', plugin_dir_url( __FILE__ ) . 'js/marked.min.js', false, '1.1.1', true );

    // TippyJS
    wp_enqueue_script( 'humble-lms-popper', plugin_dir_url( __FILE__ ) . 'js/tippy/popper.min.js', false, '2.4.4', true );
    wp_enqueue_script( 'humble-lms-tippy', plugin_dir_url( __FILE__ ) . 'js/tippy/tippy-bundle.umd.min.js', false, '6.2.5', true );

    // Humble LMS
    wp_enqueue_script( 'humble-lms-inview', plugin_dir_url( __FILE__ ) . 'js/inview.js', array( 'jquery' ), $this->version, true );
    wp_enqueue_script( 'humble-lms-quiz', plugin_dir_url( __FILE__ ) . 'js/humble-lms-quiz.js', array( 'jquery' ), $this->version, true );
    wp_enqueue_script( 'humble-lms-checkout', plugin_dir_url( __FILE__ ) . 'js/humble-lms-checkout.js', array( 'jquery' ), $this->version, true );
    wp_enqueue_script( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'js/humble-lms-public.js', array( 'jquery' ), $this->version, true );
    
    wp_localize_script( $this->humble_lms, 'humble_lms', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( 'humble_lms' ),
      'confirmResetUserProgress' => __('Are you sure? This will irrevocably reset your learning progress, including awards and certificates.', 'humble-lms'),
      'membership_undefined' => __('Invalid membership type, checkout cancelled.', 'humble-lms'),
      'price_undefined' => __('Invalid price value, checkout cancelled.', 'humble-lms'),
      'post_id_undefined' => __('Invalid content ID, checkout cancelled.', 'humble-lms'),
      'is_user_logged_in' => is_user_logged_in() ? true : false,
      'current_user_id' => get_current_user_id(),
      'buy_now_text' => __('Buy now', 'humble-lms'),
      'currency' => $this->options_manager->get_currency(),
      'has_sales' => Humble_LMS_Admin_Options_Manager::has_sales(),
      'tippy_theme' => isset( $options['tippy_theme'] ) ? $options['tippy_theme'] : 'default',
      'toggle_syllabus_text_close' => __('Collapse syllabus', 'humble-lms'),
      'toggle_syllabus_text_expand' => __('Expand syllabus', 'humble-lms'),
      'user_syllabus_state' => get_user_meta( get_current_user_id(), 'humble_lms_syllabus_state', true ) ? get_user_meta( get_current_user_id(), 'humble_lms_syllabus_state', true ) : 'expanded',
      'general_error' => __('Sorry, an error occured.', 'humble-lms'),
    ) );
  }

  /**
   * Register archive templates
   *
   * @since    0.0.1
   */
  public function humble_lms_archive_templates( $template ) {
    global $post;

    if( ! $post )
      return $template;

    // Track archive
    if ( is_archive() && $post->post_type == 'humble_lms_track' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-track-archive.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-track-archive.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-archive.php';
      }
    }

    // Course archive
    if ( is_archive() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-course-archive.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-course-archive.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php';
      }
    }

    // Membership archive
    if ( is_archive() && $post->post_type == 'humble_lms_mbship' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-membership-archive.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-membership-archive.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-membership-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-membership-archive.php';
      }
    }

    return $template;
  }

  /**
   * Register single templates
   *
   * @since    0.0.1
   */
  public function humble_lms_single_templates( $template ) {
    global $wp_query, $post;

    // Track single
    if ( is_single() && $post->post_type == 'humble_lms_track' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-track-single.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-track-single.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-single.php';
      }
    }

    // Course single
    if ( is_single() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-course-single.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-course-single.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-single.php';
      }
    }

    // Lesson single
    if ( is_single() && $post->post_type == 'humble_lms_lesson' ) {
      if ( file_exists( get_stylesheet_directory() . '/humble-lms/partials/humble-lms-lesson-single.php' ) ) {
        return get_stylesheet_directory() . '/humble-lms/partials/humble-lms-lesson-single.php';
      } else if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-lesson-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-lesson-single.php';
      }
    }

    // Certificate single
    if ( is_single() && $post->post_type == 'humble_lms_cert' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-certificate-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-certificate-single.php';
      }
    }

    // Transaction single (= invoice)
    if ( is_single() && $post->post_type == 'humble_lms_txn' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-txn-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-txn-single.php';
      }
    }

    return $template;
  }

  /**
   * Add content to pages.
   *
   * @since    0.0.1
   */
  public function humble_lms_add_content_to_pages( $content ) {
    global $post;

    $html = '';
    $options = get_option('humble_lms_options');
    $content_manager = new Humble_LMS_Content_Manager;

    if( ! is_admin() ) {
      $html .= '<div class="humble-lms-loading-layer"><div class="humble-lms-loading"></div></div>';
    }

    // Welcome message after successful registration
    if( isset( $_GET['humble-lms-welcome'] ) && (int)$_GET['humble-lms-welcome'] === 1 ) {
      echo '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Registration successful', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . sprintf( __('Welcome on board! %s We just sent you a welcome email with your account information. Please check your inbox.', 'humble-lms'), '<i class="ti-face-smile"></i>' ) . '</span>
      </div>';
    }

    $allowed_post_types = [
      'humble_lms_track',
      'humble_lms_course',
      'humble_lms_lesson',
    ];

    if( ! in_array( get_post_type( $post->ID ), $allowed_post_types ) ) {
      return $content;
    }

    $lesson_id = null;
    $course_id = null;

    // Course ID
    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' ) {
      $course_id = $post->ID;
    } elseif( isset( $_POST['course_id'] ) ) {
      $course_id = (int)$_POST['course_id'];
    }

    // Content needs to be purchased first
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'purchase' ) && ! current_user_can('manage_options' ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--error">';
      $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
      $html .= '<span class="humble-lms-message-content">' . sprintf( __('You need to purchase this course first.', 'humble-lms' ), wp_login_url() ) . '</span>';
      $html .= '</div>';
    }

    // Purchase completed
    if( isset( $_GET['purchase'] ) && $_GET['purchase'] === 'success') {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Purchase completed', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . __('Thank you for your purchase. A confirmation email is on its way to your inbox. Enjoy our online courses!', 'humble-lms') . '</span> 
      </div>';
    }

    if( isset( $_GET['purchase'] ) && $_GET['purchase'] === 'success-no-billing') {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Purchase completed', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . __('Thank you for your purchase. Enjoy our online courses!', 'humble-lms') . '</span> 
      </div>';
    }

    // Course has ended / not started yet
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'timeframe' ) && ! current_user_can('manage_options' ) ) {
      $course_is_open = $content_manager->course_is_open( $course_id );

      if( $course_is_open !== 0 ) {
        $timestamps = $content_manager->get_timestamps( $course_id );
        $msg = __('This course is currently closed.', 'humble-lms');

        switch( $course_is_open ) {
          case 1:
            $msg = sprintf( __('This course will open on %s.', 'humble-lms'), $timestamps['date_from'] );
            break;
          case 2:
            $msg = sprintf( __('This course has already been closed on %s.', 'humble-lms' ), $timestamps['date_to'] );
            break;
        }
      
        $html .= '<div class="humble-lms-message humble-lms-message--error">';
        $html .= '<span class="humble-lms-message-title">' . __('Course closed', 'humble-lms') . '</span>';
        $html .= '<span class="humble-lms-message-content">' . $msg . '</span>';
        $html .= '</div>';
      }
    }

    // Access denied
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'denied' ) && ! current_user_can('manage_options' ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--error">';
      $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
      $html .= '<span class="humble-lms-message-content">' . sprintf( __('You need to be <a href="%s">logged in</a> and have the required permissions in order to access the requested content.', 'humble-lms' ), wp_login_url() ) . '</span>';
      $html .= '</div>';
    }

    // Premium membership required
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'membership' ) && ! current_user_can('manage_options' ) ) {
      $html .= $this->html_premium_membership_required();
    }
    

    // Lesson not accessed in consecutive order
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'order' ) && ! current_user_can('manage_options' ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--error">';
      $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
      $html .= '<span class="humble-lms-message-content">' . sprintf( __('The lessons of this course need to be completed in a consecutive order. Please complete the previous lessons first.', 'humble-lms' ), wp_login_url() ) . '</span>';
      $html .= '</div>';
    }

    // Message user completed course
    if( isset( $course_id ) && $this->user->completed_course( $course_id ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Congratulations', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . __('You successfully completed this course.', 'humble-lms') . '</span> 
      </div>';
    }

    // Single lesson
    if( is_single() && get_post_type( $post->ID ) === 'humble_lms_lesson' ) {
      $level = strip_tags( get_the_term_list( $post->ID, 'humble_lms_tax_course_level', '', ', ') );
      $level = $level ? '<span class="humble-lms-lesson-level"><strong>' . __('Level', 'humble-lms') . ':</strong> <span>' . $level . '</span></span>': '';
      $html .= $level;
    }

    // Content
    if ( is_single() && ( get_post_type( $post->ID ) === 'humble_lms_course' || get_post_type( $post->ID ) === 'humble_lms_lesson' ) ) {
      $html .= $content;
    }

    // Completed => [lesson, course, track, award, certificate]
    if( isset( $_POST['completed'] ) ) {
      $completed = json_decode( $_POST['completed'] );
      $messages = isset( $options['messages'] ) ? $options['messages'] : [];

      if( $this->has_messages( $completed, $messages ) ) {
        $html .= '<div class="humble-lms-award-message"><div>';

        foreach( $completed as $key => $ids ) {
          foreach( $ids as $id ) {

            if( $key === 0 && ! in_array( 'lesson', $messages ) ) { continue; }
            if( $key === 1 && ! in_array( 'course', $messages ) ) { continue; }
            if( $key === 2 && ! in_array( 'track', $messages ) ) { continue; }
            if( $key === 3 && ! in_array( 'award', $messages ) ) { continue; }
            if( $key === 4 && ! in_array( 'certificate', $messages ) ) { continue; }

            if( $key === 0 ) { $title = __('Lesson completed', 'humble-lms'); $icon = 'ti-thumb-up'; }
            if( $key === 1 ) { $title = __('Course completed', 'humble-lms'); $icon = 'ti-medall'; }
            if( $key === 2 ) { $title = __('Track completed', 'humble-lms'); $icon = 'ti-crown'; }
            if( $key === 3 ) { $title = __('You received an award', 'humble-lms'); $icon = 'ti-medall'; }
            if( $key === 4 ) { $title = __('You have been issued a certificate', 'humble-lms'); $icon = 'ti-clipboard'; }

            $html .= '<div class="humble-lms-award-message-inner">
                <div>
                  <div class="humble-lms-award-message-close" aria-label="Close award overlay">
                    <i class="ti-close"></i>
                  </div>
                  <h3 class=humble-lms-award-message-title">' . $title . '</h3>
                  <p class="humble-lms-award-message-content-name">' . get_the_title( $id ) . '</p>';

                  if( $key < 3 ) {
                    $html .= '<div class="humble-lms-award-message-image humble-lms-bounce-in">
                    <i class="' . $icon .'"></i>
                  </div>';
                  } elseif ( $key === 3 ) {
                    if( get_the_post_thumbnail_url( $id ) ) {
                      $html .= '<img class="humble-lms-award-image humble-lms-bounce-in" src="' . get_the_post_thumbnail_url( $id ) . '" alt="" />';
                    } else {
                      $html .= '<div class="humble-lms-award-message-image humble-lms-bounce-in">
                        <i class="' . $icon .'"></i>
                      </div>';
                    }
                  } elseif ( $key === 4 ) {
                    $html .= '<div class="humble-lms-award-message-image humble-lms-bounce-in">
                      <i class="' . $icon .'"></i>
                    </div>';
                  }

                $html .= '</div>
              </div>';
          }
        }

        $html .= '</div></div>';
      }
    }

    return $html;
  }

  /**
   * Message for required premium membership.
   * 
   * @since 0.0.2
   */
  public function html_premium_membership_required() {
    $options = get_option('humble_lms_options');

    $html = '<div class="humble-lms-message humble-lms-message--error">';
    $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
    $html .= '<span class="humble-lms-message-content">' . __('You need to upgrade your account in order to access the requested content.', 'humble-lms' );
    $html .= ' <a href="' . esc_url( get_post_type_archive_link( 'humble_lms_mbship') ) . '">' . __('Upgrade your account now.', 'humble_lms') . '</a>';

    $html .= '</span>';
    $html .= '</div>';

    return $html;
  }

  /**
   * Check if there are any messages that need to be shown.
   *
   * @since    0.0.1
   */
  public function has_messages( $completed, $messages ) {
    if( empty( $completed ) || empty( $messages ) ) { return false; }
    if( ! empty( $completed[0] ) && in_array( 'lesson', $messages ) ) { return true; }
    if( ! empty( $completed[1] ) && in_array( 'course', $messages ) ) { return true; }
    if( ! empty( $completed[2] ) && in_array( 'track', $messages ) ) { return true; }
    if( ! empty( $completed[3] ) && in_array( 'award', $messages ) ) { return true; }
    if( ! empty( $completed[4] ) && in_array( 'certificate', $messages ) ) { return true; }

    return false;
  }

  /**
   * Template redirect
   *
   * This function checks user access levels and redirects accordingly. 
   * @since    0.0.1
   */
  public function humble_lms_template_redirect() {
    global $post;
    global $wp_query;

    if( is_search() ) {
      return;
    }

    if( is_single() && $post->post_type === 'humble_lms_mbship' ) {
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 );
      die;
    }

    $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
    $access = isset( $post->ID ) ? $this->access_handler->can_access_lesson( $post->ID, $course_id ) : 'allowed';
    $url = ! empty( $_POST['course_id'] ) ? esc_url( get_permalink( (int)$_POST['course_id'] ) ) : esc_url( site_url() ); 

    if( is_single() && $post->post_type === 'humble_lms_lesson' && $access !== 'allowed' ) {
      switch( $access ) {
        case 'purchase':
          wp_redirect( add_query_arg( 'access', 'purchase', $url ) );
          break;
        case 'timeframe':
          wp_redirect( add_query_arg( 'access', 'timeframe', $url ) );
          break;
        case 'order':
          wp_redirect( add_query_arg( 'access', 'order', $url ) );
          break;
        case 'membership':
          wp_redirect( add_query_arg( 'access', 'membership', $url ) );
          break;
        default:
          wp_redirect( add_query_arg( 'access', 'denied', $url ) );
          break;
      }
      die;
    }

    // Check membership access level for posts / pages
    if ( is_page() || ( is_single() && 'post' === get_post_type() ) ) {
      if( ! $this->access_handler->user_can_access_post( $post->ID ) ) {
        $post->post_content = $this->html_premium_membership_required();
      }
    }
  }

  /**
   * Hide admin bar for registered users/students
   * 
   * @since    0.0.1
   */
  public function hide_admin_bar() {
    if ( ! current_user_can('edit_posts') ) {
      show_admin_bar(false);
    }
  }

  /**
   * Get clean URLs (without "/page/xyz/")
   * 
   * @since    0.0.1
   */
  public static function get_nopaging_url() {
    global $wp;

    $current_url =  home_url( $wp->request );
    $position = strpos( $current_url , '/page' );
    $nopaging_url = ( $position ) ? substr( $current_url, 0, $position ) : $current_url;

    return trailingslashit( $nopaging_url );
  }

  /**
   * Add access level meta box for posts/pages
   * 
   * @since 0.0.2
   */
  public function add_post_page_meta_box_membership_access_level() {
    $screens = [ 'post', 'page' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'humble_lms_metabox_post_page_membership_access_level',
            __('Access level', 'humble-lms'),
            array( $this, 'post_page_meta_box_membership_access_level'),
            $screen,
            'side',
            'high'
        );
    }
  }

  /**
   * Access level meta box for posts/pages.
   * 
   * @since 0.0.2
   */
  public function post_page_meta_box_membership_access_level( $post ) {
    global $post;

    wp_nonce_field( 'humble_lms_metabox_post_page_membership_access_level', 'humble_lms_metabox_post_page_membership_access_level_nonce' );

    $memberships = Humble_LMS_Content_Manager::get_memberships();
    $membership = get_post_meta( $post->ID, 'humble_lms_membership', true );
    
    if( ! $membership || ! in_array( $membership, $memberships ) ) {
      $membership = 'free';
    }

    echo '<select name="humble_lms_membership" id="humble_lms_membership">';
      $selected = 'free' === $membership ? 'selected' : '';
      echo '<option value="free" ' . $selected . '>Free</option>';

      foreach( $memberships as $m ) {
        $selected = $m === $membership ? 'selected' : '';
        echo '<option value="' . $m . '" ' . $selected . '>' . ucfirst($m) . '</option>';
      }
    echo '</select>';
  }

  /**
   * Save access level meta box for posts/pages.
   * 
   * @since 0.0.2
   */
  public function save_post_page_meta_box_membership_access_level( $post_id ) {
    if ( ! isset( $_POST['humble_lms_metabox_post_page_membership_access_level_nonce'] ) ) {
      return $post_id;
    }

    $nonce = $_POST['humble_lms_metabox_post_page_membership_access_level_nonce'];

    if ( ! wp_verify_nonce( $nonce, 'humble_lms_metabox_post_page_membership_access_level' ) ) {
      return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    if ( 'page' === $_POST['post_type'] ) {
      if ( ! current_user_can( 'edit_page', $post_id ) ) {
        return $post_id;
      }
    } else {
      if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
      }
    }

    // All good, let's sanitize and save the metabox data.
    $access_level = sanitize_text_field( $_POST['humble_lms_membership'] );

    update_post_meta( $post_id, 'humble_lms_membership', $access_level );
  }

}
