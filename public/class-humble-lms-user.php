<?php
/**
 * This class provides front-end user data functionality.
 *
 * Creates the various functions used for user data management via front-end and AJAX interactions
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_User' ) ) {

  class Humble_LMS_Public_User {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     */
    public function __construct() {
      $this->content_manager = new Humble_LMS_Content_Manager;
      $this->translator = new Humble_LMS_Translator;
    }

    /**
     * Get user first name.
     * 
     * @return String
     * @since 0.0.3
     */
    public function first_name( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $user_info = get_userdata( $user_id );

      return isset( $user_info->first_name ) ? $user_info->first_name : '';
    }

    /**
     * Get user last name.
     * 
     * @return String
     * @since 0.0.3
     */
    public function last_name( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $user_info = get_userdata( $user_id );

      return isset( $user_info->last_name ) ? $user_info->last_name : '';
    }

    /**
     * Get user academic title.
     * 
     * @return String
     * @since 0.0.3
     */
    public function title( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $title = get_user_meta( $user_id, 'humble_lms_title', true );

      return $title ? $title : '';
    }

    /**
     * Get user address.
     * 
     * @return String
     * @since 0.0.3
     */
    public function address( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $address = get_user_meta( $user_id, 'humble_lms_address', true );

      return isset( $address ) ? $address : '';
    }

    /**
     * Get user postcode.
     * 
     * @return String
     * @since 0.0.3
     */
    public function postcode( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $postcode = get_user_meta( $user_id, 'humble_lms_postcode', true );

      return isset( $postcode ) ? $postcode : '';
    }

    /**
     * Get user city.
     * 
     * @return String
     * @since 0.0.3
     */
    public function city( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $city = get_user_meta( $user_id, 'humble_lms_city', true );

      return isset( $city ) ? $city : '';
    }

    /**
     * Get user country.
     * 
     * @return String
     * @since 0.0.3
     */
    public function country( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $country = get_user_meta( $user_id, 'humble_lms_country', true );

      return isset( $country ) ? $country : '';
    }

    /**
     * Get user company.
     * 
     * @return String
     * @since 0.0.3
     */
    public function company( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $company = get_user_meta( $user_id, 'humble_lms_company', true );

      return isset( $company ) ? $company : '';
    }

    /**
     * Get user vat ID.
     * 
     * @return String
     * @since 0.0.3
     */
    public function vat_id( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return '';
      }

      $vat_id = get_user_meta( $user_id, 'humble_lms_vat_id', true );

      return isset( $vat_id ) ? $vat_id : '';
    }

    /**
     * Checks if a user has completed a single lesson.
     *
     * @since    0.0.1
     */
    public function completed_lesson( $user_id = null, $lesson_id = null ) {
      if( ! $lesson_id )
        return;

      if( ! $user_id )
        return;

      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
      
      return $lessons_completed ? in_array( $lesson_id, $lessons_completed ) : '';
    }

    /**
     * Checks if a user has completed a course.
     *
     * @return Boolean
     * @since    0.0.1
     */
    public function completed_course( $course_id ) {
      if( ! is_user_logged_in() || ! $course_id )
        return;

      $user_id = get_current_user_id();

      $course_lessons = $this->content_manager->get_course_lessons( $course_id );

      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );

      if( ( ! $course_lessons ) || ( ! $lessons_completed ) ) {
        return;
      }

      sort( $course_lessons );
      sort( $lessons_completed );

      return empty( array_diff( $course_lessons, $lessons_completed ) );
    }

    /**
     * Checks if a user has completed a track.
     *
     * @since    0.0.1
     */
    public function completed_track( $track_id ) {
      if( ! is_user_logged_in() || ! $track_id )
        return;

      $user_id = get_current_user_id();

      $track_courses = $this->content_manager->get_track_courses( $track_id );

      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', true );

      if( ( ! $track_courses ) || ( ! $courses_completed ) ) {
        return;
      }

      sort( $track_courses );
      sort( $courses_completed );

      return empty( array_diff( $track_courses, $courses_completed ) );
    }

    /**
     * Returns an array of completed track IDs.
     *
     * @param int|string
     * @return  array
     * @since   0.0.1
     */
    public function completed_tracks( $user_id = null, $published = false ) {
      if( ! $user_id )
        return [];

      $completed_tracks = get_user_meta( $user_id, 'humble_lms_tracks_completed', false );
      $completed_tracks = isset( $completed_tracks[0] ) ? $completed_tracks[0] : [];
      
      if( empty( $completed_tracks ) )
        return [];
      
      if( ! $published ) {
        return $completed_tracks;
      }

      foreach( $completed_tracks as $key => $track ) {
        if( get_post_status( $track ) !== 'publish' ) {
          unset( $completed_tracks[$key] );
        }
      }

      return $completed_tracks;
    }

    /**
     * Returns an array of completed courses.
     *
     * @return  array
     * @since   0.0.1
     */
    public function completed_courses( $user_id = null, $published = false ) {
      if( ! $user_id )
        return [];
  
      $completed_courses = get_user_meta( $user_id, 'humble_lms_courses_completed', false );
      $completed_courses = isset( $completed_courses[0] ) ? $completed_courses[0] : [];
      
      if( empty( $completed_courses ) )
        return [];

      if( ! $published ) {
        return $completed_courses;
      }

      foreach( $completed_courses as $key => $course ) {
        if( get_post_status( $course ) !== 'publish' ) {
          unset( $completed_courses[$key] );
        }
      }

      
      return $completed_courses;
    }

    /**
     * Check if user completed all tracks.
     *
     * @return  bool
     * @since   0.0.1
     */
    public function completed_all_tracks( $user_id = null ) {
      if( ! $user_id )
        return false;

      $track_ids = $this->content_manager->get_track_ids( true );
      $completed_tracks = get_user_meta( $user_id, 'humble_lms_tracks_completed', false );
      $completed_tracks = isset( $completed_tracks[0] ) ? $completed_tracks[0] : [];

      return empty( array_diff( $track_ids, $completed_tracks ) );
    }

    /**
     * Check if user completed all courses.
     *
     * @return  bool
     * @since   0.0.1
     */
    public function completed_all_courses( $user_id = null ) {
      if( ! $user_id )
        return false;

      $course_ids = $this->content_manager->get_course_ids( true );
      $completed_courses = get_user_meta( $user_id, 'humble_lms_courses_completed', false );
      $completed_courses = isset( $completed_courses[0] ) ? $completed_courses[0] : [];

      return empty( array_diff( $course_ids, $completed_courses ) );
    }

    /**
     * Returns an array of completed lessons.
     *
     * @return  array
     * @since   0.0.1
     */
    public function completed_lessons( $user_id = null, $published = false ) {
      if( ! $user_id )
        return [];
  
      $completed_lessons = get_user_meta( $user_id, 'humble_lms_lessons_completed', false );
      $completed_lessons = isset( $completed_lessons[0] ) ? $completed_lessons[0] : [];
      
      if( empty( $completed_lessons ) )
        return [];

      if( ! $published ) {
        return $completed_lessons;
      }

      foreach( $completed_lessons as $key => $lesson ) {
        if( get_post_status( $lesson ) !== 'publish' ) {
          unset( $completed_lessons[$key] );
        }
      }

      
      return $completed_lessons;
    }

    /**
     * Returns an array of completed quizzes.
     *
     * @return  array
     * @since   0.0.1
     */
    public function completed_quizzes( $user_id = null, $published = false ) {
      if( ! $user_id )
        return [];
  
      $completed_quizzes = get_user_meta( $user_id, 'humble_lms_quizzes_completed', false );
      $completed_quizzes = isset( $completed_quizzes[0] ) ? $completed_quizzes[0] : [];

      if( empty( $completed_quizzes ) )
        return [];

      if( ! $published ) {
        return $completed_quizzes;
      }

      foreach( $completed_quizzes as $key => $quiz ) {
        if( get_post_status( $quiz ) !== 'publish' ) {
          unset( $completed_quizzes[$key] );
        }
      }

      return $completed_quizzes;
    }

    /**
     * Checks if a user has completed a quiz.
     *
     * @since    0.0.1
     */
    public function completed_quiz( $quiz_ids = null ) {
      if( ! is_user_logged_in() || ! $quiz_ids )
        return;

      if( ! is_array( $quiz_ids ) ) {
        $quiz_ids = [$quiz_ids];
      }

      $user_id = get_current_user_id();
      $completed_quizzes = $this->completed_quizzes( get_current_user_ID() );

      if( ! is_array( $completed_quizzes ) ) {
        return;
      }

      foreach( $quiz_ids as $id ) {
        if( 'publish' !== get_post_status( $id ) ) {
          continue;
        }

        if( ! in_array( $id, $completed_quizzes ) ) {
          return false;
        }
      }

      return true;
    }

    /**
     * Check if user completed all quizzes in a course.
     *
     * @param   int
     * @return  array
     * @since   0.0.1
     */
    public function completed_all_course_quizzes( $user_id = null, $quiz_id = null ) {
      $completed_courses = [];

      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return $completed_courses;
        } else {
          $user_id = get_current_user_id();
        }
      }

      if( 'humble_lms_quiz' !== get_post_type( $quiz_id ) ) {
        return $completed_courses;
      }

      $courses_by_quiz_id = $this->content_manager->get_courses_by_quiz_id( $quiz_id );

      if( empty( $courses_by_quiz_id ) ) {
        return $completed_courses;
      }

      foreach( $courses_by_quiz_id as $course_id ) {
        $completed_quizzes = [];
        $course_quizzes = $this->content_manager->get_course_quizzes( $course_id );

        foreach( $course_quizzes as $course_quiz_id ) {
          if( ! $this->completed_quiz( $course_quiz_id ) ) {
            continue;
          } else {
            array_push( $completed_quizzes, $course_quiz_id );
          }
        }

        if( count( $completed_quizzes ) === count( $course_quizzes ) ) {
          array_push( $completed_courses, $course_id );
        }
      }

      return $completed_courses;
    }

    /**
     * Check if user completed all quizzes in a track.
     *
     * @param   int
     * @return  array
     * @since   0.0.1
     */
    public function completed_all_track_quizzes( $user_id = null, $quiz_id ) {
      $completed_tracks = [];

      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return $completed_tracks;
        } else {
          $user_id = get_current_user_id();
        }
      }

      if( 'humble_lms_quiz' !== get_post_type( $quiz_id ) ) {
        return $completed_tracks;
      }

      $completed_courses = $this->completed_all_course_quizzes( $user_id, $quiz_id );
      $tracks = $this->content_manager->get_tracks( true, true );

      if( empty( $completed_courses ) || empty( $tracks ) ) {
        return $completed_tracks;
      }

      foreach( $tracks as $track ) {
        $track_courses = $this->content_manager->get_track_courses( $track->ID );

        $track_courses = sort( $track_courses );
        $completed_courses = sort( $completed_courses );

        if( $track_courses === $completed_courses ) {
          array_push( $completed_tracks, $track->ID );
        }
      }

      return $completed_tracks;
    }

    /**
     * Updates the lessons, courses, and tracks completed by the current user.
     * Returns an array of completed IDs.
     *
     * @return  array
     * @param   int
     * @since   0.0.1
     */
    public function mark_lesson_complete( $lesson_id ) {
      if( ! is_user_logged_in() )
        return [];

      $user_id = get_current_user_id();

      $completed = array( [], [], [], [], [] ); // lesson, courses, tracks, awards, certificates

      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', true );
      if( ! is_array( $lessons_completed ) ) $lessons_completed = array();

      if( $lesson_id && ! in_array( $lesson_id, $lessons_completed ) ) {
        $lessons_completed[] = $lesson_id;
        array_push($completed[0], $lesson_id);
      } else {
        if( ( $key = array_search( $lesson_id, $lessons_completed ) ) !== false ) {
          unset( $lessons_completed[$key] );
        }
      }
      
      update_user_meta( $user_id, 'humble_lms_lessons_completed', $lessons_completed );

      // Complete courses that include the completed lesson
      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', true );
      if( ! is_array( $courses_completed ) ) $courses_completed = array();

      $courses = $this->content_manager->get_courses( true );

      foreach( $courses as $course ) {
        if( $this->completed_course( $course->ID ) ) {
          if( ! in_array( $course->ID, $courses_completed ) ) {
            $courses_completed[] = $course->ID;
            array_push($completed[1], $course->ID);
          }
        } else {
          if( ( $key = array_search( $course->ID, $courses_completed ) ) !== false ) {
            unset( $courses_completed[$key] );
          }
        }

        update_user_meta( $user_id, 'humble_lms_courses_completed', $courses_completed );

        // Complete tracks that include the completed course
        $tracks_completed = get_user_meta( $user_id, 'humble_lms_tracks_completed', true );
        if( ! is_array( $tracks_completed ) ) $tracks_completed = array();

        $tracks = $this->content_manager->get_tracks( true );

        foreach( $tracks as $track ) {
          if( $this->completed_track( $track->ID ) ) {
            if( ! in_array( $track->ID, $tracks_completed ) ) {
              $tracks_completed[] = $track->ID;
              array_push($completed[2], $track->ID);
            }
          } else {
            if( ( $key = array_search( $track->ID, $tracks_completed ) ) !== false ) {
              unset( $tracks_completed[$key] );
            }
          }

          update_user_meta( $user_id, 'humble_lms_tracks_completed', $tracks_completed );
        }
      }

      // Perform acvtivities attached to completed content and add them to the completed array
      $completed = $this->perform_activities( $completed );

      return json_encode( $completed );
    }

    /**
     * Perform activities based on the completed content.
     * 
     * array( [0], [1], [2], [3], [4], [5] ) => lesson, courses, tracks, awards, certificates, quizzes
     * 
     * humble_lms_activity_trigger = lesson, course, track, quiz
     * humble_lms_activity_trigger_lesson = lesson ID
     * humble_lms_activity_trigger_course = course ID
     * humble_lms_activity_trigger_track = track ID
     * humble_lms_activity_trigger_quiz = quiz ID
     * 
     * humble_lms_activity_action = award, email, certificate
     * humble_lms_activity_action_award = award ID
     * humble_lms_activity_action_certificate = certificate ID
     * humble_lms_activity_action_email = email ID
     *
     * @return  array
     * @param   mixed
     * @since   0.0.1
     */
    public function perform_activities( $completed, $percent = 0, $current_quiz_ids = [] ) {
      if( ! is_user_logged_in() )
        return [];

      $user = wp_get_current_user();
      $user_completed_all_tracks = false;
      $user_completed_all_courses = false;
      $user_completed_track_quizzes = [];
      $user_completed_course_quizzes = [];

      foreach( $completed as $key => $ids ) {
        foreach( $ids as $id ) {
          switch( $key ) {
            case 0:
              $humble_lms_activity_trigger = 'user_completed_lesson';
              $content_type = 'lesson';
              break;
            case 1:
              $humble_lms_activity_trigger = 'user_completed_course';
              $content_type = 'course';
              break;
            case 2:
              $humble_lms_activity_trigger = 'user_completed_track';
              $content_type = 'track';
              break;
            case 5:
              $humble_lms_activity_trigger = 'user_completed_quiz';
              $content_type = 'quiz';
              break;
          }

          $args = array(
            'post_type' => 'humble_lms_activity',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
              'relation' => 'AND',
              array(
                'key' => 'humble_lms_activity_trigger',
                'value' => $humble_lms_activity_trigger,
              ),
              array(
                'key' => 'humble_lms_activity_trigger_' . $content_type,
                'value' => $id,
              )
            ),
            'lang' => $this->translator->current_language(),
          );
    
          $activities = get_posts( $args );

          // User completes a track – check if all tracks completed
          if( $humble_lms_activity_trigger === 'user_completed_track' && ! $user_completed_all_tracks ) {
            if( $this->completed_all_tracks( $user->ID ) ) {
              $user_completed_all_tracks = true;
              $args = array(
                'post_type' => 'humble_lms_activity',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                  array(
                    'key' => 'humble_lms_activity_trigger',
                    'value' => 'user_completed_all_tracks',
                  ),
                ),
                'lang' => $this->translator->current_language(),
              );

              $activities_completed_all_tracks = get_posts( $args );
              $activities = array_merge( $activities, $activities_completed_all_tracks );
            }
          }

          // User completes a course – check if all courses completed
          if( $humble_lms_activity_trigger === 'user_completed_course' && ! $user_completed_all_courses ) {
            if( $this->completed_all_courses( $user->ID ) ) {
              $user_completed_all_courses = true;
              $args = array(
                'post_type' => 'humble_lms_activity',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                  array(
                    'key' => 'humble_lms_activity_trigger',
                    'value' => 'user_completed_all_courses',
                  ),
                ),
                'lang' => $this->translator->current_language(),
              );

              $activities_completed_all_courses = get_posts( $args );
              $activities = array_merge( $activities, $activities_completed_all_courses );
            }
          }

          // Check quiz result against required percentage
          if( $humble_lms_activity_trigger === 'user_completed_quiz' ) {
            foreach( $activities as $_key => $activity ) {
              $humble_lms_activity_trigger_quiz = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_quiz', true);
              $trigger_quiz_percent = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_quiz_percent', true);

              if( ! in_array( $id, $current_quiz_ids ) ) {
                unset( $activities[$_key] );
              } else if( in_array( $id, $current_quiz_ids ) && $percent < $trigger_quiz_percent ) {
                unset( $activities[$_key] );
              }
            }
          }

          // User completes a quiz – check if all quizzes in course completed
          if( $humble_lms_activity_trigger === 'user_completed_quiz' && ! in_array( $id, $user_completed_course_quizzes ) ) {
            $completed_all_course_quizzes = $this->completed_all_course_quizzes( $user->ID, $id );

            if( ! empty( $completed_all_course_quizzes ) ) {
              $args = array(
                'post_type' => 'humble_lms_activity',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                  array(
                    'key' => 'humble_lms_activity_trigger',
                    'value' => 'user_completed_all_course_quizzes',
                  ),
                  array(
                    'key' => 'humble_lms_activity_trigger_all_course_quizzes',
                    'value' => $completed_all_course_quizzes,
                    'compare' => 'IN',
                  ),
                ),
                'lang' => $this->translator->current_language(),
              );

              $activities_completed_all_course_quizzes = get_posts( $args );

              $quiz = new Humble_LMS_Quiz;

              foreach( $activities_completed_all_course_quizzes as $_key => $activity ) {
                $humble_lms_activity_trigger_all_course_quizzes = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_all_course_quizzes', true);
                $trigger_all_course_quizzes_percent = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_all_course_quizzes_percent', true);
                $percent_average = $quiz->course_results( $user->ID, $humble_lms_activity_trigger_all_course_quizzes );

                if( $percent_average < $trigger_all_course_quizzes_percent ) {
                  unset( $activities_completed_all_course_quizzes[$_key] );
                }
              }

              $activities = array_merge( $activities, $activities_completed_all_course_quizzes );
            }
          }

          // User completes a quiz – check if all quizzes in track completed
          if( $humble_lms_activity_trigger === 'user_completed_quiz' && ! in_array( $id, $user_completed_track_quizzes ) ) {
            $completed_all_track_quizzes = $this->completed_all_track_quizzes( $user->ID, $id );

            if( ! empty( $completed_all_track_quizzes ) ) {
              $args = array(
                'post_type' => 'humble_lms_activity',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                  array(
                    'key' => 'humble_lms_activity_trigger',
                    'value' => 'user_completed_all_track_quizzes',
                  ),
                  array(
                    'key' => 'humble_lms_activity_trigger_all_track_quizzes',
                    'value' => $completed_all_track_quizzes,
                    'compare' => 'IN',
                  ),
                ),
                'lang' => $this->translator->current_language(),
              );

              $activities_completed_all_track_quizzes = get_posts( $args );

              $quiz = new Humble_LMS_Quiz;

              foreach( $activities_completed_all_track_quizzes as $_key => $activity ) {
                $humble_lms_activity_trigger_all_track_quizzes = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_all_track_quizzes', true);
                $trigger_all_track_quizzes_percent = (int)get_post_meta($activity->ID, 'humble_lms_activity_trigger_all_track_quizzes_percent', true);
                $percent_average = $quiz->track_results( $user->ID, $humble_lms_activity_trigger_all_track_quizzes );

                if( $percent_average < $trigger_all_track_quizzes_percent ) {
                  unset( $activities_completed_all_track_quizzes[$_key] );
                }
              }

              $activities = array_merge( $activities, $activities_completed_all_track_quizzes );
            }
          }

          foreach( $activities as $activity_key => $activity ) {
            $action = get_post_meta($activity->ID, 'humble_lms_activity_action', true);
            
            switch( $action ) 
            {
              case 'award':
                $award_id = (int)get_post_meta($activity->ID, 'humble_lms_activity_action_award', true);
                
                if( ! in_array( $award_id, $this->granted_awards( $user->ID ) ) ) {
                  if ( ! in_array( $id, $user_completed_course_quizzes ) ) {
                    array_push( $completed[3], $award_id );
                    array_push( $user_completed_course_quizzes, $id );
                    $this->grant_award( $user->ID, $award_id );
                  }
                }
              break;

              case 'certificate':
                $certificate_id = (int)get_post_meta($activity->ID, 'humble_lms_activity_action_certificate', true);

                if( ! in_array( $certificate_id, $this->issued_certificates( $user->ID ) ) ) {
                  if ( ! in_array( $id, $user_completed_track_quizzes ) ) {
                    array_push( $completed[4], $certificate_id );
                    array_push( $user_completed_track_quizzes, $id );
                    $this->issue_certificate( $user->ID, $certificate_id );
                  }
                }
              break;
              
              case 'email':
                $email_id = (int)get_post_meta($activity->ID, 'humble_lms_activity_action_email', true);

                if( ! get_post_status( $email_id ) === 'publish' )
                  break;

                $to = $user->user_email;
                $subject = get_post_meta($email_id, 'humble_lms_email_subject', true);
                $subject = ! empty( $subject ) ? $subject : get_bloginfo('name');
                $message = get_post_meta($email_id, 'humble_lms_email_message', true);
                $message = str_replace('STUDENT_NAME', $user->display_name, $message);
                $message = str_replace('WEBSITE_NAME', get_bloginfo('name'), $message);
                $message = str_replace('WEBSITE_URL', get_bloginfo('url'), $message);
                $format = get_post_meta($email_id, 'humble_lms_email_format', true);
                $headers = array('Content-Type: ' . $format . '; charset=UTF-8');

                wp_mail( $to, $subject, $message, $headers );
              break;

              default:
                // do nothing.
              break;
            }
          }
        }
      }

      return $completed;
    }

    /**
     * Grant award to user.
     *
     * @return  false
     * @param   int
     * @since   0.0.1
     */
    public function grant_award( $user_id, $award_id ) {
      if( ! $user_id )
        return;

      if( ! $award_id )
        return;

      $awards = $this->granted_awards( $user_id );
      
      if( ! in_array( $award_id, $awards ) ) {
        array_push( $awards, $award_id );
      }

      update_user_meta( $user_id, 'humble_lms_awards', $awards );

      return;
    }

    /**
     * Revoke award from a user.
     *
     * @return  false
     * @param   int
     * @since   0.0.1
     */
    public function revoke_award( $user_id = null, $award_id = null ) {
      if( ! $user_id )
        return;

      if( ! $award_id || 'humble_lms_award' !== get_post_type( $award_id ) )
        return;

      $awards = $this->granted_awards( $user_id );

      if( ( $key = array_search( $award_id, $awards ) ) !== false ) {
        unset( $awards[$key] );
        update_user_meta( $user_id, 'humble_lms_awards', $awards );
      }

      return;
    }

    /**
     * Get an array of granted awards.
     *
     * @param int|string
     * @return  array
     * @since   0.0.1
     */
    public function granted_awards( $user_id = null, $published = false ) {
      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return [];
        } else {
          $user_id = get_current_user_id();
        }
      }

      $awards = get_user_meta( $user_id, 'humble_lms_awards', false );
      $awards = is_array( $awards ) && ! empty( $awards[0] ) ? $awards[0] : [];
      
      if( ! $published ) {
        return $awards;
      }

      foreach( $awards as $key => $award ) {
        if( get_post_status( $award ) !== 'publish' ) {
          unset( $awards[$key] );
        }
      }

      return $awards;
    }

    /**
     * Issue a certificate to a user.
     *
     * @return  false
     * @param   int
     * @since   0.0.1
     */
    public function issue_certificate( $user_id = null, $certificate_id = null ) {
      if( ! $user_id )
        return;

      if( ! $certificate_id || 'humble_lms_cert' !== get_post_type( $certificate_id ) )
        return;

      $certificates = $this->issued_certificates( $user_id );
      
      if( ! in_array( $certificate_id, $certificates ) ) {
        array_push( $certificates, $certificate_id );
      }

      update_user_meta( $user_id, 'humble_lms_certificates', $certificates );

      return;
    }

    /**
     * Revoke certificate from a user.
     *
     * @return  false
     * @param   int
     * @since   0.0.1
     */
    public function revoke_certificate( $user_id = null, $certificate_id = null ) {
      if( ! $user_id )
        return;

      if( ! $certificate_id )
        return;

      $certificates = $this->issued_certificates( $user_id );
      
      if( ( $key = array_search( $certificate_id, $certificates ) ) !== false ) {
        unset( $certificates[$key] );
        update_user_meta( $user_id, 'humble_lms_certificates', $certificates );
      }

      return;
    }

    /**
     * Get an array of issued certificates.
     *
     * @param   int|string
     * @return  array
     * @since   0.0.1
     */
    public function issued_certificates( $user_id = null, $published = false ) {
      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return [];
        } else {
          $user_id = get_current_user_id();
        }
      }

      $certificates = get_user_meta( $user_id, 'humble_lms_certificates', false );
      $certificates = is_array( $certificates ) && ! empty( $certificates[0] ) ? $certificates[0] : [];
      
      if( ! $published ) {
        return $certificates;
      }

      foreach( $certificates as $key => $certificate ) {
        if( get_post_status( $certificate ) !== 'publish' ) {
          unset( $certificates[$key] );
        }
      }

      return $certificates;
    }

    /**
     * Get user registration date.
     * 
     * @param int|bool
     * @return string
     * @since   0.0.1
     */
    public function registered_at( $user_id = null, $formatted = false ) {
      if( ! get_userdata( (int)$user_id ) ) { 
        return;
      }
    
      $user = get_user_by( 'id', (int)$user_id );

      $registered = get_userdata( $user_id )->user_registered;

      if( ! $formatted ) {
        return $registered;
      }

      return $registered_formatted = date('F j, Y, g:i a', strtotime( $registered ) );
    }

    /**
     * Track progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function track_progress( $track_id, $user_id = null ) {
      if( ! $track_id )
        return 0;

      if( ! $user_id )
        return 0;
  
      $track_courses = $this->content_manager->get_track_courses( $track_id );

      if( ! $track_courses || sizeOf( $track_courses ) === 0 ) {
        return 0;
      }

      $track_courses_progress = array();

      foreach( $track_courses as $course_id ) {
        $course_progress = $this->course_progress( $course_id, $user_id );
        $track_courses_progress[] = $course_progress;
      }

      $sum = (int)array_sum( $track_courses_progress );
      $percent = sizeOf( $track_courses_progress ) > 0 ? $sum / sizeOf( $track_courses_progress ) : 0;

      return round( $percent, 1 );
    }

    /**
     * Course progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    public static function course_progress( $course_id, $user_id = null ) {
      if( ! $course_id || 'humble_lms_course' !== get_post_type( $course_id ) )
        return 0;

      if( ! $user_id || ! get_user_by( 'id', $user_id ) )
        return 0;
      
      $course_lessons = Humble_LMS_Content_Manager::get_course_lessons( $course_id, false );

      if( empty( $course_lessons ) )
        return 0;

      $user = new Humble_LMS_Public_User;
      $lessons_completed = $user->completed_lessons( $user_id );
      $completed_course_lessons = array_intersect( $lessons_completed, $course_lessons );

      if( ( empty( $lessons_completed ) ) )
        return 0;

      $percent = count( $completed_course_lessons ) * 100 / count( $course_lessons );

      return round( $percent, 1 );
    }

    /**
     * Reset user progress.
     *
     * @since 0.0.1
     * @return void
     */
    public function reset_user_progress( $user_id = null ) {
      if( ! $user_id || ! get_user_by( 'id', $user_id ) )
        return;

      update_user_meta( $user_id, 'humble_lms_tracks_completed', [] );
      update_user_meta( $user_id, 'humble_lms_courses_completed', [] );
      update_user_meta( $user_id, 'humble_lms_lessons_completed', [] );
      update_user_meta( $user_id, 'humble_lms_quizzes_completed', [] );
      update_user_meta( $user_id, 'humble_lms_quiz_evaluations', [] );
      update_user_meta( $user_id, 'humble_lms_awards', [] );
      update_user_meta( $user_id, 'humble_lms_certificates', [] );
    }

    /**
     * Remove user instructor status.
     *
     * @since 0.0.1
     * @return void
     */
    public function remove_instructor_status( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) )
        return;

      $courses = $this->content_manager->get_courses();
      
      foreach( $courses as $course ) {
        $course_instructors = $this->content_manager->get_instructors( $course->ID );

        if( in_array( $user_id, $course_instructors ) ) {
          $course_instructors = array_values( array_diff( $course_instructors, [$user_id] ) );
          update_post_meta( $course->ID, 'humble_lms_instructors', [$course_instructors] );
        }

        $lessons = $this->content_manager->get_course_lessons( $course->ID );

        foreach( $lessons as $lesson_id ) {
          $lesson_instructors = $this->content_manager->get_instructors( $lesson_id );

          if( in_array( $user_id, $lesson_instructors ) ) {
            $lesson_instructors = array_values( array_diff( $lesson_instructors, [$user_id] ) );
            update_post_meta( $lesson_id, 'humble_lms_instructors', [$lesson_instructors] );
          }
        }
      }
      
    }

    /**
     * Get user purchases.
     *
     * @since 0.0.1
     * @return array
     */
    public function purchases( $user_id = null ) {
      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return [];
        } else {
          $user_id = get_current_user_id();
        }
      }
  
      $purchases = get_user_meta( get_current_user_id(), 'humble_lms_purchased_content', false );
      $purchases = isset( $purchases[0] ) && ! empty( $purchases[0] ) && $purchases[0] !== '' ? $purchases[0] : [];

      foreach( $purchases as $post_id ) {
        if( get_post_type( $post_id ) === 'humble_lms_track') {
          $track_courses = $this->content_manager->get_track_courses( $post_id );
          if( ! empty( $track_courses ) ) {
            foreach( $track_courses as $course_id ) {
              if( ! in_array( $course_id, $purchases ) ) {
                array_push( $purchases, $course_id );
              }
            }
          }
        }
      }

      $purchases = array_unique( $purchases, SORT_REGULAR );

      return $purchases;
    }

    /**
     * Check if user has purchased a track/course.
     *
     * @since 0.0.1
     * @return bool
     */
    public function purchased( $post_id = null ) {
      if( ! get_post( $post_id ) )
        return;

      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course',
      );

      if( ! in_array( get_post_type( $post_id ), $allowed_post_types ) )
        return false;

      if( ! get_post_meta( $post_id, 'humble_lms_is_for_sale', true ) )
        return true;

      $purchases = $this->purchases();

      return in_array( $post_id, $purchases );
    }

    /**
     * Check if user purchased all courses in a track.
     * 
     * @return Bool
     * @since 0.0.3
     */
    public function purchased_all_track_courses( $user_id = null, $track_id = null ) {
      if( ! get_user_by( 'id', $user_id ) || 'humble_lms_track' !== get_post_type( $track_id ) ) {
        return false;
      }

      $courses = $this->content_manager->get_track_courses( $track_id );

      if( ! $courses || empty( $courses ) ) {
        return false;
      }

      $purchases = $this->purchases( $user_id );

      return ! array_diff( $courses, $purchases );
    }

    /**
     * Get user evaluations.
     *
     * @since 0.0.1
     * @return array
     */
    public function evaluations( $post_id = null ) {
      $evaluations = [];

      $user_evaluations = get_user_meta( get_current_user_id(), 'humble_lms_quiz_evaluations' );
      $evaluations = ! isset( $user_evaluations[0] ) ? [] : $user_evaluations[0];

      return $evaluations;
    }

    /**
     * Check billing information.
     * 
     * @since 0.0.3
     */
    public function billing_information_complete( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return false;
      }

      $first_name = $this->first_name( $user_id );
      $last_name = $this->last_name( $user_id );
      $address = $this->address( $user_id );
      $postcode = $this->postcode( $user_id );
      $city = $this->city( $user_id );
      $country = $this->country( $user_id );
      $company = $this->company( $user_id );
      $vat_id = $this->vat_id( $user_id );

      return ! empty( $first_name ) && ! empty( $last_name ) && ! empty( $address ) && ! empty( $postcode ) && ! empty( $city );
    }

    /**
     * Get user transactions
     * 
     * @return Posts
     * @since 0.0.3
     */
    public function transactions( $user_id = null ) {
      if( ! get_user_by( 'id', $user_id ) ) {
        return;
      }
  
      $args = array(
        'post_type' => 'humble_lms_txn',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
          array(
            'key' => 'humble_lms_txn_user_id',
            'value' => $user_id,
            'compare' => '=',
          ),
        ),
        'order' => 'DESC',
      );

      $transactions = get_posts( $args );

      return $transactions;
    }

  }

}
