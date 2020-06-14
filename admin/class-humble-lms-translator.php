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
     * Get the current language.
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

    /**
     * Get language of a single post.
     * 
     * @since    0.0.1
     * @return   string
     */
    public function get_post_language( $post_id, $field = 'slug' ) {
      if( ! function_exists('pll_get_post_language') || ! get_post_status( $post_id ) ) {
        return '';
      }

      return pll_get_post_language( $post_id, $field );
    }
    
    /**
     * Get language of a single post.
     * 
     * @since    0.0.1
     * @return   string
     */
    public function flush_rewrite_rules() {
      $options = get_option('humble_lms_options');
      $current_language = $this->current_language();

      if( ! isset( $options['current_language'] ) || empty( $options['current_language'] ) || ( $options['current_language'] !== $current_language ) ) {
        $options['current_language'] = $current_language;
        update_option('humble_lms_options', $options);

        flush_rewrite_rules();
      }
    }

    /**
     * Set post language.
     * 
     * @since    0.0.1
     * @return   bool
     */
    public function set_post_language( $post_id, $lang = null ) {
      if( ! function_exists('pll_set_post_language') || ! get_post_status( $post_id ) || empty( $lang ) ) {
        return;
      }

      pll_set_post_language( $post_id, $lang );
    }

    /**
     * Get translated post id.
     * 
     * pll_get_post will return the original post ID if no translated post exists.
     * 
     * @since    0.0.1
     * @return   integer
     */
    public function get_translated_post_id( $post_id = null ) {
      if( ! function_exists('pll_get_post') || ! get_post_status( $post_id ) ) {
        return $post_id;
      }

      $translated_post_id = pll_get_post( $post_id, $this->current_language() );

      return $translated_post_id; 
    }
    
  }
  
}
