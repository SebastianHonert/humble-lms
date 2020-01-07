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

}
