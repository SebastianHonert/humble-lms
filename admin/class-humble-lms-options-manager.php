<?php
/**
 * This class provides option management functionality.
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/admin
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
if( ! class_exists( 'Humble_LMS_Admin_Options_Manager' ) ) {

  class Humble_LMS_Admin_Options_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param      class    $access       Public access class
     */
    public function __construct() {

      $this->options = get_option('humble_lms_options');
      $this->login_url = wp_login_url();

    }

    /**
     * Add plugin options page.
     *
     * @since    0.0.1
     */
    public function add_options_page() {
      add_menu_page('Humble LMS', 'Humble LMS', 'manage_options', 'humble_lms_options', array( $this, 'humble_lms_options_page' ), 'dashicons-admin-generic', 3);
    }

    /**
     * Generate plugin options page content.
     *
     * @since    0.0.1
     */
    public function humble_lms_options_page() {
      echo '<div class="wrap">';
        echo '<h1> ' . __('Humble LMS', 'humble-lms') . '</h1>';
        
        settings_errors();

        $options_url = admin_url() . '?page=humble_lms_options';

        $active = isset( $_GET['active'] ) ? sanitize_text_field( $_GET['active'] ) : 'reporting-users';

        $nav_tab_reporting_users = $active === 'reporting-users' ? 'nav-tab-active' : '';
        $nav_tab_reporting_courses = $active === 'reporting-courses' ? 'nav-tab-active' : '';
        $nav_tab_options = $active === 'options' ? 'nav-tab-active' : '';

        echo '<h2 class="nav-tab-wrapper">
          <a href="' . $options_url . '&active=reporting-users" class="nav-tab ' . $nav_tab_reporting_users . '">' . __('Reporting: Users', 'humble-lms') . '</a>
          <a href="' . $options_url . '&active=reporting-courses" class="nav-tab ' . $nav_tab_reporting_courses . '">' . __('Reporting: Courses', 'humble-lms') . '</a>
          <a href="' . $options_url . '&active=options" class="nav-tab ' . $nav_tab_options . '">' . __('Options', 'humble-lms') . '</a>
        </h2>';
        
        switch( $active ) {
          case 'reporting-users':
            settings_fields('humble_lms_options_reporting_users');
            do_settings_sections('humble_lms_options_reporting_users');
            break;
          case 'reporting-courses':
            settings_fields('humble_lms_options_reporting_courses');
            do_settings_sections('humble_lms_options_reporting_courses');
            break;
          case 'options':
            echo '<form method="post" action="options.php">';
              settings_fields('humble_lms_options');
              do_settings_sections('humble_lms_options');
              submit_button();
            echo '</form>';
            break;
        }

      echo '</div>';
    }

    /**
     * Initialize plugin admin options.
     *
     * @since    0.0.1
     */
    function humble_lms_options_admin_init() {
      register_setting( 'humble_lms_options', 'humble_lms_options', 'humble_lms_options_validate' );
      
      add_settings_section('humble_lms_options_section_reporting_users', __('Reporting: Users', 'humble-lms'), array( $this, 'humble_lms_options_section_reporting_users' ), 'humble_lms_options_reporting_users' );
      add_settings_section('humble_lms_options_section_reporting_courses', __('Reporting: Courses', 'humble-lms'), array( $this, 'humble_lms_options_section_reporting_courses' ), 'humble_lms_options_reporting_courses' );
      add_settings_section('humble_lms_options_section_options', __('Options', 'humble-lms'), array( $this, 'humble_lms_options_section_options' ), 'humble_lms_options' );

      add_settings_field( 'tile_width_track', __('Track archive tile width', 'humble-lms'), array( $this, 'tile_width_track' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'tile_width_course', __('Course archive tile width', 'humble-lms'), array( $this, 'tile_width_course' ), 'humble_lms_options', 'humble_lms_options_section_options');
    }

    /**
     * Main content section.
     *
     * @since    0.0.1
     */
    function humble_lms_options_section_reporting_users() {
      echo '<p><em>' . __('Users', 'humble-lms') . '</em></p>';
    }

    function humble_lms_options_section_reporting_courses() {
      echo '<p><em>' . __('Courses', 'humble-lms') . '</em></p>';
    }

    function humble_lms_options_section_options() {
      echo '<p><em>' . __('Display options and general settings', 'humble-lms') . '</em></p>';
    }

    /**
     * Option for tile width on track archive page.
     *
     * @since    0.0.1
     */
    function tile_width_track() {
      $values = array('full' => '1/1', 'half' => '1/2', 'third' => '1/3', 'fourth' => '1/4');
      $tile_width_track = isset( $this->options['tile_width_track'] ) ? sanitize_text_field( $this->options['tile_width_track'] ) : 'half';

      echo '<select class="widefat" id="tile_width_track" placeholder="' . __('Default tile width', 'humble-lms') . '" name="humble_lms_options[tile_width_track]">';
      array_walk( $values, function( &$key, $value ) use ( $tile_width_track ) {
        $selected = $value === $tile_width_track ? 'selected' : '';
        echo '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
      });
      echo '</select>';
    }

    /**
     * Option for tile width on course archive page.
     *
     * @since    0.0.1
     */
    function tile_width_course() {
      $values = array('full' => '1/1', 'half' => '1/2', 'third' => '1/3', 'fourth' => '1/4');
      $tile_width_course = isset( $this->options['tile_width_course'] ) ? sanitize_text_field( $this->options['tile_width_course'] ) : 'half';

      echo '<select class="widefat" id="tile_width_course" placeholder="' . __('Default tile width', 'humble-lms') . '" name="humble_lms_options[tile_width_course]">';
      array_walk( $values, function( &$key, $value ) use ( $tile_width_course ) {
        $selected = $value === $tile_width_course ? 'selected' : '';
        echo '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
      });
      echo '</select>';
    }

    /**
     * Validate option data on save.
     *
     * @since    0.0.1
     */
    function humble_lms_options_validate( $options ) {
      $validated['tile_width'] = sanitize_text_field( $options['tile_width'] );

      return $validated;
    } 
    
  }

}
