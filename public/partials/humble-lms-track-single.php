<?php

get_header();

if (have_posts()):
  
  while (have_posts()): the_post();

    echo '<section id="humble-lms-track-content">';

      do_action( 'humble_lms_before_track_title' );
      
      echo '<h1 class="humble-lms-track-single-title">' . get_the_title() . '</h1>';

      echo do_shortcode('[humble_lms_paypal_buttons_single_item]');

      echo get_the_content();

      do_action( 'humble_lms_after_track_content' );

    echo '</section>';

    echo do_shortcode('[humble_lms_course_archive track_id="' . get_the_ID() . '"]');

  endwhile;

endif;

get_footer();
