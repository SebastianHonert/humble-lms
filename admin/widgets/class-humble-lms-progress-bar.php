<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

class Humble_LMS_Widget_Progress_Bar extends WP_Widget
{
  public function __construct()
  {
    $this->user = new Humble_LMS_Public_User;

    $widget_options = array(
      'classname' => 'humble-lms-widget-progress-bar',
      'description' => 'Humble LMS progress bar.',
    );

    parent::__construct(
      'humble-lms-widget-progress-bar',
      esc_html__('Humble LMS Progress Bar', 'humble-lms'),
      $widget_options
    );
  }

  public function widget( $args, $instance )
  {
    if( ! $this->allowed() )
      return;

    $instance_title = isset( $instance['title'] ) ? $instance['title'] : '';
    $title = apply_filters( 'widget_title', $instance_title, $instance, $this->id_base );

    echo $args['before_widget'];

    if( ! empty( $title ) ) {
      echo $args['before_title'];
      echo $title;
      echo $args['after_title'];
    }

    $progress = 0;
    
    if( is_single() && get_post_type() === 'humble_lms_track' ) {
      $progress = $this->user->track_progress( $track_id, get_current_user_id() );
    } elseif ( is_single() && get_post_type() === 'humble_lms_course' ) {
      $progress = $this->user->course_progress( get_the_ID(), get_current_user_id() );
    } elseif ( is_single() && get_post_type() === 'humble_lms_lesson' ) {
      if( isset( $_POST['course_id'] ) ) {
        $progress = $this->user->course_progress( (int)$_POST['course_id'], get_current_user_id() );
      }
    }
  
    echo do_shortcode('[humble_lms_progress_bar progress="' . $progress . '"]');

    echo $args['after_widget'];
  }

  /**
  * Back-end widget form.
  *
  * @see WP_Widget::form()
  *
  * @param array $instance Previously saved values from database.
  */
  public function form( $instance )
  {
    $title = ! empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';

    ?>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'mnmlwp' ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>

    <?php
  }

  /**
  * Sanitize widget form values as they are saved.
  *
  * @see WP_Widget::update()
  *
  * @param array $new_instance Values just sent to be saved.
  * @param array $old_instance Previously saved values from database.
  *
  * @return array Updated safe values to be saved.
  */
  public function update( $new_instance, $old_instance )
  {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

    return $instance;
  }

  public function allowed() {
    return is_single() && get_post_type() === 'humble_lms_lesson';
  }

  // Register the widget
  public function register_widget_progress_bar() {
    register_widget('Humble_LMS_Widget_Progress_Bar');
  }
}
