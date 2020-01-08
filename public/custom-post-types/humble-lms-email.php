<?php

$labels = array(
  'name'                  => _x( 'Emails', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Email', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Emails', 'humble_lms' ),
  'name_admin_bar'        => __( 'Emails', 'humble_lms' ),
  'archives'              => __( 'Email Archives', 'humble_lms' ),
  'attributes'            => __( 'Email Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Email:', 'humble_lms' ),
  'all_items'             => __( 'All Emails', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Email', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Email', 'humble_lms' ),
  'edit_item'             => __( 'Edit Email', 'humble_lms' ),
  'update_item'           => __( 'Update Email', 'humble_lms' ),
  'view_item'             => __( 'View Emails', 'humble_lms' ),
  'view_items'            => __( 'View Emails', 'humble_lms' ),
  'search_items'          => __( 'Search Email', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into email', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this email', 'humble_lms' ),
  'items_list'            => __( 'Emails list', 'humble_lms' ),
  'items_list_navigation' => __( 'Emails list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter emails list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('emails', 'humble-lms'),
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Email', 'humble_lms' ),
  'description'           => __( 'Email', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => false,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-email',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => true,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => true,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_email', $args );

// Add meta boxes
function humble_lms_email_add_meta_boxes()
{
  add_meta_box( 'humble_lms_email_message_mb', __('E-Mail message', 'humble-lms'), 'humble_lms_email_message_mb', 'humble_lms_email', 'normal', 'default' );
  add_meta_box( 'humble_lms_email_format_mb', __('E-Mail format', 'humble-lms'), 'humble_lms_email_format_mb', 'humble_lms_email', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_email_add_meta_boxes' );

// Email message
function humble_lms_email_message_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $message = get_post_meta( $post->ID, 'humble_lms_email_message', true );

  echo '<textarea class="widefat" id="humble_lms_email_message" name="humble_lms_email_message" rows="10">' . $message . '</textarea>';

  echo '<p>You can use the following strings to include specific information in your emails:</p>';
  echo '<ul>';
    echo '<li>' . __('Student name', 'humble-lms') . ': <strong>STUDENT_NAME</strong></li>';
    echo '<li>' . __('Course title', 'humble-lms') . ': <strong>COURSE_TITLE</strong></li>';
    echo '<li>' . __('Lesson title', 'humble-lms') . ': <strong>LESSON_TITLE</strong></li>';
    echo '<li>' . __('Website title', 'humble-lms') . ': <strong>WEBSITE_TITLE</strong></li>';
    echo '<li>' . __('Website URL', 'humble-lms') . ': <strong>WEBSITE_URL</strong></li>';
  echo '</ul>';

  $allowed_html = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'br', 'em', 'strong' );
  echo '<p>' . __('If you send emails in HTML format, you can use the following tags') . ':</p>';
  echo '<strong>' . implode('</strong>, <strong>', $allowed_html ) . '</strong>';
}

// Email format
function humble_lms_email_format_mb()
{
  global $post;

  $format = get_post_meta( $post->ID, 'humble_lms_email_format', true );

  echo '<select id="humble_lms_email_format" name="humble_lms_email_format">';
  $selected_html = ( ( $format === 'text/html' ) || ( ! $format ) ) ? 'selected' : '';
  $selected_text = $format === 'text/plain' ? 'selected' : '';
  echo '<option value="text/html" ' . $selected_html . '>HTML</option>';
  echo '<option value="text/plain" ' . $selected_text . '>Plain text</option>';
  echo '</select>';
}

// Save metabox data

function humble_lms_save_email_meta_boxes( $post_id, $post )
{
  $nonce = ! empty( $_POST['humble_lms_meta_nonce'] ) ? $_POST['humble_lms_meta_nonce'] : '';

  if( ! wp_verify_nonce( $nonce, 'humble_lms_meta_nonce' ) ) {
    return $post_id;
  }
  
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }

  if( ! is_admin() ) {
    return false;
  }
  
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return $post_id;
  }
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_email' ) {
    return false;
  }

  // Let's save some data!
  $allowed_html = array(
    'p' => array(),
    'h1' => array(),
    'h2' => array(),
    'h3' => array(),
    'a' => array(
      'href' => array(),
      'title' => array()
    ),
    'br' => array(),
    'em' => array(),
    'strong' => array(),
  );

  $email_meta['humble_lms_email_message'] = isset( $_POST['humble_lms_email_message'] ) ? wp_kses( $_POST['humble_lms_email_message'], $allowed_html ) : '';
  $email_meta['humble_lms_email_format'] = isset( $_POST['humble_lms_email_format'] ) ? sanitize_text_field( $_POST['humble_lms_email_format'] ) : 'text/html';

  if( ! empty( $email_meta ) && sizeOf( $email_meta ) > 0 )
  {
    foreach ($email_meta as $key => $value)
    {
      if( $post->post_type == 'revision' ) return; // Don't store custom data twice

      if( get_post_meta( $post->ID, $key, FALSE ) ) {
        update_post_meta( $post->ID, $key, $value );
      } else {
        add_post_meta( $post->ID, $key, $value );
      }

      if( ! $value ) delete_post_meta( $post->ID, $key ); // Delete if blank
    }
  }
}

add_action('save_post', 'humble_lms_save_email_meta_boxes', 1, 2);
