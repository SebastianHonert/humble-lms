<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://minimalwordpress.com/humble-lms
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS_Admin {

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
   * @param      string    $humble_lms       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $humble_lms, $version ) {

    $this->humble_lms = $humble_lms;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the admin area.
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

    wp_enqueue_style( 'multi-select', plugin_dir_url( __FILE__ ) . 'js/lou-multi-select/css/multi-select.css', array(), '0.9.12', 'all' );
    wp_enqueue_style( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'css/humble-lms-admin.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the admin area.
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

    wp_enqueue_script( 'quicksearch', plugin_dir_url( __FILE__ ) . 'js/jquery.quicksearch.js', array( 'jquery' ), '1.0.0', true );
    wp_enqueue_script( 'sortable', plugin_dir_url( __FILE__ ) . 'js/sortable.min.js', array( 'jquery' ), '1.10.1', true );
    wp_enqueue_script( 'multi-select', plugin_dir_url( __FILE__ ) . 'js/lou-multi-select/js/jquery.multi-select.js', array( 'jquery' ), '0.9.12', true );
    wp_enqueue_script( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'js/humble-lms-admin.js', array( 'jquery' ), $this->version, true );

  }

  /**
   * Block users / students from dashboard access and redirect
   * to front page instead.
   *
   * @since    0.0.1
   */
  public function block_dashboard_access( $url ) {
    if( wp_doing_ajax() )
      return;

    if( is_user_logged_in() && is_admin() && ! current_user_can('manage_options') ) {
      wp_safe_redirect( home_url() );
      die;
    }
  }

  /**
   * Register sidebars.
   * to front page instead.
   *
   * @since    0.0.1
   */
  public function register_sidebars( $url ) {
    register_sidebar( array(
      'name' => __( 'Humble LMS Sidebar', 'humble-lms' ),
      'id' => 'humble-lms-sidebar',
      'description' => __( 'Widgets in this area will be shown on Humble LMS single lesson pages.', 'humble-lms' ),
      'before_widget' => '<div class="humble-lms-widget">',
      'after_widget'  => '</div>',
      'before_title'  => '<h2>',
      'after_title'   => '</h2>',
    ) );
  }

  /**
   * Add Humble LMS user roles.
   *
   * @since    0.0.1
   */
  function add_user_roles() {  
    add_role(
      'humble_lms_student',
      'Humble LMS Student',
      array(
        'read' => true,
      )
    );
  }

  /**
   * Add user meta field for course instructors
   *
   * @since    0.0.1
   */
  public function add_user_profile_fields( $user ) {
    $checked = ( isset( $user->humble_lms_is_instructor ) && $user->humble_lms_is_instructor ) ? 'checked="checked"' : '';
    echo '<h3>Humble LMS</h3>';
    echo '<label for="humble_lms_is_instructor">';
    echo '<input name="humble_lms_is_instructor" type="checkbox" id="humble_lms_is_instructor" value="1" ' . $checked . '>';
    echo __('This user is a course instructor.', 'humble-lms');
    echo '</label>';
  }

  /**
   * Update user profile
   *
   * @since    0.0.1
   */
  public function update_user_profile( $user_id ) {
    if( current_user_can('edit_user', $user_id) ) {
      update_user_meta( $user_id, 'humble_lms_is_instructor', isset( $_POST['humble_lms_is_instructor'] ) );
    }
  }

  /**
   * Remove trashed courses/lessons from track/course meta
   * 
   * @since   0.0.1
   */
  public function remove_meta( $post_id ) {
    $allowed_post_types = [
      'humble_lms_course',
      'humble_lms_lesson',
    ];

    $post_type = get_post_type( $post_id );

    if ( ! in_array( $post_type, $allowed_post_types ) )
       return;

    switch( $post_type )
    {
      case 'humble_lms_course':

        $tracks = get_posts( array(
          'post_type' => 'humble_lms_track',
          'posts_per_page' => -1,
        ) );

        foreach( $tracks as $track ) {
          $track_courses = get_post_meta($track->ID, 'humble_lms_track_courses', true);
          $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

          if( ( $key = array_search( $post_id, $track_courses ) ) !== false ) {
            unset($track_courses[$key]);
          }

          $updated_track_courses = ['[' . implode(',', $track_courses ) . ']'];
          
          update_post_meta( $track->ID, 'humble_lms_track_courses', $updated_track_courses );
        }

      break;

      case 'humble_lms_lesson':

        $courses = get_posts( array(
          'post_type' => 'humble_lms_course',
          'post_status' => 'any',
          'posts_per_page' => -1,
        ) );

        foreach( $courses as $course ) {
          $course_lessons = get_post_meta($course->ID, 'humble_lms_course_lessons', true);
          $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

          if( ( $key = array_search( $post_id, $course_lessons ) ) !== false ) {
            unset($course_lessons[$key]);
          }

          $updated_course_lessons = ['[' . implode(',', $course_lessons ) . ']'];
          update_post_meta( $course->ID, 'humble_lms_course_lessons', $updated_course_lessons );
        }

      break;
    }

  }

  /**
   * Validate and register new user with custom registration form.
   * 
   * @since   0.0.1
   */
  public function humble_lms_register_user() {
    global $wp;

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

      if( strlen( $user_pass ) < 8 ) {
        $this->humble_lms_errors()->add('password_too_short', __('Password should be at least 8 characters.', 'humble-lms'));
      }

      if( ! preg_match('#[0-9]+#', $user_pass ) ) {
        $this->humble_lms_errors()->add('password_no_number', __('Password should include at least 1 number.', 'humble-lms'));
      }

      if( ! preg_match('#[a-zA-Z]+#', $user_pass) ) {
        $this->humble_lms_errors()->add('password_no_letter', __('Password should include at least 1 letter.', 'humble-lms'));
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
            'role' => 'humble_lms_student'
        ) );

        if( $new_user_id ) {
          // Notify admin about new user
          wp_new_user_notification( $new_user_id );
          
          // Log in new user
          wp_setcookie( $user_login, $user_pass, true );
          wp_set_current_user( $new_user_id, $user_login );	
          do_action( 'wp_login', $user_login );
          
          // Redirect user
          wp_redirect( add_query_arg( 'humble-lms-welcome', '1', home_url( $wp->request ) ) );
          exit;
        }
      }
    }
  }

  /**
   * Validate custom lost password form input.
   * 
   * @since   0.0.1
   */
  public function validate_lost_password_form() {
    if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
      $errors = retrieve_password();

      if( is_wp_error( $errors ) ) {
        $redirect_url = home_url( 'lost-password' );
        $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
      } else {
        $redirect_url = home_url( 'login' );
        $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
      }

      wp_redirect( $redirect_url );
      exit;
    }
  }

  /**
   * Handle form errors.
   * 
   * @since   0.0.1
   */
  public static function humble_lms_errors() {
    static $wp_error;
    return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
  }

  /**
   * TODO: Check if login page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public static function humble_lms_login_page_exists() {
    $custom_page_login = get_page_by_title('Humble LMS Login', OBJECT, 'page');
    return $custom_page_login && has_shortcode( $custom_page_login->post_content, 'humble_lms_login_form' ) && get_post_status( $custom_page_login->ID ) === 'publish';
  }

  /**
   * TODO: Check if registration page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public static function humble_lms_registration_page_exists() {
    $custom_page_registration = get_page_by_title('Humble LMS Registration', OBJECT, 'page');
    return $custom_page_registration && has_shortcode( $custom_page_registration->post_content, 'humble_lms_registration_form' ) && get_post_status( $custom_page_registration->ID ) === 'publish';
  }

  /**
   * TODO: Check if registration page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public static function humble_lms_lost_password_page_exists() {
    $custom_page_lost_password = get_page_by_title('Humble LMS Lost Password', OBJECT, 'page');
    return $custom_page_lost_password && has_shortcode( $custom_page_lost_password->post_content, 'humble_lms_lost_password_form' ) && get_post_status( $custom_page_lost_password->ID ) === 'publish';
  }

  /**
   * Redirect to custom login page.
   * 
   * @since   0.0.1
   */
  function redirect_login_registration_lost_password() {
    $login_page  = home_url( '/login/' );
    $registration_page = home_url( '/registration/' );
    $lost_password_page = home_url( '/lost-password/' );

    $page_viewed = basename( $_SERVER['REQUEST_URI'] );
  
    if( Humble_LMS_Admin::humble_lms_login_page_exists() && $page_viewed === 'wp-login.php' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $login_page );
      exit;
    }

    elseif( Humble_LMS_Admin::humble_lms_registration_page_exists() && $page_viewed === 'wp-login.php?action=register' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $registration_page );
      exit;
    }

    elseif( Humble_LMS_Admin::humble_lms_lost_password_page_exists() && $page_viewed === 'wp-login.php?action=lostpassword' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
      wp_redirect( $lost_password_page );
      exit;
    }

    elseif( Humble_LMS_Admin::humble_lms_lost_password_page_exists() && $page_viewed === 'wp-login.php?action=lostpassword' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $lost_password_page );
      exit;
    }
  }

  /**
   * Redirect on failed login.
   * 
   * TODO: Get login page from options
   * TODO: Check if login page exist and contains login shortcode
   * 
   * @since   0.0.1
   */
  function custom_login_failed() {
    if( ! Humble_LMS_Admin::humble_lms_login_page_exists() )
      return;

    $login_page = home_url('/login/');
    wp_redirect( $login_page . '?login=failed' );
    exit;
  }

  /**
   * Redirect when custom login form fields are empty.
   * 
   * TODO: Get login page from options
   * TODO: Check if login page exist and contains login shortcode
   * 
   * @since   0.0.1
   */
  function verify_user_pass($user, $username, $password) {
    if( ! Humble_LMS_Admin::humble_lms_login_page_exists() )
      return;

    $login_page = home_url('/login/');

    if( $username === '' || $password === '' ) {
      wp_redirect( $login_page . '?login=empty' );
      exit;
    }
  }

  /**
   * Redirect when custom login form fields are empty.
   * 
   * TODO: Get login page from options
   * TODO: Check if login page exist and contains login shortcode
   * 
   * @since   0.0.1
   */
  function logout_redirect() {
    if( ! Humble_LMS_Admin::humble_lms_login_page_exists() )
      return;

    $login_page = home_url('/login/');
    wp_redirect($login_page . '?login=false');
    exit;
  }

}
