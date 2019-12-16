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
	 * Shortcode: course archive
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_course_archive( $atts = null ) {
    extract( shortcode_atts( array (
      'tile_width' => 'half',
      'style' => '',
      'class' => '',
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
    $html .= '<div class="humble-lms-flex-columns ' . $class . '" style="' . $style . '">';

    foreach( $courses as $course ) {
      $html .= do_shortcode('[course_tile tile_width="' . $tile_width . '" id="' . $course->ID . '"]');
    }

    $html .= '</div>';

    return $html;
  }

  /**
	 * Shortcode: syllabus
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_syllabus( $atts = null ) {
    global $post;

    extract( shortcode_atts( array (
      'id' => $post->ID,
      'style' => '',
      'class' => '',
    ), $atts ) );

    $lessons = get_post_meta( $id, 'humble_lms_course_lessons', true );
    $lessons = explode( ',', $lessons );

    if( ! is_array( $lessons ) )
      return;

    $lessons = get_posts( array(
      'post_type' => 'humble_lms_lesson',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'orderby' => 'title',
      'order' => 'ASC',
      'include' => $lessons
    ) );

    $html = '<nav>';
      $html = '<h2>' . __('Course Syllabus', 'humble-lms') . '</h2>';
      $html .= '<ul class="humble-lms-syllabus ' . $class . '" style="' . $style . '">';
      
      foreach( $lessons as $key => $lesson ) {
        $description = get_post_meta( $lesson->ID, 'humble_lms_lesson_description', true );

        $html .= '<li class="humble-lms-syllabus-lesson">';
        $html .= '<a href="' . esc_url( get_permalink( $lesson->ID ) )  . '?course=' . (int)$id .'">';
        $html .= '<span class="humble-lms-syllabus-title">' . $lesson->post_title . '</span>';
        $html .= $description? '<span class="humble-lms-syllabus-description">' . $description . '</span>' : '';
        $html .= '</a>';
        $html .= '</li>';
      }
      
      $html .= '</ul>';

      $html .= '<a class="humble-lms-btn humble-lms-btn--success" href="' . esc_url( get_permalink( $lessons[0]->ID ) )  . '?course=' . (int)$id . '">';
      $html .= '<span class="humble-lms-syllabus-title">' . __('Start the course now', 'humble-lms') . '</span>';
      $html .= '</a>';
    $html .= '</nav>';

    return $html;
  }

  /**
	 * Shortcode: mark lesson complete
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_mark_complete( $atts = null ) {
    global $post;

    $course_id = isset( $_GET['course'] ) ? (int)$_GET['course'] : null;

    if( ! $course_id )
      return;

    $course = get_post( $course_id );

    if( ! $course )
      return;

    extract( shortcode_atts( array (
      'style' => '',
      'class' => '',
    ), $atts ) );

    $lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
    $lessons = explode(',', $lessons);

    $key = array_search( $post->ID, $lessons );
    $is_first = $key === array_key_first( $lessons );
    $is_last = $key === array_key_last( $lessons );

    if( ! $is_last) {
      $next_lesson = get_post( $lessons[$key+1] );
    }

    if( ! $is_first ) {
      $prev_lesson = get_post( $lessons[$key-1] );
    }

    $html = '<form method="post" id="humble-lms-mark-complete">';
      $html .= '<input type="hidden" name="course-id" id="course-id" value="' . $course_id . '">';
      $html .= '<input type="hidden" name="lesson-id" id="lesson-id" value="' . $post->ID . '">';
      $html .= '<input type="submit" class="humble-lms-btn humble-lms-btn--success" value="' . __('Mark complete and continue', 'humble-lms') . '">';
    $html .= '</form>';

    $html .= '<div class="humble-lms-next-prev-lesson">';
      $html .= $is_first ? '<a class="humble-lms-prev-lesson-link" href="' . esc_url( get_permalink( $course_id ) ) . '">' . __('Back to course', 'humble-lms') . '</a>' : '<a class="humble-lms-prev-lesson-link" href="' . esc_url( get_permalink( $prev_lesson->ID ) ) . '?course=' . $course_id . '">' . __('Previous lesson', 'humble-lms') . '</a>';
      $html .= ! $is_last ? '<a class="humble-lms-next-lesson-link" href="' . esc_url( get_permalink( $next_lesson->ID ) ) . '?course=' . $course_id . '">' . __('Next lesson', 'humble-lms') . '</a>' : '<a class="humble-lms-prev-lesson-link" href="' . esc_url( get_permalink( $course_id ) ) . '">' . __('Back to course', 'humble-lms') . '</a>';
    $html .= '</div>';

    return $html;
  }

  /**
	 * Add syllabus to single course page
	 *
	 * @since    0.0.1
	 */
  function humble_lms_add_content_to_pages( $content ) {
    global $post;

    // Single course
    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' )
    {
        $content .= do_shortcode('[syllabus]');
    }
    
    // Single lesson
    elseif ( is_single() && get_post_type( $post->ID ) === 'humble_lms_lesson' )
    {
      $content .= do_shortcode('[mark_complete]');
    }

    return $content;
  }

  /**
	 * Add post states for default plugin posts/pages
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_add_post_states( $post_states ) {
    global $post;

    if( ! $post )
      return $post_states;

    if( $post->post_name === 'courses' ) {
      $post_states[] = 'Humble LMS course archive';
    }
  
    return $post_states;
  }

  /**
	 * Shortcode: course tile
	 *
	 * @since    0.0.1
	 */
  public function humble_lms_course_tile( $atts = null ) {
    extract( shortcode_atts( array (
      'id' => '',
      'tile_width' => 'half'
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
