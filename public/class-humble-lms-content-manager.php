<?php
/**
 * This class provides the frontend plugin shortcodes.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Content_Manager' ) ) {

  class Humble_LMS_Content_Manager {

    /**
     * Get tracks (published / unpublished)
     *
     * @param   bool
     * @return  Array
     * @since   0.0.1
     */
    public function get_tracks( $published = false ) {
      $args = array(
        'post_type' => 'humble_lms_track',
        'posts_per_page' => -1,
        'post_status' => $published ? 'published' : 'any',
      );
  
      $tracks = get_posts( $args );

      return $tracks;
    }

    /**
     * Get courses for a single track id.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_track_courses( $track_id, $published = false ) {
      $track_courses = [];

      if( ! $track_id || get_post_type( $track_id ) !== 'humble_lms_track' )
        return $track_courses;

      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', true );
      $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

      return $track_courses;
    }

    /**
     * Get courses (published / unpublished).
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_courses( $published = false ) {
      $args = array(
        'post_type' => 'humble_lms_course',
        'posts_per_page' => -1,
        'post_status' => $published ? 'published' : 'any',
      );
  
      $courses = get_posts( $args );

      return $courses;
    }

    /**
     * Get lessons for a single course.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_course_lessons( $course_id ) {
      $course_lessons = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_lessons;

      $course_lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

      return $course_lessons;
    }

    /**
     * Get track IDs (published / unpublished)
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_track_ids( $published = false ) {
      $tracks = $this->get_tracks( $published );
      $track_ids = array();

      foreach( $tracks as $track ) {
        array_push( $track_ids, $track->ID );
      }

      return $track_ids;
    }

    /**
     * Get course IDs (published / unpublished)
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_course_ids( $published = false ) {
      $courses = $this->get_courses( $published );
      $course_ids = array();

      foreach( $courses as $course ) {
        array_push( $course_ids, $course->ID );
      }

      return $course_ids;
    }

    /**
     * Get course IDs by parameter.
     * 
     * @return  array
     * @since   0.0.1
     */
    public function find_courses_by( $by = null, $id = null ) {
      if( ( ! $by ) || ( ! $id ) ) {
        return [];
      }

      $course_ids = [];
      $allowed_by = array(
        'lesson'
      );

      if( ! in_array( $by, $allowed_by ) ) {
        return $course_ids;
      }

      switch( $by ) {
        case 'lesson':
          if( get_post_type( $id ) !== 'humble_lms_lesson' ) {
            return $course_ids;
          }

          $courses = $this->get_courses( true );

          foreach( $courses as $course ) {
            $course_lessons = $this->get_course_lessons( $course->ID );
            if( in_array( $id, $course_lessons ) ) {
              array_push( $course_ids, $course->ID );
            }
          }

          return $course_ids;

        default:
          return $course_ids;
      }

    }

    /**
     * Get instructors by post ID.
     * 
     * @param   int
     * @return  array
     * @since   0.0.1
     */
    function get_instructors( $post_id = null ) {
      $allowed_post_types = array(
        'humble_lms_course',
        'humble_lms_lesson',
      );

      $post_type = get_post_type( $post_id );

      if( ! in_array( $post_type, $allowed_post_types ) )
        return [];

      if( $post_type === 'humble_lms_course' ) {
        $instructors = get_post_meta( $post_id, 'humble_lms_course_instructors', true );
      } elseif( $post_type === 'humble_lms_lesson' ) {
        $instructors = get_post_meta( $post_id, 'humble_lms_lesson_instructors', true );
      }

      $instructors = ! empty( $instructors[0] ) ? json_decode( $instructors[0] ) : [];

      return $instructors;
    }

  }
  
}
