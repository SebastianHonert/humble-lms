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
      $currency = isset( $this->options['currency'] ) ? $this->options['currency'] : '';

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
      $has_vat = isset( $this->options['has_vat'] ) ? $this->options['has_vat'] : null;

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
      $has_vat = $this->has_vat();
      
      if( ! isset( $has_vat ) || empty( $has_vat ) || $has_vat == 0 ) {
        return 0;
      }

      $vat = isset( $this->options['vat'] ) ? $this->options['vat'] : null;
      
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
        $price = $this->calculate_discount( $price );
        return $this->format_price( $price );
      }

      if( $has_vat === 1 ) { // inclusive
        $price = $price / (100 + (int)$vat) * 100;
      } else if( $has_vat === 2 ) { // exclusive
        $price = $price + ( $price / 100 * 19 );
      }

      $price = $this->calculate_discount( $price );

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

      $price = get_post_meta( $membership->ID, 'humble_lms_fixed_price', true );
      $price = $this->format_price( $price );

      // Return price without vat
      if( ! $vat ) {
        return $this->calculate_discount( $price );
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
        return 0.00;
      }

      $price = number_format((float)$price, 2, '.', '');

      return $price;
    }

    /**
     * Get price, subtotal, total, discount, and VAT / VAT difference for a single price value.
     * 
     * @return Array
     * @since 0.0.3
     */
    public function sum_price( $price = null, $coupon_id = false ) {
      if( ! $price ) {
        return 0.00;
      }

      $price = $this->format_price( $price );

      $sum = array(
        'price' => 0,
        'discount' => 0,
        'discount_string' => '',
        'subtotal' => 0,
        'total' => 0,
        'vat' => 0,
        'vat_diff' => 0,
        'vat_string' => '',
      );

      $vat = $this->get_vat();
      $has_vat = $this->has_vat();

      // Active coupon
      $has_coupon = false;
      $coupon_value = 0;
      $active_coupon_id = false !== $coupon_id && 'humble_lms_coupon' === get_post_type( $coupon_id ) ? $coupon_id : get_user_meta( get_current_user_id(), 'humble_lms_active_coupon', true );
      $coupon = get_post( $active_coupon_id );

      if( $coupon ) {
        $has_coupon = true;

        $coupon_code = get_post_meta( $active_coupon_id, 'humble_lms_coupon_code', true );
        $coupon_type = get_post_meta( $active_coupon_id, 'humble_lms_coupon_type', true );
        $coupon_value = get_post_meta( $active_coupon_id, 'humble_lms_coupon_value', true );

        switch( $coupon_type ) {
          case 'percent':
            $sum['discount_string'] = $this->format_price( $coupon_value ) . '%';
          break;
          case 'fixed_amount':
            $sum['discount_string'] = $this->currency() . '&nbsp;' . $this->format_price( $coupon_value );
          break;
          default:
          break;
        }
      }

      if( $has_coupon ) {
        $sum['discount'] = $coupon_type === 'percent' ? ( $price * 100 / ( 100 - $coupon_value ) ) / 100 * $coupon_value : $coupon_value;
      }

      $sum['price'] = $price;

      if( 1 === $has_vat ) {
        $sum['subtotal'] = $sum['price'];
        $sum['total'] = $sum['price'];
        $sum['vat_diff'] = ( $sum['subtotal'] / 100 ) * $vat;
        $sum['vat_string'] = __('incl. Tax', 'humble-lms') . ' ' . $vat . '%';
      } else if( 2 === $has_vat ) {
        $sum['subtotal'] = $sum['price'];
        $sum['vat_diff'] = $sum['price'] / 100 * $vat;
        $sum['total'] = $sum['subtotal'] + $sum['vat_diff'];
        $sum['vat_string'] = __('plus Tax', 'humble-lms') . ' ' . $vat . '%';
      } else {
        $sum['subtotal'] = $sum['price'];
        $sum['total'] = $sum['subtotal'];
        $sum['vat_diff'] = 0;
        $sum['vat_string'] = __('Tax', 'humble-lms') . ' ' . $vat . '%';
      }

  
      $sum['price'] = $this->format_price( $sum['price'] );
      $sum['discount'] = $this->format_price( $sum['discount'] );
      $sum['coupon_value'] = $this->format_price( $coupon_value );
      $sum['subtotal'] = $this->format_price( $sum['subtotal'] );
      $sum['total'] = $this->format_price( $sum['total'] );
      $sum['vat'] = $this->format_price( $vat );
      $sum['vat_diff'] = $this->format_price( $sum['vat_diff'] );
      
      return $sum;
    }

    /**
     * Get price, subtotal, total, and VAT difference for a transaction by post ID.
     * 
     * @return Array
     * @since 0.0.3
     */
    public function sum_transaction( $txn_id = null ) {
      $sum = array(
        'price' => 0,
        'discount' => 0,
        'discount_string' => '',
        'subtotal' => 0,
        'total' => 0,
        'vat_diff' => 0,
        'vat_string' => '',
      );

      if( 'humble_lms_txn' !== get_post_type( $txn_id ) ) {
        return $sum;
      }

      $content_manager = new Humble_LMS_Content_Manager;
      $transaction = $content_manager->transaction_details( $txn_id );
      $has_coupon = $this->transaction_has_coupon( $txn_id );

      if( $has_coupon ) {
        switch( $transaction['coupon_type'] ) {
          case 'percent':
            $transaction['value'] = $transaction['value'] * 100 / (100 - $transaction['coupon_value'] );
            $sum['discount_string'] = $this->format_price( $transaction['coupon_value'] ) . '%';
          break;
          case 'fixed_amount':
            $transaction['value'] = 2 === $transaction['has_vat'] ? $transaction['value'] + $transaction['coupon_value'] + ( $transaction['coupon_value'] * $transaction['vat'] / 100 ) : $transaction['value'] + $transaction['coupon_value'];
            $sum['discount_string'] = $this->currency() . '&nbsp;' . $this->format_price( $transaction['coupon_value'] );
          break;
          default:
          break;
        }
      }

      if( 1 === $transaction['has_vat'] ) {
        $sum['price'] = $transaction['value'];

        if( $has_coupon ) {
          $sum['discount'] = $transaction['coupon_type'] === 'percent' ? $sum['price'] / 100 * $transaction['coupon_value'] : $transaction['coupon_value'];
        }

        $sum['subtotal'] = $sum['price'] - $sum['discount'];
        $sum['vat_string'] = __('incl. Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['vat_diff'] = ( $sum['subtotal'] / 100 ) * $transaction['vat'];
        $sum['total'] = $sum['subtotal'];
      } else if( 2 === $transaction['has_vat'] ) {
        $sum['price'] = $transaction['value'] - ( $transaction['value'] / ( 100 + $transaction['vat'] ) * $transaction['vat'] );

        if( $has_coupon ) {
          $sum['discount'] = $transaction['coupon_type'] === 'percent' ? $sum['price'] / 100 * $transaction['coupon_value'] : $transaction['coupon_value'];
        }

        $sum['vat_string'] = __('plus Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['subtotal'] = $sum['price'] - $sum['discount'];
        $sum['vat_diff'] = $sum['subtotal'] / 100 * $transaction['vat'];
        $sum['total'] = $sum['subtotal'] + $sum['vat_diff'];
      } else {
        $sum['price'] = $transaction['value'];

        if( $has_coupon ) {
          $sum['discount'] = $transaction['coupon_type'] === 'percent' ? $sum['price'] / 100 * $transaction['coupon_value'] : $transaction['coupon_value'];
        }

        $sum['subtotal'] = $sum['price'] - $sum['discount'];
        $sum['vat_string'] = __('Tax', 'humble-lms') . ' ' . $transaction['vat'] . '%';
        $sum['vat_diff'] = 0;
        $sum['total'] = $sum['subtotal'];
      }

      $sum['price'] = $this->format_price( $sum['price'] );
      $sum['discount'] = $this->format_price( $sum['discount'] );
      $sum['coupon_value'] = $this->format_price( $transaction['coupon_value'] );
      $sum['subtotal'] = $this->format_price( $sum['subtotal'] );
      $sum['total'] = $this->format_price( $sum['total'] );
      $sum['vat_diff'] = $this->format_price( $sum['vat_diff'] );
      
      return $sum;
    }

    /**
     * Check if a transaction has complete coupon info.
     * 
     * @since 0.0.7
     * @return Boolean
     */
    public function transaction_has_coupon( $txn_id = null ) {
      if( ! $txn_id || 'humble_lms_txn' !== get_post_type( $txn_id ) ) {
        return false;
      }

      $content_manager = new Humble_LMS_Content_Manager;
      $transaction = $content_manager->transaction_details( $txn_id );

      return ! empty( $transaction['coupon_id'] ) && ! empty( $transaction['coupon_code'] ) && ! empty( $transaction['coupon_type'] ) && ! empty( $transaction['coupon_value'] );
    }

    /**
     * Get membership price for upgrade if user alread purchased a membership.
     * 
     * @return Float
     * @since 0.0.3
     */
    public function upgrade_membership_price( $post_id = null ) {
      if( 'humble_lms_mbship' !== get_post_type( $post_id ) || ! is_user_logged_in() ) {
        return 0.00;
      }

      $price = $this->get_price( $post_id );
      $user_membership = get_user_meta( get_current_user_id(), 'humble_lms_membership', true );

      if( $user_membership === 'free' || ! $user_membership ) {
        $price = $this->format_price( $price );
        // $price = $this->calculate_discount( $price );

        return $price;
      } else {
        $membership = Humble_LMS_Content_Manager::get_membership_by_slug( $user_membership );
        $user_membership_price = $this->get_price( $membership->ID );
      }

      $price = $price - $user_membership_price;
      $price = $price < 0 ? 0 : $price;

      // $price = $this->calculate_discount( $price );

      return $this->format_price( $price );
    }

    /**
     * Get the sum of all track course prices.
     * 
     * @return Float
     * @since 0.0.3
     */
    public function track_courses_sum( $track_id = null, $user_id = null ) {
      if( 'humble_lms_track' !== get_post_type( $track_id ) ) {
        return 0.00;
      }
  
      $courses = Humble_LMS_Content_Manager::get_track_courses( $track_id, true );

      if( empty( $courses ) ) {
        return 0.00;
      }

      if( $user_id ) {
        $user = new Humble_LMS_Public_User;
        $purchases = $user->purchases( $user_id );

        foreach( $purchases as $key => $purchase ) {
          if( 'humble_lms_course' !== get_post_type( $purchase ) ) {
            unset( $purchases[$key] );
          }
        }

        $courses = array_diff( $courses, $purchases );
      }

      $sum = 0.00;

      foreach( $courses as $course_id ) {
        $course_price = get_post_meta( $course_id, 'humble_lms_fixed_price', true );
  
        if( isset( $course_price ) && ! empty( $course_price ) ) {
          $sum += $course_price;
        }
      }

      $sum = $this->calculate_discount( $sum );

      return $this->format_price( $sum );
    }

    /**
     * Determine if track price is greater than price or courses, purchased courses, and 
     * 
     * @return Bool
     * @since 0.0.3
     */
    public function is_track_purchase_lucrative( $price = 0, $track_id = null, $user_id = null ) {
      $price = $this->calculate_discount( $price );

      if( ! get_user_by( 'id', $user_id ) ) {
        if( ! is_user_logged_in() ) {
          return true;
        } else {
          $user_id = get_current_user_id();
        }
      }

      if( 'humble_lms_track' !== get_post_type( $track_id ) ) {
        return false;
      }

      $public_user = new Humble_LMS_Public_User;

      if( $public_user->purchased_all_track_courses( $user_id, $track_id ) ) {
        return false;
      }

      if( ! $user_id && $price <= $this->track_courses_sum( $track_id ) ) {
        return true;
      }

      if( $price <= $this->track_courses_sum( $track_id, $user_id ) ) {
        return true;
      }

      return false;
    }

    /**
     * Calculate discount for a price.
     * 
     * @return Float
     * @since 0.0.7
     */
    public function calculate_discount( $price = 0.00, $_coupon_id = false ) {
      if( $price === 0.00 ) {
        return $price;
      }

      $coupon = new Humble_LMS_Coupon;
      $options_manager = new Humble_LMS_Admin_Options_Manager;
  
      $price = $options_manager->has_coupons() ? $coupon->calculate_price( $price, $_coupon_id ) : $price;

      return $this->format_price( $price );
    }

    /**
     * Get price of items that can be sold for a fixed price.
     * 
     * @return  float
     * @since   0.0.1
     */
    public function get_price( $post_id = null, $with_vat = false, $discount = true ) {
      $price = 0.00;

      if( ! get_post( $post_id ) )
        return $price;

      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course',
        'humble_lms_mbship',
      );

      if( ! in_array( get_post_type( $post_id ), $allowed_post_types ) )
        return $price;
      
      $price = get_post_meta($post_id, 'humble_lms_fixed_price', true);
      $price = $discount ? $this->calculate_discount( $price ) : $price;
      $price = $this->format_price( $price );

      return $price;

      if( ! $with_vat ) {
        return $price;
      }

      // Value added tax
      $vat = $this->get_vat();
      $has_vat = $this->has_vat();

      if( $has_vat === 0 || $vat === 0 || ! $vat ) {
        return $price;
      }

      if( $has_vat === 1 ) { // Inclusive of vat
        $price = $price / (100 + $vat ) * 100;
      } else if( $has_vat === 2 ) { // Exclusive of vat
        $price = $price + ( $price / 100 * $vat );
      }

      $price = $this->format_price( $price );

      return $price;
    }

    /**
     * Get price of items that can be sold for a fixed price.
     * 
     * @return  float
     * @since   0.0.1
     */
    public function display_price( $post_id = null, $hide_vat = false ) {
      $price = 0.00;

      if( ! get_post( $post_id ) )
        return $price;

      $allowed_post_types = array(
        'humble_lms_track',
        'humble_lms_course',
        'humble_lms_mbship',
      );

      if( ! in_array( get_post_type( $post_id ), $allowed_post_types ) )
        return $price;

      $price = get_post_meta($post_id, 'humble_lms_fixed_price', true);
      $price = $this->calculate_discount( $price );

      // Hide additional VAT?
      if( $hide_vat ) {
        $_hide_vat = $this->options['hide_vat'];

        if( 1 === $_hide_vat ) {
          return $this->format_price( $price );
        }
      }

      // Value added tax
      $vat = $this->get_vat();
      $has_vat = $this->has_vat();

      if( $has_vat === 0 || $vat === 0 || ! $vat ) {
        return $this->format_price( $price );
      }

      if( $has_vat === 1 ) { // Inclusive of vat
        $price = $price;
      } else if( $has_vat === 2 ) { // Exclusive of vat
        $price = $price + ( $price / 100 * $vat );
      }

      return $this->format_price( $price );
    }

  }

}
