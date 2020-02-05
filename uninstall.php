<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://sebastianhonert.com
 * @since      0.0.1
 *
 * @package    Humble_LMS
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

// Remove custom posts and pages
$custom_page_login = get_page_by_title('Humble LMS Login', OBJECT, 'page');
$custom_page_registration = get_page_by_title('Humble LMS Registration', OBJECT, 'page');
$custom_page_lost_password = get_page_by_title('Humble LMS Lost Password', OBJECT, 'page');

if( $custom_page_login ) { wp_delete_post( $custom_page_login->ID ); }
if( $custom_page_registration ) { wp_delete_post( $custom_page_registration->ID ); }
if( $custom_page_lost_password ) { wp_delete_post( $custom_page_lost_password->ID ); } 
