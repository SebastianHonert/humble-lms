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

    public function __construct() {
      $this->translator = new Humble_LMS_Translator;
      $this->calculator = new Humble_LMS_Calculator;
    }

    /**
     * Get tracks (published / unpublished)
     *
     * @param   bool
     * @return  Array
     * @since   0.0.1
     */
    public function get_tracks( $published = false, $translation = true ) {
      $args = array(
        'post_type' => 'humble_lms_track',
        'posts_per_page' => -1,
        'post_status' => $published ? 'publish' : 'any',
        'lang' => $translation ? $this->translator->current_language() : '',
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
    public static function get_track_courses( $track_id, $published = false, $translation = true ) {
      $track_courses = [];

      if( ! $track_id || get_post_type( $track_id ) !== 'humble_lms_track' )
        return $track_courses;

      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', false );
      $track_courses = isset( $track_courses[0] ) && is_array( $track_courses[0] ) && ! empty( $track_courses[0] ) && ( isset( $track_courses[0][0] ) && $track_courses[0][0] !== '' ) ? $track_courses[0] : [];

      if( empty( $track_courses ) ) {
        return $track_courses;
      }

      // Remove unpublished
      if( $published ) {
        foreach( $track_courses as $key => $course_id ) {
          if( 'publish' !== get_post_status( $course_id ) ) {
            unset( $track_courses[$key] );
          }
        }
      }

      $track_courses = array_values( $track_courses );
      $track_courses = array_unique( $track_courses );
      
      return $track_courses;
    }

    /**
     * Get courses (published / unpublished).
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_courses( $published = false, $translation = true ) {
      $args = array(
        'post_type' => 'humble_lms_course',
        'posts_per_page' => -1,
        'post_status' => $published ? 'publish' : 'any',
        'lang' => $translation ? $this->translator->current_language() : '',
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
    public static function get_course_lessons( $course_id = null, $translation = true ) {
      $course_lessons = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_lessons;

      $course_sections = self::get_course_sections( $course_id, true );

      foreach( $course_sections as $section ) {
        $lesson_ids = $section['lessons'];

        foreach( $lesson_ids as $lesson_id ) {
          if( get_post_status( $lesson_id ) ) {
            array_push( $course_lessons, $lesson_id );
          }
        }
      }

      $course_lessons = array_unique( $course_lessons );

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
    public static function get_course_sections( $course_id = null, $published = true ) {
      $post_status = $published ? 'publish' : 'any';
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

        return $course_sections;
      }

      foreach( $course_sections as $section_key => $section ) {
        $lessons = ! is_array( $section['lessons'] ) ? explode(',', $section['lessons'] ) : [];

        foreach( $lessons as $key => $lesson_id ) {
          if( ! get_post( $lesson_id ) || get_post_status( $lesson_id ) !== $post_status ) {
            if( ( $key = array_search($lesson_id, $lessons ) ) !== false ) {
              unset($lessons[$key]);
            }
          }
        }

        $course_sections[$section_key]['lessons'] = array_unique( $lessons );
      }

      return $course_sections;
    }

    /**
     * Get all tracks that contain a single course.
     *
     * @param   int
     * @return  array
     * @since   0.0.1
     */
    public function get_tracks_by_course_id( $course_id = null ) {
      $tracks_by_course_id = [];

      if( ! $course_id || 'humble_lms_course' !== get_post_type( $course_id ) ) {
        return $tracks_by_course_id;
      }

      $tracks = $this->get_tracks( true, true );

      foreach( $tracks as $track ) {
        $courses = $this->get_track_courses( $track->ID );

        if( in_array( $course_id, $courses ) ) {
          array_push( $tracks_by_course_id, $track->ID );
        }
      }

      return $tracks_by_course_id;
    }

    /**
     * Get all courses that contain a single lesson.
     *
     * @param   int
     * @return  array
     * @since   0.0.2
     */
    public function get_courses_by_lesson_id( $lesson_id = null ) {
      $courses_by_lesson_id = [];

      if( ! $lesson_id || 'humble_lms_lesson' !== get_post_type( $lesson_id ) ) {
        return $courses_by_lesson_id;
      }

      $courses = $this->get_courses( true, true );

      foreach( $courses as $course ) {
        $lessons = $this->get_course_lessons( $course->ID );

        if( in_array( $lesson_id, $lessons ) ) {
          array_push( $courses_by_lesson_id, $course->ID );
        }
      }

      return $courses_by_lesson_id;
    }

    /**
     * Get parent track ID.
     * 
     * @param   int
     * @return  object
     * @since   0.0.1
     */
    public function get_parent_track( $course_id = null, $published = false ) {
      if( ! $course_id || 'humble_lms_course' !== get_post_type( $course_id ) ) {
        return false;
      }

      $track_ids = $this->get_tracks_by_course_id( $course_id );
      $track_id = count( $track_ids ) === 1 ? $track_ids[0] : false;

      if( ! $track_id ) {
        return false;
      }

      if( $published && get_post_status( $track_id ) !== 'publish' ) {
        return false;
      }

      $track = get_post( $track_id );

      return $track;
    }

    /**
     * Get parent course ID.
     * 
     * @param   int
     * @return  object
     * @since   0.0.2
     */
    public function get_parent_course( $lesson_id = null, $published = false ) {
      if( ! $lesson_id || 'humble_lms_lesson' !== get_post_type( $lesson_id ) ) {
        return false;
      }

      $course_ids = $this->get_courses_by_lesson_id( $lesson_id );
      $course_id = count( $course_ids ) === 1 ? $course_ids[0] : false;

      if( ! $course_id ) {
        return false;
      }

      if( $published && get_post_status( $course_id ) !== 'publish' ) {
        return false;
      }

      $course = get_post( $course_id );

      return $course;
    }

    /**
     * Get quizzes (published / unpublished).
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_quizzes( $published = false, $translation = true ) {
      $args = array(
        'post_type' => 'humble_lms_quiz',
        'posts_per_page' => -1,
        'post_status' => $published ? 'publish' : 'any',
        'lang' => $translation ? $this->translator->current_language() : '',
      );
  
      $quizzes = get_posts( $args );

      return $quizzes;
    }

    /**
     * Get quizzes in a single course.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_course_quizzes( $course_id = null ) {
      $course_quizzes = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_quizzes;

      $course_sections = self::get_course_sections( $course_id );

      foreach( $course_sections as $section ) {
        $lesson_ids = $section['lessons'];

        foreach( $lesson_ids as $lesson_id ) {
            $lesson_quizzes = self::get_lesson_quizzes( $lesson_id );

            foreach( $lesson_quizzes as $quiz_id ) {
              array_push( $course_quizzes, $quiz_id );
            }
        }
      }

      $course_quizzes = array_unique( $course_quizzes );

      return $course_quizzes;
    }

    /**
     * Get all courses that contain a single quiz.
     *
     * @param   int
     * @return  array
     * @since   0.0.1
     */
    public function get_courses_by_quiz_id( $quiz_id = null ) {
      $courses_by_quiz_id = [];

      if( ! $quiz_id || 'humble_lms_quiz' !== get_post_type( $quiz_id ) ) {
        return $courses_by_quiz_id;
      }

      $courses = $this->get_courses( true, true );

      foreach( $courses as $course ) {
        $quizzes = $this->get_course_quizzes( $course->ID );

        if( in_array( $quiz_id, $quizzes ) ) {
          array_push( $courses_by_quiz_id, $course->ID );
        }
      }

      return $courses_by_quiz_id;
    }

    /**
     * Get quizzes for a single track id.
     *
     * @param   int|bool
     * @return  array
     * @since   0.0.1
     */
    public static function get_track_quizzes( $track_id, $published = false ) {
      $track_quizzes = [];

      if( ! $track_id || get_post_type( $track_id ) !== 'humble_lms_track' )
        return $track_quizzes;

      $track_courses = self::get_track_courses( $track_id );
      
      foreach( $track_courses as $course_id ) {
        $course_quizzes = self::get_course_quizzes( $course_id );
        foreach( $course_quizzes as $quiz_id ) {
          array_push( $track_quizzes, $quiz_id );
        }
      }

      $track_quizzes = array_unique( $track_quizzes );
      
      return $track_quizzes;
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
     * Get track IDs by parameter.
     * 
     * @return  array
     * @since   0.0.1
     */
    public function find_tracks_by( $by = null, $id = null ) {
      if( ( ! $by ) || ( ! $id ) ) {
        return [];
      }

      $track_ids = [];
      $allowed_by = array(
        'course'
      );

      if( ! in_array( $by, $allowed_by ) ) {
        return $track_ids;
      }

      switch( $by ) {
        case 'course':
          if( get_post_type( $id ) !== 'humble_lms_course' ) {
            return $track_ids;
          }

          $tracks = $this->get_tracks( true );

          foreach( $tracks as $track ) {
            $track_courses = $this->get_track_courses( $track->ID );
            if( in_array( $id, $track_courses ) ) {
              array_push( $track_ids, $track->ID );
            }
          }

          return $track_ids;

        default:
          return $track_ids;
      }

    }

    /**
     * Get questions for a single quiz.
     *
     * @param   int|bool
     * @return  Array
     * @since   0.0.1
     */
    public static function get_quiz_questions( $quiz_id, $published = false ) {
      $quiz_questions = [];

      if( ! $quiz_id || get_post_type( $quiz_id ) !== 'humble_lms_quiz' )
        return $quiz_questions;

      $quiz_questions = get_post_meta( $quiz_id, 'humble_lms_quiz_questions', false );
      $quiz_questions = isset( $quiz_questions[0] ) && ! empty( $quiz_questions[0] ) ? $quiz_questions[0] : [];
      $quiz_questions = array_unique( $quiz_questions );

      return $quiz_questions;
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
     * @param   int
     * @return  Array
     * @since   0.0.1
     */
    public static function get_lesson_quizzes( $lesson_id ) {
      $lesson_quizzes = [];

      if( ! $lesson_id || get_post_type( $lesson_id ) !== 'humble_lms_lesson' )
        return $lesson_quizzes;

      $lesson_quizzes = get_post_meta( $lesson_id, 'humble_lms_quizzes', false );
      $lesson_quizzes = isset( $lesson_quizzes[0] ) && ! empty( $lesson_quizzes[0] ) && ( isset( $lesson_quizzes[0][0] ) && $lesson_quizzes[0][0] !== '' ) ? $lesson_quizzes[0] : [];
      $lesson_quizzes = array_unique( $lesson_quizzes );

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
      if( $locale === 'de_DE_formal' || $locale === 'de_DE' ) {
        setlocale(LC_ALL, 'de_DE', 'de', 'de_DE.utf8', 'de_DE@euro');
      } else {
        setlocale(LC_ALL, $locale, 'en_GB', 'en_US');
      }

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

    /**
     * Get memberships sorted by price.
     * 
     * @since   0.0.1
     */
    public static function get_memberships( $array = true, $publish = true ) {
      $post_status = $publish ? 'publish' : 'any';

      $memberships = get_posts( array(
        'post_type' => 'humble_lms_mbship',
        'post_status' => $post_status,
        'posts_per_page' => -1,
        'meta_key' => 'humble_lms_fixed_price',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'lang' =>  '',
      ) );

      if( ! $array ) {
        return $memberships;
      }

      $memberships_array = array();

      foreach( $memberships as $membership ) {
        array_push( $memberships_array, $membership->post_name );
      }

      return $memberships_array; 
    }

    /**
     * Get memberships.
     * 
     * @since   0.0.1
     */
    public static function get_membership_by_slug( $slug = null ) {
      if( ! $slug ) {
        return;
      }

      $args = array(
        'name' => $slug,
        'post_type'   => 'humble_lms_mbship',
        'post_status' => 'publish',
        'numberposts' => 1,
        'lang' => '',
      );

      $posts = get_posts( $args );

      return $posts ? $posts[0] : false;
    }

    /**
     * Check if user can upgrade membership
     * 
     * @return  bool
     * @since   0.0.2
     */
    public static function user_can_upgrade_membership( $user_id = null ) {
      if( ! $user_id ) {
        if( is_user_logged_in() ) {
          $user_id = get_current_user_id();
        } else {
          return false;
        }
      }

      $args = array(
        'post_type' => 'humble_lms_mbship',
        'post_status' => 'publish',
        'posts_per_page' => -1,
      );

      $memberships = get_posts( $args );
      $user_membership = get_user_meta( $user_id, 'humble_lms_membership', true );

      $calculator = new Humble_LMS_Calculator;
      $user_membership_price = $calculator->get_membership_price_by_slug( $user_membership );

      foreach( $memberships as $membership ) {
        $membership_price = $calculator->get_membership_price_by_slug( $membership->post_name );
        if( floatval($membership_price) > $user_membership_price ) {
          return true;
        }
      }

      return false;
    }

    /**
     * Check if a track/course is for sale.
     * 
     * @return Boolean
     * @since 0.0.3
     */
    public function is_for_sale( $post_id = null ) {
      if( ! get_post( $post_id ) ) {
        return false;
      }

      $post_type = get_post_type( $post_id );
      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course'
      );

      if( ! in_array( $post_type, $allowed_post_types ) ) {
        return false;
      }

      $is_for_sale = get_post_meta( $post_id, 'humble_lms_is_for_sale', true );

      return 1 == $is_for_sale;
    }

    /**
     * Get content title by reference ID (humble_lms_txn)
     * 
     * @return String
     * @since 0.0.3
     */
    public function get_content_description_by_reference_id( $reference_id = null ) {
      $title = '';

      if( ! $reference_id ) {
        return '';
      }

      $post = get_post( $reference_id );

      if( $post ) {
        $post_type = get_post_type( $post->ID );

        if( $post_type == 'humble_lms_track' ) {
          $content_type = '(' . __('Track', 'humble-lms') . ')';
        } else {
          $content_type = '(' . __('Course', 'humble-lms') . ')';
        }

        return $post->post_title . ' ' . $content_type;
      }

      $membership = $this->get_membership_by_slug( $reference_id );

      if( $membership ) {
        return $membership->post_title . ' (' . __('Membership', 'humble-lms') . ')';
      }

      return __('Unknown', 'humble-lms');
    }

    /**
     * Get invoice template data.
     * 
     * @return Array
     * @since 0.0.3
     */
    public function invoice_template_data() {
      $options = get_option('humble_lms_options');

      $template_data = array(
        'seller_info' => $options['seller_info'],
        'seller_logo' => $options['seller_logo'],
        'invoice_prefic' => $options['invoice_prefix'],
        'invoice_text_before' => $options['invoice_text_before'],
        'invoice_text_after' => $options['invoice_text_after'],
        'invoice_text_footer' => $options['invoice_text_footer'],
      );

      return $template_data;
    }

    /**
     * Get transaction details by post ID.
     * 
     * @return Array
     * @since 0.0.3
     */
    public function transaction_details( $post_id = null ) {
      $transaction_details = array();

      if( 'humble_lms_txn' !== get_post_type( $post_id ) ) {
        return;
      }

      $user = new Humble_LMS_Public_User;

      $order_details = get_post_meta( $post_id, 'humble_lms_order_details', false );
      $order_details = isset( $order_details[0] ) ? $order_details[0] : $order_details;

      $user_id = isset( $order_details['user_id'] ) ? (int)$order_details['user_id'] : null;
      $user_id_txn = get_post_meta( $post_id, 'humble_lms_txn_user_id', true );

      $transaction = array(
        'user_id' => $user_id,
        'txn_user_id' => $user_id_txn,
        'order_details' => $order_details,
        'first_name' => isset( $order_details['first_name'] ) ? $order_details['first_name'] : '',
        'last_name' => isset( $order_details['last_name'] ) ? $order_details['last_name'] : '',
        'company' => isset( $order_details['company'] ) ? $order_details['company'] : '',
        'postcode' => isset( $order_details['postcode'] ) ? $order_details['postcode'] : '',
        'city' => isset( $order_details['city'] ) ? $order_details['city'] : '',
        'address' => isset( $order_details['address'] ) ? $order_details['address'] : '',
        'country' => isset( $order_details['country'] ) ? $order_details['country'] : '',
        'vat_id' => isset( $order_details['vat_id'] ) ? $order_details['vat_id'] : '',
        'order_id' => isset( $order_details['order_id'] ) ? $order_details['order_id'] : '',
        'email_address' => isset( $order_details['email_address'] ) ? $order_details['email_address'] : '',
        'payer_id' => isset( $order_details['payer_id'] ) ? $order_details['payer_id'] : '',
        'status' => isset( $order_details['status'] ) ? $order_details['status'] : '',
        'payment_service_provider' => isset( $order_details['payment_service_provider'] ) ? $order_details['payment_service_provider'] : '',
        'create_time' => isset( $order_details['create_time'] ) ? $order_details['create_time'] : '',
        'update_time' => isset( $order_details['update_time'] ) ? $order_details['update_time'] : '',
        'given_name' => isset( $order_details['given_name'] ) ? $order_details['given_name'] : '',
        'surname' => isset( $order_details['surname'] ) ? $order_details['surname'] : '',
        'reference_id' => isset( $order_details['reference_id'] ) ? $order_details['reference_id'] : '',
        'currency_code' => isset( $order_details['currency_code'] ) ? $order_details['currency_code'] : '',
        'value' => isset( $order_details['value'] ) ? $order_details['value'] : '',
        'description' => isset( $order_details['description'] ) ? $order_details['description'] : '',
        'invoice_number' => isset( $order_details['invoice_number'] ) ? $order_details['invoice_number'] : null,
        'has_vat' => isset( $order_details['has_vat'] ) ? $order_details['has_vat'] : $this->calculator->has_vat(),
        'vat' => isset( $order_details['vat'] ) ? $order_details['vat'] : $this->calculator->get_vat(),
        'coupon_id' => isset( $order_details['coupon_id'] ) ? $order_details['coupon_id'] : '',
        'coupon_code' => isset( $order_details['coupon_code'] ) ? $order_details['coupon_code'] : '',
        'coupon_type' => isset( $order_details['coupon_type'] ) ? $order_details['coupon_type'] : '',
        'coupon_value' => isset( $order_details['coupon_value'] ) ? $order_details['coupon_value'] : '',
      );

      return $transaction;
    }

    /**
     * Create the HTML for a single invoice.
     * 
     * @return String
     * @since 0.0.3
     */
    public function create_invoice_html( $transaction_id = null ) {
      if( 'humble_lms_txn' !== get_post_type( $transaction_id ) ) {
        return;
      }

      $user_manager = new Humble_LMS_Public_User;
      $transaction = $this->transaction_details( $transaction_id );

      $date = date_parse( $transaction['create_time'] );
      $date = $date['year'] . '-' . $date['month'] . '-' . $date['day'];

      $invoice_template_data = $this->invoice_template_data();
      $css = dirname( plugin_dir_url( __FILE__ ) ) . '/public/css/invoice/invoice.css';

      $sum = $this->calculator->sum_transaction( $transaction_id );
      
      $content = '<img id="humble-lms-seller-logo" src="' . $invoice_template_data['seller_logo'] . '" alt="" />';

      $content .= '<div id="humble-lms-seller-customer-info">';
        $content .= '<div id="humble-lms-seller-info">' . $invoice_template_data['seller_info'] . '</div>';
        $content .= '<div id="humble-lms-customer-info">';
          $content .= '<p>';
            $content .= $transaction['invoice_number'] ? '<p>' . __('Invoice #', 'humble-lms') . ' ' . $transaction['invoice_number'] . '<br>' : '';
            $content .= __('Date', 'humble-lms') . ': ' . $date . '<br>';
            $content .= __('Due', 'humble-lms') . ': ' . $date;
          $content .= '</p>';
          $content .= $transaction['company'] ? '<strong>' . $transaction['company'] . '</strong><br>' : '';
          $content .= $transaction['first_name'] . ' ' . $transaction['last_name'] . '<br>';
          $content .= $transaction['address'] . '<br>';
          $content .= $transaction['postcode'] . ' ' . $transaction['city'] . '<br>';
          $content .= $transaction['country'] . ' ' . $transaction['country'] . '<br>';
          $content .= $transaction['vat_id'] ? $transaction['vat_id'] : '';
        $content .= '</div>';
      $content .= '</div>';

      $content .= '<h1>' . __('Invoice', 'humble-lms') . '</h1>';

      $content .= '<div id="humble-lms-invoice-text-before">' . wpautop( $invoice_template_data['invoice_text_before'] ) . '</div>';

      $content .= '<table id="humble-lms-invoice-table">';
      $content .= '<tr>';
        $content .= '<th>' . __('Description', 'humble-lms') . '</th>';
        $content .= '<th>' . __('Quantity', 'humble-lms') . '</th>';
        $content .= '<th>' . __('Price', 'humble-lms') . '</th>';
        $content .= '<th>' . __('Amount', 'humble-lms') . '</th>';
      $content .= '</tr>';
      $content .= '<tr>';
        $content .= '<td>' . $transaction['description'] . '</td>';
        $content .= '<td>1</td>';
        $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['price'] . '</td>';
        $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['price'] . '</td>';
      $content .= '</tr>';

      if( $this->calculator->transaction_has_coupon( $transaction_id ) ) {
        $content .= '<tr id="humble-lms-discount">';
          $content .= '<td colspan="2">' . __('Discount', 'humble-lms') . '</td>';
          $content .= '<td>- ' . $sum['discount_string'] . '</td>';
          $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['discount'] . '</td>';
        $content .= '</tr>';
      }

      $content .= '<tr id="humble-lms-subtotal">';
        $content .= '<td colspan="3">' . __('Subtotal', 'humble-lms') . '</td>';
        $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['subtotal'] . '</td>';
      $content .= '</tr>';
      $content .= '<tr id="humble-lms-taxes">';
        $content .= '<td colspan="2">' . __('VAT', 'humble-lms') . '</td>';
        $content .= '<td>' . $sum['vat_string'] . '</td>';
        $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['vat_diff'] . '</td>';
      $content .= '</tr>';
      $content .= '<tr id="humble-lms-total">';
        $content .= '<td colspan="3">' . __('Total', 'humble-lms') . '</td>';
        $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['total'] . '</td>';
      $content .= '</tr>';

      $content .= '</table>';

      $content .= '<div id="humble-lms-invoice-text-after">' . wpautop( $invoice_template_data['invoice_text_after'] ) . '</div>';
      $content .= '<div id="humble-lms-invoice-text-footer"><div>' . wpautop( $invoice_template_data['invoice_text_footer'] ) . '</div></div>';

        
      $html = '<!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta http-equiv="X-UA-Compatible" content="ie=edge">
          <title>' . __('Invoice', 'humble-lms') . '</title>
          <link rel="stylesheet" href="' . $css . '">
        </head>
        <body class="humble-lms-invoice">
          <div id="humble-lms-invoice">' . wpautop( $content ) . '</div>
        </body>
      </html>';

      return $html;
    }

  }
  
}
