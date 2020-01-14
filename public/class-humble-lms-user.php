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
     * Get courses (published / unpublished)
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
     * @since    0.0.1
     */
    public function completed_course( $course_id ) {
      if( ! is_user_logged_in() || ! $course_id )
        return;

      $user_id = get_current_user_id();

      $course_lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

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

      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', true );
      $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];

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

      $track_ids = $this->get_track_ids( true );
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

      $course_ids = $this->get_course_ids( true );
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

      $completed = array( [], [], [], [] ); // lesson, courses, tracks, awards
      
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

      $courses = $this->get_courses( true );

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

        $tracks = $this->get_tracks( true );

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
     * humble_lms_activity_trigger = lesson, course, track
     * humble_lms_activity_trigger_lesson = lesson ID
     * humble_lms_activity_trigger_course = course ID
     * humble_lms_activity_trigger_track = track ID
     * 
     * humble_lms_activity_action = award, email
     * humble_lms_activity_action_award = award ID
     * humble_lms_activity_action_email = email ID
     *
     * @return  array
     * @param   int
     * @since   0.0.1
     */
    public function perform_activities( $completed ) {
      if( ! is_user_logged_in() )
        return [];

      $user = wp_get_current_user();
      $user_completed_all_tracks = false;
      $user_completed_all_courses = false;

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
            )
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
                )
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
                )
              );

              $activities_completed_all_courses = get_posts( $args );
              $activities = array_merge( $activities, $activities_completed_all_courses );
            }
          }

          foreach( $activities as $activity ) {
            $action = get_post_meta($activity->ID, 'humble_lms_activity_action', true);
            
            switch( $action ) 
            {
              case 'award':
                $award_id = (int)get_post_meta($activity->ID, 'humble_lms_activity_action_award', true);
                array_push( $completed[3], $award_id );
                $this->grant_award( $user->ID, $award_id );
              break;
              
              case 'email':
                $email_id = (int)get_post_meta($activity->ID, 'humble_lms_activity_action_email', true);

                if( ! get_post_status( $email_id ) === 'publish' )
                  break;

                $to = $user->user_email;
                $subject = 'Humble LMS';
                $message = get_post_meta($email_id, 'humble_lms_email_message', true);
                $message = str_replace('STUDENT_NAME', $user->nickname, $message);
                $message = str_replace('WEBSITE_TITLE', get_bloginfo('name'), $message);
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

      // update_user_meta( $user_id, 'humble_lms_awards', [] );

      $awards = get_user_meta( $user_id, 'humble_lms_awards', false );
      $awards = is_array( $awards ) && ! empty( $awards[0] ) ? $awards[0] : [];
      
      if( ! in_array( $award_id, $awards ) ) {
        array_push( $awards, $award_id );
      }

      update_user_meta( $user_id, 'humble_lms_awards', $awards );

      return;
    }

    /**
     * Returns an array of granted awards
     *
     * @param int|string
     * @return  array
     * @since   0.0.1
     */
    public function granted_awards( $user_id = null, $published = false ) {
      if( ! $user_id ) {
        if( ! is_current_user_logged_in() ) {
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
      
      $track_courses = get_post_meta( $track_id, 'humble_lms_track_courses', true );
      $track_courses = ! empty( $track_courses[0] ) ? json_decode( $track_courses[0] ) : [];
      $courses_completed = get_user_meta( $user_id, 'humble_lms_courses_completed', false );

      if( ! isset( $courses_completed[0] ) || ! is_array( $courses_completed[0] ) )
        return 0;

      $completed_track_courses = array_intersect( $courses_completed[0], $track_courses );

      if( ( empty( $track_courses ) ) || ( empty( $courses_completed[0] ) ) )
        return 0;

      $percent = count( $completed_track_courses ) * 100 / count( $track_courses );

      return round( $percent, 1 );
    }

    /**
     * Course progress in percent.
     * 
     * @return float
     * @since   0.0.1
     */
    function course_progress( $course_id, $user_id = null ) {
      if( ! $course_id )
        return 0;

      if( ! $user_id )
        return 0;
      
      $course_lessons = get_post_meta( $course_id, 'humble_lms_course_lessons', true );
      $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];
      $lessons_completed = get_user_meta( $user_id, 'humble_lms_lessons_completed', false );

      if( ! isset( $lessons_completed[0] ) || ! is_array( $lessons_completed[0] ) )
        return 0;

      $completed_course_lessons = array_intersect( $lessons_completed[0], $course_lessons );

      if( ( empty( $course_lessons ) ) || ( empty( $lessons_completed[0] ) ) )
        return 0;

      $percent = count( $completed_course_lessons ) * 100 / count( $course_lessons );

      return round( $percent, 1 );
    }

  }

}
