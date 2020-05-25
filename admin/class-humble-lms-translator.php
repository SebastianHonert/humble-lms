<?php
/**
 * Content translation functionalities.
 * 
 * The plugin currently only provides support for Polylang:
 * https://de.wordpress.org/plugins/polylang/
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Translator' ) ) {

  class Humble_LMS_Translator {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     */
    public function __construct() {
      // ...
    }

    /**
     * Check if Polylang plugin is installed and active.
     * 
     * @since    0.0.1
     * @return   bool
     */
    public function has_polylang() {
      return function_exists('pll_current_language');
    }

    /**
     * Get the current language
     * 
     * @since    0.0.1
     * @return   string
     */
    public function current_language() {
      if( ! $this->has_polylang() ) {
        return '';
      }

      return pll_current_language();
    }
    
  }
  
}
