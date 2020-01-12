<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

if (have_posts()):
  
  while (have_posts()): the_post(); ?>

  <div class="humble-lms-loading-layer">
    <div class="humble-lms-loading"></div>
  </div>

  <h1><?php echo get_the_title(); ?></h1>

  <?php if( is_active_sidebar('humble-lms-sidebar') ): ?>
  <div class="humble-lms-flex-columns">
    <div class="humble-lms-flex-column--two-third">
  <?php endif; ?>

      <?php
        the_content();

        echo do_shortcode('[humble_lms_mark_complete_button]');
      ?>

    </div>

    <?php if( is_active_sidebar('humble-lms-sidebar') ): ?>
    <div class="humble-lms-flex-column--third">
      <?php dynamic_sidebar('humble-lms-sidebar'); ?>
      <?php echo do_shortcode('[humble_lms_course_instructors]'); ?>
    </div>
  </div>
  <?php endif; ?>

  <?php

  endwhile;

endif;

get_footer();
