<?php

/**
 * Fired when the plugin is uninstalled.
 * 
 * @since      0.0.4
 *
 * @package    Humble_LMS
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

global $wpdb;

// Check if data should be deleted
$options = is_multisite() ? get_site_option('humble_lms_options') : get_option('humble_lms_options');
$delete_plugin_data_on_uninstall = isset( $options['delete_plugin_data_on_uninstall'] ) && ( 1 === $options['delete_plugin_data_on_uninstall'] ) ? true : false;

// Only delete data if option is checked
if( $delete_plugin_data_on_uninstall ) {

  // Delete user roles
  remove_role( 'humble_lms_student' );

  // Options to delete
  $delete_options = array(
    'humble_lms_options',
    'humble_lms_invoice_counter',
  );

  // Custom post types content to delete
  $delete_cpts = array(
    'humble_lms_activity',
    'humble_lms_award',
    'humble_lms_cert',
    'humble_lms_coupon',
    'humble_lms_course',
    'humble_lms_email',
    'humble_lms_lesson',
    'humble_lms_mbship',
    'humble_lms_question',
    'humble_lms_quiz',
    'humble_lms_track',
    'humble_lms_txn',
  );

  // Uninstall CPT content
  function hlms_uninstall_delete_cpt_posts() {
    foreach( $delete_cpts as $cpt ) {
      $args = array('post_type' => $cpt, 'posts_per_page' => -1);
      $posts = get_posts( $args );
      
      foreach ( $posts as $post ) {
        wp_delete_post( $post->ID, false );
      }
    }
  }

  // Uninstall terms
  function hlms_uninstall_delete_terms() {
    $terms = get_terms( array(
      'taxonomy' => array(
        'humble_lms_tax_course_level',
        'humble_lms_tax_provider',
      ),
      'fields' => 'all',
      'hide_empty' => false
    ) );

    if( ! empty( $terms ) ) {
      foreach( $terms as $term ) {
        wp_delete_term( $term->term_id, $term->taxonomy );
      }
    }
  }

  // Uninstall post meta
  function hlms_uninstall_delete_post_meta() {
    $sql = 'SELECT * FROM ' . $wpdb->prefix . 'postmeta';
    $results = $wpdb->get_results( $sql );    

    if( ! empty( $results ) ) {
      foreach( $results as $result ) {
        if( strpos( $result->meta_key, 'humble_lms_' ) !== false ) {
          delete_post_meta( $result->meta_id, $result->meta_key );
        }
      }
    }
  }

  // Uninstall user meta
  function hlms_uninstall_delete_user_meta() {
    $sql = 'SELECT * FROM ' . $wpdb->prefix . 'usermeta';
    $results = $wpdb->get_results( $sql );    

    if( ! empty( $results ) ) {
      foreach( $results as $result ) {
        if( strpos( $result->meta_key, 'humble_lms_' ) !== false ) {
          delete_user_meta( $result->$user_id, $result->meta_key );
        }
      }
    }
  }

  // Alright, let's delete some content!
  if( ! is_multisite() ) {

    // Delete plugin options
    foreach( $delete_options as $option ) {
      delete_option( $option );
    }

    // Delete custom post type content
    hlms_uninstall_delete_cpt_posts();

    // Delete terms
    hlms_uninstall_delete_terms();

    // Delete post meta
    hlms_uninstall_delete_post_meta();

    // Delete user meta
    hlms_uninstall_delete_user_meta();

    // Delete user roles
    remove_role( 'humble_lms_student' );
  }
  
  else {

    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

    foreach( $blog_ids as $blog_id ) {
      switch_to_blog( $blog_id );

      // Delete plugin options
      foreach( $delete_options as $option ) {
        delete_blog_option( $blog_id, $option );
      }

      // Delete custom post type content
      hlms_uninstall_delete_cpt_posts();

      // Delete terms
      hlms_uninstall_delete_terms();

      // Delete post meta
      hlms_uninstall_delete_post_meta();

      // Delete user meta
      hlms_uninstall_delete_user_meta();

      // Delete user roles
      remove_role( 'humble_lms_student' );
    }

    restore_current_blog();
  }

}
