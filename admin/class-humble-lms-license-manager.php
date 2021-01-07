<?php
/**
 * License manager class.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_License_Manager' ) ) {

  class Humble_LMS_License_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.9
     */
    public function __construct() {
      $this->license_status = 0;
      $this->secret_key = '5fba5d909a6c83.38241175';
      $this->license_server_url = 'https://humblelms.de';
      $this->item_reference = 'Humble LMS';
      $this->license_key = '';
    }

    /**
     * Get license status.
     * 
     * @since 0.0.9
     */
    public function get_status() {
      return $this->license_status;
    }

    /**
     * Deactivate license.
     * 
     * @since 0.0.0
     */
    public function deactivate() {
      $options = get_option('humble_lms_options');

      $api_params = array(
        'slm_action' => 'slm_deactivate',
        'secret_key' => $this->secret_key,
        'license_key' => $options['license_key'],
        'registered_domain' => $_SERVER['SERVER_NAME'],
        'item_reference' => urlencode( $this->item_reference ),
      );
    }

  }
  
}
