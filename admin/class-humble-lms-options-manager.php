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
     * @param    class    $access   Public access class
     */
    public function __construct() {

      $this->license_status = 0;
      $this->secret_key = '5fba5d909a6c83.38241175';
      $this->license_server_url = 'https://humblelms.de';
      $this->item_reference = 'Humble LMS';
      $this->license_key = '';

      $this->user = new Humble_LMS_Public_User;
      $this->content_manager = new Humble_LMS_Content_Manager;
      $this->translator = new Humble_LMS_Translator;
      $this->options = get_option('humble_lms_options');
      $this->active = 'reporting-users';
      $this->page_sections = array();
      $this->login_url = wp_login_url();
      $this->admin_url = add_query_arg( 'page', 'humble_lms_options', esc_url( admin_url('options.php') ) );
      $this->countries = array_map('trim', explode(',', 'Afghanistan, Albania, Algeria, Andorra, Angola, Antigua & Deps, Argentina, Armenia, Australia, Austria, Azerbaijan, Bahamas, Bahrain, Bangladesh, Barbados, Belarus, Belgium, Belize, Benin, Bhutan, Bolivia, Bosnia Herzegovina, Botswana, Brazil, Brunei, Bulgaria, Burkina, Burundi, Cambodia, Cameroon, Canada, Cape Verde, Central African Rep, Chad, Chile, China, Colombia, Comoros, Congo, Congo {Democratic Rep}, Costa Rica, Croatia, Cuba, Cyprus, Czech Republic, Denmark, Djibouti, Dominica, Dominican Republic, East Timor, Ecuador, Egypt, El Salvador, Equatorial Guinea, Eritrea, Estonia, Ethiopia, Fiji, Finland, France, Gabon, Gambia, Georgia, Germany, Ghana, Greece, Grenada, Guatemala, Guinea, Guinea-Bissau, Guyana, Haiti, Honduras, Hungary, Iceland, India, Indonesia, Iran, Iraq, Ireland {Republic}, Israel, Italy, Ivory Coast, Jamaica, Japan, Jordan, Kazakhstan, Kenya, Kiribati, Korea North, Korea South, Kosovo, Kuwait, Kyrgyzstan, Laos, Latvia, Lebanon, Lesotho, Liberia, Libya, Liechtenstein, Lithuania, Luxembourg, Macedonia, Madagascar, Malawi, Malaysia, Maldives, Mali, Malta, Marshall Islands, Mauritania, Mauritius, Mexico, Micronesia, Moldova, Monaco, Mongolia, Montenegro, Morocco, Mozambique, Myanmar, {Burma}, Namibia, Nauru, Nepal, Netherlands, New Zealand, Nicaragua, Niger, Nigeria, Norway, Oman, Pakistan, Palau, Panama, Papua New Guinea, Paraguay, Peru, Philippines, Poland, Portugal, Qatar, Romania, Russian Federation, Rwanda, St Kitts & Nevis, St Lucia, Saint Vincent & the Grenadines, Samoa, San Marino, Sao Tome & Principe, Saudi Arabia, Senegal, Serbia, Seychelles, Sierra Leone, Singapore, Slovakia, Slovenia, Solomon Islands, Somalia, South Africa, South Sudan, Spain, Sri Lanka, Sudan, Suriname, Swaziland, Sweden, Switzerland, Syria, Taiwan, Tajikistan, Tanzania, Thailand, Togo, Tonga, Trinidad & Tobago, Tunisia, Turkey, Turkmenistan, Tuvalu, Uganda, Ukraine, United Arab Emirates, United Kingdom, United States, Uruguay, Uzbekistan, Vanuatu, Vatican City, Venezuela, Vietnam, Yemen, Zambia, Zimbabwe'));
      $this->current_language = null;

      $this->messages = array(
        'lesson' => __('Lessons', 'humble-lms'),
        'course' => __('Courses', 'humble-lms'),
        'track' => __('Tracks', 'humble-lms'),
        'award' => __('Awards', 'humble-lms'),
        'certificate' => __('Certificates', 'humble-lms'),
      );

      $this->default_button_labels = array(
        'Mark as complete',
        'Mark as incomplete',
      );

      $this->custom_pages = array(
        'login' => 0,
        'registration' => 0,
        'lost_password' => 0,
        'reset_password' => 0,
        'user_profile' => 0,
      );

      $this->allowed_currencies = array(
        'AUD',
        'BRL',
        'CAD',
        'CHF',
        'CZK',
        'DKK',
        'EUR',
        'GBP',
        'HKD',
        'HUF',
        'INR',
        'ILS',
        'JPY',
        'MYR',
        'MXN',
        'NZD',
        'NOK',
        'PHP',
        'PLN',
        'RUB',
        'SEK',
        'SGD',
        'THB',
        'TWD',
        'USD',
      );

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

        $this->active = isset( $_GET['active'] ) ? sanitize_text_field( $_GET['active'] ) : 'reporting-users';
        $active = $this->active;

        $nav_tab_reporting_users = $active === 'reporting-users' ? 'nav-tab-active' : '';
        $nav_tab_reporting_courses = $active === 'reporting-courses' ? 'nav-tab-active' : '';
        $nav_tab_options = $active === 'options' ? 'nav-tab-active' : '';
        $nav_tab_registration = $active === 'registration' ? 'nav-tab-active' : '';
        $nav_tab_paypal = $active === 'paypal' ? 'nav-tab-active' : '';
        $nav_tab_billing = $active === 'billing' ? 'nav-tab-active' : '';
        $nav_tab_license = $active === 'license' ? 'nav-tab-active' : '';

        echo '<h2 class="nav-tab-wrapper">
          <a href="' . $this->admin_url . '&active=reporting-users" class="nav-tab ' . $nav_tab_reporting_users . '">' . __('Users', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=reporting-courses" class="nav-tab ' . $nav_tab_reporting_courses . '">' . __('Courses', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=options" class="nav-tab ' . $nav_tab_options . '">' . __('Options', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=registration" class="nav-tab ' . $nav_tab_registration . '">' . __('Registration', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=paypal" class="nav-tab ' . $nav_tab_paypal . '">PayPal</a>
          <a href="' . $this->admin_url . '&active=billing" class="nav-tab ' . $nav_tab_billing . '">' . __('Billing', 'humble-lms'). '</a>
          <a href="' . $this->admin_url . '&active=license" class="nav-tab ' . $nav_tab_license . '">' . __('License', 'humble-lms'). '</a>
        </h2>';

        echo '<form method="post" action="options.php">';
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
              settings_fields('humble_lms_options');
              do_settings_sections('humble_lms_options');
              submit_button();
              break;
            case 'registration':
              settings_fields('humble_lms_options_registration');
              do_settings_sections('humble_lms_options_registration');
              submit_button();
              break;
            case 'paypal':
              settings_fields('humble_lms_options_paypal');
              do_settings_sections('humble_lms_options_paypal');
              submit_button();
              break;
            case 'billing':
              settings_fields('humble_lms_options_billing');
              do_settings_sections('humble_lms_options_billing');
              submit_button();
              break;
            case 'license':
              settings_fields('humble_lms_options_license');
              do_settings_sections('humble_lms_options_license');
              break;
          }
        echo '</form>';

      echo '</div>';
    }

    /**
     * Initialize plugin admin options.
     *
     * @since    0.0.1
     */
    public function humble_lms_options_admin_init() {
      register_setting( 'humble_lms_options_reporting_users', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      register_setting( 'humble_lms_options', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      register_setting( 'humble_lms_options_registration', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      register_setting( 'humble_lms_options_paypal', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      register_setting( 'humble_lms_options_billing', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      register_setting( 'humble_lms_options_license', 'humble_lms_options', array( 'sanitize_callback' => array( $this, 'humble_lms_options_validate' ) ) );
      
      add_settings_section('humble_lms_options_section_reporting_users', '', array( $this, 'humble_lms_options_section_reporting_users' ), 'humble_lms_options_reporting_users' );
      add_settings_section('humble_lms_options_section_reporting_courses', __('Reporting: Courses', 'humble-lms'), array( $this, 'humble_lms_options_section_reporting_courses' ), 'humble_lms_options_reporting_courses' );
      add_settings_section('humble_lms_options_section_options', __('Options', 'humble-lms'), array( $this, 'humble_lms_options_section_options' ), 'humble_lms_options' );
      add_settings_section('humble_lms_options_section_registration', __('User Registration', 'humble-lms'), array( $this, 'humble_lms_options_section_registration' ), 'humble_lms_options_registration' );
      add_settings_section('humble_lms_options_section_paypal', 'PayPal', array( $this, 'humble_lms_options_section_paypal' ), 'humble_lms_options_paypal' );
      add_settings_section('humble_lms_options_section_billing', __('Billing', 'humble-lms'), array( $this, 'humble_lms_options_section_billing' ), 'humble_lms_options_billing' );
      add_settings_section('humble_lms_options_section_license', __('License', 'humble-lms'), array( $this, 'humble_lms_options_section_license' ), 'humble_lms_options_license' );

      add_settings_field( 'tiles_per_page', __('Tiles per page (track/course archive)', 'humble-lms'), array( $this, 'tiles_per_page' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'tile_width_track', __('Track archive tile width', 'humble-lms'), array( $this, 'tile_width_track' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'tile_width_course', __('Course archive tile width', 'humble-lms'), array( $this, 'tile_width_course' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'syllabus_max_height', __('Syllabus max. height in px', 'humble-lms'), array( $this, 'syllabus_max_height' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'sort_tracks_by_category', __('Sort tracks by category?', 'humble-lms'), array( $this, 'sort_tracks_by_category' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'sort_courses_by_category', __('Sort courses by category?', 'humble-lms'), array( $this, 'sort_courses_by_category' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'messages', __('Which messages should be shown when students complete a lesson?', 'humble-lms'), array( $this, 'messages' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'button_labels', __('Button texts', 'humble-lms'), array( $this, 'button_labels' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'custom_pages', __('Custom page IDs', 'humble-lms'), array( $this, 'custom_pages' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'max_evaluations', __('Max. number of logged quiz evaluations', 'humble-lms'), array( $this, 'max_evaluations' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'tippy_theme', __('TippyJS tooltips theme', 'humble-lms'), array( $this, 'tippy_theme' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'delete_plugin_data_on_uninstall', __('Delete plugin data on uninstall', 'humble-lms'), array( $this, 'delete_plugin_data_on_uninstall' ), 'humble_lms_options', 'humble_lms_options_section_options');
      
      add_settings_field( 'registration_has_country', __('Include country in registration form?', 'humble-lms'), array( $this, 'registration_has_country' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'registration_countries', __('Which countries should be included (multiselect)?', 'humble-lms'), array( $this, 'registration_countries' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'email_welcome', __('Welcome email', 'humble-lms'), array( $this, 'email_welcome' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'email_lost_password', __('Lost password email', 'humble-lms'), array( $this, 'email_lost_password' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'email_agreement', __('Email agreement', 'humble-lms'), array( $this, 'email_agreement' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'recaptcha_keys', __('Google reCAPTCHA', 'humble-lms'), array( $this, 'recaptcha_keys' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
 
      add_settings_field( 'use_coupons', 'Use coupons?', array( $this, 'use_coupons' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');
      add_settings_field( 'paypal_client_id', 'Client ID', array( $this, 'paypal_client_id' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');
      add_settings_field( 'currency', __('Currency', 'humble-lms'), array( $this, 'currency' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');
      add_settings_field( 'has_vat', __('Prices include value added tax (vat)', 'humble-lms'), array( $this, 'has_vat' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');
      add_settings_field( 'vat', __('Value added tax (VAT) in %', 'humble-lms'), array( $this, 'vat' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');
      add_settings_field( 'email_checkout', __('Checkout confirmation email', 'humble-lms'), array( $this, 'email_checkout' ), 'humble_lms_options_paypal', 'humble_lms_options_section_paypal');

      add_settings_field( 'seller_info', __('Seller info', 'humble-lms'), array( $this, 'seller_info' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');
      add_settings_field( 'seller_logo', __('Logo', 'humble-lms'), array( $this, 'seller_logo' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');
      add_settings_field( 'invoice_prefix', __('Invoice ID prefix', 'humble-lms'), array( $this, 'invoice_prefix' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');
      add_settings_field( 'invoice_text_before', __('Invoice text (before)', 'humble-lms'), array( $this, 'invoice_text_before' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');
      add_settings_field( 'invoice_text_after', __('Invoice text (after)', 'humble-lms'), array( $this, 'invoice_text_after' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');
      add_settings_field( 'invoice_text_footer', __('Invoice footer text', 'humble-lms'), array( $this, 'invoice_text_footer' ), 'humble_lms_options_billing', 'humble_lms_options_section_billing');

      add_settings_field( 'license_settings', __('License key', 'humble-lms'), array( $this, 'license_settings' ), 'humble_lms_options_license', 'humble_lms_options_section_license');    
    }

    /**
     * Main content section.
     *
     * @since    0.0.1
     */
    public function humble_lms_options_section_reporting_users() {
      $user_id = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : false;
      
      if( ! $user_id ) {
        echo '<h2>' . __('Registered users', 'humble-lms' ). ' (' . count_users()['total_users'] . ')' . '</h2>';
        $this->reporting_users_table();
        submit_button();
      } else {
        $this->reporting_user_single( $user_id );
      }
    }

    public function humble_lms_options_section_reporting_courses() {
      $this->reporting_courses_table();
    }

    public function humble_lms_options_section_options() {
      echo '<p><em>' . __('Display options and general settings', 'humble-lms') . '</em></p>';
    }

    public function humble_lms_options_section_registration() {
      // TODO: options
    }

    public function humble_lms_options_section_paypal() {
      echo '<p><em>' . __('In order to use PayPal you need to register a developer account first.', 'humble-lms') . '</em> <a href="https://developer.paypal.com/" target="_blank">' . __('Register developer account', 'humble-lms') . '</a></p>';
    }

    public function humble_lms_options_section_billing() {
      echo '<p><em>' . __('Set up your billing information and invoice layout.', 'humble-lms') . '</em></p>';
    }

    public function humble_lms_options_section_license() {
      echo '<p><em>' . __('Enter your license code to activate the Humble LMS plugin.', 'humble-lms') . '</em></p>';
    }

    /**
     * Option for track/course archive pagination.
     *
     * @since    0.0.1
     */
    public function tiles_per_page() {
      $tiles_per_page = isset( $this->options['tiles_per_page'] ) ? (int)$this->options['tiles_per_page'] : 10;

      echo '<input type="number" step="1" min="1" max="100" name="humble_lms_options[tiles_per_page]" value="' . $tiles_per_page . '">';
      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';
    }

    /**
     * Option for tile width on track archive page.
     *
     * @since    0.0.1
     */
    public function tile_width_track() {
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
    public function tile_width_course() {
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
     * Option for syllabus max height.
     *
     * @since    0.0.1
     */
    public function syllabus_max_height() {
      $syllabus_max_height = isset( $this->options['syllabus_max_height'] ) ? (int)$this->options['syllabus_max_height'] : 640;
      echo '<input type="number" min="0" max="9999" step="1" id="syllabus_max_height" name="humble_lms_options[syllabus_max_height]" value="' . $syllabus_max_height . '">';
    }

    /**
     * Option for sorting tracks by category on archive page.
     *
     * @since    0.0.1
     */
    public function sort_tracks_by_category() {
      $sort_tracks_by_category = isset( $this->options['sort_tracks_by_category'] ) ? (int)$this->options['sort_tracks_by_category'] : 0;

      echo '<select class="widefat" id="sort_tracks_by_category" name="humble_lms_options[sort_tracks_by_category]">';
        $selected = ! isset( $sort_tracks_by_category ) || $sort_tracks_by_category === 0 ? 'selected' : '';
        echo '<option value="0" ' . $selected . '>' . __('No') . '</option>';
        $selected = $sort_tracks_by_category === 1 ? 'selected' : '';
        echo '<option value="1" ' . $selected . '>' . __('Yes') . '</option>';
      echo '</select>';
    }

    /**
     * Option for sorting courses by category on archive page.
     *
     * @since    0.0.1
     */
    public function sort_courses_by_category() {
      $sort_courses_by_category = isset( $this->options['sort_courses_by_category'] ) ? (int)$this->options['sort_courses_by_category'] : 0;

      echo '<select class="widefat" id="sort_courses_by_category" name="humble_lms_options[sort_courses_by_category]">';
        $selected = ! isset( $sort_courses_by_category ) || $sort_courses_by_category === 0 ? 'selected' : '';
        echo '<option value="0" ' . $selected . '>' . __('No') . '</option>';
        $selected = $sort_courses_by_category === 1 ? 'selected' : '';
        echo '<option value="1" ' . $selected . '>' . __('Yes') . '</option>';
      echo '</select>';
    }

    /**
     * Option for messages showing when students complete a lesson.
     *
     * @since    0.0.1
     */
    public function messages() {
      $messages = $this->messages;
      $selected_messages = isset( $this->options['messages'] ) ? $this->options['messages'] : [];

      echo '<p>';
      foreach( $messages as $key => $message ) {
        $checked = in_array( $key, $selected_messages ) ? 'checked' : '';
        echo '<input id="messages" name="humble_lms_options[messages][]" type="checkbox" value="' . $key . '" ' . $checked . '>' . $message . '<br>';
      }
      echo '</p>';
    }

    /**
     * Option for lesson complete/incomplete button texts.
     *
     * @since    0.0.1
     */
    public function button_labels() {
      $button_labels = isset( $this->options['button_labels'] ) ? $this->options['button_labels'] : $this->default_button_labels;

      echo '<p><strong>' . __('Mark as complete', 'humble-lms') . '</strong></p>';
      echo '<p><input type="text" maxlength="32" id="button_labels_complete" name="humble_lms_options[button_labels][]" value="' . $button_labels[0] . '" placeholder="' . __($this->default_button_labels[0], 'humble-lms') . '"></p>';
      echo '<p><strong>' . __('Mark as incomplete', 'humble-lms') . '</strong></p>';
      echo '<p><input type="text" maxlength="32" id="button_labels_incomplete" name="humble_lms_options[button_labels][]" value="' . $button_labels[1] . '" placeholder="' . __($this->default_button_labels[1], 'humble-lms') . '"></p>';
    }

    /**
     * Option for messages showing when students complete a lesson.
     *
     * @since    0.0.1
     */
    public function custom_pages() {
      $custom_pages = isset( $this->options['custom_pages'] ) ? $this->options['custom_pages'] : $this->custom_pages;

      echo '<p><strong>' . __('Login', 'humble-lms') . '</strong> | <a href="' . get_edit_post_link( (int)$custom_pages['login'] ) . '">' . __('Edit page', 'humble-lms') . '</a></p>';
      echo '<p><input type="number" name="humble_lms_options[custom_pages][login]" value="' . (int)$custom_pages['login'] . '" /></p>';
      echo '<p><strong>' . __('Registration', 'humble-lms') . '</strong> | <a href="' . get_edit_post_link( (int)$custom_pages['registration'] ) . '">' . __('Edit page', 'humble-lms') . '</a></p>';
      echo '<p><input type="number" name="humble_lms_options[custom_pages][registration]" value="' . (int)$custom_pages['registration'] . '" /></p>';
      echo '<p><strong>' . __('Lost Password', 'humble-lms') . '</strong> | <a href="' . get_edit_post_link( (int)$custom_pages['lost_password'] ) . '">' . __('Edit page', 'humble-lms') . '</a></p>';
      echo '<p><input type="number" name="humble_lms_options[custom_pages][lost_password]" value="' . (int)$custom_pages['lost_password'] . '" /></p>';
      echo '<p><strong>' . __('Reset Password', 'humble-lms') . '</strong> | <a href="' . get_edit_post_link( (int)$custom_pages['reset_password'] ) . '">' . __('Edit page', 'humble-lms') . '</a></p>';
      echo '<p><input type="number" name="humble_lms_options[custom_pages][reset_password]" value="' . (int)$custom_pages['reset_password'] . '" /></p>';
      echo '<p><strong>' . __('User Profile', 'humble-lms') . '</strong> | <a href="' . get_edit_post_link( (int)$custom_pages['user_profile'] ) . '">' . __('Edit page', 'humble-lms') . '</a></p>';
      echo '<p><input type="number" name="humble_lms_options[custom_pages][user_profile]" value="' . (int)$custom_pages['user_profile'] . '" /></p>';
    }

    /**
     * Option for max. number of evaluations to be logged per user/quiz.
     *
     * @since    0.0.1
     */
    public function max_evaluations() {
      $max_evaluations = isset( $this->options['max_evaluations'] ) ? (int)$this->options['max_evaluations'] : 50;
      echo '<input type="number" min="10" max="999" step="1" id="max_evaluations" name="humble_lms_options[max_evaluations]" value="' . $max_evaluations . '">';
    }

    /**
     * Option for TippyJS tooltip theme.
     *
     * @since    0.0.1
     */
    public function tippy_theme() {
      $options = get_option('humble_lms_options');

      $tippy_theme = isset( $options['tippy_theme'] ) ? sanitize_text_field( $options['tippy_theme'] ) : 'default';

      echo '<select id="tippy_theme" name="humble_lms_options[tippy_theme]">';
        $selected = $tippy_theme === 'default' ? 'selected="selected"' : '';
        echo '<option value="default" ' . $selected . '>default</option>';
        $selected = $tippy_theme === 'light' ? 'selected="selected"' : '';
        echo '<option value="light" ' . $selected . '>light</option>';
        $selected = $tippy_theme === 'light-border' ? 'selected="selected"' : '';
        echo '<option value="light-border" ' . $selected . '>light-border</option>';
        $selected = $tippy_theme === 'material' ? 'selected="selected"' : '';
        echo '<option value="material" ' . $selected . '>material</option>';
        $selected = $tippy_theme === 'translucent' ? 'selected="selected"' : '';
        echo '<option value="translucent" ' . $selected . '>translucent</option>';
      echo '</select>';
    }

    /**
     * Option for deleting plugin data on uninstall.
     *
     * @since    0.0.1
     */
    public function delete_plugin_data_on_uninstall() {
      $delete_plugin_data_on_uninstall = isset( $this->options['delete_plugin_data_on_uninstall'] ) ? (int)$this->options['delete_plugin_data_on_uninstall'] : 0;
      $checked = $delete_plugin_data_on_uninstall === 1 ? 'checked' : '';
  
      echo '<p><input id="delete_plugin_data_on_uninstall" name="humble_lms_options[delete_plugin_data_on_uninstall]" type="checkbox" value="1" ' . $checked . '>' . __('Yes, delete plugin data on uninstall.', 'humble-lms') . '</p>';
    }

    /**
     * Option for displaying country field in registration form.
     *
     * @since    0.0.1
     */
    public function registration_has_country() {
      $registration_has_country = isset( $this->options['registration_has_country'] ) ? (int)$this->options['registration_has_country'] : 0;
      $checked = $registration_has_country === 1 ? 'checked' : '';
  
      echo '<p><input id="registration_has_country" name="humble_lms_options[registration_has_country]" type="checkbox" value="1" ' . $checked . '>' . __('Yes, include country in registration form.', 'humble-lms') . '</p>';
      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';
    }

    /**
     * Option for selecting individual countries to be included in registration form.
     *
     * @since    0.0.1
     */
    public function registration_countries() {
      $countries = $this->countries;
      $registration_countries = isset( $this->options['registration_countries'] ) ? maybe_unserialize( $this->options['registration_countries'] ) : $this->countries;

      echo '<select multiple size="20" class="humble-lms-searchable" id="registration_countries" placeholder="' . __('Wich countries would you like to include?', 'humble-lms') . '" name="humble_lms_options[registration_countries][]" data-content="registration_countries"  multiple="multiple">';
        foreach( $countries as $key => $country ) {
          $selected = in_array( $country, $registration_countries ) ? 'selected' : '';
          echo '<option data-id="' . $country . '" value="' . $country . '" ' . $selected . '>' . $country . '</option>';
        }
      echo '</select>';
      echo '<input class="humble-lms-multiselect-value" id="humble_lms_registration_countries" name="humble_lms_options[registration_countries]" type="hidden" value="' . implode(',', $registration_countries) . '">';
      echo '<p><a class="humble-lms-multiselect-select-all">' . __('Select all', 'humble-lms') . '</a> | <a class="humble-lms-multiselect-deselect-all">' . __('Deselect all', 'humble-lms') . '</a></p>';
    }

    /**
     * Content of the welcome email.
     *
     * @since    0.0.1
     */
    function email_welcome()
    {
      $message = isset( $this->options['email_welcome'] ) ? $this->options['email_welcome'] : '';

      echo '<p class="description">' . __('This email will be send in text/html format. You can use the following strings to include specific information in your email:', 'humble-lms') . '</p>';
      echo '<p><strong>WEBSITE_NAME</strong>, <strong>WEBSITE_URL</strong>, <strong>LOGIN_URL</strong>, <strong>USER_NAME</strong>, <strong>USER_EMAIL</strong>, <strong>CURRENT_DATE</strong>, <strong>ADMIN_EMAIL</strong></p>';
      echo '<div class="humble-lms-test-email" id="humble-lms-test-email-welcome">';
        echo '<p><textarea class="widefat" id="email_welcome" name="humble_lms_options[email_welcome]" rows="7">' . $message . '</textarea></p>';
        echo '<p><input id="humble-lms-test-email-recipient" type="email" class="widefat" value="' . get_bloginfo( 'admin_email' ) . '" /></p>';
        echo '<input type="hidden" name="subject" value="' . __('Test email: Welcome', 'humble-lms') . '" />';
        echo '<p><a class="button humble-lms-send-test-email" data-format="text/html">' . __('Send a test email', 'humble-lms') . '</a></p>';
      echo '</div>';
    }

    /**
     * Content of the lost password email.
     *
     * @since    0.0.1
     */
    function email_lost_password()
    {
      $message = isset( $this->options['email_lost_password'] ) ? $this->options['email_lost_password'] : '';

      echo '<p class="description">' . __('This email will be send in text/html format. You can use the following strings to include specific information in your email:', 'humble-lms') . '</p>';
      echo '<p><strong>RESET_PASSWORD_URL</strong>, <strong>WEBSITE_NAME</strong>, <strong>WEBSITE_URL</strong>, <strong>LOGIN_URL</strong>, <strong>USER_NAME</strong>, <strong>USER_EMAIL</strong>, <strong>CURRENT_DATE</strong>, <strong>ADMIN_EMAIL</strong></p>';
      echo '<div class="humble-lms-test-email" id="humble-lms-test-email-welcome">';
        echo '<p><textarea class="widefat" id="email_lost_password" name="humble_lms_options[email_lost_password]" rows="7">' . $message . '</textarea></p>';
        echo '<p><input id="humble-lms-test-email-recipient" type="email" class="widefat" value="' . get_bloginfo( 'admin_email' ) . '" /></p>';
        echo '<input type="hidden" name="subject" value="' . __('Test email: Lost password', 'humble-lms') . '" />';
        echo '<p><a class="button humble-lms-send-test-email" data-format="text/html">' . __('Send a test email', 'humble-lms') . '</a></p>';
      echo '</div>';
    }

    /**
     * Option for making email agreement required or optional.
     *
     * @since    0.0.1
     */
    public function email_agreement() {
      $email_agreement = isset( $this->options['email_agreement'] ) ? (int)$this->options['email_agreement'] : 0;
      $checked = $email_agreement === 1 ? 'checked' : '';
  
      echo '<p><input id="email_agreement" name="humble_lms_options[email_agreement]" type="checkbox" value="1" ' . $checked . '>' . __('Yes, make the agreement for receiving essential emails from this website required at registration.', 'humble-lms') . '</p>';
    }

    /**
     * reCAPTCHA keys.
     *
     * @since    0.0.1
     */
    function recaptcha_keys()
    {
      $recaptcha_enabled = isset( $this->options['recaptcha_enabled'] ) ? $this->options['recaptcha_enabled'] : '';
      $checked = $recaptcha_enabled === 1 ? 'checked="checked"' : '';
      $recaptcha_website_key = isset( $this->options['recaptcha_website_key'] ) ? $this->options['recaptcha_website_key'] : '';
      $recaptcha_secret_key = isset( $this->options['recaptcha_secret_key'] ) ? $this->options['recaptcha_secret_key'] : '';
      
      echo '<p class="humble-lms-enable-recaptcha"><input type="checkbox" class="widefat" name="humble_lms_options[recaptcha_enabled]" value="1" ' . $checked . '"> ' . __('Enable reCAPTCHA in registration form', 'humble-lms') . '</p>';
      echo '<p><input type="text" class="widefat" name="humble_lms_options[recaptcha_website_key]" value="' . $recaptcha_website_key . '" placeholder="' . __('Website key', 'humble-lms') . '&hellip;"></p>';
      echo '<p><input type="text" class="widefat" name="humble_lms_options[recaptcha_secret_key]" value="' . $recaptcha_secret_key . '" placeholder="' . __('Secret key', 'humble-lms') . '&hellip;"></p>';
      echo '<p class="description">' . __('Make sure you adjust your data privacy disclaimer to include Google reCAPTCHA.', 'humble-lms') . ' <a href="https://www.google.com/recaptcha/" target="_blank">' . __('More infos on Google reCAPTCHA', 'humble-lms') . '</a></p>';
    }

    /**
     * Option for activiating the use of coupons.
     *
     * @since    0.0.5
     */
    public function use_coupons() {
      $use_coupons = isset( $this->options['use_coupons'] ) ? (int)$this->options['use_coupons'] : 0;
      $checked = $use_coupons === 1 ? 'checked' : '';
  
      echo '<p><input id="use_coupons" name="humble_lms_options[use_coupons]" type="checkbox" value="1" ' . $checked . '>' . __('Yes, use coupons.', 'humble-lms') . '</p>';
    }

    /**
     * PayPal client ID.
     *
     * @since    0.0.1
     */
    function paypal_client_id()
    {
      $paypal_client_id = isset( $this->options['paypal_client_id'] ) ? $this->options['paypal_client_id'] : '';

      echo '<p><input type="text" class="widefat" name="humble_lms_options[paypal_client_id]" value="' . $paypal_client_id . '"></p>';
      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';
    }

    /**
     * PayPal currency.
     *
     * @since    0.0.1
     */
    function currency()
    {
      $currencies = $this->allowed_currencies;
      $currency = isset( $this->options['currency'] ) ? $this->options['currency'] : 'USD';

      echo '<p><select type="select" class="widefat" name="humble_lms_options[currency]">';
      
      foreach( $this->allowed_currencies as $cur ) {
        $selected = $currency === $cur ? 'selected="selected"' : '';
        echo '<option value="' . $cur . '" ' . $selected . '">' . $cur . '</option>';
      }

      echo '</select></p>';
    }

    /**
     * Prices including value added taxes (vat)
     *
     * @since    0.0.1
     */
    public function has_vat() {
      $has_vat = isset( $this->options['has_vat'] ) ? $this->options['has_vat'] : 0;

      echo '<p><select id="has_vat" name="humble_lms_options[has_vat]">';
        $selected = $has_vat === 0 ? 'selected="selected"' : '';
        echo '<option value="0" ' . $selected . '">' . __('Without vat', 'humble-lms') . '</option>';
        $selected = $has_vat === 1 ? 'selected="selected"' : '';
        echo '<option value="1" ' . $selected . '">' . __('Including VAT', 'humble-lms') . '</option>';
        $selected = $has_vat === 2 ? 'selected="selected"' : '';
        echo '<option value="2" ' . $selected . '">' . __('Excluding VAT', 'humble-lms') . '</option>';
      echo '</select></p><p class="description">' . __('Would you like to list your prices inclusive of, exclusive of, or without value added taxes?', 'humble-lms') . '</p>';
    }

    /**
     * vat amount in percent.
     *
     * @since    0.0.1
     */
    function vat()
    {
      $vat = isset( $this->options['vat'] ) ? (int)$this->options['vat'] : 0;

      echo '<p><input type="number" min="0" max="100" step="1" class="widefat" name="humble_lms_options[vat]" value="' . $vat . '"></p>';
    }

    /**
     * Custom checkout email.
     *
     * @since    0.0.1
     */
    function email_checkout()
    {
      $message = isset( $this->options['email_checkout'] ) ? wp_kses_post( $this->options['email_checkout'] ) : '';

      echo '<p class="description"><strong><em>' . __('If you leave this field blank, a default email will be sent.', 'humble-lms') . '</em></strong></p>';
      echo '<p class="description">' . __('This email will be send in text/html format. You can use the following strings to include specific information in your email:', 'humble-lms') . '</p>';
      echo '<p><strong>ORDER_DETAILS</strong>, <strong>WEBSITE_NAME</strong>, <strong>WEBSITE_URL</strong>, <strong>USER_NAME</strong>, <strong>CURRENT_DATE</strong>, <strong>ADMIN_EMAIL</strong></p>';
      echo '<div class="humble-lms-test-email" id="humble-lms-test-email-checkout">';
        echo '<p><textarea class="widefat" id="email_checkout" name="humble_lms_options[email_checkout]" rows="7">' . $message . '</textarea></p>';
        echo '<p><input id="humble-lms-test-email-recipient" type="email" class="widefat" value="' . get_bloginfo( 'admin_email' ) . '" /></p>';
        echo '<input type="hidden" name="subject" value="' . __('Test email: Checkout', 'humble-lms') . '" />';
        echo '<p><a class="button humble-lms-send-test-email" data-format="text/html">' . __('Send a test email', 'humble-lms') . '</a></p>';
      echo '</div>';
    }

    /**
     * Seller info.
     *
     * @since    0.0.3
     */
    function seller_info()
    {
      $allowed_tags = array(
        'a' => array(
          'href' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'p' => array(),
        'strong' => array(),
      );

      $seller_info = isset( $this->options['seller_info'] ) ? wp_kses( $this->options['seller_info'], $allowed_tags ) : '';

      echo '<p class="description">' . __('Your personal and/or company information. Line breaks will be recognized automatically. Allowed HTML tags: a, br, em, p, strong.', 'humble-lms') . '</p>';
      echo '<p><textarea class="widefat" id="seller_info" name="humble_lms_options[seller_info]" rows="7">' . $seller_info . '</textarea></p>';
      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';
    }

    /**
     * Seller logo.
     *
     * @since    0.0.3
     */
    function seller_logo()
    {
      $seller_logo = isset( $this->options['seller_logo'] ) ? esc_url_raw( $this->options['seller_logo'] ) : '';

      echo '<p class="description">' . __('You company or personal logo (URL)', 'humble-lms') . '</p>';
      echo '<p><input class="widefat" id="seller_logo" name="humble_lms_options[seller_logo]" value="' . $seller_logo . '"></p>';
    }

    /**
     * Invoice prefix.
     *
     * @since    0.0.3
     */
    function invoice_prefix()
    {
      $invoice_counter = get_option('humble_lms_invoice_counter');
      $invoice_counter = isset( $invoice_counter ) ? absint( $invoice_counter ) : 0;
      $invoice_prefix = isset( $this->options['invoice_prefix'] ) ? sanitize_text_field( $this->options['invoice_prefix'] ) : '';

      echo '<p class="description">' . __('Invoice IDs will increment automatically (1,2,3&hellip;). You can add a prefix to the invoice ID here.', 'humble-lms') . '</p>';
      echo '<p><input class="widefat" id="invoice_prefix" name="humble_lms_options[invoice_prefix]" value="' . $invoice_prefix . '"></p>';

      echo '<p>' . __('Current invoice number', 'humble-lms') . ': ' . $invoice_prefix . '<input id="invoice_counter" name="humble_lms_options[invoice_counter]" type="number" min="0" step="1" value="' . $invoice_counter . '"></p>';    
    }

    /**
     * Invoice text (before)
     *
     * @since    0.0.3
     */
    function invoice_text_before()
    {
      $allowed_tags = array(
        'a' => array(
          'href' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'p' => array(),
        'strong' => array(),
      );

      $invoice_text_before = isset( $this->options['invoice_text_before'] ) ? wp_kses( $this->options['invoice_text_before'], $allowed_tags ) : '';

      echo '<p class="description">' . __('Text displayed before the table of purchased items. Line breaks will be recognized automatically. Allowed HTML tags: a, br, em, strong.', 'humble-lms') . '</p>';
      echo '<p><textarea class="widefat" id="invoice_text_before" name="humble_lms_options[invoice_text_before]" rows="5">' . $invoice_text_before . '</textarea></p>';
    }

    /**
     * Invoice text (after)
     *
     * @since    0.0.3
     */
    function invoice_text_after()
    {
      $allowed_tags = array(
        'a' => array(
          'href' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'p' => array(),
        'strong' => array(),
      );

      $invoice_text_after = isset( $this->options['invoice_text_after'] ) ? wp_kses( $this->options['invoice_text_after'], $allowed_tags ) : '';

      echo '<p class="description">' . __('Text displayed after the table of purchased items. Line breaks will be recognized automatically. Allowed HTML tags: a, br, em, strong.', 'humble-lms') . '</p>';
      echo '<p><textarea class="widefat" id="invoice_text_after" name="humble_lms_options[invoice_text_after]" rows="5">' . $invoice_text_after . '</textarea></p>';
    }

    /**
     * Invoice footer text
     *
     * @since    0.0.3
     */
    function invoice_text_footer()
    {
      $allowed_tags = array(
        'a' => array(
          'href' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'p' => array(),
        'strong' => array(),
      );

      $invoice_text_footer = isset( $this->options['invoice_text_footer'] ) ? wp_kses( $this->options['invoice_text_footer'], $allowed_tags ) : '';

      echo '<p class="description">' . __('Text displayed at the bottom of your invoices. Line breaks will be recognized automatically. Allowed HTML tags: a, br, em, strong.', 'humble-lms') . '</p>';
      echo '<p><textarea class="widefat" id="invoice_text_footer" name="humble_lms_options[invoice_text_footer]" rows="3">' . $invoice_text_footer . '</textarea></p>';
    }

    /**
     * License settings
     *
     * @since    0.0.3
     */
    function license_settings()
    {
      $license_status = isset( $this->options['license_status'] ) ? absint( $this->options['license_status'] ) : 0;
      $license_key = isset( $this->options['license_key'] ) ? sanitize_text_field( $this->options['license_key'] ) : '';

      echo '<p><input class="widefat" type="text" id="html_license_key" name="humble_lms_options[license_key]"  value="' . sanitize_text_field( $license_key ) . '"></p>';

      echo '<p class="submit">';
        if( 0 === $license_status ) {
          echo '<input type="submit" name="activate_license" value="' . __('Activate', 'humble-lms') . '" class="button-primary" />';
        } else {
          echo '<input type="submit" name="deactivate_license" value="' . __('Deactivate', 'humble-lms') . '" class="button" />';
        }
      echo '</p>';

      if( 1 === $license_status ) {
        echo '<p class="humble-lms-message humble-lms-message--success">' . __('You\'re plugin license has been activated.', 'humble-lms') . '</p>';
      } else {
        echo '<p class="humble-lms-message humble-lms-message--error">' . __('Plugin not activated. Please enter your license key.', 'humble-lms') . '</p>';
      }

      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';
    }

    /**
     * Validate options on save.
     *
     * @param   array
     * @return  array
     * @since   0.0.1
     */
    public function humble_lms_options_validate( $input ) {
      $options = $this->options;
      $active = isset( $input['active'] ) ? sanitize_text_field( $input['active'] ) : '';

      $allowed_tags = array(
        'a' => array(
          'href' => array(),
        ),
        'br' => array(),
        'em' => array(),
        'p' => array(),
        'strong' => array(),
      );

      if( $active === 'reporting-users' ) {
        if( isset( $input['users_per_page'] ) ) {
          $options['users_per_page'] = (int)$input['users_per_page'];
        }
      }

      if( $active === 'options' ) {
        if( isset( $input['tile_width_course'] ) )
          $options['tile_width_course'] = sanitize_text_field( $input['tile_width_course'] );

        if( isset( $input['tiles_per_page'] ) )
          $options['tiles_per_page'] = (int)$input['tiles_per_page'];

        if( isset( $input['syllabus_max_height'] ) )
          $options['syllabus_max_height'] = (int)$input['syllabus_max_height'];

        if( isset( $input['sort_tracks_by_category'] ) )
          $options['sort_tracks_by_category'] = (int)$input['sort_tracks_by_category'];

        if( isset( $input['sort_courses_by_category'] ) )
          $options['sort_courses_by_category'] = (int)$input['sort_courses_by_category'];

        if( isset( $input['tile_width_track'] ) )
          $options['tile_width_track'] = sanitize_text_field( $input['tile_width_track'] );
        
        if( isset( $input['messages'] ) )
          $options['messages'] = $input['messages'];

        if( isset( $input['button_labels'] ) ) {
          $options['button_labels'][0] = sanitize_text_field( $input['button_labels'][0] );
          $options['button_labels'][1] = sanitize_text_field( $input['button_labels'][1] );
        }
        
        if( isset( $input['custom_pages'] ) )
          $options['custom_pages'] = $input['custom_pages'];

        if( isset( $input['max_evaluations'] ) )
          $options['max_evaluations'] = (int)$input['max_evaluations'];

        if( isset( $input['tippy_theme'] ) )
          $options['tippy_theme'] = sanitize_text_field( $input['tippy_theme'] );

        $options['delete_plugin_data_on_uninstall'] = isset( $input['delete_plugin_data_on_uninstall'] ) ? 1 : 0;

        if( $options['max_evaluations'] < 10 ) {
          $options['max_evaluations'] = 10;
        } elseif( $options['max_evaluations'] > 999 ) {
          $options['max_evaluations'] = 999;
        }
      }

      if( $active === 'registration' ) {
        $options['registration_has_country'] = isset( $input['registration_has_country'] ) ? 1 : 0;
        $options['email_agreement'] = isset( $input['email_agreement'] ) ? 1 : 0;
        $options['recaptcha_website_key'] = esc_attr( trim( $input['recaptcha_website_key'] ) );
        $options['recaptcha_secret_key'] = esc_attr( trim( $input['recaptcha_secret_key'] ) );
        $options['recaptcha_enabled'] = isset( $input['recaptcha_enabled'] ) && ! empty( $options['recaptcha_website_key'] ) && ! empty( $options['recaptcha_secret_key'] ) ? 1 : 0;
        
        if( isset( $input['registration_countries'] ) ) {
          $options['registration_countries'] = ! empty( $input['registration_countries'] ) ? serialize( explode( ',', $input['registration_countries'] ) ) : [];
        }
  
        if( empty( $options['registration_countries'] ) ) {
          $options['registration_has_country'] = 0;
        }
  
        if( isset( $input['email_welcome'] ) ) {
          $options['email_welcome'] = esc_html( $input['email_welcome'] );
        }

        if( isset( $input['email_lost_password'] ) ) {
          $options['email_lost_password'] = esc_html( $input['email_lost_password'] );
        }
      }

      if( $active === 'paypal' ) {
        $options['use_coupons'] = isset( $input['use_coupons'] ) ? 1 : 0;

        if( isset( $input['paypal_client_id'] ) ) {
          $options['paypal_client_id'] = sanitize_text_field( trim( $input['paypal_client_id'] ) );
        }

        $options['currency'] = sanitize_text_field( $input['currency'] );
        $options['currency'] = in_array( $options['currency'], $this->allowed_currencies ) ? $options['currency'] : 'USD';

        if( isset( $input['has_vat'] ) ) {
          $options['has_vat'] = (int)$input['has_vat'];
        }

        if( isset( $input['vat'] ) ) {
          $options['vat'] = absint( $input['vat'] );
          if( $options['vat'] > 100 ) {
            $options['vat'] = 100;
          }
        }

        if( isset( $input['email_checkout'] ) ) {
          $options['email_checkout'] = wp_kses( $input['email_checkout'], $allowed_tags );
        }
      }

      if( $active === 'billing' ) {
        $options['seller_info'] =  wp_kses( $input['seller_info'], $allowed_tags );
        $options['seller_logo'] =  esc_url_raw( $input['seller_logo'] );
        $options['invoice_prefix'] =  sanitize_text_field( $input['invoice_prefix'] );
        $options['invoice_text_before'] =  wp_kses( $input['invoice_text_before'], $allowed_tags );
        $options['invoice_text_after'] =  wp_kses( $input['invoice_text_after'], $allowed_tags );
        $options['invoice_text_footer'] =  wp_kses( $input['invoice_text_footer'], $allowed_tags );

        update_option( 'humble_lms_invoice_counter', absint( $input['invoice_counter'] ) );
      }

      if( $active === 'license' ) {
        $options['license_key'] = sanitize_text_field( $input['license_key'] );

        // Activate license
        if( isset( $_REQUEST['activate_license'] ) ) {
          if( 0 === $options['license_status'] ) {
            $license_key = $options['license_key'];
    
            // API query parameters
            $api_params = array(
              'slm_action' => 'slm_activate',
              'secret_key' => $this->secret_key,
              'license_key' => $license_key,
              'registered_domain' => $_SERVER['SERVER_NAME'],
              'item_reference' => urlencode( $this->item_reference ),
            );
    
            // Send query to the license manager server
            $query = esc_url_raw( add_query_arg( $api_params, $this->license_server_url ) );
            $response = wp_remote_get( $query, array(
              'timeout' => 20,
              'sslverify' => false
            ) );
    
            if( is_wp_error( $response ) ) {
              $options['license_status'] = 0;
            }
            
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
            
            if( isset( $license_data ) && $license_data->result == 'success' ) {
              $options['license_status'] = 1;
            } else {
              $options['license_key'] = '';
            }
          }
        }
      
        // Deactivate license
        if ( isset( $_REQUEST['deactivate_license'] ) ) {
          if( 1 === $options['license_status'] ) {
            $options['license_key'] = sanitize_text_field( $input['license_key'] );
            $license_key = $options['license_key'];

            $api_params = array(
              'slm_action' => 'slm_deactivate',
              'secret_key' => $this->secret_key,
              'license_key' => $license_key,
              'registered_domain' => $_SERVER['SERVER_NAME'],
              'item_reference' => urlencode( $this->item_reference ),
            );

            $query = esc_url_raw( add_query_arg($api_params, $this->license_server_url ) );
            $response = wp_remote_get( $query, array(
              'timeout' => 20,
              'sslverify' => false
            ) );

            if( is_wp_error( $response ) ) {
              $options['license_status'] = 1;
            } else {
              $license_data = json_decode( wp_remote_retrieve_body( $response ) );
              
              if( isset( $license_data ) && $license_data->result == 'success' ) {
                $options['license_status'] = 0;
                $options['license_key'] = '';
              }
              else {
                $options['license_status'] = 1;
              }
            }
          }
        }
      }

      return $options;
    }

    /**
     * Generate reporting table for all users.
     *
     * @return  false
     * @since   0.0.1
     */
    public function reporting_users_table() {
      $users_per_page = $this->users_per_page();
      $total_users = count_users()['total_users'];
      $paged = isset( $_GET['paged'] ) ? (int)$_GET['paged'] : 0;

      if( $total_users < 1 + ( $paged - 1 ) * $users_per_page ) {
        $paged = 0;
      }

      $args = array(
        'count_total' => false,
        'offset' => $paged ? ($paged - 1) * $users_per_page : 0,
        'number' => $users_per_page,
        'orderby' => 'login',
        'order' => 'ASC'
      );

      $users = get_users( $args );

      echo '<table class="widefat">';
        echo '<thead>
          <tr>
            <th width="5%">ID</th>
            <th width="10%">Name</th>
            <th width="10%">Role</th>
            <th width="15%">Tracks (' . wp_count_posts('humble_lms_track')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_track') ) . ')</th>
            <th width="15%">Courses (' . wp_count_posts('humble_lms_course')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_course') ) . ')</th>
            <th width="15%">Lessons (' . wp_count_posts('humble_lms_lesson')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_lesson') ) . ')</th>
            <th width="15%">Awards (' . wp_count_posts('humble_lms_award')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_award') ) . ')</th>
            <th width="15%">Certificates (' . wp_count_posts('humble_lms_cert')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_cert') ) . ')</th>
          </tr>
        </thead>
        <tbody>';
          foreach( $users as $user ) {
            $user_meta = get_userdata( $user->ID );

            $tracks_total = wp_count_posts('humble_lms_track')->publish;
            $completed_tracks = count( $this->user->completed_tracks( $user->ID, true ) );
            $completed_tracks_percent = $tracks_total > 0 ? ( $completed_tracks / $tracks_total ) * 100 : 0;

            $courses_total = wp_count_posts('humble_lms_course')->publish;
            $completed_courses = count( $this->user->completed_courses( $user->ID, true ) );
            $completed_courses_percent = $courses_total > 0 ? ( $completed_courses / $courses_total ) * 100 : 0;

            $lessons_total = wp_count_posts('humble_lms_lesson')->publish;
            $completed_lessons = count( $this->user->completed_lessons( $user->ID, true ) );
            $completed_lessons_percent = $lessons_total > 0 ? ( $completed_lessons / $lessons_total ) * 100 : 0;

            $awards_total = wp_count_posts('humble_lms_award')->publish;
            $completed_awards = count( $this->user->granted_awards( $user->ID, true ) );
            $completed_awards_percent = $awards_total > 0 ? ( $completed_awards / $awards_total ) * 100 : 0;

            $certificates_total = wp_count_posts('humble_lms_cert')->publish;
            $completed_certificates = count( $this->user->issued_certificates( $user->ID, true ) );
            $completed_certificates_percent = $certificates_total > 0 ? ( $completed_certificates / $certificates_total ) * 100 : 0;

            echo '<tr>
              <td><a href="' . get_edit_user_link( $user->ID ) . '">' . $user->ID . '</a></td>
              <td><a href="' . $this->admin_url . '&user_id=' . $user->ID . '&users_per_page=' . $users_per_page . '"><strong>' . $user->user_login . '</strong></a></td>
              <td>' . implode(',', $user_meta->roles ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_tracks_percent, $completed_tracks ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_courses_percent, $completed_courses ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_lessons_percent, $completed_lessons ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_awards_percent, $completed_awards ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_certificates_percent, $completed_certificates ) . '</td>
            </tr>';
          }
        echo '</tbody>';
      echo '</table>';

      // Users per page
      $users_per_page_values = array(10, 25, 50, 100, 250, 500);

      echo '<p><strong>' . __('Users per page', 'humble-lms') . '</strong><p>
        <select id="users_per_page" name="humble_lms_options[users_per_page]">';
        array_walk( $users_per_page_values, function( $value, $key, $users_per_page ) {
          $selected = $value === $users_per_page ? 'selected' : '';
          echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
        }, $users_per_page);
      echo '</select>';
      echo '<input type="hidden" name="humble_lms_options[active]" value="' . $this->active . '">';

      if( $total_users > $users_per_page ) {
        $args = array(
          'base' => add_query_arg( 'paged', '%#%' ),
          'total' => ceil($total_users / $users_per_page),
          'current' => max(1, $paged),
        );

        echo '<p>' . paginate_links( $args ) . '</p>';
      }
    }

    /**
     * Generate reporting table for all courses.
     *
     * @return  false
     * @since   0.0.1
     */
    public function reporting_user_single( $user_id = null ) {
      if( ! get_userdata( (int)$user_id ) ) { 
        echo __('Please provide a valid user ID.', 'humble-lms');
        return;
      }
    
      $user = get_user_by( 'id', (int)$user_id );
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th>' . $user->user_login . ' (ID <a href="' . get_edit_user_link( $user->ID ) . '">' . $user->ID . '</a>)</th></tr></thead>
      <tr><td>' . __('Registered', 'humble-lms') . ': <strong>' . $this->user->registered_at( $user_id, true ) . '</strong></td>
      </tr></table>';

      $completed_tracks = $this->user->completed_tracks( $user->ID, true );
      $completed_courses = $this->user->completed_courses( $user->ID, true );
      $completed_lessons = $this->user->completed_lessons( $user->ID, true );
      $granted_awards = $this->user->granted_awards( $user->ID, true );
      $issued_certificates = $this->user->issued_certificates( $user->ID, true );

      $tracks = get_posts( array(
          'post_type' => 'humble_lms_track',
          'post_status' => 'publish',
          'posts_per_page' => -1,
          'lang' => '',
      ) );

      $courses = get_posts( array(
        'post_type' => 'humble_lms_course',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'lang' => '',
      ) );

      $lessons = get_posts( array(
        'post_type' => 'humble_lms_lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'lang' => '',
      ) );

      $awards = get_posts( array(
        'post_type' => 'humble_lms_award',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'lang' => '',
      ) );

      $certificates = get_posts( array(
        'post_type' => 'humble_lms_cert',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'lang' => '',
      ) );

      // Tracks
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="25%">' . __('Tracks', 'humble-lms') . '</th>
        <th width="75%">' . __('Progress', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $tracks as $track ) {
        echo '<tr class="humble-lms-reporting-track">';
          echo '<td><strong><a href="' . get_edit_post_link( $track->ID ) . '">' . get_the_title( $track->ID ) . '</a></strong></td>';
          echo '<td>'. $this->progress_bar( $this->user->track_progress( $track->ID, $user->ID ) ) . '</td>';
        echo '</tr>';
      }

      echo '</table>';

      // Courses
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="25%">' . __('Courses and Lessons', 'humble-lms') . '</th>
        <th width="75%">' . __('Progress', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $courses as $course ) {
        echo '<tr class="humble-lms-reporting-course">
          <td><strong><a href="' . get_edit_post_link( $course->ID ) . '">' . get_the_title( $course->ID ) . '</a></strong></td>
          <td>'. $this->progress_bar( $this->user->course_progress( $course->ID, $user->ID ) ) . '</td>
        </tr>';

        $course_lessons = Humble_LMS_Content_Manager::get_course_lessons( $course->ID );

        foreach( $course_lessons as $lesson_id ) {
          $completed = $this->user->completed_lesson( $user->ID, $lesson_id ) ? '<span class="humble-lms-options-complete">&check;</span>' : '<span class="humble-lms-options-incomplete">&times;</span>';
          echo '<tr class="humble-lms-reporting-lesson">
            <td><a href="' . get_edit_post_link( $lesson_id ) . '">' . get_the_title( $lesson_id ) . '</a></td>
            <td>'. $completed . '</td>  
          </tr>';
        }
      }
      
      echo '</table>';

      // Awards
      echo '<table class="widefat humble-lms-reporting-table humble-lms-reporting-table--awards"><thead><tr>
        <th width="25%">' . __('Awards', 'humble-lms') . '</th>
        <th width="50%">' . __('Progress', 'humble-lms') . '</th>
        <th width="25%">' . __('Action', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $awards as $award ) {
        $completed = in_array( $award->ID, $granted_awards );
        $icon = $completed ? '<span class="humble-lms-options-icon humble-lms-options-complete">&check;</span>' : '<span class="humble-lms-options-icon humble-lms-options-incomplete">&times;</span>';

        echo '<tr class="humble-lms-reporting-award" data-user-id="' . $user->ID . '" data-id="' . $award->ID . '">';
          echo '<td><strong><a href="' . get_edit_post_link( $award->ID ) . '">' . get_the_title( $award->ID ) . '</a></strong></td>';
          echo '<td>'. $icon . '</td>';
          echo '<td><a class="button humble-lms-toggle-award-certificate">' . __('Grant / revoke', 'humble-lms') . '</a></td>';
        echo '</tr>';
      }

      if( ! $awards ) {
        echo '<tr class="humble-lms-reporting-awards">';
          echo '<td>' . __('No awards available.', 'humble-lms') . '</td>';
          echo '<td>–</td>';
        echo '</tr>';
      }

      echo '</table>';

      // Certificates
      echo '<table class="widefat humble-lms-reporting-table humble-lms-reporting-table--certificates"><thead><tr>
        <th width="25%">' . __('Certificates', 'humble-lms') . '</th>
        <th width="50%">' . __('Progress', 'humble-lms') . '</th>
        <th width="25%">' . __('Action', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $certificates as $certificate ) {
        $completed = in_array( $certificate->ID, $issued_certificates );
        $icon = $completed ? '<span class="humble-lms-options-icon humble-lms-options-complete">&check;</span>' : '<span class="humble-lms-options-icon humble-lms-options-incomplete">&times;</span>';

        echo '<tr class="humble-lms-reporting-certificates" data-user-id="' . $user->ID . '" data-id="' . $certificate->ID . '">';
          echo '<td><strong><a href="' . get_edit_post_link( $certificate->ID ) . '">' . get_the_title( $certificate->ID ) . '</a></strong></td>';
          echo '<td>'. $icon . '</td>';
          echo '<td><a class="button humble-lms-toggle-award-certificate">' . __('Issue / revoke', 'humble-lms') . '</a></td>';
        echo '</tr>';
      }

      if( ! $certificates ) {
        echo '<tr class="humble-lms-reporting-certificates">';
          echo '<td>' . __('No certificates available.', 'humble-lms') . '</td>';
          echo '<td>–</td>';
        echo '</tr>';
      }

      echo '</table>';

      // Quizzes
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="25%">' . __('Quizzes', 'humble-lms') . '</th>
        <th width="75%">' . __('Attempts', 'humble-lms') . '</th>
      </tr></thead>';

      $evaluations = $this->user->evaluations( $user->ID );
      $quizzes = array();
  
      foreach( $evaluations as $evaluation ) {
        if( empty( $evaluation ) || ! is_array( $evaluation ) ) {
          continue;
        }

        $quiz_ids = implode( ',', $evaluation['quizIds'] );
        
        if( array_key_exists( $quiz_ids, $quizzes ) ) {
          array_push( $quizzes[$quiz_ids], $evaluation );
        } else {
          $quizzes[$quiz_ids][0] = $evaluation;
        }
      }

      foreach( $quizzes as $quiz ) {

        echo '<tr class="humble-lms-reporting-quizzes">';
          echo '<td>';
            $quiz_ids = $quiz[0]['quizIds'];
            foreach( $quiz_ids as $quiz_id ) {
              echo '<strong><a href="' . esc_url( get_edit_post_link( $quiz_id ) ) . '">' . sanitize_text_field( get_the_title( $quiz_id ) ) . '</a></strong>';
              if( $quiz_id !== end( $quiz_ids ) ) {
                echo ' / ';
              }
            }
          echo '</td>';

          array_multisort( array_column( $quiz, 'datetime'), SORT_DESC, $quiz );
          
          echo '<td><select class="widefat">';
            foreach( $quiz as $evaluation ) {
              if( empty( $evaluation ) || ! is_array( $evaluation ) ) {
                continue;
              }

              $completed = isset( $evaluation['completed'] ) && (int)$evaluation['completed'] === 1 ? '&check;' : '&times;';
              echo '<option>' . $completed . ' (' .  (int)$evaluation['percent'] . '%) ' . date("d-m-Y H:i:s", ($evaluation['datetime'] / 1000) ) . '</option>'; 
            }
          echo '</select></td>';
        echo '</tr>';
      }

      if( ! $quizzes ) {
        echo '<tr class="humble-lms-reporting-quizzes">';
          echo '<td>' . __('No quiz evaluations available.', 'humble-lms') . '</td>';
          echo '<td>–</td>';
        echo '</tr>';
      }

      echo '</table>';

      echo '<p><a class="button button-primary humble-lms-reset-user-progress" data-user-id="' . $user->ID . '">' . __('Reset learning progress for this user?', 'humble-lms') . '</a></p>';
  
      $users_per_page = $this->users_per_page();
      $users_per_page = $users_per_page !== 50 ? '&users_per_page=' . $users_per_page : '';

      echo '<p><a class="button" href="' . $this->admin_url . $users_per_page . '">' . __('Back', 'humble-lms') . '</a></p>';
    }

    /**
     * Generate reporting table for all courses.
     *
     * @return  false
     * @since   0.0.1
     */
    public function reporting_courses_table() {
      $courses = $this->content_manager->get_courses();

      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="34%">' . __('Course', 'humble-lms') . '</th>
        <th width="66%">' . __('Completed (counting only user role "Student")', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $courses as $course ) {
        $completed = $this->content_manager->course_completion_percentage( $course->ID );
        echo '<tr class="humble-lms-reporting-courses">';
          echo '<td><strong><a href="' . get_edit_post_link( $course->ID ) . '">' . $course->post_title . '</a></strong></td>';
          echo '<td>'. $this->progress_bar( $completed ) . '</td>';
        echo '</tr>';
      }

      if( ! $courses ) {
        echo '<tr class="humble-lms-reporting-courses">';
          echo '<td>' . __('No courses available.', 'humble-lms') . '</td>';
          echo '<td>–</td>';
        echo '</tr>';
      }

      echo '</table>';
    }

    /**
     * Generate progress bar.
     *
     * @return  false
     * @since   0.0.1
     */
    public function progress_bar( $percent = 0, $total = 0 ) {
      $class = (int)$percent === 0 ? 'humble-lms-progress-none' : '';
      $total = $total > 0 ? $total . ' / ' : '';

      $html = '';
      $html .= '<span class="humble-lms-admin-progress-bar">
        <span class="humble-lms-admin-progress-bar-text ' . $class . '">' . $total . $percent . '%</span>
        <span class="humble-lms-admin-progress-bar-inner" style="width: ' . $percent . '%"></span>
      </span>';

      return $html;
    }

    /**
     * Users per page.
     *
     * @return  int
     * @since   0.0.1
     */
    public function users_per_page() {
      if( isset( $_GET['users_per_page'] ) ) {
        $users_per_page = (int)$_GET['users_per_page'];
      } else {
        $users_per_page = isset( $this->options['users_per_page'] ) ? (int)$this->options['users_per_page'] : 50;
      }

      return $users_per_page > 0 ? $users_per_page : 50;
    }

    /**
     * Check if Google reCAPTCHA is enabled.
     *
     * @return  bool
     * @since   0.0.1
     */
    public static function has_recaptcha() {
      $options = get_option('humble_lms_options');
      return ( isset( $options['recaptcha_enabled'] ) && $options['recaptcha_enabled'] === 1 && ! empty( $options['recaptcha_website_key'] ) && ! empty( $options['recaptcha_secret_key'] ) );
    }

    /**
     * Check if the PayPal settings are available.
     *
     * @return  bool
     * @since   0.0.1
     */
    public static function has_paypal() {
      $options = get_option('humble_lms_options');
      return ( ! empty( $options['paypal_client_id'] ) );
    }

    /**
     * Get currency.
     *
     * @return  string
     * @since   0.0.1
     */
    public function get_currency() {
      $options = get_option('humble_lms_options');
      return isset( $options['currency'] ) && in_array( $options['currency'], $this->allowed_currencies ) ? $options['currency'] : 'USD';
    }

    /**
     * Get button labels.
     *
     * @return  string
     * @since   0.0.1
     */
    public function get_button_labels() {
      $options = get_option('humble_lms_options');
      $button_labels = array();
      $button_labels[0] = isset( $options['button_labels'][0] ) && ! empty( $options['button_labels'][0] ) && $options['button_labels'][0] !== '' ? $options['button_labels'][0] : __($this->default_button_labels[0], 'humble-lms');
      $button_labels[1] = isset( $options['button_labels'][1] ) && ! empty( $options['button_labels'][1] ) && $options['button_labels'][1] !== '' ? $options['button_labels'][1] : __($this->default_button_labels[1], 'humble-lms');
      return $button_labels;
    }
    
  }

}
