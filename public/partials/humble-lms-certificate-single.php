<?php

use Dompdf\Dompdf;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

require_once dirname( plugin_dir_path( __FILE__ ) ) . '../../lib/dompdf/autoload.inc.php';

$obj_id = get_queried_object_id();
$current_url = get_permalink( $obj_id );

if( ! is_user_logged_in() ) {
  wp_redirect( wp_login_url( $current_url ) );
  die;
}

global $post;

$_user = new Humble_LMS_Public_User;

$user = get_user_by( 'id', get_current_user_id() );
$user_info = get_userdata( $user->ID );
$first_name = $_user->first_name( $user->ID );
$last_name = $_user->last_name( $user->ID );

$user_certificates = $_user->issued_certificates( $user->ID );

if( ! current_user_can('manage_options') ) {
  if( ! in_array( $post->ID, $user_certificates ) ) {
    wp_redirect( esc_url( site_url() ) );
    die;
  }
}

$template = get_post_meta( $post->ID, 'humble_lms_cert_template', true );
$template_dir = dirname( plugin_dir_url( __FILE__ ) ) . '/css/certificate/';

if( $template !== 'default' && file_exists( get_stylesheet_directory() . '/humble-lms/certificate/' . $template . '.css' ) ) {
  $template_dir = get_stylesheet_directory_uri() . '/humble-lms/certificate/';
} else {
  $template = 'default';
}

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

$featured_image_url = get_the_post_thumbnail_url( $post->ID );

if( $featured_image_url ) {
  $background_style = 'style="background-image:url(' . $featured_image_url . ')"';
} else {
  $background_style = '';
}
  
$html = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Certificate</title>
  <link rel="stylesheet" href="' . $template_dir . esc_html( $template ) . '.css' . '">
</head>
<body class="humble-lms-certificate">
  <div id="humble-lms-certificate" ' . $background_style . '>';
    $html .= $heading ? '<h1 class="humble-lms-certificate-title">' . $heading . '</h1>' : '';
    $html .= $subheading ? '<h2 class="humble-lms-certificate-subtitle">' . $subheading . '</h2>' : '';
    $html .= $content ? '<div class="humble-lms-certificate-content">' . wpautop( do_shortcode( $content ) ) . '</div>' : '';
  $html .= '</div>';
  $html .= '<p class="humble-lms-certificate-back-link"><a href="' . get_bloginfo('url') . '">← ' . __('Back to home page', 'humble-lms') . '</a></p>';

$html .= '</body>
</html>';

// Generate PDF
$orientation = get_post_meta( $post->ID, 'humble_lms_cert_orientation', true );

if( ! $orientation ) {
  $orientation = 'portrait';
}

if( isset( $_GET['display'] ) && ( 'html' === $_GET['display'] ) ) {
  echo $html;
} else {
  $dompdf = new Dompdf();
  $options = $dompdf->getOptions();
  $options->setIsRemoteEnabled(true);
  $dompdf->setOptions($options);
  $dompdf->loadHtml( $html );
  $dompdf->setPaper('A4', $orientation);
  $dompdf->render();
  $dompdf->stream('certificate.pdf');
}
