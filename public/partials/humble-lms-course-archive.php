<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

  echo '<h1>' . __('Courses', 'humble-lms') . '</h1>';

  echo do_shortcode('[course_archive]');

get_footer();
