<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sebastianhonert.com
 * @since      0.0.1
 *
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Humble_LMS
 * @subpackage Humble_LMS/includes
 * @author     Sebastian Honert <hello@sebastianhonert.com>
 */
class Humble_LMS_Activator {

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    0.0.1
   */
  public function activate() {
    flush_rewrite_rules();

    $this->add_custom_pages();
    $this->init_options();
  }

  /**
   * Add custom pages for login, registration and password reset.
   * 
   * @since   0.0.1
   */
  public function add_custom_pages() {
    $custom_page_course_archive = array(
      'post_title' => 'Humble LMS Course Archive',
      'post_name' => __('courses', 'humble-lms'),
      'post_content' => '[humble_lms_course_archive]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_track_archive = array(
      'post_title' => 'Humble LMS Track Archive',
      'post_name' => __('tracks', 'humble-lms'),
      'post_content' => '[humble_lms_track_archive]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_login = array(
      'post_title' => 'Humble LMS Login',
      'post_name' => 'login',
      'post_content' => '[humble_lms_login_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );
    
    $custom_page_registration = array(
      'post_title' => 'Humble LMS Registration',
      'post_name' => __('registration', 'humble-lms'),
      'post_content' => '[humble_lms_registration_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_lost_password = array(
      'post_title' => 'Humble LMS Lost Password',
      'post_name' => __('lost-password', 'humble-lms'),
      'post_content' => '[humble_lms_lost_password_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_reset_password = array(
      'post_title' => 'Humble LMS Reset Password',
      'post_name' => __('reset-password', 'humble-lms'),
      'post_content' => '[humble_lms_reset_password_form]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    $custom_page_user_profile = array(
      'post_title' => 'Humble LMS User Profile',
      'post_name' => __('account', 'humble-lms'),
      'post_content' => '[humble_lms_user_profile]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => 'page',
    );

    if( ! get_page_by_title('Humble LMS Course Archive', OBJECT, 'page') )
      wp_insert_post( $custom_page_course_archive );
    
    if( ! get_page_by_title('Humble LMS Track Archive', OBJECT, 'page') )
      wp_insert_post( $custom_page_track_archive );

    if( ! get_page_by_title('Humble LMS Login', OBJECT, 'page') )
      wp_insert_post( $custom_page_login );
    
    if( ! get_page_by_title('Humble LMS Registration', OBJECT, 'page') )
      wp_insert_post( $custom_page_registration );

    if( ! get_page_by_title('Humble LMS Lost Password', OBJECT, 'page') )
      wp_insert_post( $custom_page_lost_password );

    if( ! get_page_by_title('Humble LMS Reset Password', OBJECT, 'page') )
      wp_insert_post( $custom_page_reset_password );

    if( ! get_page_by_title('Humble LMS User Profile', OBJECT, 'page') )
      wp_insert_post( $custom_page_user_profile );
  }

  /**
   * Initialize plugin options.
   * 
   * @since   0.0.1
   */
  public function init_options() {
    $custom_page_login = get_page_by_title('Humble LMS Login', OBJECT, 'page');
    $custom_page_registration = get_page_by_title('Humble LMS Registration', OBJECT, 'page');
    $custom_page_lost_password = get_page_by_title('Humble LMS Lost Password', OBJECT, 'page');
    $custom_page_reset_password = get_page_by_title('Humble LMS Reset Password', OBJECT, 'page');
    $custom_page_user_profile = get_page_by_title('Humble LMS User Profile', OBJECT, 'page');

    // Set invoice counter
    $invoice_counter = get_option('humble_lms_invoice_counter');

    if( ! isset( $invoice_counter ) || ! $invoice_counter ) {
      update_option('humble_lms_invoice_counter', 0);
    }

    // Set default plugin options
    $options = get_option('humble_lms_options');

    if( ! isset( $options ) || ! is_array( $options ) ) {
      update_option('humble_lms_options', array(
        'secret_key' => '5fba5d909a6c83.38241175',
        'item_reference' => 'Humble LMS',
        'delete_plugin_data_on_uninstall' => 0,
        'has_sales' => 0,
        'use_coupons' => 0,
        'tiles_per_page' => 10,
        'syllabus_max_height' => 640,
        'messages' => array('lesson', 'course', 'track', 'award', 'certificate'),
        'registration_countries' => array_map('trim', explode(',', 'Afghanistan, Albania, Algeria, Andorra, Angola, Antigua & Deps, Argentina, Armenia, Australia, Austria, Azerbaijan, Bahamas, Bahrain, Bangladesh, Barbados, Belarus, Belgium, Belize, Benin, Bhutan, Bolivia, Bosnia Herzegovina, Botswana, Brazil, Brunei, Bulgaria, Burkina, Burundi, Cambodia, Cameroon, Canada, Cape Verde, Central African Rep, Chad, Chile, China, Colombia, Comoros, Congo, Congo {Democratic Rep}, Costa Rica, Croatia, Cuba, Cyprus, Czech Republic, Denmark, Djibouti, Dominica, Dominican Republic, East Timor, Ecuador, Egypt, El Salvador, Equatorial Guinea, Eritrea, Estonia, Ethiopia, Fiji, Finland, France, Gabon, Gambia, Georgia, Germany, Ghana, Greece, Grenada, Guatemala, Guinea, Guinea-Bissau, Guyana, Haiti, Honduras, Hungary, Iceland, India, Indonesia, Iran, Iraq, Ireland {Republic}, Israel, Italy, Ivory Coast, Jamaica, Japan, Jordan, Kazakhstan, Kenya, Kiribati, Korea North, Korea South, Kosovo, Kuwait, Kyrgyzstan, Laos, Latvia, Lebanon, Lesotho, Liberia, Libya, Liechtenstein, Lithuania, Luxembourg, Macedonia, Madagascar, Malawi, Malaysia, Maldives, Mali, Malta, Marshall Islands, Mauritania, Mauritius, Mexico, Micronesia, Moldova, Monaco, Mongolia, Montenegro, Morocco, Mozambique, Myanmar, {Burma}, Namibia, Nauru, Nepal, Netherlands, New Zealand, Nicaragua, Niger, Nigeria, Norway, Oman, Pakistan, Palau, Panama, Papua New Guinea, Paraguay, Peru, Philippines, Poland, Portugal, Qatar, Romania, Russian Federation, Rwanda, St Kitts & Nevis, St Lucia, Saint Vincent & the Grenadines, Samoa, San Marino, Sao Tome & Principe, Saudi Arabia, Senegal, Serbia, Seychelles, Sierra Leone, Singapore, Slovakia, Slovenia, Solomon Islands, Somalia, South Africa, South Sudan, Spain, Sri Lanka, Sudan, Suriname, Swaziland, Sweden, Switzerland, Syria, Taiwan, Tajikistan, Tanzania, Thailand, Togo, Tonga, Trinidad & Tobago, Tunisia, Turkey, Turkmenistan, Tuvalu, Uganda, Ukraine, United Arab Emirates, United Kingdom, United States, Uruguay, Uzbekistan, Vanuatu, Vatican City, Venezuela, Vietnam, Yemen, Zambia, Zimbabwe')),
        'countries_without_vat' => array(),
        'custom_pages' => array(
          'login' => $custom_page_login->ID,
          'registration' => $custom_page_registration->ID,
          'lost_password' => $custom_page_lost_password->ID,
          'reset_password' => $custom_page_reset_password->ID,
          'user_profile' => $custom_page_user_profile->ID,
        ),
        'currency' => 'EUR',
        'max_evaluations' => 25,
        'tippy_theme' => 'default',
        'email_welcome' => "Hi there,

  welcome to WEBSITE_NAME! Here's how to log in:

  Username: USER_NAME
  Email: USER_EMAIL
  Login URL: LOGIN_URL

  Please use the password you entered in the registration form.

  If you have any problems, please contact us via email to ADMIN_EMAIL.

  Best wishes –
  Sebastian",
          'email_lost_password' => "Hi there,

  you asked us to reset your password for your account using the email address USER_EMAIL.

  If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.

  To reset your password please visit the following address: RESET_PASSWORD_URL

  Thank you!"
      ) );
    }
 
  }

}
