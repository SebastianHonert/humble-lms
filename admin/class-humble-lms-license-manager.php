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

    private $license_status;
    private $license_key;
    private $secret_key = '5fba5d909a6c83.38241175';
    private $license_server_url = 'https://humblelms.de';
    private $item_reference = 'Humble LMS';

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.9
     */
    public function __construct() {
      $options = get_option('humble_lms_options');

      $this->license_status = $options['license_status'];
      $this->license_key = $options['license_key'];
    }

    /**
     * Activate/deactivate license.
     * 
     * @return Boolean
     * @since 0.0.0
     */
    public function activate( $license_key = null, $activate = true ) {
      if( ! $license_key ) {
        $license_key = $this->license_key;
      }

      $sml_action = $activate ? 'slm_activate' : 'slm_deactivate';

      $api_params = array(
        'slm_action' => $sml_action,
        'secret_key' => $this->secret_key,
        'license_key' => $license_key,
        'registered_domain' => $_SERVER['SERVER_NAME'],
        'item_reference' => $this->item_reference,
      );

      $query = add_query_arg( $api_params, $this->license_server_url );
      $response = wp_remote_get( esc_url_raw( $query ), array(
        'timeout' => 20,
        'sslverify' => false
      ) );

      if( is_wp_error( $response ) ) {
        return false;
      }

      $license_data = json_decode( wp_remote_retrieve_body( $response ) );

      if( isset( $license_data ) && $license_data->result === 'success' ) {
        return true;
      }

      return false;
    }

  }
  
}
