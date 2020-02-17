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

      $this->user = new Humble_LMS_Public_User;
      $this->content_manager = new Humble_LMS_Content_Manager;

      $this->page_sections = array();
      $this->login_url = wp_login_url();
      $this->options = get_option('humble_lms_options');
      $this->admin_url = add_query_arg( 'page', 'humble_lms_options', admin_url() );
      $this->countries = array_map('trim', explode(',', 'Afghanistan, Albania, Algeria, Andorra, Angola, Antigua & Deps, Argentina, Armenia, Australia, Austria, Azerbaijan, Bahamas, Bahrain, Bangladesh, Barbados, Belarus, Belgium, Belize, Benin, Bhutan, Bolivia, Bosnia Herzegovina, Botswana, Brazil, Brunei, Bulgaria, Burkina, Burundi, Cambodia, Cameroon, Canada, Cape Verde, Central African Rep, Chad, Chile, China, Colombia, Comoros, Congo, Congo {Democratic Rep}, Costa Rica, Croatia, Cuba, Cyprus, Czech Republic, Denmark, Djibouti, Dominica, Dominican Republic, East Timor, Ecuador, Egypt, El Salvador, Equatorial Guinea, Eritrea, Estonia, Ethiopia, Fiji, Finland, France, Gabon, Gambia, Georgia, Germany, Ghana, Greece, Grenada, Guatemala, Guinea, Guinea-Bissau, Guyana, Haiti, Honduras, Hungary, Iceland, India, Indonesia, Iran, Iraq, Ireland {Republic}, Israel, Italy, Ivory Coast, Jamaica, Japan, Jordan, Kazakhstan, Kenya, Kiribati, Korea North, Korea South, Kosovo, Kuwait, Kyrgyzstan, Laos, Latvia, Lebanon, Lesotho, Liberia, Libya, Liechtenstein, Lithuania, Luxembourg, Macedonia, Madagascar, Malawi, Malaysia, Maldives, Mali, Malta, Marshall Islands, Mauritania, Mauritius, Mexico, Micronesia, Moldova, Monaco, Mongolia, Montenegro, Morocco, Mozambique, Myanmar, {Burma}, Namibia, Nauru, Nepal, Netherlands, New Zealand, Nicaragua, Niger, Nigeria, Norway, Oman, Pakistan, Palau, Panama, Papua New Guinea, Paraguay, Peru, Philippines, Poland, Portugal, Qatar, Romania, Russian Federation, Rwanda, St Kitts & Nevis, St Lucia, Saint Vincent & the Grenadines, Samoa, San Marino, Sao Tome & Principe, Saudi Arabia, Senegal, Serbia, Seychelles, Sierra Leone, Singapore, Slovakia, Slovenia, Solomon Islands, Somalia, South Africa, South Sudan, Spain, Sri Lanka, Sudan, Suriname, Swaziland, Sweden, Switzerland, Syria, Taiwan, Tajikistan, Tanzania, Thailand, Togo, Tonga, Trinidad & Tobago, Tunisia, Turkey, Turkmenistan, Tuvalu, Uganda, Ukraine, United Arab Emirates, United Kingdom, United States, Uruguay, Uzbekistan, Vanuatu, Vatican City, Venezuela, Vietnam, Yemen, Zambia, Zimbabwe'));

      $this->messages = array(
        'lesson' => __('Lessons', 'humble-lms'),
        'course' => __('Courses', 'humble-lms'),
        'track' => __('Tracks', 'humble-lms'),
        'award' => __('Awards', 'humble-lms'),
        'certificate' => __('Certificates', 'humble-lms'),
      );

      $this->custom_pages = array(
        'login' => 0,
        'registration' => 0,
        'lost_password' => 0,
        'reset_password' => 0,
        'user_profile' => 0,
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
      echo '<div class="humble-lms-loading-layer">
        <div class="humble-lms-loading"></div>
      </div>';

      echo '<div class="wrap">';
        echo '<h1> ' . __('Humble LMS', 'humble-lms') . '</h1>';
        
        settings_errors();

        $active = isset( $_GET['active'] ) ? sanitize_text_field( $_GET['active'] ) : 'reporting-users';
        $nav_tab_reporting_users = $active === 'reporting-users' ? 'nav-tab-active' : '';
        $nav_tab_reporting_courses = $active === 'reporting-courses' ? 'nav-tab-active' : '';
        $nav_tab_options = $active === 'options' ? 'nav-tab-active' : '';
        $nav_tab_registration = $active === 'registration' ? 'nav-tab-active' : '';

        echo '<h2 class="nav-tab-wrapper">
          <a href="' . $this->admin_url . '&active=reporting-users" class="nav-tab ' . $nav_tab_reporting_users . '">' . __('Reporting: Users', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=reporting-courses" class="nav-tab ' . $nav_tab_reporting_courses . '">' . __('Reporting: Courses', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=options" class="nav-tab ' . $nav_tab_options . '">' . __('Options', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=registration" class="nav-tab ' . $nav_tab_registration . '">' . __('Registration', 'humble-lms') . '</a>
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
      register_setting( 'humble_lms_options', 'humble_lms_options', 'humble_lms_options_validate' );
      register_setting( 'humble_lms_options_registration', 'humble_lms_options', 'humble_lms_options_validate' );
      
      add_settings_section('humble_lms_options_section_reporting_users', '', array( $this, 'humble_lms_options_section_reporting_users' ), 'humble_lms_options_reporting_users' );
      add_settings_section('humble_lms_options_section_reporting_courses', __('Reporting: Courses', 'humble-lms'), array( $this, 'humble_lms_options_section_reporting_courses' ), 'humble_lms_options_reporting_courses' );
      add_settings_section('humble_lms_options_section_options', __('Options', 'humble-lms'), array( $this, 'humble_lms_options_section_options' ), 'humble_lms_options' );
      add_settings_section('humble_lms_options_section_registration', __('User Registration', 'humble-lms'), array( $this, 'humble_lms_options_section_registration' ), 'humble_lms_options_registration' );

      add_settings_field( 'tile_width_track', __('Track archive tile width', 'humble-lms'), array( $this, 'tile_width_track' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'tile_width_course', __('Course archive tile width', 'humble-lms'), array( $this, 'tile_width_course' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'messages', __('Which messages should be shown when students complete a lesson?', 'humble-lms'), array( $this, 'messages' ), 'humble_lms_options', 'humble_lms_options_section_options');
      add_settings_field( 'custom_pages', __('Please select the custom page IDs (login, registration, lost password)', 'humble-lms'), array( $this, 'custom_pages' ), 'humble_lms_options', 'humble_lms_options_section_options');
      
      add_settings_field( 'registration_has_country', __('Include country in registration form?', 'humble-lms'), array( $this, 'registration_has_country' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'registration_countries', __('Which countries should be included (multiselect)?', 'humble-lms'), array( $this, 'registration_countries' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'email_welcome', __('Welcome email', 'humble-lms'), array( $this, 'email_welcome' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
      add_settings_field( 'email_lost_password', __('Lost password email', 'humble-lms'), array( $this, 'email_lost_password' ), 'humble_lms_options_registration', 'humble_lms_options_section_registration');
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
     * Option for displaying country field in registration form.
     *
     * @since    0.0.1
     */
    public function registration_has_country() {
      $registration_has_country = isset( $this->options['registration_has_country'] ) ? (int)$this->options['registration_has_country'] : 0;
      $checked = $registration_has_country === 1 ? 'checked' : '';
  
      echo '<p><input id="registration_has_country" name="humble_lms_options[registration_has_country]" type="checkbox" value="1" ' . $checked . '>' . __('Yes, include country in registration form.', 'humble-lms') . '</p>';
    }

    /**
     * Option for selecting individual countries to be included in registration form.
     *
     * @since    0.0.1
     */
    public function registration_countries() {
      $countries = $this->countries;
      $registration_countries = isset( $this->options['registration_countries'] ) ? maybe_unserialize( $this->options['registration_countries'] ) : $this->countries;
  
      echo '<select multiple size="20" class="widefat" id="registration_countries" placeholder="' . __('Wich countries would you like to include?', 'humble-lms') . '" name="humble_lms_options[registration_countries][]">';
        foreach( $countries as $key => $country ) {
          $selected = in_array( $country, $registration_countries ) ? 'selected' : '';
          echo '<option value="' . $country . '" ' . $selected . '>' . $country . '</option>';
        }
      echo '</select>';
    }

    /**
     * Content of the welcome email.
     *
     * @since    0.0.1
     */
    function email_welcome()
    {
      $message = isset( $this->options['email_welcome'] ) ? $this->options['email_welcome'] : '';

      echo '<p>' . __('This email will be send in plain text format. HTML is currently not allowed. You can use the following strings to include specific information in your email:', 'humble-lms') . '</p>';
      echo '<p><strong>WEBSITE_NAME</strong>, <strong>WEBSITE_URL</strong>, <strong>LOGIN_URL</strong>, <strong>USER_NAME</strong>, <strong>USER_EMAIL</strong>, <strong>CURRENT_DATE</strong>, <strong>ADMIN_EMAIL</strong></p>';
      echo '<div class="humble-lms-test-email" id="humble-lms-test-email-welcome">';
        echo '<p><textarea class="widefat" id="email_welcome" name="humble_lms_options[email_welcome]" rows="7">' . $message . '</textarea></p>';
        echo '<p><input id="humble-lms-test-email-recipient" type="email" class="widefat" value="' . get_bloginfo( 'admin_email' ) . '" /></p>';
        echo '<input type="hidden" name="subject" value="' . __('Test email: Welcome', 'humble-lms') . '" />';
        echo '<p><a class="button humble-lms-send-test-email">' . __('Send a test email', 'humble-lms') . '</a></p>';
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

      echo '<p>' . __('This email will be send in plain text format. HTML is currently not allowed. You can use the following strings to include specific information in your email:', 'humble-lms') . '</p>';
      echo '<p><strong>RESET_PASSWORD_URL</strong>, <strong>WEBSITE_NAME</strong>, <strong>WEBSITE_URL</strong>, <strong>LOGIN_URL</strong>, <strong>USER_NAME</strong>, <strong>USER_EMAIL</strong>, <strong>CURRENT_DATE</strong>, <strong>ADMIN_EMAIL</strong></p>';
      echo '<div class="humble-lms-test-email" id="humble-lms-test-email-welcome">';
        echo '<p><textarea class="widefat" id="email_lost_password" name="humble_lms_options[email_lost_password]" rows="7">' . $message . '</textarea></p>';
        echo '<p><input id="humble-lms-test-email-recipient" type="email" class="widefat" value="' . get_bloginfo( 'admin_email' ) . '" /></p>';
        echo '<input type="hidden" name="subject" value="' . __('Test email: Lost password', 'humble-lms') . '" />';
        echo '<p><a class="button humble-lms-send-test-email">' . __('Send a test email', 'humble-lms') . '</a></p>';
      echo '</div>';
    }

    /**
     * Validate option data on save.
     *
     * @param   array
     * @return  array
     * @since   0.0.1
     */
    public function humble_lms_options_validate( $options ) {
      $validated['tile_width_course'] = sanitize_text_field( $options['tile_width_course'] );
      $validated['tile_width_course'] = sanitize_text_field( $options['tile_width_course'] );
      $validated['messages'] = $options['messages'];
      $validated['custom_pages'] = $options['custom_pages'];

      $validated['registration_has_country'] = (int)$options['registration_has_country'] === 1 ? 1 : 0;
      $validated['registration_countries'] = ! empty( $options['registration_countries'] ) ? serialize( $options['registration_countries'] ) : [];

      $validated['email_welcome'] = isset( $options['email_welcome'] ) ? sanitize_test_field( $options['email_welcome'] ) : '';
      $validated['email_lost_password'] = isset( $options['email_lost_password'] ) ? sanitize_test_field( $options['email_lost_password'] ) : '';


      return $validated;
    }

    /**
     * Generate reporting table for all users.
     *
     * @return  false
     * @since   0.0.1
     */
    public function reporting_users_table() {
      // TODO: pagination
      $users_per_page = $this->users_per_page();

      $paged = isset( $_GET['paged'] ) ? (int)$_GET['paged'] : 0;
      $total_users = count_users()['total_users'];

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

            // TODO: link to single user reporting view
            echo '<tr>
              <td><a href="' . get_edit_user_link( $user->ID ) . '">' . $user->ID . '</a></td>
              <td><a href="' . $this->admin_url . '&user_id=' . $user->ID . '&users-per-page=' . $users_per_page . '"><strong>' . $user->user_login . '</strong></a></td>
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
      $users_per_page = $this->users_per_page();
      $users_per_page_values = array(10, 25, 50, 100, 250, 500);

      echo '<p><strong>' . __('Users per page', 'humble-lms') . '</strong><p>
      <form method="post" action="' . $this->admin_url . '">
        <select id="users-per-page" name="users-per-page">';
        array_walk( $users_per_page_values, function( $value, $key, $users_per_page ) {
          $selected = $value === $users_per_page ? 'selected' : '';
          echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
        }, $users_per_page);
        echo '</select>
        <input type="submit" value="' . __('Submit') . '" />
      </form>';

      if( $total_users > $users_per_page ) {
        $args = array(
           'format' => '?paged=%#%&users-per-page=' . $users_per_page,
           'total' => ceil($total_users / $users_per_page),
           'current' => max(1, $paged),
        );

        echo '<br>' . paginate_links( $args );
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
      ) );

      $courses = get_posts( array(
        'post_type' => 'humble_lms_course',
        'post_status' => 'publish',
        'posts_per_page' => -1,
      ) );

      $lessons = get_posts( array(
        'post_type' => 'humble_lms_lesson',
        'post_status' => 'publish',
        'posts_per_page' => -1,
      ) );

      $awards = get_posts( array(
        'post_type' => 'humble_lms_award',
        'post_status' => 'publish',
        'posts_per_page' => -1,
      ) );

      $certificates = get_posts( array(
        'post_type' => 'humble_lms_cert',
        'post_status' => 'publish',
        'posts_per_page' => -1,
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
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="25%">' . __('Awards', 'humble-lms') . '</th>
        <th width="75%">' . __('Progress', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $awards as $award ) {
        $completed = in_array( $award->ID, $granted_awards ) ? '<span class="humble-lms-options-complete">&check;</span>' : '<span class="humble-lms-options-incomplete">&times;</span>';

        echo '<tr class="humble-lms-reporting-award">';
          echo '<td><strong><a href="' . get_edit_post_link( $award->ID ) . '">' . get_the_title( $award->ID ) . '</a></strong></td>';
          echo '<td>'. $completed . '</td>';
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
      echo '<table class="widefat humble-lms-reporting-table"><thead><tr>
        <th width="25%">' . __('Certificates', 'humble-lms') . '</th>
        <th width="75%">' . __('Progress', 'humble-lms') . '</th>
      </tr></thead>';

      foreach( $certificates as $certificate ) {
        $completed = in_array( $certificate->ID, $issued_certificates ) ? '<span class="humble-lms-options-complete">&check;</span>' : '<span class="humble-lms-options-incomplete">&times;</span>';

        echo '<tr class="humble-lms-reporting-certificates">';
          echo '<td><strong><a href="' . get_edit_post_link( $certificate->ID ) . '">' . get_the_title( $certificate->ID ) . '</a></strong></td>';
          echo '<td>'. $completed . '</td>';
        echo '</tr>';
      }

      if( ! $certificates ) {
        echo '<tr class="humble-lms-reporting-certificates">';
          echo '<td>' . __('No certificates available.', 'humble-lms') . '</td>';
          echo '<td>–</td>';
        echo '</tr>';
      }

      echo '</table>';

      echo '<p><a class="button button-primary humble-lms-reset-user-progress" data-user-id="' . $user->ID . '">' . __('Reset learning progress for this user?', 'humble-lms') . '</a></p>';
  
      $users_per_page = $this->users_per_page();
      $users_per_page = $users_per_page !== 50 ? '&users-per-page=' . $users_per_page : '';

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
      if( isset( $_GET['users-per-page'] ) && $_GET['users-per-page'] !== '' ) {
        $users_per_page = (int)$_GET['users-per-page'];
      } else if( isset( $_POST['users-per-page'] ) ) {
        $users_per_page = (int)$_POST['users-per-page'];
      } else {
        $users_per_page = 50;
      }

      return $users_per_page;
    }
    
  }

}
