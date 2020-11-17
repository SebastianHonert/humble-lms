<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

class Humble_LMS_Widget_Syllabus extends WP_Widget
{
  public function __construct()
  {
    $widget_options = array(
      'classname' => 'humble-lms-widget-syllabus',
      'description' => 'Humble LMS course syllabus.',
    );

    parent::__construct(
      'humble-lms-widget-syllabus',
      esc_html__('Humble LMS Syllabus', 'humble-lms'),
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
      echo __($title, 'humble-lms');
      echo $args['after_title'];
    }

    echo do_shortcode('[humble_lms_syllabus]');

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
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'humble-lms' ); ?></label>
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
  public function register_widget_syllabus() {
    register_widget('Humble_LMS_Widget_Syllabus');
  }
}
