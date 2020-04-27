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
        'post_status' => $published ? 'publish' : 'any',
      );
  
      $tracks = get_posts( $args );

      return $tracks;
    }

    /**
     * Get categories (array of terms) for post type.
     *
     * @param   string|bool.
     * @return  Array
     * @since   0.0.1
     */
    public function get_categories( $post_type = null, $published = false ) {
      $categories = array();

      switch( $post_type ) {
        case 'humble_lms_course':
          $posts = $this->get_courses( $published );
          break;
        case 'humble_lms_track':
          $posts = $this->get_tracks( $published );
          break;
        default:
          return $categories;
      }

      foreach( $posts as $post ) {
        $post_categories = get_the_category( $post->ID );
        foreach( $post_categories as $category ) {
          if( ! in_array( $category, $categories ) ) {
            array_push( $categories, $category );
          }
        }
      }

      return $categories;
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

      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', false );
      $track_courses = isset( $track_courses[0] ) && is_array( $track_courses[0] ) && ! empty( $track_courses[0] ) && ( isset( $track_courses[0][0] ) && $track_courses[0][0] !== '' ) ? $track_courses[0] : [];
      
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
        'post_status' => $published ? 'publish' : 'any',
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
    public static function get_course_lessons( $course_id = null ) {
      $course_lessons = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_lessons;

      $course_sections = self::get_course_sections( $course_id );
      foreach( $course_sections as $section ) {
        $lesson_ids = ! is_array( $section['lessons'] ) ? explode( ',', $section['lessons'] ) : [];
        foreach( $lesson_ids as $lesson_id ) {
          array_push( $course_lessons, (int)$lesson_id );
        }
      }

      return $course_lessons;
    }

    /**
     * Get sections for a single course.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public function get_course_id( $post_id = null ) {
      $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;

      if( ! $course_id && get_post_type( $post_id ) === 'humble_lms_lesson' ) {
        $course_ids = $this->find_courses_by('lesson', $post_id );
        if( is_array( $course_ids ) && sizeOf( $course_ids ) === 1 ) {
          $course_id = $course_ids[0];
        }
      }

      return $course_id;
    }

    /**
     * Get sections for a single course.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_course_sections( $course_id = null ) {
      $course_sections = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_sections;

      $course_sections = get_post_meta( $course_id, 'humble_lms_course_sections', true );
      $course_sections = json_decode( $course_sections, true );

      if( ! is_array( $course_sections ) ) {
        $course_sections = array(
          array(
            'title' => '',
            'lessons' => array(),
          ),
        );
      }

      return $course_sections;
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
    public static function get_instructors( $post_id = null ) {
      if( ! get_post( $post_id ) )
        return [];

      $instructors = [];
      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course',
        'humble_lms_lesson',
      );

      $post_type = get_post_type( $post_id );

      if( ! in_array( $post_type, $allowed_post_types ) )
        return $instructors;

      $instructors = get_post_meta( $post_id, 'humble_lms_instructors', false );
      $instructors = isset( $instructors[0] ) && ! empty( $instructors[0] ) && ( isset( $instructors[0][0] ) && $instructors[0][0] !== '' ) ? $instructors[0] : [];

      return $instructors;
    }

    /**
     * Get quizzes for a single lesson.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_lesson_quizzes( $lesson_id ) {
      $lesson_quizzes = [];

      if( ! $lesson_id || get_post_type( $lesson_id ) !== 'humble_lms_lesson' )
        return $lesson_quizzes;

      $lesson_quizzes = get_post_meta( $lesson_id, 'humble_lms_quizzes', false );
      $lesson_quizzes = isset( $lesson_quizzes[0] ) && ! empty( $lesson_quizzes[0] ) && ( isset( $lesson_quizzes[0][0] ) && $lesson_quizzes[0][0] !== '' ) ? $lesson_quizzes[0] : [];

      return $lesson_quizzes;
    }

    /**
     * How many users have completed a course in percent.
     * 
     * @param   int
     * @return  float
     * @since   0.0.1
     */
    function course_completion_percentage( $course_id = null ) {
      if( ! get_post( $course_id ) )
        return [];

      $percent = 0;
      $users = get_users();
      $num_users = count_users();
      $num_students = isset( $num_users['avail_roles']['humble_lms_student'] ) ? $num_users['avail_roles']['humble_lms_student'] : 0;

      if( $num_students === 0 ) {
        return 0;
      }

      $course = get_post( $course_id );
      
      if( ! $course )
        return 0;

      foreach( $users as $user ) {
        if( ! in_array( 'humble_lms_student', (array) $user->roles ) )
          continue;

        $course_progress = Humble_LMS_Public_User::course_progress( $course_id, $user->ID );
        $percent += $course_progress;
      }

      return $percent === 100 ? 100 : round( ( $percent / $num_students ), 1 );
      
    }

    /**
     * Get course timestamps
     * 
     * @return   array
     * @since    0.0.1
     */
    public static function get_timestamps( $course_id = null ) {
      if( ! $course_id || ! get_post_type( $course_id ) ) {
        return false;
      }

      $locale = get_locale();
      if( $locale === 'de_DE_formal' ) {
        $locale = 'de_DE';
      }

      setlocale(LC_ALL, $locale);

      $date_format = get_option('date_format');
      $date_format = self::dateFormatToStrftime( $date_format );
      
      $timestamps = get_post_meta( $course_id, 'humble_lms_course_timestamps', false );
      $timestamps_array = array(
        'from' => isset( $timestamps[0]['from'] ) && ! empty( $timestamps[0]['from'] ) ? $timestamps[0]['from'] : '',
        'to' => isset( $timestamps[0]['to'] ) && ! empty( $timestamps[0]['to'] ) ? $timestamps[0]['to'] : '',
        'date_from' => isset( $timestamps[0]['from'] ) && ! empty( $timestamps[0]['from'] ) ? strftime( $date_format, $timestamps[0]['from'] ) : '',
        'date_to' => isset( $timestamps[0]['to'] ) && ! empty( $timestamps[0]['to'] ) ? strftime( $date_format, $timestamps[0]['to'] ) : '',
        'info' => isset( $timestamps[0]['info'] ) ? $timestamps[0]['info'] : '',
      );

      setlocale(LC_ALL, 0);

      return $timestamps_array;
    }

    /**
    * Convert a date format to a strftime format
    *
    * Timezone conversion is done for unix. Windows users must exchange %z and %Z.
    *
    * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
    * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
    *
    * @param string $dateFormat a date format
    * @return string
    */
    public static function dateFormatToStrftime( $date_format ) {
      $chars = array(
          // Day - no strf eq : S
          'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%A', 'N' => '%u', 'w' => '%w', 'z' => '%j',
          // Week - no date eq : %U, %W
          'W' => '%V', 
          // Month - no strf eq : n, t
          'F' => '%B', 'm' => '%m', 'M' => '%b',
          // Year - no strf eq : L; no date eq : %C, %g
          'o' => '%G', 'Y' => '%Y', 'y' => '%y',
          // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
          'a' => '%P', 'A' => '%p', 'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M', 's' => '%S',
          // Timezone - no strf eq : e, I, P, Z
          'O' => '%z', 'T' => '%Z',
          // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x 
          'U' => '%s'
      );
    
      return strtr((string)$date_format, $chars);
    }

    /**
     * Check if course has started.
     * 
     * 0 => open
     * 1 => not open yet
     * 2 => closed
     * 
     * @return   int
     * @since    0.0.1
     */
    public function course_is_open( $course_id = null ) {
      if( get_post_type( $course_id ) !== 'humble_lms_course' )
        return 3;

      $current_time = time();
      $timestamps = self::get_timestamps( $course_id );

      // Start and end not set
      if( empty( $timestamps['from'] ) && empty( $timestamps['to'] ) ) {
        return 0;
      }

      // Start and end set
      elseif( ! empty( $timestamps['from'] ) && ! empty( $timestamps['to'] ) ) {
        if( $timestamps['from'] <= $current_time && $timestamps['to'] >= $current_time ) {
          return 0;
        } elseif( $timestamps['from'] > $current_time ) {
          return 1;
        } elseif( $timestamps['to'] < $current_time ) {
          return 2;
        }
      }

      // Start set, end not set
      elseif( ! empty( $timestamps['from'] ) && empty( $timestamps['to'] ) ) {
        return $timestamps['from'] <= $current_time ? 0 : 1;
      }

      // Start not set, end set
      elseif( empty( $timestamps['from'] ) && ! empty( $timestamps['to'] ) ) {
        return $timestamps['to'] >= $current_time ? 0 : 2;
      }
    }

  }
  
}
