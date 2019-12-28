<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://minimalwordpress.com/humble-lms
 * @since             0.0.1
 * @package           Humble_LMS
 *
 * @wordpress-plugin
 * Plugin Name:       Humble LMS
 * Plugin URI:        https://minimalwordpress.com/humble-lms
 * Description:       Humble LMS is a learning management system plugin for WordPress with a focus on simplicity.
 * Version:           0.0.1
 * Author:            Sebastian Honert
 * Author URI:        https://sebastianhonert.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       humble-lms
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 */
define( 'HUMBLE_LMS_VERSION', '0.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-humble-lms-activator.php
 */
function activate_humble_lms() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-humble-lms-activator.php';
  Humble_LMS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-humble-lms-deactivator.php
 */
function deactivate_humble_lms() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-humble-lms-deactivator.php';
  Humble_LMS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_humble_lms' );
register_deactivation_hook( __FILE__, 'deactivate_humble_lms' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-humble-lms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_humble_lms() {

  $plugin = new Humble_LMS();
  $plugin->run();

}
run_humble_lms();
