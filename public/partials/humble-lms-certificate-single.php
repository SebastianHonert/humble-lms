<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$obj_id = get_queried_object_id();
$current_url = get_permalink( $obj_id );

if( ! is_user_logged_in() ) {
  wp_redirect( wp_login_url( $current_url ) );
  die;
}

global $post;

$user = get_user_by( 'id', get_current_user_id() );
$user_info = get_userdata( $user->ID );
$first_name = isset( $user_info->first_name ) ? $user_info->first_name : __('not set', 'humble-lms');
$last_name = isset( $user_info->last_name ) ? $user_info->last_name : __('not set', 'humble-lms');

$_user = new Humble_LMS_Public_User;
$user_certificates = $_user->issued_certificates( $user->ID );

if( ! in_array( $post->ID, $user_certificates ) ) {
  wp_redirect( esc_url( site_url() ) );
  die;
}
  
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Humble LMS Certificate</title>
  <link rel="stylesheet" href="<?php echo plugins_url( 'humble-lms/public/css/certificate/default.css' ); ?>">
</head>
<body><?

  $meta = get_post_meta( $post->ID );
  $heading = isset( $meta['humble_lms_cert_heading'][0] ) && ! empty( $meta['humble_lms_cert_heading'][0] ) ? $meta['humble_lms_cert_heading'][0] : '';
  $subheading = isset( $meta['humble_lms_cert_subheading'][0] ) && ! empty( $meta['humble_lms_cert_subheading'][0] ) ? $meta['humble_lms_cert_subheading'][0] : '';
  $date_format = isset( $meta['humble_lms_cert_date_format'][0] ) && ! empty( $meta['humble_lms_cert_date_format'][0] ) ? $meta['humble_lms_cert_date_format'][0] : 'F j, Y';
  $date = current_time( $date_format );
  $content = isset( $meta['humble_lms_cert_content'][0] ) && ! empty( $meta['humble_lms_cert_content'][0] ) ? $meta['humble_lms_cert_content'][0] : '';

  $content = str_replace( 'STUDENT_NAME', $user->display_name, $content );
  $content = str_replace( 'STUDENT_FIRST_NAME', $first_name, $content );
  $content = str_replace( 'STUDENT_LAST_NAME', $last_name, $content );
  $content = str_replace( 'CURRENT_DATE', $date, $content );
  $content = str_replace( 'WEBSITE_NAME', get_bloginfo('name'), $content );
  $content = str_replace( 'WEBSITE_URL', get_bloginfo('url'), $content );

  echo '<div id="certificate">';

    echo $heading ? '<h1>' . $heading . '</h1>' : '';
    echo $subheading ? '<h2>' . $subheading . '</h2>' : '';

    echo $content ? '<div class="content">' . $content . '</div>' : '';

  echo '</div>';

  echo '<p class="back"><a href="' . get_bloginfo('url') . '">← ' . __('Back to home page', 'humble-lms') . '</a></p>';

?></body>
</html>