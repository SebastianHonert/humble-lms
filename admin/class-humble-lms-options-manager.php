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
      $this->admin_url = admin_url() . '?page=humble_lms_options';
      $this->login_url = wp_login_url();
      $this->user = new Humble_LMS_Public_User;

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

        $active = isset( $_GET['active'] ) ? sanitize_text_field( $_GET['active'] ) : 'reporting-users';
        $nav_tab_reporting_users = $active === 'reporting-users' ? 'nav-tab-active' : '';
        $nav_tab_reporting_courses = $active === 'reporting-courses' ? 'nav-tab-active' : '';
        $nav_tab_options = $active === 'options' ? 'nav-tab-active' : '';

        echo '<h2 class="nav-tab-wrapper">
          <a href="' . $this->admin_url . '&active=reporting-users" class="nav-tab ' . $nav_tab_reporting_users . '">' . __('Reporting: Users', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=reporting-courses" class="nav-tab ' . $nav_tab_reporting_courses . '">' . __('Reporting: Courses', 'humble-lms') . '</a>
          <a href="' . $this->admin_url . '&active=options" class="nav-tab ' . $nav_tab_options . '">' . __('Options', 'humble-lms') . '</a>
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
    public function humble_lms_options_admin_init() {
      register_setting( 'humble_lms_options', 'humble_lms_options', 'humble_lms_options_validate' );
      
      add_settings_section('humble_lms_options_section_reporting_users', '', array( $this, 'humble_lms_options_section_reporting_users' ), 'humble_lms_options_reporting_users' );
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
     * Validate option data on save.
     *
     * @param   array
     * @return  array
     * @since   0.0.1
     */
    public function humble_lms_options_validate( $options ) {
      $validated['tile_width_track'] = sanitize_text_field( $options['tile_width_track'] );
      $validated['tile_width_course'] = sanitize_text_field( $options['tile_width_course'] );

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
        'meta_key' => 'nickname',
        'orderby' => 'meta_value',
        'order' => 'ASC'
      );

      $users = get_users( $args );

      echo '<table class="widefat">';
        echo '<thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Tracks (' . wp_count_posts('humble_lms_track')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_track') ) . ')</th>
            <th>Courses (' . wp_count_posts('humble_lms_course')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_course') ) . ')</th>
            <th>Lessons (' . wp_count_posts('humble_lms_lesson')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_lesson') ) . ')</th>
            <th>Awards (' . wp_count_posts('humble_lms_award')->publish . '/' . array_sum( (array)wp_count_posts('humble_lms_award') ) . ')</th>
          </tr>
        </thead>
        <tbody>';
          foreach( $users as $user ) {
            $user_meta = get_userdata( $user->ID );

            $tracks_total = wp_count_posts('humble_lms_track')->publish;
            $completed_tracks = count( $this->user->completed_tracks( $user->ID, true ) );
            $completed_tracks_percent = ( $completed_tracks / $tracks_total ) * 100;

            $courses_total = wp_count_posts('humble_lms_course')->publish;
            $completed_courses = count( $this->user->completed_courses( $user->ID, true ) );
            $completed_courses_percent = ( $completed_courses / $courses_total ) * 100;

            $lessons_total = wp_count_posts('humble_lms_lesson')->publish;
            $completed_lessons = count( $this->user->completed_lessons( $user->ID, true ) );
            $completed_lessons_percent = ( $completed_lessons / $lessons_total ) * 100;

            $awards_total = wp_count_posts('humble_lms_award')->publish;
            $completed_awards = count( $this->user->granted_awards( $user->ID, true ) );
            $completed_awards_percent = ( $completed_awards / $awards_total ) * 100;

            // TODO: link to single user reporting view
            echo '<tr>
              <td><a href="' . get_edit_user_link( $user->ID ) . '">' . $user->ID . '</a></td>
              <td><a href="' . $this->admin_url . '&user_id=' . $user->ID . '&users-per-page=' . $users_per_page . '"><strong>' . $user->nickname . '</strong></a></td>
              <td>' . implode(',', $user_meta->roles ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_tracks_percent, $completed_tracks ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_courses_percent, $completed_courses ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_lessons_percent, $completed_lessons ) . '</td>
              <td>' . $this->progress_bar( (int)$completed_awards_percent, $completed_awards ) . '</td>
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
        <th>' . $user->nickname . ' (ID <a href="' . get_edit_user_link( $user->ID ) . '">' . $user->ID . '</a>)</th></tr></thead>
      <tr><td>' . __('Registered', 'humble-lms') . ': <strong>' . $this->user->registered_at( $user_id, true ) . '</strong></td>
      </tr></table>';

      $completed_tracks = $this->user->completed_tracks( $user->ID, true );
      $completed_courses = $this->user->completed_courses( $user->ID, true );
      $completed_lessons = $this->user->completed_lessons( $user->ID, true );
      $granted_awards = $this->user->granted_awards( $user->ID, true );

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

        $course_lessons = get_post_meta( $course->ID, 'humble_lms_course_lessons', true );
        $course_lessons = ! empty( $course_lessons[0] ) ? json_decode( $course_lessons[0] ) : [];

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

      echo '</table>';
  
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
      echo 'TODO: reporting_courses_table()';
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
