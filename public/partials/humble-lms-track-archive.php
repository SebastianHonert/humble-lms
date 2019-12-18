<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

  echo '<h1>' . __('Tracks', 'humble-lms') . '</h1>';

  echo do_shortcode('[track_archive]');

get_footer();
