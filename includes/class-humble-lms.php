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
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humble-lms-public.php';
    
    /**
		 * The class responsible for handling public ajax requests
		 */
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-plugin-ajax.php';

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
    $this->loader->add_filter( 'archive_template', $plugin_public, 'humble_lms_custom_templates' );
    $this->loader->add_filter( 'display_post_states', $plugin_public, 'humble_lms_add_post_states' );
    $this->loader->add_filter( 'the_content', $plugin_public, 'humble_lms_add_content_to_pages' );
    $this->loader->add_shortcode( 'course_archive', $plugin_public, 'humble_lms_course_archive' );
    $this->loader->add_shortcode( 'course_tile', $plugin_public, 'humble_lms_course_tile' );
    $this->loader->add_shortcode( 'syllabus', $plugin_public, 'humble_lms_syllabus' );
    $this->loader->add_shortcode( 'mark_complete', $plugin_public, 'humble_lms_mark_complete' );

    /**
     * AJAX
     */
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
