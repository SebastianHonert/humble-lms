<?php

/**
 * Template Name: Course archive
 */

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

  echo do_shortcode('[course_archive]');

get_footer();