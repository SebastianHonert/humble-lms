<?php
/**
 * This class calculates prices and vat.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Calculator' ) ) {

  class Humble_LMS_Calculator {

    /**
     * Initialize the class and set its properties.
     *
     * @since 0.0.1
     */
    public function __construct() {
      $this->options = get_option('humble_lms_options');
    }

    /**
     * Get currency.
     * 
     * @return String
     * @since 0.0.1
     */
    public function currency() {
      $options = $this->options;
      $currency = isset( $options['currency'] ) ? $options['currency'] : '';

      return $currency;
    }

    /**
     * Get vat type.
     * 
     * 0 = none
     * 1 = inclusive
     * 2 = exclusive
     * 
     * @since 0.0.1
     */
    public function has_vat() {
      $options = $this->options;
      $has_vat = isset( $options['has_vat'] ) ? $options['has_vat'] : null;

      if( empty( $has_vat ) || ! isset( $has_vat) || ( $has_vat !== 1 && $has_vat !== 2 ) ) {
        return 0;
      }

      return $has_vat;
    }

    /**
     * Get vat amount.
     * 
     * @since 0.0.1
     */
    public function get_vat() {
      $options = $this->options;
      $vat = isset( $options['vat'] ) ? $options['vat'] : null;
      
      if( empty( $vat ) || ! isset( $vat) ) {
        return 0;
      }

      return (int)$vat;
    }

    /**
     * Get price including vat.
     * 
     * @since 0.0.1
     */
    public function get_vat_price( $price = 0 ) {
      if( ! $price ) {
        return $this->format_price( $price );
      }
      
      $vat = $this->get_vat();

      if( 0 === $vat ) {
        return $this->format_price( $price );
      }

      $has_vat = $this->has_vat();

      if( 0 === $has_vat ) {
        return $this->format_price( $price );
      }

      if( $has_vat === 1 ) { // inclusive
        $price = $price / (100 + (int)$vat) * 100;
      } else if( $has_vat === 2 ) { // exclusive
        $price = $price + ( $price / 100 * 19 );
      }

      return $this->format_price( $price );
    }

    /**
     * Get base price of membership by slug.
     * 
     * @since 0.0.1
     */
    public function get_membership_price_by_slug( $slug = null, $vat = false ) {
      if( ! $slug ) {
        return 0;
      }

      $content_manager = new Humble_LMS_Content_Manager;
      $membership = $content_manager::get_membership_by_slug( $slug );

      if( ! $membership ) {
        return 0;
      }

      $price = get_post_meta( $membership->ID, 'humble_lms_mbship_price', true );
      $price = filter_var( $price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

      // Return price without vat
      if( ! $vat ) {
        return $price;
      }

      $price = $this->get_vat_price( $price );

      return $this->format_price( $price );
    }

    /**
     * Get base price of membership by slug.
     * 
     * @since 0.0.1
     */
    public function get_membership_price_vat_diff( $slug = null ) {
      if( ! $slug ) {
        return;
      }
  
      $vat = $this->get_vat();

      if( 0 === $vat ) {
        return 0;
      }

      $has_vat = $this->has_vat();

      if( 0 === $has_vat ) {
        return 0;
      }

      if( $has_vat === 1 ) { // inclusive
        $price = $this->get_membership_price_by_slug( $slug, true );
        $diff = $price / 100 * $vat;
      } else if( $has_vat === 2 ) { // exclusive
        $price = $this->get_membership_price_by_slug( $slug );
        $diff = $price / 100 * $vat;
      }
      
      return $this->format_price( $diff );
    }

    /**
     * Format price by two digits.
     * 
     * @since 0.0.1
     */
    public function format_price( $price = 0 ) {
      if( ! $price ) {
        return 0;
      }
  
      return number_format((float)$price, 2, '.', '');
    }

    /**
     * Get price, subtotal, total, and VAT difference for a transaction by post ID.
     * 
     * @return Array
     * @since 0.0.3
     */
    public function sum_transaction( $post_id = null ) {
      $sum = array(
        'price' => 0,
        'subtotal' => 0,
        'total' => 0,
        'vat' => 0,
        'vat_diff' => 0,
        'vat_string' => '',
      );

      if( 'humble_lms_txn' !== get_post_type( $post_id ) ) {
        return $sum;
      }

      $content_manager = new Humble_LMS_Content_Manager;
      $transaction = $content_manager->transaction_details( $post_id );

      if( 1 === $transaction['has_vat'] ) {
        $sum['price'] = $transaction['value'];
        $sum['vat_string'] = __('incl. Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['vat_diff'] = ( $transaction['value'] / 100 ) * $transaction['vat'];
        $sum['subtotal'] = $transaction['value'];
        $sum['total'] = $transaction['value'];
      } else if( 2 === $transaction['has_vat'] ) {
        $sum['vat_string'] = __('plus Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['price'] = $transaction['value'] - ( $transaction['value'] / ( 100 + $transaction['vat'] ) * $transaction['vat'] );
        $sum['subtotal'] = $transaction['value'] - ( $transaction['value'] / ( 100 + $transaction['vat'] ) * $transaction['vat'] );
        $sum['vat_diff'] = $transaction['value'] / ( 100 + $transaction['vat'] ) * $transaction['vat'];
        $sum['total'] = $transaction['value'];
      } else {
        $sum['vat_string'] = __('Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['price'] = $transaction['value'];
        $sum['subtotal'] = $transaction['value'];
        $sum['vat_diff'] = 0;
        $sum['total'] = $transaction['value'];
      }

      $sum['price'] = $this->format_price( $sum['price'] );
      $sum['subtotal'] = $this->format_price( $sum['subtotal'] );
      $sum['total'] = $this->format_price( $sum['total'] );
      $sum['vat_diff'] = $this->format_price( $sum['vat_diff'] );
      
      return $sum;
    }

  }

}
