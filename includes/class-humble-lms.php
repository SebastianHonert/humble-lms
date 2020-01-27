<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://minimalwordpress.com/humble-lms
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      Humble_LMS_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $humble_lms    The string used to uniquely identify this plugin.
   */
  protected $humble_lms;

  /**
   * The current version of the plugin.
   *
   * @since    0.0.1
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    0.0.1
   */
  public function __construct() {
    if ( defined( 'HUMBLE_LMS_VERSION' ) ) {
      $this->version = HUMBLE_LMS_VERSION;
    } else {
      $this->version = '0.0.1';
    }
    $this->humble_lms = 'humble-lms';

    $this->load_dependencies();
    $this->set_locale();
    $this->add_image_sizes();
    $this->define_admin_hooks();
    $this->define_public_hooks();

  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Humble_LMS_Loader. Orchestrates the hooks of the plugin.
   * - Humble_LMS_i18n. Defines internationalization functionality.
   * - Humble_LMS_Admin. Defines all hooks for the admin area.
   * - Humble_LMS_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function load_dependencies() {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-humble-lms-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-humble-lms-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-humble-lms-admin.php';

    /**
     * The class providing widgets.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/widgets/class-humble-lms-syllabus.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/widgets/class-humble-lms-course-instructors.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/widgets/class-humble-lms-progress-bar.php';

    /**
     * The class providing options management functionalities.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-humble-lms-options-manager.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-public.php';

    /**
     * The class provides track/course/lesson content functionalities.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-content-manager.php';
    
    /**
     * The class provides the plugin shortcodes
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-shortcodes.php';

    /**
     * The class responsible for handling public ajax requests
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-ajax.php';

    /**
     * The class responsible for handling user data
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-user.php';

    /**
     * The class responsible for handling user access.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-access-handler.php';

    /**
     * The class responsible for handling quiz activities.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-quiz.php';

    $this->loader = new Humble_LMS_Loader();

  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Humble_LMS_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new Humble_LMS_i18n();

    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

  }
  
  /**
   * Define the required images sizes
   *
   * Uses the Humble_LMS_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    0.0.1
   * @access   private
   */
  private function add_image_sizes() {

    add_image_size('humble-lms-course-tile', 900, 450);
    
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    0.0.1
   * @access   private
   */
  private function define_admin_hooks() {

    $plugin_admin = new Humble_LMS_Admin( $this->get_humble_lms(), $this->get_version() );

    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    $this->loader->add_action( 'admin_init', $plugin_admin, 'block_dashboard_access' );
    $this->loader->add_action( 'admin_init', $plugin_admin, 'add_user_roles' );
    $this->loader->add_action( 'widgets_init', $plugin_admin, 'register_sidebars' );
    $this->loader->add_action( 'trashed_post', $plugin_admin, 'remove_meta' );
    $this->loader->add_action( 'edit_user_profile', $plugin_admin, 'add_user_profile_fields' );
    $this->loader->add_action( 'show_user_profile', $plugin_admin, 'add_user_profile_fields' );
    $this->loader->add_action( 'personal_options_update', $plugin_admin, 'update_user_profile' );
    $this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'update_user_profile' );

    // Widgets
    $plugin_widget_syllabus = new Humble_LMS_Widget_Syllabus( $plugin_admin );
    $plugin_widget_course_instructors = new Humble_LMS_Widget_Course_Instructors( $plugin_admin );
    $plugin_widget_progress_bar = new Humble_LMS_Widget_Progress_Bar( $plugin_admin );

    $this->loader->add_action( 'widgets_init', $plugin_widget_syllabus, 'register_widget_syllabus');
    $this->loader->add_action( 'widgets_init', $plugin_widget_course_instructors, 'register_widget_course_instructors');
    $this->loader->add_action( 'widgets_init', $plugin_widget_progress_bar, 'register_widget_progress_bar');
    
    // Options
    $plugin_options_manager = new Humble_LMS_Admin_Options_Manager( $plugin_admin );

    $this->loader->add_action( 'admin_menu', $plugin_options_manager, 'add_options_page' );
    $this->loader->add_action( 'admin_init', $plugin_options_manager, 'humble_lms_options_admin_init' );
    
    // Login, registration, lost password
    $this->loader->add_action( 'wp_login_failed', $plugin_admin, 'custom_login_failed' );
    $this->loader->add_filter( 'authenticate', $plugin_admin, 'verify_user_pass', 1, 3 );
    $this->loader->add_action( 'wp_logout', $plugin_admin, 'logout_redirect' );
    $this->loader->add_action( 'init', $plugin_admin, 'humble_lms_register_user' );
    $this->loader->add_action( 'init', $plugin_admin, 'humble_lms_update_user' );
    $this->loader->add_action( 'login_form_lostpassword', $plugin_admin, 'do_password_lost' );
    $this->loader->add_action( 'login_form_rp', $plugin_admin, 'do_password_reset' );
    $this->loader->add_action( 'login_form_resetpass', $plugin_admin, 'do_password_reset' );
    $this->loader->add_action( 'login_form_rp', $plugin_admin, 'redirect_custom_password_reset' );
    $this->loader->add_action( 'login_form_resetpass', $plugin_admin, 'redirect_custom_password_reset' );
    $this->loader->add_action( 'login_form_register', $plugin_admin, 'redirect_login_registration_lost_password' );
    $this->loader->add_action( 'login_form_lostpassword', $plugin_admin, 'redirect_login_registration_lost_password' );

    // Retrieve password custom email
    $this->loader->add_action( 'retrieve_password_message', $plugin_admin, 'replace_retrieve_password_message', 10, 4 );
    $this->loader->add_action( 'wp_new_user_notification_email', $plugin_admin, 'custom_new_user_notification_email', 10, 3 );
  
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    0.0.1
   * @access   private
   */
  private function define_public_hooks() {

    $plugin_public = new Humble_LMS_Public( $this->get_humble_lms(), $this->get_version() );

    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    $this->loader->add_action( 'init', $plugin_public, 'register_custom_post_types' );
    $this->loader->add_action( 'init', $plugin_public, 'register_custom_taxonomies' );
    $this->loader->add_action( 'set_current_user', $plugin_public, 'hide_admin_bar' );
    $this->loader->add_filter( 'archive_template', $plugin_public, 'humble_lms_archive_templates' );
    $this->loader->add_filter( 'single_template', $plugin_public, 'humble_lms_single_templates' );
    $this->loader->add_filter( 'the_content', $plugin_public, 'humble_lms_add_content_to_pages' );
    $this->loader->add_filter( 'template_redirect', $plugin_public, 'humble_lms_template_redirect' );

    // Shortcodes
    $plugin_shortcodes = new Humble_LMS_Public_Shortcodes( $plugin_public );

    $this->loader->add_shortcode( 'humble_lms_track_archive', $plugin_shortcodes, 'track_archive' );
    $this->loader->add_shortcode( 'humble_lms_track_tile', $plugin_shortcodes, 'track_tile' );
    $this->loader->add_shortcode( 'humble_lms_course_archive', $plugin_shortcodes, 'course_archive' );
    $this->loader->add_shortcode( 'humble_lms_course_tile', $plugin_shortcodes, 'course_tile' );
    $this->loader->add_shortcode( 'humble_lms_progress_bar', $plugin_shortcodes, 'progress_bar' );
    $this->loader->add_shortcode( 'humble_lms_syllabus', $plugin_shortcodes, 'syllabus' );
    $this->loader->add_shortcode( 'humble_lms_course_instructors', $plugin_shortcodes, 'course_instructors' );
    $this->loader->add_shortcode( 'humble_lms_mark_complete_button', $plugin_shortcodes, 'mark_complete_button' );
    $this->loader->add_shortcode( 'humble_lms_user_progress', $plugin_shortcodes, 'user_progress' );
    $this->loader->add_shortcode( 'humble_lms_user_awards', $plugin_shortcodes, 'user_awards' );
    $this->loader->add_shortcode( 'humble_lms_user_certificates', $plugin_shortcodes, 'user_certificates' );
    $this->loader->add_shortcode( 'humble_lms_login_form', $plugin_shortcodes, 'humble_lms_custom_login_form' );
    $this->loader->add_shortcode( 'humble_lms_registration_form', $plugin_shortcodes, 'humble_lms_custom_registration_form' );
    $this->loader->add_shortcode( 'humble_lms_lost_password_form', $plugin_shortcodes, 'humble_lms_custom_lost_password_form' );
    $this->loader->add_shortcode( 'humble_lms_reset_password_form', $plugin_shortcodes, 'humble_lms_custom_reset_password_form' );
    $this->loader->add_shortcode( 'humble_lms_user_profile', $plugin_shortcodes, 'humble_lms_custom_user_profile' );

    // AJAX
    $plugin_ajax = new Humble_LMS_Public_Ajax( $plugin_public );

    $this->loader->add_action( 'wp_ajax_nopriv_mark_lesson_complete', $plugin_ajax, 'mark_lesson_complete' );
    $this->loader->add_action( 'wp_ajax_mark_lesson_complete', $plugin_ajax, 'mark_lesson_complete' );

  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    0.0.1
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     0.0.1
   * @return    string    The name of the plugin.
   */
  public function get_humble_lms() {
    return $this->humble_lms;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     0.0.1
   * @return    Humble_LMS_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     0.0.1
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}
