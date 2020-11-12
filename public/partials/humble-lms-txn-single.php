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

  $calculator = new Humble_LMS_Calculator;
  $user_manager = new Humble_LMS_Public_User;
  $content_manager = new Humble_LMS_Content_Manager;

  $transaction = $content_manager->transaction_details( $post->ID );
  $user_txn_id = get_post_meta( $post->ID, 'humble_lms_txn_user_id', true );

  if( ! current_user_can('manage_options') ) {
    if( (int)$user_txn_id !== get_current_user_ID() ) {
      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 );
      die;
    }
  }

  $date = date_parse( $transaction['create_time'] );
  $date = $date['year'] . '-' . $date['month'] . '-' . $date['day'];

  $invoice_template_data = $content_manager->invoice_template_data();
  $css = dirname( plugin_dir_url( __FILE__ ) ) . '/css/invoice/invoice.css';

  // Calculate price fields
  $sum = $calculator->sum_transaction( $post->ID );
  
  $content = '<img id="humble-lms-seller-logo" src="' . $invoice_template_data['seller_logo'] . '" alt="" />';

  $content .= '<div id="humble-lms-seller-customer-info">';
    $content .= '<div id="humble-lms-seller-info">' . $invoice_template_data['seller_info'] . '</div>';
    $content .= '<div id="humble-lms-customer-info">';
      $content .= '<p>';
        $content .= $transaction['invoice_number'] ? '<p>' . __('Invoice #', 'humble-lms') . ' ' . $transaction['invoice_number'] . '<br>' : '';
        $content .= __('Date', 'humble-lms') . ': ' . $transaction['create_time'] . '<br>';
        $content .= __('Due', 'humble-lms') . ': ' . $transaction['create_time'];
      $content .= '</p>';
      $content .= $transaction['company'] ? '<strong>' . $transaction['company'] . '</strong><br>' : '';
      $content .= $transaction['first_name'] . ' ' . $transaction['last_name'] . '<br>';
      $content .= $transaction['postcode'] . ' ' . $transaction['city'] . '<br>';
      $content .= $transaction['address'] . '<br>';
      $content .= $transaction['vat_id'] ? $transaction['vat_id'] : '';
    $content .= '</div>';
  $content .= '</div>';

  $content .= '<h1>' . __('Invoice', 'humble-lms') . '</h1>';

  $content .= '<div id="humble-lms-invoice-text-before">' . wpautop( $invoice_template_data['invoice_text_before'] ) . '</div>';

  $content .= '<table id="humble-lms-invoice-table">';
  $content .= '<tr>';
    $content .= '<th>' . __('Description', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Quantity', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Price', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Amount', 'humble-lms') . '</th>';
  $content .= '</tr>';
  $content .= '<tr>';
    $content .= '<td>' . $transaction['description'] . '</td>';
    $content .= '<td>1</td>';
    $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['price'] . '</td>';
    $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['price'] . '</td>';
  $content .= '</tr>';
  $content .= '<tr id="humble-lms-subtotal">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . __('Subtotal', 'humble-lms') . '</td>';
    $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['subtotal'] . '</td>';
  $content .= '</tr>';
  $content .= '<tr id="humble-lms-taxes">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . $sum['vat_string'] . '</td>';
    $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['vat_diff'] . '</td>';
  $content .= '</tr>';
  $content .= '<tr class="humble-lms-total">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . __('Total', 'humble-lms') . '</td>';
    $content .= '<td>' . $transaction['currency_code'] . ' ' . $sum['total'] . '</td>';
  $content .= '</tr>';

  $content .= '</table>';

  $content .= '<div id="humble-lms-invoice-text-after">' . wpautop( $invoice_template_data['invoice_text_after'] ) . '</div>';
  $content .= '<div id="humble-lms-invoice-text-footer"><div>' . wpautop( $invoice_template_data['invoice_text_footer'] ) . '</div></div>';

    
  $html = '<!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    <link rel="stylesheet" href="' . $css . '">
  </head>
  <body class="humble-lms-invoice">
    <div id="humble-lms-invoice">' . wpautop( $content ) . '</div>
  </body>
  </html>';

  // Generate PDF
  $dompdf = new Dompdf();
  $options = $dompdf->getOptions();
  $options->setIsRemoteEnabled(true);
  $dompdf->setOptions($options);
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream('invoice-' . $transaction['invoice_number'] . '.pdf');
