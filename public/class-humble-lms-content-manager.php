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
    }

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
        'lang' => $this->translator->current_language(),
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

      // Get translated post ids if custom fields are synchronized
      $translator = new Humble_LMS_Translator;

      foreach( $track_courses as $key => $course_id ) {
        $translated_post_id = $translator->get_translated_post_id( $course_id );

        if( $translated_post_id ) {
          $track_courses[$key] = $translated_post_id;
        } else {
          $track_courses = array_diff( $track_courses, array( $course_id ) );
        }
      }

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
    public function get_courses( $published = false ) {
      $args = array(
        'post_type' => 'humble_lms_course',
        'posts_per_page' => -1,
        'post_status' => $published ? 'publish' : 'any',
        $this->translator->current_language(),
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
      $translator = new Humble_LMS_Translator;
      $course_lessons = [];

      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' )
        return $course_lessons;

      $course_sections = self::get_course_sections( $course_id );

      foreach( $course_sections as $section ) {
        $lesson_ids = $section['lessons'];

        foreach( $lesson_ids as $lesson_id ) {
          $translated_post_id = $translator->get_translated_post_id( (int)$lesson_id );
          
          if( $translated_post_id ) {
            array_push( $course_lessons, $translated_post_id );
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

      $translator = new Humble_LMS_Translator;

      foreach( $course_sections as $section_key => $section ) {
        // Turn comma separated string into array of IDs
        $lessons = ! is_array( $section['lessons'] ) ? explode(',', $section['lessons'] ) : [];

        foreach( $lessons as $key => $lesson_id ) {
          $translated_post_id = $translator->get_translated_post_id( $lesson_id );

          if( $translated_post_id ) {
            $lessons[$key] = $translated_post_id;
          } else {
            $lessons = array_diff( $lessons, array( $lesson_id ) );
          }
        }

        foreach( $lessons as $key => $lesson_id ) {
          if( ! get_post( $lesson_id ) || get_post_status( $lesson_id ) !== $post_status ) {
            if( ( $key = array_search($lesson_id, $lessons ) ) !== false ) {
              unset($lessons[$key]);
            }
          }
        }

        $lessons = array_unique( $lessons );
        $course_sections[$section_key]['lessons'] = $lessons;
      }

      return $course_sections;
    }

    /**
     * Get quizzes (published / unpublished).
     *
     * @param   bool
     * @return  array
     * @since   0.0.1
     */
    public function get_quizzes( $published = false ) {
      $args = array(
        'post_type' => 'humble_lms_quiz',
        'posts_per_page' => -1,
        'post_status' => $published ? 'publish' : 'any',
        $this->translator->current_language(),
      );
  
      $quizzes = get_posts( $args );

      return $quizzes;
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
  
      // Get translated post ids if custom fields are synchronized
      $translator = new Humble_LMS_Translator;

      // foreach( $quiz_questions as $key => $quiz_id ) {
      //   $translated_post_id = $translator->get_translated_post_id( $quiz_id );

      //   if( $translated_post_id ) {
      //     $quiz_questions[$key] = $translated_post_id;
      //   } else {
      //     $quiz_questions = array_diff( $quiz_questions, array( $quiz_id ) );
      //   }
      // }
      
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

      // Get translated post ids if custom fields are synchronized
      $translator = new Humble_LMS_Translator;

      foreach( $lesson_quizzes as $key => $quiz_id ) {
        $translated_post_id = $translator->get_translated_post_id( $quiz_id );

        if( $translated_post_id ) {
          $lesson_quizzes[$key] = $translated_post_id;
        } else {
          $lesson_quizzes = array_diff( $lesson_quizzes, array( $quiz_id ) );
        }
      }

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
     * Get price of items that can be sold for a fixed price.
     * 
     * @return  float
     * @since   0.0.1
     */
    public static function get_price( $post_id, $vat = false ) {
      $price = 0.00;

      if( ! get_post( $post_id ) )
        return $price;

      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course'
      );

      if( ! in_array( get_post_type( $post_id ), $allowed_post_types ) )
        return $price;

      $price = get_post_meta($post_id, 'humble_lms_fixed_price', true);
      $price = number_format((float)$price, 2, '.', '');

      if( $price ) {
        $price = filter_var( $price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
      }

      // Value added tax
      if( ! $vat ) {
        return $price;
      }

      $options = get_option('humble_lms_options');

      if(! empty( $options['hasVAT'] ) && ! empty( $options['VAT'] ) ) {
        if( $options['hasVAT'] === 1 ) { // Inclusive of VAT
          $price = $price / (100 + (int)$options['VAT'] ) * 100;
        } else if( $options['hasVAT'] === 2 ) { // Exclusive of VAT
          $price = $price + ( $price / 100 * 19 );
        }
      }

      $price = number_format((float)$price, 2, '.', '');

      return $price;
    }

    /**
     * Check if user can upgrade membership
     * 
     * @return  bool
     * @since   0.0.1
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
      $user_membership_price = Humble_LMS_Admin::get_membership_price_by_slug( $user_membership );

      foreach( $memberships as $membership ) {
        $membership_price = Humble_LMS_Admin::get_membership_price_by_slug( $membership->post_name );
        if( floatval($membership_price) > $user_membership_price ) {
          return true;
        }
      }

      return false;
    }

  }
  
}
