<?php
/**
 * This class calculates prices and VAT.
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
     * Get VAT type.
     * 
     * 0 = none
     * 1 = inclusive
     * 2 = exclusive
     * 
     * @since 0.0.1
     */
    public function has_VAT() {
      $options = $this->options;
      $hasVAT = $options['hasVAT'];

      if( empty( $hasVAT ) || ! isset( $hasVAT) || ( $hasVAT !== 1 && $hasVAT !== 2 ) ) {
        return 0;
      }

      return $hasVAT;
    }

    /**
     * Get VAT amount.
     * 
     * @since 0.0.1
     */
    public function get_VAT() {
      $options = $this->options;
      $VAT = $options['VAT'];
      
      if( empty( $VAT ) || ! isset( $VAT) ) {
        return 0;
      }

      return (int)$VAT;
    }

    /**
     * Get price including VAT.
     * 
     * @since 0.0.1
     */
    public function get_VAT_price( $price = 0 ) {
      if( ! $price ) {
        return $this->format_price( $price );
      }
      
      $VAT = $this->get_VAT();

      if( 0 === $VAT ) {
        return $this->format_price( $price );
      }

      $has_VAT = $this->has_VAT();

      if( 0 === $has_VAT ) {
        return $this->format_price( $price );
      }

      if( $has_VAT === 1 ) { // inclusive
        $price = $price / (100 + (int)$VAT) * 100;
      } else if( $has_VAT === 2 ) { // exclusive
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

      // Return price without VAT
      if( ! $vat ) {
        return $price;
      }

      $price = $this->get_VAT_price( $price );

      return $this->format_price( $price );
    }

    /**
     * Get base price of membership by slug.
     * 
     * @since 0.0.1
     */
    public function get_membership_price_VAT_diff( $slug = null ) {
      if( ! $slug ) {
        return;
      }
  
      $VAT = $this->get_VAT();

      if( 0 === $VAT ) {
        return 0;
      }

      $has_VAT = $this->has_VAT();

      if( 0 === $has_VAT ) {
        return 0;
      }

      if( $has_VAT === 1 ) { // inclusive
        $price = $this->get_membership_price_by_slug( $slug, true );
        $diff = $price / 100 * $VAT;
      } else if( $has_VAT === 2 ) { // exclusive
        $price = $this->get_membership_price_by_slug( $slug );
        $diff = $price / 100 * $VAT;
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

  }

}
