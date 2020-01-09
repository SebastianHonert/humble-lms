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

    return $template;
  }

  /**
   * Add syllabus to single course page
   *
   * @since    0.0.1
   */
  function humble_lms_add_content_to_pages( $content ) {
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

    // Success message: User completed course
    if ( is_single() && get_post_type( $post->ID ) === 'humble_lms_course' ) {
      $course_id = $post->ID;
    } elseif( isset( $_POST['course_id'] ) ) {
      $course_id = (int)$_POST['course_id'];
    }

    if( isset( $_GET['access'] ) && sanitize_text_field( $_GET['access'] === 'denied' ) && ! current_user_can('manage_options' ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--error">';
      $html .= '<span class="humble-lms-message-title">' . __('Access denied', 'humble-lms') . '</span>';
      $html .= '<span class="humble-lms-message-content">' . sprintf( __('You need to be <a href="%s">logged in</a> and have the required permissions in order to access the requested content.', 'humble-lms' ), wp_login_url() ) . '</span>';
      $html .= '</div>';
    }

    if( $this->user->completed_course( $course_id ) ) {
      $html .= '<div class="humble-lms-message humble-lms-message--success">
        <span class="humble-lms-message-title">' . __('Congratulations', 'humble-lms') . '</span>
        <span class="humble-lms-message-content">' . sprintf( __('You successfully completed the course "%s".', 'humble-lms'), '<a href="#">' . get_the_title( $course_id ) ) . '</a></span> 
      </div>';
    }

    if ( is_single() && ( get_post_type( $post->ID ) === 'humble_lms_course' || get_post_type( $post->ID ) === 'humble_lms_lesson' ) ) {
      $html .= $content;
    }

    // Completed lesson/course/track
    if( isset( $_POST['completed'] ) ) {
      $completed = json_decode( $_POST['completed'] );
      if( ! empty( $completed[0] ) ) {
        // Display array content (testing)
        // echo '<pre>';
        // print_r( $completed );
        // echo '</pre>';

        $html .= '<div class="humble-lms-award-message"><div>';

        foreach( $completed as $key => $ids ) {
          foreach( $ids as $id ) {
            if( $key === 0 ) { $title = __('Lesson completed', 'humble-lms'); $icon = 'ti-thumb-up'; }
            if( $key === 1 ) { $title = __('Course completed', 'humble-lms'); $icon = 'ti-medall'; }
            if( $key === 2 ) { $title = __('Track completed', 'humble-lms'); $icon = 'ti-crown'; }
            if( $key === 3 ) { $title = __('You received an award', 'humble-lms'); $icon = 'ti-medall'; }

            $html .= '<div class="humble-lms-award-message-inner">
                <div>
                  <div class="humble-lms-award-message-close" aria-label="Close award overlay">
                    <i class="ti-close"></i>
                  </div>
                  <h3 class=humble-lms-award-message-title">' . $title . '</h3>
                  <p class="humble-lms-award-message-content-name">' . get_the_title( $id ) . '</p>';

                  if( $key !== 3 ) {
                    $html .= '<div class="humble-lms-award-message-image humble-lms-bounce-in">
                    <i class="' . $icon .'"></i>
                  </div>';
                  } else {
                    $html .= '<img class="humble-lms-award-image humble-lms-bounce-in" src="' . get_the_post_thumbnail_url( $id ) . '" alt="" />';
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
  function humble_lms_template_redirect() {
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
  function hide_admin_bar() {
    if ( ! current_user_can('edit_posts') ) {
      show_admin_bar(false);
    }
  }

}
