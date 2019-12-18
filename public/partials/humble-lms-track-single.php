<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

  echo '<h1>' . get_the_title() . '</h1>';

  echo do_shortcode('[course_archive track_id="' . get_the_ID() . '"]');

get_footer();