<?php
/**
 * The public-facing AJAX functionality.
 *
 * Creates the various functions used for AJAX on the front-end.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/public
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Public_Ajax' ) ) {

	class Humble_LMS_Public_Ajax {
		/**
		 * An example AJAX callback.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function mark_lesson_complete() {
			// Check the nonce for permission.
			if( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'humble_lms' ) ) {
				die( 'Permission Denied' );
      }
      
			// Define an empty response array.
			$response = array(
				'status'  => 200,
				'content' => 'This is an AJAX response.'
      );
      
			// Terminate the callback and return a proper response.
			wp_die( json_encode( $response ) );
		}
  }
  
}