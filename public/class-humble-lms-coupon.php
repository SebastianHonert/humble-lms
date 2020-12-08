
<?php

/**
 * This class validates and calculates coupon discounts.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Coupon' ) ) {

  class Humble_LMS_Coupon {

    /**
     * Initialize the class and set its properties.
     *
     * @since 0.0.7
     */
    public function __construct() {
      $this->options = get_option('humble_lms_options');
    }

    /**
     * Check if coupon is valid.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function validate( $code = null, $user_id = null, $activate = false ) {
      if( ! $code || ! $user_id ) {
        return false;
      }

      $code = sanitize_text_field( $code );
      $coupon_id = $this->exists( $code );

      // Coupon not found
      if( false === $coupon_id ) {
        return false;
      }

      // User not allowed to redeem coupon.
      if( ! $this->is_valid_for_user( $coupon_id, $user_id ) ) {
        return false;
      }

      // User has already redeemed the coupon.
      if( $this->redeemed( $coupon_id, $user_id ) ) {
        return false;
      }

      if( $activate ) {
        $this->activate_for_user( $coupon_id, $user_id );
      }

      return true;
    }

    /**
     * Check if coupon exists by code. Returns false or the coupon post ID.
     * 
     * @return Bool
     * @since 0.0.7
     */
    public function exists( $code = null ) {
      if( ! $code ) {
        return false;
      }

      $args = array(
        'post_type' => 'humble_lms_coupon',
        'numberposts' => 1,
        'post_status' => 'publish',
        'meta_query' => array(
          array(
            'key' => 'humble_lms_coupon_code',
            'value' => $code,
            'compare' => '=',
          )
        )
      );

      $coupon = get_posts( $args );

      if( ! $coupon ) {
        return false;
      }

      return $coupon[0]->ID;
    }

    /**
     * Check if coupon is valid for a single user.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function is_valid_for_user( $coupon_id, $user_id ) {
      if( ! $coupon_id || ! $user_id || 'humble_lms_coupon' !== get_post_type( $coupon_id ) ) {
        return false;
      }

      $allowed_users = get_post_meta( $coupon_id, 'humble_lms_coupon_users', true );

      if( ! isset( $allowed_users ) || empty( $allowed_users ) ) {
        return true;
      }

      if( ! isset( $allowed_users ) || empty( $allowed_users ) ) {
        return false;
      }

      return in_array( $user_id, $allowed_users );
    }

    /**
     * Redeem a coupon for a single user.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function redeem( $coupon_id = null, $user_id = null ) {
      if( ! $coupon_id || ! $user_id || 'humble_lms_coupon' !== get_post_type( $coupon_id ) ) {
        return false;
      }

      $redeemed = get_user_meta( $user_id, 'humble_lms_redeemed_coupons', true );

      if( ! is_array( $redeemed ) ) {
        $redeemed = array();
      }

      array_push( $redeemed, $coupon_id );
      $redeemed = array_unique( $redeemed );
      update_user_meta( $user_id, 'humble_lms_redeemed_coupons', $redeemed );

      $this->deactivate_for_user( $user_id );

      return true;
    }

    /**
     * Check if a user already redeemed a coupon.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function redeemed( $coupon_id = null, $user_id = null ) {
      if( ! $coupon_id || ! $user_id || 'humble_lms_coupon' !== get_post_type( $coupon_id ) ) {
        return false;
      }

      $redeemed_coupons = get_user_meta( $user_id, 'humble_lms_redeemed_coupons', true );

      if( ! isset( $redeemed_coupons ) || empty( $redeemed_coupons ) || ! is_array( $redeemed_coupons ) || ! in_array( $coupon_id, $redeemed_coupons ) ) {
        return false;
      }

      if( in_array( $coupon_id, $redeemed_coupons ) ) {
        return true;
      }

      return false;
    }

    /**
     * Activate coupon for a single user.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function activate_for_user( $coupon_id = null, $user_id = null ) {
      if( ! $coupon_id || ! $user_id || 'humble_lms_coupon' !== get_post_type( $coupon_id ) ) {
        return false;
      }

      update_user_meta( $user_id, 'humble_lms_active_coupon', $coupon_id );

      return true;
    }

    /**
     * Deactivate coupon for a single user.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function deactivate_for_user( $user_id = null ) {
      if( ! $user_id ) {
        return false;
      }

      delete_user_meta( $user_id, 'humble_lms_active_coupon' );

      return true;
    }

    /**
     * Clear redeemed coupons for a single user.
     * 
     * @return Boolean
     * @since 0.0.7
     */
    public function clear_for_user( $user_id = null ) {
      if( ! $user_id ) {
        return false;
      }

      delete_user_meta( $user_id, 'humble_lms_active_coupon' );
      delete_user_meta( $user_id, 'humble_lms_redeemed_coupons' );

      return true;
    }

    /**
     * Get active coupon id.
     * 
     * @return Integer
     * @since 0.0.7
     */
    public function get_active_coupon_id( $user_id = null ) {
      if( ! $user_id ) {
        if( ! is_user_logged_in() ) {
          return false;
        } else {
          $user_id = get_current_user_id();
        }
      }

      $active_coupon_id = get_user_meta( $user_id, 'humble_lms_active_coupon', true );

      return $active_coupon_id;

    }

    /**
     * This method calculates the new price based on the activated coupon.
     * 
     * @return Float
     * @since 0.0.7
     */
    public function calculate_price( $price = 0.00 ) {
      if( ! is_user_logged_in() || $price === 0.00 ) {
        return $price;
      }

      $active_coupon_id = $this->get_active_coupon_id();

      if( false === $active_coupon_id ) {
        return $price;
      }

      $coupon = get_post( $active_coupon_id );
      $coupon_code = get_post_meta( $active_coupon_id, 'humble_lms_coupon_code', true );

      if( ! $this->validate( $coupon_code, get_current_user_id() ) ) {
        return $price;
      }

      $coupon_type = get_post_meta( $active_coupon_id, 'humble_lms_coupon_type', true );
      $coupon_value = get_post_meta( $active_coupon_id, 'humble_lms_coupon_value', true );
      $coupon_targets = get_post_meta( $active_coupon_id, 'humble_lms_coupon_targets', true );

      switch( $coupon_type ) {
        case 'fixed_amount':
          $price = $price - $coupon_value;
          break;
        case 'percent':
          $discount = $price / 100 * $coupon_value;
          $price = $price - $discount;
          break;
        default:
          break;
      }

      if( $price <= 1 ) {
        $price = 1.00;
      }

      return $price;
    }

  }

}
