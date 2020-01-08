<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

  echo '<h1>' . __('Course Tracks', 'humble-lms') . '</h1>';

  echo do_shortcode('[humble_lms_track_archive]');

get_footer();
