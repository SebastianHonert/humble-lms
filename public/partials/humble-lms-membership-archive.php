<?php

if ( ! defined( 'ABSPATH' ) )
  exit;

get_header();

do_action( 'humble_lms_before_membership_archive_title' );

echo '<h1 class="humble-lms-course-archive-title">' . __('Memberships', 'humble-lms') . '</h1>';

do_action( 'humble_lms_before_membership_archive' );

echo do_shortcode('[humble_lms_paypal_buttons]');

echo '<div class="humble-lms-loading-layer"><div class="humble-lms-loading"></div></div>';

get_footer();