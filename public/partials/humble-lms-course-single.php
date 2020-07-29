<?php

get_header();

if (have_posts()):
  
  while (have_posts()): the_post();

    echo '<section id="humble-lms-course-content">';

      do_action( 'humble_lms_before_course_thumbnail' );

      $show_featured_image = get_post_meta( $post->ID, 'humble_lms_course_show_featured_image', true );

      if( $show_featured_image === '1' )
        the_post_thumbnail('post-thumbnail', ['class' => 'humble-lms-featured-image', 'title' => 'Featured image']);
      
      do_action( 'humble_lms_before_course_title' );
      
      echo '<h1 class="humble-lms-course-single-title">' . get_the_title() . '</h1>';

      $content_manager = new Humble_LMS_Content_Manager();
      $parent_track = $content_manager->get_parent_track( $post->ID, true );

      if( $parent_track ) {
        echo '<p>' . __('Track', 'humble-lms') . ': ' . '<a class="humble-lms-course-single-parent-track" href="' . esc_html( get_permalink( $parent_track->ID ) ) . '">' . $parent_track->post_title . '</a></p>'; 
      }

      the_content();

      do_action( 'humble_lms_after_course_content' );

    echo '</section>';

    echo do_shortcode('[humble_lms_paypal_buttons_single_item]');

    echo do_shortcode('[humble_lms_syllabus]');

  endwhile;

endif;

get_footer();
