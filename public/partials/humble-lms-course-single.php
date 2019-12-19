<?php

get_header();

if (have_posts()):
  
  while (have_posts()): the_post();

    echo '<section id="humble-lms-course-content">';

      the_post_thumbnail('post-thumbnail', ['class' => 'humble-lms-featured-image', 'title' => 'Featured image']);

      echo '<h1>' . get_the_title() . '</h1>';

      the_content();

    echo '</section>';

    echo do_shortcode('[syllabus]');

  endwhile;

endif;

get_footer();
