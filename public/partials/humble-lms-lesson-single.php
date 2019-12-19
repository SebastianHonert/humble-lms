<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

if (have_posts()):
  
  while (have_posts()): the_post(); ?>

  <h1><?php echo get_the_title(); ?></h1>

  <div class="humble-lms-flex-columns">
    <div class="humble-lms-flex-column--two-third">
      <?php
        the_content();

        echo do_shortcode('[mark_complete]');
      ?>
    </div>
    <div class="humble-lms-flex-column--third">
      <?php echo do_shortcode('[syllabus context="lesson"]'); ?>
    </div>
  </div>

  <?php

  endwhile;

endif;

get_footer();
