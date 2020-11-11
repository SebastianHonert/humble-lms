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

  $user_id_txn = get_post_meta( $post->ID, 'humble_lms_txn_user_id', true );

  $order_details = get_post_meta( $post->ID, 'humble_lms_order_details', false );
  $order_details = isset( $order_details[0] ) ? $order_details[0] : $order_details;

  $user_id = isset( $order_details['user_id'] ) ? $order_details['user_id'] : '';
  $user = get_user_by( 'id', $user_id );
  $first_name = $user_manager->first_name( $user_id_txn );
  $last_name = $user_manager->last_name( $user_id_txn );
  $company = $user_manager->company( $user_id_txn );
  $postcode = $user_manager->postcode( $user_id_txn );
  $city = $user_manager->city( $user_id_txn );
  $address = $user_manager->address( $user_id_txn );
  $country = $user_manager->country( $user_id_txn );
  $vat_id = $user_manager->vat_id( $user_id_txn );

  $order_id = isset( $order_details['order_id'] ) ? $order_details['order_id'] : '';
  $email_address = isset( $order_details['email_address'] ) ? $order_details['email_address'] : '';
  $payer_id = isset( $order_details['payer_id'] ) ? $order_details['payer_id'] : '';
  $status = isset( $order_details['status'] ) ? $order_details['status'] : '';
  $payment_service_provider = isset( $order_details['payment_service_provider'] ) ? $order_details['payment_service_provider'] : '';
  $create_time = isset( $order_details['create_time'] ) ? $order_details['create_time'] : '';
  $update_time = isset( $order_details['update_time'] ) ? $order_details['update_time'] : '';
  $given_name = isset( $order_details['given_name'] ) ? $order_details['given_name'] : '';
  $surname = isset( $order_details['surname'] ) ? $order_details['surname'] : '';
  $reference_id = isset( $order_details['reference_id'] ) ? $order_details['reference_id'] : '';
  $currency_code = isset( $order_details['currency_code'] ) ? $order_details['currency_code'] : '';
  $value = isset( $order_details['value'] ) ? $order_details['value'] : '';
  $description = $content_manager->get_content_description_by_reference_id( $reference_id );

  // New values
  $invoice_number = isset( $order_details['invoice_number'] ) ? $order_details['invoice_number'] : null;
  $has_VAT = isset( $order_details['has_VAT'] ) ? $order_details['has_VAT'] : $calculator->has_VAT();
  $VAT = isset( $order_details['vat'] ) ? $order_details['vat'] : $calculator->get_VAT();

  $user_transactions = $user_manager->transactions( $user_id_txn );

  if( ! current_user_can('manage_options') ) {
    if( ! in_array( $post->ID, $user_transactions ) ) {
      wp_redirect( esc_url( site_url() ) );
      die;
    }
  }

  $date = date_parse( $create_time );
  $date = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
  $due_date = $date;

  $options = get_option('humble_lms_options');
  $seller_info = $options['seller_info'];
  $seller_logo = $options['seller_logo'];
  $invoice_prefic = $options['invoice_prefix'];
  $invoice_text_before = $options['invoice_text_before'];
  $invoice_text_after = $options['invoice_text_after'];
  $invoice_text_footer = $options['invoice_text_footer'];
  $css = dirname( plugin_dir_url( __FILE__ ) ) . '/css/invoice/invoice.css';

  // Calculate price fields
  
  $price = $value;
  $subtotal = $value;
  $total = $value;
  $vat_diff = 0;
  $VAT_string = '';

  if( $has_VAT === 1 && $VAT ) { // inclusive
    $VAT_string = __('incl.', 'humble-lms');
    $vat_diff = $value / 100 * 19;
  } else if( $has_VAT === 2 ) { // exclusive
    $VAT_string = __('excl.', 'humble-lms');
    $price = $value - ($value / 119 * $VAT);
    $subtotal = $price;
    $vat_diff = $value / 119 * $VAT;
  }

  $price_vat = $value / 100 * $VAT;

  $price = $calculator->format_price( $price );
  $subtotal = $calculator->format_price( $subtotal );
  $total = $calculator->format_price( $total );
  $price_without_vat = $calculator->format_price( $price_vat );
  
  $content = '<img id="humble-lms-seller-logo" src="' . $seller_logo . '" alt="" />';

  $content .= '<div id="humble-lms-seller-customer-info">';
    $content .= '<div id="humble-lms-seller-info">' . $seller_info . '</div>';
    $content .= '<div id="humble-lms-customer-info">';
      $content .= '<p>';
        $content .= $invoice_number ? '<p>' . __('Invoice #', 'humble-lms') . ' ' . $invoice_number . '<br>' : '';
        $content .= __('Date', 'humble-lms') . ': ' . $date . '<br>';
        $content .= __('Due', 'humble-lms') . ': ' . $due_date;
      $content .= '</p>';
      $content .= $company ? '<strong>' . $company . '</strong><br>' : '';
      $content .= $first_name . ' ' . $last_name . '<br>';
      $content .= $postcode . ' ' . $city . '<br>';
      $content .= $address . '<br>';
      $content .= $vat_id ? $vat_id : '';
    $content .= '</div>';
  $content .= '</div>';

  $content .= '<h1>' . __('Invoice', 'humble-lms') . '</h1>';

  $content .= '<div id="humble-lms-invoice-text-before">' . wpautop( $invoice_text_before ) . '</div>';

  $content .= '<table id="humble-lms-invoice-table">';
  $content .= '<tr>';
    $content .= '<th>' . __('Description', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Quantity', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Price', 'humble-lms') . '</th>';
    $content .= '<th>' . __('Amount', 'humble-lms') . '</th>';
  $content .= '</tr>';
  $content .= '<tr>';
    $content .= '<td>' . $description . '</td>';
    $content .= '<td>1</td>';
    $content .= '<td>' . $currency_code . ' ' . $price . '</td>';
    $content .= '<td>' . $currency_code . ' ' . $price . '</td>';
  $content .= '</tr>';
  $content .= '<tr id="humble-lms-subtotal">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . __('Subtotal', 'humble-lms') . '</td>';
    $content .= '<td>' . $currency_code . ' ' . $subtotal . '</td>';
  $content .= '</tr>';
  $content .= '<tr id="humble-lms-taxes">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . $VAT_string . ' ' . $VAT . '%</td>';
    $content .= '<td>' . $currency_code . ' ' . $vat_diff . '</td>';
  $content .= '</tr>';
  $content .= '<tr class="humble-lms-total">';
    $content .= '<td colspan="2"></td>';
    $content .= '<td>' . __('Total', 'humble-lms') . '</td>';
    $content .= '<td>' . $currency_code . ' ' . $total . '</td>';
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
  $dompdf = new Dompdf();
  $options = $dompdf->getOptions();
  $options->setIsRemoteEnabled(true);
  $dompdf->setOptions($options);
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream('invoice-' . $invoice_number . '.pdf');
