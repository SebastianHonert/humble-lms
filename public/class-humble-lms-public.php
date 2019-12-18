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
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-course.php';
    require_once plugin_dir_path( __FILE__ ) . 'custom-post-types/humble-lms-lesson.php';
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
	 * Register custom templates
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_custom_templates( $template ) {
    global $wp_query, $post;

    // Course archive template
    if ( is_archive() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php';
      }
    }

    // Course single template
    if ( is_single() && $post->post_type == 'humble_lms_course' ) {
      if ( file_exists( plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php' ) ) {
          return plugin_dir_path( __FILE__ ) . '/partials/humble-lms-course-archive.php';
      }
    }

    return $template;
  }

  /**
	 * Add syllabus to single course page
	 *
	 * @since    0.0.1
	 */
  function humble_lms_add_content_to_pages( $content ) {
    global $post;

    $html = '';
    $course_id = null;
    $lesson_id = null;

    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' ) {
      $course_id = $post->ID;
    } elseif( isset( $_POST['course_id'] ) ) {
      $course_id = (int)$_POST['course_id'];
    }

    // Success message: User completed course
    if( $this->user->completed_course( $course_id ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">Congratulations!</span>
        <span class="humble-lms-message-content">You successfully completed the course "' . get_the_title( $course_id ) . '".</span> 
      </div>';
    }

    // Single course
    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' )
    {
        $html .= $content . do_shortcode('[syllabus]');
    }
    
    // Single lesson
    elseif ( is_single() && get_post_type( $post->ID ) === 'humble_lms_lesson' )
    {
      $html .= '<div class="humble-lms-flex-columns">';
        $html .= '<div class="humble-lms-flex-column--two-third">';

        if( isset( $_POST['course_id'] ) ) {
          $html .= '<p class="humble-lms-course-meta humble-lms-course-meta--lesson">' . __('Course', 'humble-lms') . ': <a href="' . esc_url( get_permalink( (int)$_POST['course_id'] ) ) . '">' . get_the_title( (int)$_POST['course_id'] ) . '</a></p>';
        }

        $html .= $content;
        $html .= do_shortcode('[mark_complete]');
        $html .= '</div>';
        $html .= '<div class="humble-lms-flex-column--third">';
        $html .= do_shortcode('[syllabus context="lesson"]');
        $html .= '</div>';
      $html .= '</div>';
    }

    return $html;
  }

}
