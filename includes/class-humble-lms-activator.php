<?php

/**
 * Fired during plugin activation
 *
 * @link       https://minimalwordpress.com/humble-lms
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
    $has_archive_page = get_page_by_path('courses');

    if( ! $has_archive_page ) {
      $post = array(
        'post_type' => 'page',
        'post_title' => 'Courses',
        'post_name' => 'courses',
        'post_content' => '[course_archive tile_width="half"]',
        'post_status' => 'publish',
        'post_author' => 1
      );

      $post_id = wp_insert_post( $post );
    }
  }

}
