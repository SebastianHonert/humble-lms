<?php

$labels = array(
  'name'                  => _x( 'Transactions', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Transaction', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Transactions', 'humble-lms' ),
  'name_admin_bar'        => __( 'Transaction', 'humble-lms' ),
  'archives'              => __( 'Transaction Archives', 'humble-lms' ),
  'attributes'            => __( 'Transaction Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Transaction:', 'humble-lms' ),
  'all_items'             => __( 'All Transactions', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Transaction', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Transaction', 'humble-lms' ),
  'edit_item'             => __( 'Edit Transaction', 'humble-lms' ),
  'update_item'           => __( 'Update Transaction', 'humble-lms' ),
  'view_item'             => __( 'View Transaction', 'humble-lms' ),
  'view_items'            => __( 'View Transactions', 'humble-lms' ),
  'search_items'          => __( 'Search Transaction', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into transaction', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this transaction', 'humble-lms' ),
  'items_list'            => __( 'Transactions list', 'humble-lms' ),
  'items_list_navigation' => __( 'Transactions list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter transactions list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => __('invoice', 'humble-lms'),
  'with_front'            => true,
);

$args = array(
  'label'                 => __( 'Transaction', 'humble-lms' ),
  'description'           => __( 'Transaction', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'revisions' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-chart-line',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => false,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => true,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_txn', $args );

// Transaction meta boxes

function humble_lms_txn_add_meta_boxes() {
  add_meta_box( 'humble_lms_order_details_mb', __('Order details for this transaction', 'humble-lms'), 'humble_lms_order_details_mb', 'humble_lms_txn', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_txn_add_meta_boxes' );

// Order details meta box

function humble_lms_order_details_mb() {
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $user_id_txn = get_post_meta( $post->ID, 'humble_lms_txn_user_id', true );

  $order_details = get_post_meta( $post->ID, 'humble_lms_order_details', false );
  $order_details = isset( $order_details[0] ) ? $order_details[0] : $order_details;

  $user_id = isset( $order_details['user_id'] ) ? $order_details['user_id'] : '';
  $user = get_user_by( 'id', $user_id );

  $content_manager = new Humble_LMS_Content_Manager;
  $transaction_details = $content_manager->transaction_details( $post->ID );
  
  if( $user ) {
    $userdata = get_userdata( $user->ID );

    echo '<label for="humble_lms_user"><strong>' . __('User details', 'humble-lms') . '</strong></label>';
    echo '<p>' . __('Login name', 'humble-lms') . ': ' . '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->user_login . '</a>';
    echo '<br>' . __('First name', 'humble-lms') . ': ' . $user->first_name;
    echo '<br>' . __('Last name', 'humble-lms') . ': ' . $user->last_name;
    echo '<br>' . __('Registered at', 'humble-lms') . ': ' . $userdata->user_registered;
    echo '<br>' . __('User roles', 'humble-lms') . ': ' . implode(',', $user->roles);
    echo '</p>';
  } else {
    echo '<p><strong>' . __('The user ID of this transaction does not match an existing user.', 'humble-lms') . '</strong></p>';
  }

  echo '<label for="user_id">' . __('User ID', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="user_id" value="' . $user_id . '"></p>';

  echo '<label for="order_id">' . __('Order ID', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="order_id" value="' . $transaction_details['order_id'] . '"></p>';

  echo '<label for="email_address">' . __('Email address', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="email" name="email_address" value="' . $transaction_details['email_address'] . '"></p>';

  echo '<label for="payer_id">' . __('Payer ID', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="payer_id" value="' . $transaction_details['payer_id'] . '"></p>';

  echo '<label for="status">' . __('Transaction status', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="status" value="' . $transaction_details['status'] . '"></p>';

  echo '<label for="payment_service_provider">' . __('Payment service provider', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="payment_service_provider" value="' . $transaction_details['payment_service_provider'] . '"></p>';

  echo '<label for="create_time">' . __('Create time', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="create_time" value="' . $transaction_details['create_time'] . '"></p>';

  echo '<label for="update_time">' . __('Update time', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="update_time" value="' . $transaction_details['update_time'] . '"></p>';

  echo '<label for="given_name">' . __('Given name', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="given_name" value="' . $transaction_details['given_name'] . '"></p>';

  echo '<label for="surname">' . __('Surname', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="surname" value="' . $transaction_details['surname'] . '"></p>';

  echo '<label for="reference_id">' . __('Reference ID', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="reference_id" value="' . $transaction_details['reference_id'] . '"></p>';

  echo '<label for="currency_code">' . __('Currency code', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="currency_code" value="' . $transaction_details['currency_code'] . '"></p>';

  echo '<label for="value">' . __('Value', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="value" value="' . $transaction_details['value'] . '"></p>';

  echo '<label for="vat">' . __('vat in %', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="number" min="0" max="100" step="1" name="vat" value="' . $transaction_details['vat'] . '"></p>';

  echo '<label for="vat-type">' . __('vat type (0 = none, 1 = inclusive, 2 = exclusive)', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="number" min="0" max="2" step="1" name="has_vat" value="' . $transaction_details['has_vat'] . '"></p>';

  echo '<label for="invoice-number">' . __('Invoice #', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="invoice_number" value="' . $transaction_details['invoice_number'] . '"></p>';

  echo '<br><p><strong>' . __('Billing information', 'humble-lms') . '</strong></p>';

  echo '<label for="first-name">' . __('First name', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="first_name" value="' . $transaction_details['first_name'] . '"></p>';

  echo '<label for="last-name">' . __('Last name', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="last_name" value="' . $transaction_details['last_name'] . '"></p>';
  
  echo '<label for="country">' . __('Country', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="country" value="' . $transaction_details['country'] . '"></p>';

  echo '<label for="postcode">' . __('Postcode', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="postcode" value="' . $transaction_details['postcode'] . '"></p>';

  echo '<label for="city">' . __('City', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="city" value="' . $transaction_details['city'] . '"></p>';

  echo '<label for="address">' . __('Address', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="address" value="' . $transaction_details['address'] . '"></p>';

  echo '<label for="company">' . __('Company', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="company" value="' . $transaction_details['company'] . '"></p>';

  echo '<label for="vat_id">' . __('VAT ID', 'humble-lms') . '</label>';
  echo '<p class="humble-lms-less-margin"><input class="widefat" type="text" name="vat_id" value="' . $transaction_details['vat_id'] . '"></p>';

  // User ID meta
  echo '<label for="user_id_txn">' . __('User ID', 'humble-lms') . '</label>';
  echo '<input class="widefat" type="number" name="humble_lms_txn_user_id" value="' . $user_id_txn . '" disabled="disabled">';

  // Invoice
  echo '<p><a class="button" href="' . esc_url( get_permalink( $post->ID ) ) . '" target="_blank">' . __('Show invoice', 'humble-lms') . '</a> <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
}

// Save metabox data

function humble_lms_save_txn_meta_boxes( $post_id, $post )
{
  $nonce = ! empty( $_POST['humble_lms_meta_nonce'] ) ? $_POST['humble_lms_meta_nonce'] : '';

  if( ! wp_verify_nonce( $nonce, 'humble_lms_meta_nonce' ) ) {
    return $post_id;
  }
  
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }

  if( ! is_admin() ) {
    return false;
  }
  
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return $post_id;
  }
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_txn' ) {
    return false;
  }

  $txn_meta['humble_lms_order_details'] = array(
    'user_id' => (int)$_POST['user_id'],
    'order_id' => sanitize_text_field( $_POST['order_id'] ),
    'email_address' => sanitize_email( $_POST['email_address'] ),
    'payer_id' => sanitize_text_field( $_POST['payer_id'] ),
    'status' => sanitize_text_field( $_POST['status'] ),
    'payment_service_provider' => sanitize_text_field( $_POST['payment_service_provider'] ),
    'create_time' => sanitize_text_field( $_POST['create_time'] ),
    'update_time' => sanitize_text_field( $_POST['update_time'] ),
    'given_name' => sanitize_text_field( $_POST['given_name'] ),
    'surname' => sanitize_text_field( $_POST['surname'] ),
    'reference_id' => sanitize_text_field( $_POST['reference_id'] ),
    'currency_code' => sanitize_text_field( $_POST['currency_code'] ),
    'value' => sanitize_text_field( $_POST['value'] ),
    'vat' => (int)$_POST['vat'],
    'has_vat' => (int)$_POST['has_vat'],
    'invoice_number' => sanitize_text_field( $_POST['invoice_number'] ),

    // Billing information
    'first_name' => sanitize_text_field( $_POST['first_name'] ),
    'last_name' => sanitize_text_field( $_POST['last_name'] ),
    'country' => sanitize_text_field( $_POST['country'] ),
    'postcode' => sanitize_text_field( $_POST['postcode'] ),
    'city' => sanitize_text_field( $_POST['city'] ),
    'address' => sanitize_text_field( $_POST['address'] ),
    'company' => sanitize_text_field( $_POST['company'] ),
    'vat_id' => sanitize_text_field( $_POST['vat_id'] ),
  );

  if( $txn_meta['humble_lms_order_details']['vat'] > 100 || $txn_meta['humble_lms_order_details']['vat'] < 0 ) {
    $txn_meta['humble_lms_order_details']['vat'] = 0;
  }

  if( $txn_meta['humble_lms_order_details']['has_vat'] < 0 || $txn_meta['humble_lms_order_details']['has_vat'] > 2 ) {
    $txn_meta['humble_lms_order_details']['has_vat'] = 0;
  }

  $txn_meta['humble_lms_txn_user_id'] = (int)$_POST['user_id'];

  if( ! empty( $txn_meta ) && sizeOf( $txn_meta ) > 0 )
  {
    foreach ($txn_meta as $key => $value)
    {
      if( $post->post_type == 'revision' ) return; // Don't store custom data twice

      if( get_post_meta( $post->ID, $key, FALSE ) ) {
        update_post_meta( $post->ID, $key, $value );
      } else {
        add_post_meta( $post->ID, $key, $value );
      }

      if( ! $value ) delete_post_meta( $post->ID, $key ); // Delete if blank
    }
  }
}

add_action('save_post', 'humble_lms_save_txn_meta_boxes', 1, 2);
