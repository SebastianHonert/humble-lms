<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sebastianhonert.com
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
    $this->options = get_option('humble_lms_options');

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

    wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.12.1', 'all' );
    wp_enqueue_style( 'multi-select', plugin_dir_url( __FILE__ ) . 'js/lou-multi-select/css/multi-select.css', array(), '0.9.12', 'all' );
    wp_enqueue_style( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'css/humble-lms-admin.css', array(), $this->version, 'all' );
    wp_enqueue_style( 'wp-color-picker' );

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

    $translator = new Humble_LMS_Translator;

    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'quicksearch', plugin_dir_url( __FILE__ ) . 'js/jquery.quicksearch.js', array( 'jquery' ), '1.0.0', true );
    wp_enqueue_script( 'sortable', plugin_dir_url( __FILE__ ) . 'js/sortable.min.js', array( 'jquery' ), '1.10.1', true );
    wp_enqueue_script( 'multi-select', plugin_dir_url( __FILE__ ) . 'js/lou-multi-select/js/jquery.multi-select.js', array( 'jquery' ), '0.9.12', true );
    wp_enqueue_script( $this->humble_lms, plugin_dir_url( __FILE__ ) . 'js/humble-lms-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );

    wp_localize_script( $this->humble_lms, 'humble_lms', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'sendTestEmailSuccess' => __('Email sent successfully.', 'humble-lms'),
      'sendTestEmailError' => __('Sorry, something went wrong sending the test email.', 'humble-lms'),
      'sendTestEmailValidation' => __('Please provide a message and a valid email address.', 'humble-lms'),
      'confirmResetUserProgress' => __('Are you sure? This will irrevocably reset this user\'s learning progress, including awards and certificates.', 'humble-lms'),
      'uncheckUserIsInstructor' => __('Please note: This user is currently marked as an instructor. Removing instructor status and saving this user\'s settings will also result in removing this user as an instructor from all courses and lessons.', 'humble-lms'),
      'questionMissing' => __('Please enter a question first.', 'humble-lms'),
      'correctAnswerMissing' => __('Please mark at least one answer as correct.', 'humble-lms'),
      'oneSectionMessage' => __('This section cannot be removed. Courses consist of at least one section.', 'humble-lms'),
      'nonce' => wp_create_nonce( 'humble_lms' ),
      'current_language' => $translator->current_language(),
    ) );
  }

  /**
   * Register custom post types
   *
   * @since    0.0.1
   */
  public function register_custom_post_types() {
    $options = get_option('humble_lms_options');

    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-track.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-course.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-lesson.php';

    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-award.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-certificate.php';

    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-activity.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-email.php';
    
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-quiz.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-question.php';

    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-membership.php';

    if( isset( $options['use_coupons'] ) && 1 === $options['use_coupons'] ) {
      require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-coupon.php';
    }
    
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-txn.php';
  }

  /**
   * Register custom taxonomies
   *
   * @since    0.0.1
   */
  public function register_custom_taxonomies() {
    require_once plugin_dir_path( __FILE__ ) . 'custom-taxonomies/humble-lms-course-level.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-taxonomies/humble-lms-provider.php';
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

    if( is_user_logged_in() && is_admin() && ! current_user_can('edit_others_pages') ) {
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
    $user_is_instructor = ( isset( $user->humble_lms_is_instructor ) && $user->humble_lms_is_instructor );
    $checked = $user_is_instructor ? 'checked="checked"' : '';
    $was_instructor = $user_is_instructor ? 1 : 0;
    echo '<hr>';
    echo '<h3>Humble LMS</h3>';
    echo '<h4>' . __('Course Instructor', 'humble-lms') . '</h4>';
    echo '<p><input name="humble_lms_is_instructor" type="checkbox" id="humble_lms_is_instructor" value="1" ' . $checked . '>';
    echo '<input type="hidden" name="humble_lms_was_instructor" value="' . $was_instructor . '" />';
    echo __('This user is an instructor.', 'humble-lms') . '</p>';

    $content_manager = new Humble_LMS_Content_Manager;
    $options = new Humble_LMS_Admin_Options_Manager;
    $countries = $options->countries;
    $memberships = $content_manager::get_memberships();

    $user_country = get_user_meta( $user->ID, 'humble_lms_country', true);
    echo '<h4>' . __('Country', 'humble-lms') . '</h4>';
    echo '<select name="humble_lms_country" id="humble_lms_country">';
      echo '<option value="">' . __('Please select a country', 'humble-lms') . '</option>';
      foreach( $countries as $key => $country ) {
        $selected = $country === $user_country ? 'selected' : '';
        echo '<option value="' . $country . '" ' . $selected . '>' . $country . '</option>';
      }
    echo '</select>';

    $user_membership = get_user_meta( $user->ID, 'humble_lms_membership', true);

    if( ! in_array( $user_membership, $memberships ) ) {
      $user_membership = 'free';
    }

    echo '<h4>' . __('Membership', 'humble-lms') . '</h4>';
    echo '<select name="humble_lms_membership" id="humble_lms_membership">';
      echo '<option value="" disabled>' . __('Please select a membership type', 'humble-lms') . '</option>';

      $selected = $user_membership === 'free' ? 'selected="selected"' : '';
      echo '<option value="free" ' . $selected . '>' . __('Free', 'humble-lms') . '</option>';

      foreach( $memberships as $key => $membership ) {
        $selected = $membership === $user_membership ? 'selected' : '';
        echo '<option value="' . $membership . '" ' . $selected . '>' . ucfirst( $membership ) . '</option>';
      }
    echo '</select>';

    $checked = get_user_meta( $user->ID, 'humble_lms_email_agreement', true ) === '1' ? 'checked' : '';
    echo '<h4>' . __('Email agreement', 'humble-lms') . '</h4>';
    echo '<input type="checkbox" name="humble_lms_email_agreement" id="humble_lms_email_agreement" value="1" ' . $checked . '> ' . __('Yes, I wish to receive emails from this website which are essential for participating in the online courses.', 'humble-lms');
  
    // Purchased content
    $tracks = $content_manager->get_tracks();
    $courses = $content_manager->get_courses();
    $purchased = get_user_meta( $user->ID, 'humble_lms_purchased_content', false );

    $content = array();

    foreach( $tracks as $track ) {
      array_push( $content, array(
        'post_id' => $track->ID,
        'post_title' => $track->post_title . ' (' . __('Track', 'humble-lms') . ', ID ' . $track->ID . ')',
      ));
    }

    foreach( $courses as $course ) {
      array_push( $content, array(
        'post_id' => $course->ID,
        'post_title' => $course->post_title . ' (' . __('Course', 'humble-lms') . ', ID ' . $course->ID . ')',
      ));
    }

    echo '<h4>' . __('Purchased content', 'humble-lms') . '</h4>';
    echo '<select name="humble_lms_purchased_content[]" id="humble_lms_purchased_content" class="humble-lms-user-purchased-courses humble-lms-searchable" multiple="multiple">';
      foreach( $content as $item ) {
        $selected = in_array( $item['post_id'], $purchased[0] ) ? 'selected="selected"' : '';
        echo '<option name="humble_lms_purchased_content[]"  value="' . $item['post_id'] . '" ' . $selected . '>' . $item['post_title'] . '</option>';
      }
    echo '</select>';
  }

  /**
   * Update user profile
   *
   * @since    0.0.1
   */
  public function update_user_profile( $user_id ) {
    $user = new Humble_LMS_Public_User;
    $is_instructor = isset( $_POST['humble_lms_is_instructor'] );
    $was_instructor = isset( $_POST['humble_lms_was_instructor'] );
    $email_agreement = isset( $_POST['humble_lms_email_agreement'] );
    $purchased_content = isset( $_POST['humble_lms_purchased_content'] ) ? $_POST['humble_lms_purchased_content'] : [];
    
    if( ! $is_instructor && $was_instructor ) {
      $user->remove_instructor_status( $user_id );
    }

    if( current_user_can('edit_user', $user_id) ) {
      update_user_meta( $user_id, 'humble_lms_is_instructor', $is_instructor );
      update_user_meta( $user_id, 'humble_lms_country', sanitize_text_field( $_POST['humble_lms_country'] ) );
      update_user_meta( $user_id, 'humble_lms_membership', sanitize_text_field( $_POST['humble_lms_membership'] ) );
      update_user_meta( $user_id, 'humble_lms_email_agreement', $email_agreement );
      update_user_meta( $user_id, 'humble_lms_purchased_content', $purchased_content );
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
          'lang' => '',
        ) );

        foreach( $tracks as $track ) {
          $track_courses = Humble_LMS_Content_Manager::get_track_courses( $track->ID );

          if( ( $key = array_search( $post_id, $track_courses ) ) !== false ) {
            unset( $track_courses[$key] );
          }

          update_post_meta( $track->ID, 'humble_lms_track_courses', $track_courses );
        }

      break;

      case 'humble_lms_lesson':

        $courses = get_posts( array(
          'post_type' => 'humble_lms_course',
          'post_status' => 'any',
          'posts_per_page' => -1,
          'lang' => '',
        ) );

        foreach( $courses as $course ) {
          $course_lessons = Humble_LMS_Content_Manager::get_course_lessons( $course->ID );

          if( ( $key = array_search( $post_id, $course_lessons ) ) !== false ) {
            unset( $course_lessons[$key] );
          }

          update_post_meta( $course->ID, 'humble_lms_course_lessons', $course_lessons );
        }

      break;
    }

  }

  /**
   * Get login page URL.
   * 
   * @return  String
   * @since   0.0.1
   */
  public function login_page() {
    if( ! $this->humble_lms_login_page_exists() ) {
      return wp_login_url();
    }

    $translator = new Humble_LMS_Translator;
    $options = get_option('humble_lms_options');
    $custom_login_page_id = $options['custom_pages']['login'];

    return esc_url( get_permalink( $translator->get_translated_post_id( $custom_login_page_id ) ) );
  }

  /**
   * Check if login page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public function humble_lms_login_page_exists() {
    $options = get_option('humble_lms_options');

    if( ! isset( $options['custom_pages']['login'] ) ) return false;

    $custom_page_login = get_post( (int)$options['custom_pages']['login'] );

    return get_post_status( $custom_page_login ) === 'publish' && has_shortcode( $custom_page_login->post_content, 'humble_lms_login_form' );
  }

  /**
   * Get registration page URL.
   * 
   * @return  String
   * @since   0.0.1
   */
  public function registration_page() {
    if( ! $this->humble_lms_registration_page_exists() ) {
      return wp_registration_url();
    }

    $translator = new Humble_LMS_Translator;
    $options = get_option('humble_lms_options');
    $custom_registration_page_id = $options['custom_pages']['registration'];

    return esc_url( get_permalink( $translator->get_translated_post_id( $custom_registration_page_id ) ) );
  }

  /**
   * Check if registration page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public function humble_lms_registration_page_exists() {
    $options = get_option('humble_lms_options');
    if( ! isset( $options['custom_pages']['registration'] ) ) return false;

    $custom_page_registration = get_post( $options['custom_pages']['registration'] );
    return get_post_status( $custom_page_registration ) === 'publish' && has_shortcode( $custom_page_registration->post_content, 'humble_lms_registration_form' );
  }

  /**
   * Get lost password page URL.
   * 
   * @return  String
   * @since   0.0.1
   */
  public function lost_password_page() {
    if( ! $this->humble_lms_lost_password_page_exists() ) {
      return wp_lostpassword_url();
    }

    $translator = new Humble_LMS_Translator;
    $options = get_option('humble_lms_options');
    $custom_lost_password_page_id = $options['custom_pages']['lost_password'];

    return esc_url( get_permalink( $translator->get_translated_post_id( $custom_lost_password_page_id ) ) );
  }

  /**
   * Check if lost password page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public function humble_lms_lost_password_page_exists() {
    $options = get_option('humble_lms_options');
    if( ! isset( $options['custom_pages']['lost_password'] ) ) return false;

    $custom_page_lost_password = get_post( $options['custom_pages']['lost_password'] );
    return get_post_status( $custom_page_lost_password ) === 'publish' && has_shortcode( $custom_page_lost_password->post_content, 'humble_lms_lost_password_form' );
  }

  /**
   * Get reset password page URL.
   * 
   * @return  String
   * @since   0.0.1
   */
  public function reset_password_page() {
    if( ! $this->humble_lms_reset_password_page_exists() ) {
      return site_url('wp-login.php?action=rp');
    }

    $translator = new Humble_LMS_Translator;
    $options = get_option('humble_lms_options');
    $custom_reset_password_page_id = $options['custom_pages']['reset_password'];

    return esc_url( get_permalink( $translator->get_translated_post_id( $custom_reset_password_page_id ) ) );
  }

  /**
   * Check if reset password page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public function humble_lms_reset_password_page_exists() {
    $options = get_option('humble_lms_options');
    if( ! isset( $options['custom_pages']['reset_password'] ) ) return false;

    $custom_page_reset_password = get_post( $options['custom_pages']['reset_password'] );
    return get_post_status( $custom_page_reset_password ) === 'publish' && has_shortcode( $custom_page_reset_password->post_content, 'humble_lms_reset_password_form' );
  }

  /**
   * Check if user profile page exists and contains shortcode
   * 
   * @since   0.0.1
   */
  public function humble_lms_user_profile_page_exists() {
    $options = get_option('humble_lms_options');
    if( ! isset( $options['custom_pages']['user_profile'] ) ) return false;

    $custom_page_user_profile = get_post( $options['custom_pages']['user_profile'] );
    return get_post_status( $custom_page_user_profile ) === 'publish' && has_shortcode( $custom_page_user_profile->post_content, 'humble_lms_user_profile' );
  }

  /**
   * Verify user name and password on login and redirect accordingly.
   * 
   * @since   0.0.1
   */
  public function verify_user_pass( $user, $username, $password ) {
    if( ! $this->humble_lms_login_page_exists() )
      return;

    if( $username === '' || $password === '' ) {
      wp_redirect( $this->login_page());
      exit();
    }

    return $user;
  }

  /**
   * Redirect after login based on selected language.
   * 
   * @since   0.0.1
   */
  public function humble_lms_login_redirect( $redirect_to, $request, $user) {
    $translator = new Humble_LMS_Translator;
    $redirect_to = esc_url( get_permalink( $translator->get_translated_post_id( url_to_postid( home_url() ) ) ) );

    return $redirect_to;
  }

  /**
   * Validate and register new user with custom registration form.
   * 
   * @since   0.0.1
   */
  public function humble_lms_register_user() {
    global $wp;

    if( ! get_option( 'users_can_register' ) )
      return;

    if( ! isset( $_POST['humble-lms-form'] ) || $_POST['humble-lms-form'] !== 'humble-lms-registration' )
      return;

    // Honeypot
    if( isset( $_POST['humble-lms-honeypot'] ) && ! empty( $_POST['humble-lms-honeypot'] ) )
      return;

    $options_manager = new Humble_LMS_Admin_Options_Manager;
    $countries = $options_manager->countries;

    if( isset( $_POST['humble-lms-user-login'] ) && wp_verify_nonce( $_POST['humble-lms-register-nonce'], 'humble-lms-register-nonce' ) ) {
      $user_login = $_POST['humble-lms-user-login'];	
      $user_email	= $_POST['humble-lms-user-email'];
      $user_email_confirm	= isset( $_POST['humble-lms-user-email-confirm'] ) ? $_POST['humble-lms-user-email-confirm'] : '';
      $user_first = $_POST['humble-lms-user-first'];
      $user_last = $_POST['humble-lms-user-last'];
      $user_title = sanitize_text_field( $_POST['humble-lms-user-title'] );
      $user_country = sanitize_text_field( $_POST['humble-lms-user-country'] );
      $user_postcode = sanitize_text_field( $_POST['humble-lms-user-postcode'] );
      $user_city = sanitize_text_field( $_POST['humble-lms-user-city'] );
      $user_address = sanitize_text_field( $_POST['humble-lms-user-address'] );
      $user_company = sanitize_text_field( $_POST['humble-lms-user-company'] );
      $user_vat_id = sanitize_text_field( $_POST['humble-lms-user-vat-id'] );
      $user_pass = $_POST['humble-lms-user-pass'];
      $user_pass_confirm = isset( $_POST['humble-lms-user-pass-confirm'] ) ? sanitize_text_field( $_POST['humble-lms-user-pass-confirm'] ) : '';
      $email_agreement = isset( $_POST['humble-lms-email-agreement'] ) ? 1 : 0;
      $terms_of_service = isset( $_POST['humble-lms-terms-of-service'] ) ? 1 : 0;
      
      if( username_exists( $user_login ) ) {
        $this->humble_lms_errors()->add('username_unavailable', __('Username already taken.', 'humble-lms'));
      }

      if( ! validate_username( $user_login ) ) {
        $this->humble_lms_errors()->add('username_invalid', __('Invalid username. Please user letters and numbers only.', 'humble-lms'));
      } else if( $user_login === '' ) {
        $this->humble_lms_errors()->add('username_empty', __('Please enter a username.', 'humble-lms'));
      }

      if( $user_first === '' ) {
        $this->humble_lms_errors()->add('first_name_empty', __('Please enter your first name.', 'humble-lms'));
      }

      if( $user_last === '' ) {
        $this->humble_lms_errors()->add('last_name_empty', __('Please enter your last name', 'humble-lms'));
      }

      if( $user_country !== '' && ( ! in_array( $user_country, $countries ) ) ) {
        $this->humble_lms_errors()->add('country_empty', __('Please select your country.', 'humble-lms'));
      }
  
      if( ! is_email( $user_email ) || ! filter_var( $_POST['humble-lms-user-email'], FILTER_VALIDATE_EMAIL ) ) {
        $this->humble_lms_errors()->add('email_invalid', __('Please enter a valid email address.', 'humble-lms'));
      }
  
      if( email_exists( $user_email ) ) {
        $this->humble_lms_errors()->add('email_used', __('Email address already registered.', 'humble-lms'));
      }

      if( ! isset( $user_email_confirm ) || $user_email !== $user_email_confirm ) {
        $this->humble_lms_errors()->add('emails_do_not_match', __('The email addresses you entered do not match.', 'humble-lms'));
      }

      if( $user_pass === '') {
        $this->humble_lms_errors()->add('password_empty', __('Please enter a password.', 'humble-lms'));
      }

      if( strlen( $user_pass ) < 12 ) {
        $this->humble_lms_errors()->add('password_too_short', __('Password should be at least 12 characters.', 'humble-lms'));
      }

      if( ! preg_match('#[0-9]+#', $user_pass ) ) {
        $this->humble_lms_errors()->add('password_no_number', __('Password should include at least 1 number.', 'humble-lms'));
      }

      if( ! preg_match('#[a-zA-Z]+#', $user_pass) ) {
        $this->humble_lms_errors()->add('password_no_letter', __('Password should include at least 1 letter.', 'humble-lms'));
      } 

      if( $user_pass !== $user_pass_confirm ) {
        $this->humble_lms_errors()->add('password_mismatch', __('Passwords do not match.', 'humble-lms'));
      }

      if( isset( $options_manager->options['email_agreement'] ) && $options_manager->options['email_agreement'] === 1 ) {
        if( $email_agreement === 0 ) {
          $this->humble_lms_errors()->add('email_agreement', __('Please agree to receiving essential emails from our website.', 'humble-lms'));
        }
      }

      if( isset( $options_manager->options['terms_of_service'] ) && $options_manager->options['terms_of_service'] === 1 ) {
        if( $terms_of_service === 0 ) {
          $this->humble_lms_errors()->add('terms_of_service', __('Please agree to our terms of service (TOS) and privacy policy.', 'humble-lms'));
        }
      }

      // reCAPTCHA
      if( $options_manager->has_recaptcha() ) {
        if( isset( $_POST['g-recaptcha-response'] ) ) {
          $captcha = $_POST['g-recaptcha-response'];
        }

        if( ! $captcha ) {
          $this->humble_lms_errors()->add('recaptcha', __('Please check the reCAPTCHA form field.', 'humble-lms'));
        } else {
          $secret_key = $options_manager->options['recaptcha_secret_key'];
          $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode( $secret_key ) .  '&response=' . urlencode( $captcha );
          $response = file_get_contents( $url );
          $responseKeys = json_decode( $response, true );
  
          if( ! $responseKeys['success'] ) {
            $this->humble_lms_errors()->add('recaptca', __('Sorry, your registration did not pass reCAPTCHA verification.', 'humble-lms'));
          }
        }        
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
          // Add country to user meta
          add_user_meta( $new_user_id, 'humble_lms_title', $user_title );
          add_user_meta( $new_user_id, 'humble_lms_country', $user_country );
          add_user_meta( $new_user_id, 'humble_lms_membership', 'free' );
          add_user_meta( $new_user_id, 'humble_lms_email_agreement', $email_agreement );
          add_user_meta( $new_user_id, 'humble_lms_terms_of_service', $terms_of_service );
          add_user_meta( $new_user_id, 'humble_lms_postcode', $user_postcode );
          add_user_meta( $new_user_id, 'humble_lms_city', $user_city );
          add_user_meta( $new_user_id, 'humble_lms_address', $user_address );
          add_user_meta( $new_user_id, 'humble_lms_company', $user_company );
          add_user_meta( $new_user_id, 'humble_lms_vat_id', $user_vat_id );

          // Notify admin and user (=> 'both')
          wp_new_user_notification( $new_user_id, null, 'both' );
          
          // Log in new user
          wp_setcookie( $user_login, $user_pass, true );
          wp_set_current_user( $new_user_id, $user_login );	
          do_action( 'wp_login', $user_login );
          
          // Redirect user
          $translator = new Humble_LMS_Translator;
          wp_redirect( add_query_arg( 'humble-lms-welcome', '1', $translator->home_url() ) );
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
  public function humble_lms_update_user() {
    if( ! is_user_logged_in() || ! isset( $_POST['humble-lms-form'] ) || $_POST['humble-lms-form'] !== 'humble-lms-update-user' )
      return;

    global $wp;

    $user_id = get_current_user_ID();
    $userdata = get_userdata( $user_id );
    $options_manager = new Humble_LMS_Admin_Options_Manager;
    $countries = $options_manager->countries;

    if( wp_verify_nonce( $_POST['humble-lms-update-user-nonce'], 'humble-lms-update-user-nonce' ) ) {
      $user_email	= $_POST['humble-lms-user-email'];
      $user_email_confirm	= $_POST['humble-lms-user-email-confirm'];
      $user_title	= sanitize_text_field( $_POST['humble-lms-user-title'] );
      $user_country = sanitize_text_field( $_POST['humble-lms-user-country'] );
      $user_pass = isset( $_POST['humble-lms-user-pass'] ) ? $_POST['humble-lms-user-pass'] : '';
      $user_pass_confirm = isset( $_POST['humble-lms-user-pass-confirm'] ) ? sanitize_text_field( $_POST['humble-lms-user-pass-confirm'] ) : '';

      // Billing information
      if( Humble_LMS_Admin_Options_Manager::has_sales() ) {
        $user_postcode = sanitize_text_field( $_POST['humble-lms-user-postcode'] );
        $user_city = sanitize_text_field( $_POST['humble-lms-user-city'] );
        $user_address = sanitize_text_field( $_POST['humble-lms-user-address'] );
        $user_company = sanitize_text_field( $_POST['humble-lms-user-company'] );
        $user_vat_id = sanitize_text_field( $_POST['humble-lms-user-vat-id'] );
      }

      if( $user_country !== '' && ( ! in_array( $user_country, $countries ) ) ) {
        $this->humble_lms_errors()->add('country_empty', __('Please select your country.', 'humble-lms'));
      }
  
      if( ! is_email( $user_email ) || ! filter_var( $_POST['humble-lms-user-email'], FILTER_VALIDATE_EMAIL ) ) {
        $this->humble_lms_errors()->add('email_invalid', __('Please enter a valid email address.', 'humble-lms'));
      }
  
      if( $user_email !== $userdata->user_email && email_exists( $user_email ) ) {
        $this->humble_lms_errors()->add('email_used', __('Email address already registered.', 'humble-lms'));
      }

      if( ( ! empty( $user_email ) && ! empty( $user_email_confirm ) ) && $user_email !== $user_email_confirm ) {
        $this->humble_lms_errors()->add('emails_do_not_match', __('The email addresses you entered do not match.', 'humble-lms'));
      }

      if( ( ! empty( $user_email ) && empty( $user_email_confirm ) ) && $user_email !== $userdata->user_email ) {
        $this->humble_lms_errors()->add('confirm_email', __('Please fill in the email confirmation field.', 'humble-lms'));
      }

      if( ! empty( $user_pass ) ) {
        if( $user_pass === '') {
          $this->humble_lms_errors()->add('password_empty', __('Please enter a password.', 'humble-lms'));
        }

        if( strlen( $user_pass ) < 12 ) {
          $this->humble_lms_errors()->add('password_too_short', __('Password should be at least 12 characters.', 'humble-lms'));
        }

        if( ! preg_match('#[0-9]+#', $user_pass ) ) {
          $this->humble_lms_errors()->add('password_no_number', __('Password should include at least 1 number.', 'humble-lms'));
        }

        if( ! preg_match('#[a-zA-Z]+#', $user_pass) ) {
          $this->humble_lms_errors()->add('password_no_letter', __('Password should include at least 1 letter.', 'humble-lms'));
        } 

        if( $user_pass !== $user_pass_confirm ) {
          $this->humble_lms_errors()->add('password_mismatch', __('Passwords do not match.', 'humble-lms'));
        }
      }
      
      $errors = $this->humble_lms_errors()->get_error_messages();
      
      // No errors => create user
      if( empty( $errors ) ) {
        
        $args = array(
          'ID' => $user_id,
          'user_email' => $user_email
        );

        if( ! empty( $user_pass ) ) {
          $args['user_pass'] = $user_pass;
        }

        $updated_user_id = wp_update_user( $args );

        if( $updated_user_id ) {
          update_user_meta( $updated_user_id, 'humble_lms_title', $user_title );
          update_user_meta( $updated_user_id, 'humble_lms_country', $user_country );

          if( Humble_LMS_Admin_Options_Manager::has_sales() ) {
            update_user_meta( $updated_user_id, 'humble_lms_postcode', $user_postcode );
            update_user_meta( $updated_user_id, 'humble_lms_city', $user_city );
            update_user_meta( $updated_user_id, 'humble_lms_address', $user_address );
            update_user_meta( $updated_user_id, 'humble_lms_company', $user_company );
            update_user_meta( $updated_user_id, 'humble_lms_vat_id', $user_vat_id );
          }
        }
      }
    }
  }

  /**
   * Validate custom lost password form input.
   * 
   * @since   0.0.1
   */
  public function do_password_lost() {
    if( 'POST' !== $_SERVER['REQUEST_METHOD'] )
      return;
    
    $errors = retrieve_password();
    $login_page = $this->login_page();
    $lost_password_page = $this->lost_password_page();

    if( is_wp_error( $errors ) ) {
      $redirect_url = $lost_password_page;
      $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
    } else {
      $redirect_url = $login_page;
      $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
    }

    wp_redirect( $redirect_url );
    exit;
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
   * Redirect to custom login page.
   * 
   * @since   0.0.1
   */
  public function redirect_login_registration_lost_password() {
    $page_viewed = basename( $_SERVER['REQUEST_URI'] );
    $request_uri = $_SERVER['REQUEST_URI'];
  
    if( $page_viewed === 'wp-login.php' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $this->login_page());
      exit;
    }

    elseif( strpos($request_uri, 'wp-login.php?action=register') !== false && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $this->registration_page() );
      exit;
    }

    elseif( $page_viewed === 'wp-login.php?action=lostpassword' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
      wp_redirect( $this->lost_password_page() );
      exit;
    }

    elseif( $page_viewed === 'wp-login.php?action=lostpassword' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
      wp_redirect( $this->lost_password_page() );
      exit;
    }
  }

  /**
   * Redirect password reset.
   * 
   * @since   0.0.1
   */
  public function redirect_custom_password_reset() {
    if( $this->humble_lms_reset_password_page_exists() && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
      $reset_password_page = $this->reset_password_page();
      $login_page = $this->login_page();
      $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

      if( ! $user || is_wp_error( $user ) ) {
        if ( $user && $user->get_error_code() === 'expired_key' ) {
          wp_redirect( add_query_arg( 'login', 'expiredkey', $login_page ) );
        } else {
          wp_redirect( add_query_arg( 'login', 'invalidkey', $login_page ) );
        }
        exit;
      }

      $reset_password_page = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $reset_password_page );
      $reset_password_page = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $reset_password_page );

      wp_redirect( $reset_password_page  );
      exit;
    }
  }

  /**
   * Redirect on failed login.
   * 
   * @since   0.0.1
   */
  public function custom_login_failed() {
    $translator = new Humble_LMS_Translator;
    $login_page = $this->login_page();
    wp_redirect( add_query_arg( 'login', 'failed', $login_page ) );
    exit;
  }

  /**
   * Redirect when custom login form fields are empty.
   * 
   * @since   0.0.1
   */
  public function logout_redirect() {
    wp_redirect( add_query_arg( 'login', 'false', $this->login_page() ) );
    exit;
  }

  /**
   * Resets the user's password if the password reset form was submitted.
   */
  public function do_password_reset() {
    $translator = new Humble_LMS_Translator;

    if( $this->humble_lms_reset_password_page_exists() && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
      $login_page = $this->login_page();
      $reset_password_page = $this->reset_password_page();

      $rp_key = $_REQUEST['rp_key'];
      $rp_login = $_REQUEST['rp_login'];

      $user = check_password_reset_key( $rp_key, $rp_login );

      if( ! $user || is_wp_error( $user ) ) {
        if ( $user && $user->get_error_code() === 'expired_key' ) {
          wp_redirect( add_query_arg( 'login', 'expiredkey', $login_page ) );
        } else {
          wp_redirect( add_query_arg( 'login', 'invalidkey', $login_page ) );
        }
        exit;
      }

      if( isset( $_POST['pass1'] ) ) {
        if( $_POST['pass1'] !== $_POST['pass2'] ) {
          $redirect_url = $reset_password_page;

          $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
          $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
          $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

          wp_redirect( $redirect_url );
          exit;
        }

        if( ( empty( $_POST['pass1'] ) ) || ( strlen( $_POST['pass1'] ) < 12 ) || ( ! preg_match('#[0-9]+#', $_POST['pass1'] ) ) || ( ! preg_match('#[a-zA-Z]+#', $_POST['pass1'] ) ) ) {
          $redirect_url = $reset_password_page;

          $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
          $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
          $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

          wp_redirect( $redirect_url );
          exit;
        }

        // Parameter checks OK, reset password
        reset_password( $user, $_POST['pass1'] );
        wp_redirect( add_query_arg( 'password', 'changed', $login_page ) );
      } else {
        echo __('Invalid request.', 'humble-lms');
      }

      exit;
    }
  }

  /**
   * Returns the message body for the password reset mail.
   * Called through the retrieve_password_message filter.
   *
   * @param string  $message    Default mail message.
   * @param string  $key        The activation key.
   * @param string  $user_login The username for the user.
   * @param WP_User $user_data  WP_User object.
   * @since         0.0.1
   *
   * @return string   The mail message to send.
   */
  public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
    $options = new Humble_LMS_Admin_Options_Manager;
    $options = $options->options;
  
    if( ! isset( $options['email_lost_password'] ) || empty( $options['email_lost_password'] ) || ! $options['email_lost_password'] ) {
      $message  = __( 'Hi there,', 'humble-lms' ) . "\r\n\r\n";
      $message .= sprintf( __( 'you asked us to reset your password for your account using the email address %s.', 'humble-lms' ), $user_data->user_email ) . "\r\n\r\n";
      $message .= __( 'If this was a mistake, or you didn\'t ask for a password reset, just ignore this email and nothing will happen.', 'humble-lms' ) . "\r\n\r\n";
      $message .= __( 'To reset your password, visit the following address:', 'humble-lms' ) . "\r\n\r\n";
      $message .= esc_url_raw( site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ), 'login' ) ) . "\r\n\r\n";
      $message .= __( 'Thanks!', 'humble-lms' ) . "\r\n";
    } else {
      $date_format = 'F j, Y';
      $date = current_time( $date_format );
      $message = $options['email_lost_password'];
      $message = str_replace( 'USER_NAME', $user_login, $message );
      $message = str_replace( 'USER_EMAIL', $user_data->user_email, $message );
      $message = str_replace( 'CURRENT_DATE', $date, $message );
      $message = str_replace( 'WEBSITE_NAME', get_bloginfo('name'), $message );
      $message = str_replace( 'WEBSITE_URL', get_bloginfo('url'), $message );
      $message = str_replace( 'LOGIN_URL', wp_login_url(), $message );
      $message = str_replace( 'ADMIN_EMAIL', get_option('admin_email'), $message );
      $message = str_replace( 'RESET_PASSWORD_URL', esc_url_raw( site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ), 'login' ) ), $message );
    }

    return $message;
  }

  /**
   * Custom user notification email – sent on registration.
   * 
   * @since   0.0.1
   */
  function custom_new_user_notification_email( $msg, $user, $blogname ) {
    $options = new Humble_LMS_Admin_Options_Manager;
    $options = $options->options;

    $user_login = stripslashes( $user->user_login );
    $user_email = stripslashes( $user->user_email );
    $login_url = wp_login_url();

    if( ! isset( $options['email_welcome'] ) || empty( $options['email_welcome'] ) || ! $options['email_welcome'] ) {
      $message = __( 'Hi there,' ) . "\r\n\r\n";
      $message .= sprintf( __( 'welcome to %s! Here\'s how to log in:' ), get_option('blogname') ) . "\r\n\r\n";
      $message .= sprintf( __('Username: %s'), $user_login ) . "\r\n";
      $message .= sprintf( __('Email: %s'), $user_email ) . "\r\n";
      $message .= sprintf( __('Website: %s'), wp_login_url() ) . "\r\n\r\n";
      $message .= __( 'Please use the password you entered in the registration form.' ) . "\r\n\r\n";
      $message .= sprintf( __('If you have any problems, please contact us via email to %s.'), get_option('admin_email') ) . "\r\n\r\n";
    } else {
      $date_format = 'F j, Y';
      $date = current_time( $date_format );
      $message = $options['email_welcome'];
      $message = str_replace( 'USER_NAME', $user_login, $message );
      $message = str_replace( 'USER_EMAIL', $user_email, $message );
      $message = str_replace( 'CURRENT_DATE', $date, $message );
      $message = str_replace( 'WEBSITE_NAME', get_bloginfo('name'), $message );
      $message = str_replace( 'WEBSITE_URL', get_bloginfo('url'), $message );
      $message = str_replace( 'LOGIN_URL', wp_login_url(), $message );
      $message = str_replace( 'ADMIN_EMAIL', get_option('admin_email'), $message );
    }

    $msg['subject'] = __('Your credentials', 'humble-lms');
    $msg['headers'] = array('From: ' . get_bloginfo('name') . ' <' . get_option( 'admin_email' ) . '>', 'Content-Type: text/plain; charset=UTF-8');
      
    $msg['message'] = $message;

    return $msg;
  }

  /**
   * Add columns to display user country information.
   * 
   * @since   0.0.1
   */
  public function add_user_column_country( $column ) {
    $column['country'] = __('Country', 'humble-lms');
    return $column;
  }

  /**
   * Modify column 'country' in user table row.
   * 
   * @since   0.0.1
   */
  public function modify_user_table_row( $val, $column_name, $user_id ) {
    switch( $column_name ) {
      case 'country' :
        $val = get_user_meta( $user_id, 'humble_lms_country', true );
        return ! empty( $val ) && $val !== '' ? $val : '–';
        break;
    }

    return $val;
  }

  /**
   * Make column 'country' in user table row sortable.
   * 
   * @since   0.0.1
   */
  public function sortable_column_country( $columns ) {
    $columns['country'] = __('Country', 'humble-lms');

    return $columns;
  }

  /**
   * Modify the ordering of user table column 'Country'.
   * 
   * @since   0.0.1
   */
  public function column_users_sort( $vars ) {
    if( isset( $vars->orderby ) && 'country' === $vars->orderby ) {    
      $vars = array_merge( $vars, array(
        'orderby' => 'meta_key',
      ));
    }

    return $vars;
  }

  /**
   * Add course column to humble_lms_lesson (dashboard).
   * 
   * @since   0.0.1
   */
  public function add_humble_lms_lesson_column_courses( $columns ) {
    $new = array();
    foreach( $columns as $key => $title ) {
      if( $key === 'categories' ) {
        $new['courses'] = __('Courses', 'humble-lms');
      }

      $new[$key] = $title;
    }

    return $new;
  }

  /**
   * Add sortable course column content to humble_lms_lesson (dashboard).
   * 
   * @since   0.0.1
   */
  public function humble_lms_lesson_sortable_column_courses( $column, $post_id ) {
    $content_manager = new Humble_LMS_Content_Manager;

    switch( $column ) {
      case 'courses':
        $courses_array = array();
        $courses = $content_manager->get_courses( false, false );

        foreach( $courses as $course ) {
          $lessons = $content_manager->get_course_lessons( $course->ID, false );

          foreach( $lessons as $lesson ) {
            if( in_array( $post_id, $lessons ) ) {
              $new = array(
                'id' => $course->ID,
                'name' => $course->post_title,
              );

              if( ! in_array( $new, $courses_array ) ) {
                array_push( $courses_array, $new );
              }
            }
          }
        }

        $courses_array = array_unique( $courses_array, SORT_REGULAR );

        foreach( $courses_array as $course ) {
          echo '<a href="' . get_edit_post_link( $course['id'] ) . '">' . $course['name'] . '</a>';
          
          if( $course !== end( $courses_array ) ) {
            echo ', ';
          }
        }

        break;
    }
  }

  /**
   * Sort lessons based on column "Courses".
   * 
   * @since   0.0.1
   */
  public function humble_lms_lesson_courses_sort( $columns ) {
    $custom = array(
      'courses' => 'courses',
    );

    return wp_parse_args($custom, $columns);
  }

  /**
   * Add quiz column to humble_lms_question (dashboard).
   * 
   * @since   0.0.1
   */
  public function add_humble_lms_question_column_quizzes( $columns ) {
    $new = array();
    foreach( $columns as $key => $title ) {
      if( $key === 'categories' ) {
        $new['quizzes'] = __('Quizzes', 'humble-lms');
      }

      $new[$key] = $title;
    }

    return $new;
  }

  /**
   * Add sortable quiz column content to humble_lms_question (dashboard).
   * 
   * @since   0.0.1
   */
  public function humble_lms_question_sortable_column_quizzes( $column, $post_id ) {
    $content_manager = new Humble_LMS_Content_Manager;
    switch ( $column ) {
      case 'quizzes' :
        $quizzes_array = array();
        $quizzes = $content_manager->get_quizzes( false, false );

        foreach( $quizzes as $quiz ) {
          $questions = $content_manager->get_quiz_questions( $quiz->ID );

          foreach( $questions as $question ) {
            if( in_array( $post_id, $questions ) ) {
              array_push( $quizzes_array, array(
                'id' => $quiz->ID,
                'name' => $quiz->post_title,
              ) );
            }
          }
        }

        $quizzes_array = array_unique( $quizzes_array, SORT_REGULAR );

        foreach( $quizzes_array as $quiz ) {
          echo '<a href="' . get_edit_post_link( $quiz['id'] ) . '">' . $quiz['name'] . '</a>';
          
          if( $quiz !== end( $quizzes_array ) ) {
            echo ', ';
          }
        }

        break;
    }
  }

  /**
   * Sort questions based on column "Quizzes".
   * 
   * @since   0.0.1
   */
  public function humble_lms_question_quizzes_sort( $columns ) {
    $custom = array(
      'quizzes' => 'quizzes',
    );

    return wp_parse_args($custom, $columns);
  }

  /**
   * Add track column to humble_lms_course (dashboard).
   * 
   * @since   0.0.1
   */
  public function add_humble_lms_course_column_tracks( $columns ) {
    $new = array();
    foreach( $columns as $key => $title ) {
      if( $key === 'categories' ) {
        $new['tracks'] = __('Tracks', 'humble-lms');
      }

      $new[$key] = $title;
    }

    return $new;
  }

  /**
   * Add sortable track column content to humble_lms_course (dashboard).
   * 
   * @since   0.0.1
   */
  public function humble_lms_course_sortable_column_tracks( $column, $post_id ) {
    $content_manager = new Humble_LMS_Content_Manager;
    switch ( $column ) {
      case 'tracks' :
        $tracks_array = array();
        $tracks = $content_manager->get_tracks( false, false );

        foreach( $tracks as $track ) {
          $courses = $content_manager->get_track_courses( $track->ID, false, false );

          foreach( $courses as $course ) {
            if( in_array( $post_id, $courses ) ) {
              array_push( $tracks_array, array(
                'id' => $track->ID,
                'name' => $track->post_title,
              ) );
            }
          }
        }

        $tracks_array = array_unique( $tracks_array, SORT_REGULAR );

        foreach( $tracks_array as $track ) {
          echo '<a href="' . get_edit_post_link( $track['id'] ) . '">' . $track['name'] . '</a>';
          
          if( $track !== end( $tracks_array ) ) {
            echo ', ';
          }
        }

        break;
    }
  }

  /**
   * Sort courses based on column "Tracks".
   * 
   * @since   0.0.1
   */
  public function humble_lms_course_tracks_sort( $columns ) {
    $custom = array(
      'tracks' => 'tracks',
    );

    return wp_parse_args($custom, $columns);
  }

  /**
   * Add content to admin edit screen.
   * 
   * @since   0.0.1
   */
  public static function footer_content( $slug = null ) {
    echo '<div class="humble-lms-loading-layer"><div class="humble-lms-loading"></div></div>';
  }

}
