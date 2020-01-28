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
        'post__in' => $quiz_ids
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

      $question_ids = get_post_meta( $quiz_id, 'humble_lms_quiz_questions', true );
      $question_ids = ! empty( $question_ids[0] ) ? json_decode( $question_ids[0] ) : [];
      
      $args = array(
        'post_type' => 'humble_lms_question',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post__in' => $question_ids
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

      return $answers;
    }

    /**
     * Generate single choice question.
     *
     * @since    0.0.1
     * @param    array
     * @return   string
     */
    public function single_choice( $answers = null ) {
      if( ! $answers )
        return;

      $html = '';
      foreach( $answers as $answer ) {
        $html .= '<div class="humble-lms-multiple-choice-question">';
        $html .= '<input type="radio" name="single-choice" value="1">' . $answer['answer'];
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
    public function multiple_choice( $answers = null ) {
      if( ! $answers )
        return;

      $html = '';
      foreach( $answers as $answer ) {
        $html .= '<div class="humble-lms-multiple-choice-question">';
        $html .= '<input type="checkbox" name="multiple-choice" value="1">' . $answer['answer'];
        $html .= '</div>';
      }

      return $html;
    }
    
  }
  
}
