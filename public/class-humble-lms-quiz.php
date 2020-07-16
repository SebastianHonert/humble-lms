<?php
/**
 * The public-facing quiz functionalities.
 *
 * Creates the various functions used for AJAX on the front-end.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Quiz' ) ) {

  class Humble_LMS_Quiz {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      string    $humble_lms       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {

      $this->user = new Humble_LMS_Public_User;
      $this->translator = new Humble_LMS_Translator;
      $this->content_manager = new Humble_LMS_Content_Manager;

    }

    /**
     * Get quizzes by ids.
     *
     * @since    0.0.1
     * @param    int
     * @return   array
     */
    public function get( $quiz_ids = null ) {
      if ( ! $quiz_ids )
        return [];

      if( ! is_array( $quiz_ids ) ) {
        $quiz_ids = array_map('trim', explode(',', $quiz_ids ));
      }

      if( empty( $quiz_ids ) )
        return [];
      
      $args = array(
        'post_type' => 'humble_lms_quiz',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post__in' => $quiz_ids,
        'lang' => $this->translator->current_language(),
      );

      $quizzes = get_posts( $args );

      return $quizzes;
    }

    /**
     * Get quiz questions.
     *
     * @since    0.0.1
     * @param    int
     * @return   array
     */
    public function questions( $quiz_id = null ) {
      if ( ! $quiz_id )
        return [];

      if( get_post_status( $quiz_id ) !== 'publish' )
        return [];

      if( get_post_type( $quiz_id ) !== 'humble_lms_quiz' )
        return [];

      $question_ids = get_post_meta( $quiz_id, 'humble_lms_quiz_questions', false );
      $question_ids = isset( $question_ids[0] ) && ! empty( $question_ids[0] ) && ( isset( $question_ids[0][0] ) && $question_ids[0][0] !== '' ) ?  $question_ids[0] : [];
      
      $args = array(
        'post_type' => 'humble_lms_question',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post__in' => $question_ids,
        'orderby' => 'post__in',
        'lang' => $this->translator->current_language(),
      );
    
      $questions = get_posts( $args );

      return $questions;      
    }

    /**
     * Get quiz question type.
     *
     * @since    0.0.1
     * @param    int
     * @return   array
     */
    public function question_type( $question_id = null ) {
      if( ! $question_id )
        return;
      
      if( get_post_status( $question_id ) !== 'publish' )
        return;

      if( get_post_type( $question_id ) !== 'humble_lms_question' )
        return;

      $type = get_post_meta( $question_id, 'humble_lms_question_type', true );

      if( ! $type )
        return;

      switch( $type ) {
        case 'multiple_choice':
          $type = $this->count_correct_answers( $question_id ) === 1 ? 'single_choice' : 'multiple_choice';
            break;
      }

      return $type;
    }

    /**
     * Count correct answers in single/multiple choice questions.
     *
     * @since    0.0.1
     * @param    int
     * @return   int
     */
    public function count_correct_answers( $question_id = null ) {
      $question = get_post( $question_id );

      if( get_post_type( $question_id ) !== 'humble_lms_question' )
        return;

      $answers = $this->answers( $question_id );

      $count = 0;
      foreach( $answers as $answer ) {
        if( $answer['correct'] === 1 ) {
          $count++;
        }
      }

      return $count;
    }

    /**
     * Get answers for a single question.
     *
     * @since    0.0.1
     * @param    int
     * @return   array
     */
    public function answers( $question_id = null ) {
      if( get_post_type( $question_id ) !== 'humble_lms_question' )
        return [];

      $question = get_post_meta( $question_id, 'humble_lms_question', true );
      $answers = get_post_meta( $question_id, 'humble_lms_question_answers', true );
      $answers = maybe_unserialize( $answers );
      $answers = ! isset( $answers ) || ! isset( $answers[0]['answer'] ) || empty( $answers[0]['answer'] ) ? array(['answer' => __('Please provide at least one answer.', 'humble-lms'), 'correct' => 0]) : $answers;
      $answers = $this->shuffle_answers( $answers, $question_id );
      
      return $answers;
    }

    /**
     * Generate single choice question.
     *
     * @since    0.0.1
     * @param    array
     * @return   string
     */
    public function single_choice( $quiz_id = null, $answers = null ) {
      if( ! $answers || ! $quiz_id )
        return;

      $html = '';
      $uid = uniqid();
      $completed = $this->user->completed_quiz( $quiz_id );
      $answers = $this->shuffle_answers( $answers, $quiz_id );

      foreach( $answers as $answer ) {
        $correct = $answer['correct'] == 1 ? '1' : '0';
        $checked = $correct && $completed ? 'checked' : '';
        $html .= '<div class="humble-lms-answer">';
        $html .= '<label class="humble-lms-label-container">';
        $html .= '<input type="radio" name="single-choice-' . $uid . '" value="' . $correct . '" ' . $checked  . '>' . $answer['answer'];
        $html .= '<span class="humble-lms-radio"></span>';
        $html .= '</label>';
        $html .= '</div>';
      }

      return $html;
    }

    /**
     * Generate multiple choice question.
     *
     * @since    0.0.1
     * @param    array
     * @return   string
     */
    public function multiple_choice( $quiz_id = null, $answers = null ) {
      if( ! $answers || ! $quiz_id )
        return;

      $html = '';
      $uid = uniqid();
      $completed = $this->user->completed_quiz( $quiz_id );
      $answers = $this->shuffle_answers( $answers, $quiz_id );
      foreach( $answers as $answer ) {
        $correct = $answer['correct'] == 1 ? '1' : '0';
        $checked = $correct && $completed ? 'checked' : '';
        $html .= '<div class="humble-lms-answer">';
        $html .= '<label class="humble-lms-label-container">';
        $html .= '<input type="checkbox" name="multiple-choice-' . $uid . '" value="' . $correct . '" ' . $checked . '>' . $answer['answer'];
        $html .= '<span class="humble-lms-checkmark"></span>';
        $html .= '</label>';
        $html .= '</div>';
      }

      return $html;
    }

    /**
     * Get passing percentage for a quiz.
     *
     * @since    0.0.1
     * @param    int
     * @return   int
     */
    public function get_passing_percent( $quiz_id = null ) {
      if( ! $quiz_id || get_post_type( $quiz_id ) !== 'humble_lms_quiz' )
        return 0;

      $passing_percent = get_post_meta( $quiz_id, 'humble_lms_quiz_passing_percent', true );

      if( ! $passing_percent || ! isset( $passing_percent ) || empty( $passing_percent ) )
        return 0;
      
      return $passing_percent;
    }

    /**
     * Get passing percentage for a quiz.
     *
     * @since    0.0.1
     * @param    int
     * @return   bool
     */
    public function get_passing_required( $quiz_id = null ) {
      if( ! $quiz_id || get_post_type( $quiz_id ) !== 'humble_lms_quiz' )
        return false;

      $passing_required = get_post_meta( $quiz_id, 'humble_lms_quiz_passing_required', true );

      if( ! $passing_required || ! isset( $passing_required ) || empty( $passing_required ) )
        return;
      
      return true;
    }

    /**
     * Shuffle answers
     *
     * @since    0.0.1
     * @param    array
     * @return   array
     */
    public function shuffle_answers( $answers = null, $post_id = null ) {
      if( ! $answers || ! is_array( $answers ) || ! $post_id )
        return [];

      $shuffle = get_post_meta( $post_id, 'humble_lms_shuffle', true );

      if( $shuffle === '1' )
        shuffle( $answers );

      return $answers;
    }

    /**
     * Evaluate quizzes by id.
     * 
     * $results = array(
     *   array(
     *     'id' => quiz_id,
     *     'questions' => array(
     *       'id' => question_id,
     *         'answers' => array(
     *           'id' => answer_id,
     *           'correct' => boolean
     *         )
     *       )
     *     )
     *   )
     * );
     *
     * @since    0.0.1
     * @param    array
     * @return   array
     */
    public function evaluate( $quiz_ids = [] ) {
      if( empty( $quiz_ids ) )
        return [];      
    }

    /**
     * Check if user exceeded max. attempts for a set of quizzes.
     * 
     * @since    0.0.1
     * @param    array // quiz IDs
     * @return   bool
     */
    public function max_attempts_exceeded( $quiz_ids = [] ) {
      if( empty( $quiz_ids ) || ! get_current_user_id() ) {
        return false;
      }

      $remaining_attempts = $this->remaining_attempts( $quiz_ids );

      return $remaining_attempts === 0;
    }

    /**
     * Get number of remaining attempts.
     * 
     * @since    0.0.1
     * @param    array
     * @return   int
     */
    public function remaining_attempts( $quiz_ids = [] ) {
      if( empty( $quiz_ids ) || ! get_current_user_id() ) {
        return 0;
      }
      
      foreach( $quiz_ids as $id ) {
        $max_attempts = (int)get_post_meta( $id, 'humble_lms_quiz_max_attempts', true );

        if( ! $max_attempts || $max_attempts === 0 ) {
          return -1;
        }

        $evaluations = $this->user->evaluations();

        $attempts = array();

        foreach( $evaluations as $evaluation ) {
          if( ! isset( $evaluation['quizIds'] ) ) {
            continue;
          }

          foreach( $evaluation['quizIds'] as $quiz_id ) {
            if( $quiz_id === $id ) {
              array_push( $attempts, $evaluation );
            }
          }
        }
      }

      $remaining_attempts = $max_attempts - count( $attempts );

      if( $remaining_attempts < 0 ) {
        $remaining_attempts = 0;
      }

      return $remaining_attempts;
    }

    /**
     * Get max. number of attempts for multiple quizzes.
     * 
     * @since    0.0.1
     * @param    array
     * @return   int
     */
    public function max_attempts( $quiz_ids = [] ) {
      if( empty( $quiz_ids ) || ! get_current_user_id() ) {
        return 0;
      }

      $max_attempts = 0;
      foreach( $quiz_ids as $id ) {
        $quiz_max_attempts = (int)get_post_meta( $id, 'humble_lms_quiz_max_attempts', true );
        if( $quiz_max_attempts > $max_attempts ) {
          $max_attempts = $quiz_max_attempts;
        }
      }

      return $max_attempts;
    }

    /**
     * Get best quiz attempts for a single user.
     * 
     * @param   int
     * @return  int
     * @since   0.0.1
     */
    public function single_user_best_quiz_attempt( $user_id = null, $quiz_id = null ) {
      if( ! $quiz_id || 'humble_lms_quiz' !== get_post_type( $quiz_id ) ) {
        return false;
      }

      if( ! $user_id || false === get_userdata( $user_id ) ) {
        if( ! get_current_user_id() ) {
          return false;
        } else {
          $user_id = get_current_user_id();
        }
      }

      $evaluations = $this->user->evaluations();

      $quiz_results = array();
  
      foreach( $evaluations as $evaluation ) {
        if( empty( $evaluation ) || ! is_array( $evaluation ) ) {
          continue;
        }

        if( in_array( $quiz_id, $evaluation['quizIds'] ) ) {
          array_push( $quiz_results, (int)$evaluation['percent'] );
        }
      }

      if( count( $quiz_results ) === 0 ) {
        return false;
      }

      rsort( $quiz_results );

      return $quiz_results[0];
    }

    /**
     * Get course quiz results
     * 
     * @param   int
     * @return  int
     * @since   0.0.1
     */
    public function course_results( $user_id = null, $course_id = null ) {
      if( ! $course_id || get_post_type( $course_id ) !== 'humble_lms_course' ) {
        return false;
      }

      if( ! $user_id || false === get_userdata( $user_id ) ) {
        if( ! get_current_user_id() ) {
          return false;
        } else {
          $user_id = get_current_user_id();
        }
      }

      $quiz_results = array();
      $course_quizzes = $this->content_manager->get_course_quizzes( $course_id );

      foreach( $course_quizzes as $quiz_id ) {
        $include_result = (int)get_post_meta( $quiz_id, 'humble_lms_quiz_include_result', true);

        if( 1 === $include_result ) {
          $best_result = $this->single_user_best_quiz_attempt( $user_id, $quiz_id );

          if( false !== $best_result ) {
            array_push( $quiz_results, $best_result );
          }
        }
      }

      if( count( $quiz_results ) === 0 ) {
        return false;
      }

      $percent = 0;
      foreach( $quiz_results as $quiz_result ) {
        $percent += $quiz_result;
      }

      return $percent / count( $quiz_results );
    }

    /**
     * Get track quiz results
     * 
     * @param   int
     * @return  int
     * @since   0.0.1
     */
    public function track_results( $user_id = null, $track_id = null ) {
      if( ! $track_id || get_post_type( $track_id ) !== 'humble_lms_track' ) {
        return 0;
      }

      if( ! $user_id || false === get_userdata( $user_id ) ) {
        if( ! get_current_user_id() ) {
          return 0;
        } else {
          $user_id = get_current_user_id();
        }
      }

      $quiz_results = array();
      $track_courses = $this->content_manager->get_track_courses( $track_id );

      foreach( $track_courses as $course_id ) {
        $course_results = $this->course_results( $user_id, $course_id );

        if( false !== $course_results ) {
          array_push( $quiz_results, $course_results );
        }
      }

      if( count( $quiz_results ) === 0 ) {
        return 0;
      }

      $percent = 0;
      foreach( $quiz_results as $quiz_result ) {
        $percent += $quiz_result;
      }

      echo $percent / count( $quiz_results );

      return $percent / count( $quiz_results );
    }
    
  }
  
}
