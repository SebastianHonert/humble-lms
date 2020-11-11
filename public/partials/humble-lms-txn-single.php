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

  $user_manager = new Humble_LMS_Public_User;

  $user = get_user_by( 'id', get_current_user_id() );
  $user_info = get_userdata( $user->ID );
  $first_name = $user_manager->first_name( $user->ID );
  $last_name = $user_manager->last_name( $user->ID );

  $user_transactions = $user_manager->transactions( $user->ID );

  if( ! current_user_can('manage_options') ) {
    if( ! in_array( $post->ID, $user_transactions ) ) {
      wp_redirect( esc_url( site_url() ) );
      die;
    }
  }

  $css = dirname( plugin_dir_url( __FILE__ ) ) . '/css/invoice/invoice.css';
  $options = get_option('humble_lms_options');
  $seller_info = $options['seller_info'];
  $seller_logo = $options['seller_logo'];
  $invoice_prefic = $options['invoice_prefix'];
  $invoice_text_before = $options['invoice_text_before'];
  $invoice_text_after = $options['invoice_text_after'];
  $invoice_text_footer = $options['invoice_text_footer'];

  $content = '<img id="humble-lms-seller-logo" src="' . $seller_logo . '" alt="" />';

  $content .= '<div id="humble-lms-seller-customer-info">';
    $content .= '<div id="humble-lms-seller-info">' . $seller_info . '</div>';
    $content .= '<div id="humble-lms-customer-info">';
      $content .= 'Invoice ID<br>';
      $content .= 'Lorem Ipsum<br>';
      $content .= 'Address<br>';
      $content .= 'Date';
    $content .= '</div>';
  $content .= '</div>';

  $content .= '<div id="humble-lms-invoice-text-before">' . wpautop( $invoice_text_before ) . '</div>';

  $content .= '<table id="humble-lms-invoice-table">';
  $content .= '<tr>';
    $content .= '<th>' . __('Description', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Quantity', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Price', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Total', 'humble-lms') . '</th>';
  $content .= '</tr>';
  $content .= '<tr>';
    $content .= '<td colspan="4">' . __('Subtotal', 'humble-lms') . '</td>';
  $content .= '</tr>';
  $content .= '<tr>';
    $content .= '<td colspan="4">' . __('Taxes', 'humble-lms') . '</td>';
  $content .= '</tr>';
  $content .= '<tr>';
    $content .= '<td colspan="4">' . __('Total', 'humble-lms') . '</td>';
  $content .= '</tr>';

  $content .= '</table>';

  $content .= '<div id="humble-lms-invoice-text-after">' . wpautop( $invoice_text_after ) . '</div>';
  $content .= '<div id="humble-lms-invoice-text-footer"><div>' . wpautop( $invoice_text_footer ) . '</div></div>';

    
  $html = '<!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Humble LMS Certificate</title>
    <link rel="stylesheet" href="' . $css . '">
  </head>
  <body class="humble-lms-invoice">
    <div id="humble-lms-invoice">' . wpautop( $content ) . '</div>
  </body>
  </html>';

  // Generate PDF
  // $dompdf = new Dompdf();
  // $options = $dompdf->getOptions();
  // $options->setIsRemoteEnabled(true);
  // $dompdf->setOptions($options);
  // $dompdf->loadHtml($html);
  // $dompdf->setPaper('A4', 'portrait');
  // $dompdf->render();
  // $dompdf->stream('invoice.pdf');

  echo $html;
