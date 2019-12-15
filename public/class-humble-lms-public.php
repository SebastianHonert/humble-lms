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
	 * Shortcode: course archive
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_course_archive( $atts = null ) {
    extract( shortcode_atts( array (
      'tile_width' => 'half',
    ), $atts ) );

    $args = array(
      'post_type' => 'humble_lms_course',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
    );

    $courses = get_posts( $args );

    $html = '';
    $html .= '<div class="humble-lms-flex-columns">';

    foreach( $courses as $course ) {
      $html .= do_shortcode('[course_tile tile_width="' . $tile_width . '" id="' . $course->ID . '"]');
    }

    $html .= '</div>';

    return $html;
  }

  /**
	 * Shortcode: course tile
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_course_tile( $atts = null ) {
    extract( shortcode_atts( array (
      'id' => '',
      'tile_width' => 'third'
    ), $atts ) );

    $course = get_post( $id );
    $featured_img_url = get_the_post_thumbnail_url( $id, 'humble-lms-course-tile'); 
    $level = strip_tags( get_the_term_list( $id, 'humble_lms_course_level', '', ', ') );
    $level = $level ? $level : __('not specified', 'humble-lms');
    $duration = get_post_meta( $id, 'humble_lms_course_duration', true );
    $duration = $duration ? $duration : __('not specified', 'humble-lms');

    $html = '<div class="humble-lms-course-tile-wrapper humble-lms-flex-column--' . $tile_width . '">';
      $html .= '<a style="background-image: url(' . $featured_img_url . ')" href="' . esc_url( get_permalink( $id ) ) . '" class="humble-lms-course-tile">';
        $html .= '<div class="humble-lms-course-tile-layer"></div>';
        $html .= '<div class="humble-lms-16-9">';
          $html .= '<div class="humble-lms-course-title">' . $course->post_title . '</div>';
        $html .= '</div>';
      $html .= '</a>';
      $html .= '<div class="humble-lms-course-tile-meta">';
        $html .= '<span class="humble-lms-difficulty"><strong>' . __('Level', 'humble-lms') . ':</strong> ' . $level . '</span>';
        $html .= '<span class="humble-lms-duration"><strong>' . __('Duration', 'humble-lms') . ':</strong> ' . $duration  . '</span>';
      $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

}
