<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://minimalwordpress.com/humble-lms
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

    wp_enqueue_script( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'js/humble-lms-public.js', array( 'jquery' ), $this->version, false );

    wp_localize_script( $this->humble_lms, 'humble_lms', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce' => wp_create_nonce( 'humble_lms' )
    ) );
  }
  
  /**
   * Register custom post types
   *
   * @since    0.0.1
   */
  public function register_custom_post_types() {
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-track.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-course.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-lesson.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-activity.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-award.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-email.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-certificate.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-quiz.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-question.php';
  }

  /**
   * Register custom taxonomies
   *
   * @since    0.0.1
   */
  public function register_custom_taxonomies() {
    require_once plugin_dir_path( __FILE__ ) . 'custom-taxonomies/humble-lms-course-level.php';
  }

  /**
   * Register archive templates
   *
   * @since    0.0.1
   */
  public function humble_lms_archive_templates( $template ) {
    global $wp_query, $post;

    // Track archive
    if ( is_archive() && $post->post_type == 'humble_lms_track' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-archive.php';
      }
    }

    // Course archive
    if ( is_archive() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php';
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
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-track-single.php';
      }
    }

    // Course single
    if ( is_single() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-single.php';
      }
    }

    // Lesson single
    if ( is_single() && $post->post_type == 'humble_lms_lesson' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-lesson-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-lesson-single.php';
      }
    }

    // Certificate single
    if ( is_single() && $post->post_type == 'humble_lms_cert' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-certificate-single.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-certificate-single.php';
      }
    }

    return $template;
  }

  /**
   * Add syllabus to single course page
   *
   * @since    0.0.1
   */
  public function humble_lms_add_content_to_pages( $content ) {
    global $post;

    $allowed_post_types = [
      'humble_lms_track',
      'humble_lms_course',
      'humble_lms_lesson',
    ];

    if( ! in_array( get_post_type( $post->ID ), $allowed_post_types ) ) {
      return $content;
    }

    $html = '';
    $course_id = null;
    $lesson_id = null;

    // Access denied
    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'denied' ) && ! current_user_can('manage_options' ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--error">';
      $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
      $html .= '<span class="humble-lms-message-content">' . sprintf( __('You need to be <a href="%s">logged in</a> and have the required permissions in order to access the requested content.', 'humble-lms' ), wp_login_url() ) . '</span>';
      $html .= '</div>';
    }

    // Message user completed course
    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' ) {
      $course_id = $post->ID;
    } elseif( isset( $_POST['course_id'] ) ) {
      $course_id = (int)$_POST['course_id'];
    }

    if( isset( $course_id ) && $this->user->completed_course( $course_id ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Congratulations', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . sprintf( __('You successfully completed the course "%s".', 'humble-lms'), '<a href="#">' . get_the_title( $course_id ) ) . '</a></span> 
      </div>';
    }

    // Single lesson
    if( is_single() && get_post_type( $post->ID ) === 'humble_lms_lesson' ) {
      $level = strip_tags( get_the_term_list( $post->ID, 'humble_lms_course_level', '', ', ') );
      $level = $level ? '<span class="humble-lms-lesson-level"><strong>' . __('Level', 'humble-lms') . ':</strong> <span>' . $level . '</span></span>': '';
      $html .= $level;
    }

    // Content
    if ( is_single() && ( get_post_type( $post->ID ) === 'humble_lms_course' || get_post_type( $post->ID ) === 'humble_lms_lesson' ) ) {
      $html .= $content;
    }

    // Completed lesson/course/track
    if( isset( $_POST['completed'] ) ) {
      $completed = json_decode( $_POST['completed'] );
      if( ! empty( $completed[0] ) ) {
        $html .= '<div class="humble-lms-award-message"><div>';

        foreach( $completed as $key => $ids ) {
          foreach( $ids as $id ) {
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
                    $html .= '<img class="humble-lms-award-image humble-lms-bounce-in" src="' . get_the_post_thumbnail_url( $id ) . '" alt="" />';
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
   * Template redirect
   *
   * This function checks user access levels and redirects accordingly. 
   * @since    0.0.1
   */
  public function humble_lms_template_redirect() {
    global $post;

    if ( is_single() && $post->post_type == 'humble_lms_lesson' && ! $this->access_handler->can_access_lesson( $post->ID ) ) {
      if( ! empty( $_POST['course_id'] ) ) {
        wp_redirect( esc_url( get_permalink( (int)$_POST['course_id'] ) . '?access=denied' ) );
      } else {
        wp_redirect( esc_url( get_post_type_archive_link( 'humble_lms_course') ) . '?access=denied' );
      }

      die;
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
   * Redirect to custom login page.
   * 
   * @since   0.0.1
   */
  function redirect_login_page() {
    $login_page  = home_url( '/login/' );
    $registration_page  = home_url( '/register/' );
    $page_viewed = basename( $_SERVER['REQUEST_URI'] );
  
    if( $page_viewed === 'wp-login.php' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $login_page );
      exit;
    }

    if( $page_viewed === 'wp-login.php?action=register' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $registration_page );
      exit;
    }
  }

  /**
   * Redirect on failed login.
   * 
   * @since   0.0.1
   */
  function custom_login_failed() {
    $login_page = home_url('/login/');
    wp_redirect( $login_page . '?login=failed' );
    exit;
  }

  /**
   * Redirect when custom login form fields are empty.
   * 
   * @since   0.0.1
   */
  function verify_user_pass($user, $username, $password) {
    $login_page = home_url('/login/');

    if( $username === '' || $password === '' ) {
      wp_redirect( $login_page . '?login=empty' );
      exit;
    }
  }

  /**
   * Redirect when custom login form fields are empty.
   * 
   * @since   0.0.1
   */
  function logout_redirect() {
    $login_page  = home_url('/login/');
    wp_redirect($login_page . '?login=false');
    exit;
  }

  /**
   * Validate and register new user with custom registration form.
   * 
   * @since   0.0.1
   */
  public function humble_lms_register_user() {
    if( isset( $_POST['humble-lms-user-login'] ) && wp_verify_nonce( $_POST['humble-lms-register-nonce'], 'humble-lms-register-nonce' ) ) {
      $user_login = $_POST['humble-lms-user-login'];	
      $user_email	= $_POST['humble-lms-user-email'];
      $user_first = $_POST['humble-lms-user-first'];
      $user_last = $_POST['humble-lms-user-last'];
      $user_pass = $_POST['humble-lms-user-pass'];
      $user_pass_confirm = isset( $_POST['humble-lms-user-pass-confirm'] ) ? sanitize_text_field( $_POST['humble-lms-user-pass-confirm'] ) : '';
      
      if( username_exists( $user_login ) ) {
        $this->humble_lms_errors()->add('username_unavailable', __('Username already taken', 'humble-lms'));
      }

      if( ! validate_username( $user_login ) ) {
        $this->humble_lms_errors()->add('username_invalid', __('Invalid username', 'humble-lms'));
      } else if( $user_login === '' ) {
        $this->humble_lms_errors()->add('username_empty', __('Please enter a username', 'humble-lms'));
      }

      if( $user_first === '' ) {
        $this->humble_lms_errors()->add('first_name_empty', __('Please enter a first name', 'humble-lms'));
      }

      if( $user_last === '' ) {
        $this->humble_lms_errors()->add('last_name_empty', __('Please enter a last name', 'humble-lms'));
      }
  
      if( ! is_email( $user_email ) || ! filter_var( $_POST['humble-lms-user-email'], FILTER_VALIDATE_EMAIL ) ) {
        $this->humble_lms_errors()->add('email_invalid', __('Please enter a valid email address', 'humble-lms'));
      }
  
      if( email_exists( $user_email ) ) {
        $this->humble_lms_errors()->add('email_used', __('Email address already registered', 'humble-lms'));
      }

      if( $user_pass === '') {
        $this->humble_lms_errors()->add('password_empty', __('Please enter a password', 'humble-lms'));
      }

      if( $user_pass !== $user_pass_confirm ) {
        $this->humble_lms_errors()->add('password_mismatch', __('Passwords do not match', 'humble-lms'));
      }
      
      $errors = $this->humble_lms_errors()->get_error_messages();
      
      // No errors => create user
      if( empty( $errors ) ) {
        
        $new_user_id = wp_insert_user( array(
            'user_login' => $user_login,
            'user_pass'	=> $user_pass,
            'user_email' => $user_email,
            'first_name' => $user_first,
            'last_name'	=> $user_last,
            'user_registered'	=> date('Y-m-d H:i:s'),
            'role' => 'subscriber'
        ) );

        if( $new_user_id ) {
          // Notify admin about new user
          wp_new_user_notification( $new_user_id );
          
          // Log in new user
          wp_setcookie( $user_login, $user_pass, true );
          wp_set_current_user( $new_user_id, $user_login );	
          do_action( 'wp_login', $user_login );
          
          // Redirect user
          wp_redirect( home_url() );
          exit;
        }
      }
    }
  }

  /**
   * Validate and register new user with custom registration form.
   * 
   * @since   0.0.1
   */
  static function humble_lms_errors() {
    static $wp_error;
    return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
  }

}
