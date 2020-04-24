<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

do_action( 'humble_lms_before_course_archive_title' );

echo '<h1 class="humble-lms-course-archive-title">' . __('Courses', 'humble-lms') . '</h1>';

do_action( 'humble_lms_before_course_archive' );

echo do_shortcode('[humble_lms_course_archive]');

get_footer();
