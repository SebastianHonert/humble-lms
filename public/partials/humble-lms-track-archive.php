<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

do_action( 'humble_lms_before_track_archive_title' );

echo '<h1 class="humble-lms-track-archive-title">' . __('Course Tracks', 'humble-lms') . '</h1>';

do_action( 'humble_lms_before_track_archive' );

echo do_shortcode('[humble_lms_track_archive]');

get_footer();
