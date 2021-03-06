<?php

global $multipage, $numpages, $page;

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

if (have_posts()):
  
  while (have_posts()): the_post();
  
  $options = Humble_LMS_Admin_Options_Manager::hlms_get_option('humble_lms_options');
  $humble_lms_has_lesson_progress_bar = isset( $options['has_lesson_progress_bar'] ) && $options['has_lesson_progress_bar'] === 1;
  $humble_lms_has_lesson_breadcrumbs = isset( $options['has_lesson_breadcrumbs'] ) && $options['has_lesson_breadcrumbs'] === 1;
  if( $humble_lms_has_lesson_progress_bar || $humble_lms_has_lesson_breadcrumbs ) {
    $content_manager = new Humble_LMS_Content_Manager;
    $course_id = isset( $_POST['course_id'] ) ? (int)$_POST['course_id'] : null;
    $parent_course = $course_id ? get_post( $course_id ) : $content_manager->get_parent_course( get_the_ID() );

    if( isset( $parent_course->ID ) ) { 
      if( $humble_lms_has_lesson_progress_bar ) {
        echo do_shortcode('[humble_lms_progress_bar is_before_lesson="1" course_id="' . $parent_course->ID . '" show_label="1"]');
      }

      if( $humble_lms_has_lesson_breadcrumbs ) {
        echo do_shortcode('[humble_lms_lesson_breadcrumbs]');
      }
    }
  }

  do_action( 'humble_lms_before_lesson_title' );

  ?>

  <h1 class="humble-lms-lesson-single-title"><?php echo get_the_title(); ?></h1>

  <?php if( is_active_sidebar('humble-lms-sidebar') ): ?>
    <div class="humble-lms-flex-columns">
      <div class="humble-lms-flex-column--two-third humble-lms-flex-column--lesson">
  <?php endif; ?>

  <div class="humble-lms-lesson-content">

      <?php
        the_content();

        $quizzes = Humble_LMS_Content_Manager::get_lesson_quizzes( $post->ID );

        if( isset( $quizzes ) && ! empty( $quizzes ) ) {
          $quiz_ids = implode( ',', $quizzes );
          echo do_shortcode('[humble_lms_quiz ids="' . $quiz_ids . '"]');
        }

        if( ! $multipage || ( $multipage && $page === $numpages ) ) {
          echo do_shortcode('[humble_lms_mark_complete_button]');
        }

        $args = array (
          'before' => '<div class="humble-lms-page-links"><span class="page-link-text">' . __( 'Pages', 'humble-lms' ) . ':</span>',
          'after' => '</div>',
          'link_before' => '<span class="humble-lms-page-link">',
          'link_after' => '</span>',
          'next_or_number' => 'number',
          'separator' => '<span class="humble-lms-page-links-separator">|</span>',
          // 'nextpagelink' => __( 'Next &raquo', 'humble-lms' ),
          // 'previouspagelink' => __( '&laquo Previous', 'humble-lms' ),
      );
       
      wp_link_pages( $args );
        
      ?>

  </div>

    <?php if( is_active_sidebar('humble-lms-sidebar') ): ?>
        </div>
        <div class="humble-lms-flex-column--third humble-lms-sidebar humble-lms-flex-column--lesson">
          <?php dynamic_sidebar('humble-lms-sidebar'); ?>
        </div>
      </div>
    <?php endif; ?>

  <?php

  endwhile;

endif;

get_footer();
