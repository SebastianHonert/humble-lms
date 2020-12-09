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

  $content_manager = new Humble_LMS_Content_Manager;
  $html = $content_manager->create_invoice_html( $post->ID );
  $txn_details = $content_manager->transaction_details( $post->ID );
  $invoice_number = $txn_details['invoice_number'];

  // Generate PDF
  $dompdf = new Dompdf();
  $options = $dompdf->getOptions();
  $options->setIsRemoteEnabled(true);
  $dompdf->setOptions($options);
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream('invoice-' . $invoice_number . '.pdf');
