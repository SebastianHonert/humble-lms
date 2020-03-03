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
      
      echo '<h1>' . get_the_title() . '</h1>';

      the_content();

      do_action( 'humble_lms_after_course_content' );

    echo '</section>';

    echo do_shortcode('[humble_lms_syllabus]');

  endwhile;

endif;

get_footer();
