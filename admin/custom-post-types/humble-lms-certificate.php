<?php

$labels = array(
  'name'                  => _x( 'Certificates', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Certificate', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Certificates', 'humble-lms' ),
  'name_admin_bar'        => __( 'Certificate', 'humble-lms' ),
  'archives'              => __( 'Certificate Archives', 'humble-lms' ),
  'attributes'            => __( 'Certificate Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Certificate:', 'humble-lms' ),
  'all_items'             => __( 'All Certificates', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Certificate', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Certificate', 'humble-lms' ),
  'edit_item'             => __( 'Edit Certificate', 'humble-lms' ),
  'update_item'           => __( 'Update Certificate', 'humble-lms' ),
  'view_item'             => __( 'View Certificates', 'humble-lms' ),
  'view_items'            => __( 'View Certificates', 'humble-lms' ),
  'search_items'          => __( 'Search Certificate', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into certificate', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this certificate', 'humble-lms' ),
  'items_list'            => __( 'Certificates list', 'humble-lms' ),
  'items_list_navigation' => __( 'Certificates list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter certificates list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => __('certificate', 'humble-lms'),
  'with_front'            => true,
);

$args = array(
  'label'                 => __( 'Certificate', 'humble-lms' ),
  'description'           => __( 'Certificate', 'humble-lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'thumbnail', 'revisions' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-clipboard',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => false,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => true,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

// "humble_lms_certificate" exceeds 20 bytes limit
// that's why this post type is called "humble_lms_cert"
register_post_type( 'humble_lms_cert', $args ); 

function humble_lms_cert_add_meta_boxes()
{
  add_meta_box( 'humble_lms_cert_heading_mb', __('Heading', 'humble-lms'), 'humble_lms_cert_heading_mb', 'humble_lms_cert', 'normal', 'default' );
  add_meta_box( 'humble_lms_cert_subheading_mb', __('Subtitle', 'humble-lms'), 'humble_lms_cert_subheading_mb', 'humble_lms_cert', 'normal', 'default' );
  add_meta_box( 'humble_lms_cert_date_format_mb', __('Date format', 'humble-lms'), 'humble_lms_cert_date_format_mb', 'humble_lms_cert', 'normal', 'default' );
  add_meta_box( 'humble_lms_cert_content_mb', __('Content (HTML allowed)', 'humble-lms'), 'humble_lms_cert_content_mb', 'humble_lms_cert', 'normal', 'default' );
  add_meta_box( 'humble_lms_cert_orientation_mb', __('Orientation', 'humble-lms'), 'humble_lms_cert_orientation_mb', 'humble_lms_cert', 'normal', 'default' );
  add_meta_box( 'humble_lms_cert_template_mb', __('Template', 'humble-lms'), 'humble_lms_cert_template_mb', 'humble_lms_cert', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_cert_add_meta_boxes' );

// Heading
function humble_lms_cert_heading_mb()
{
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');

  $heading = get_post_meta( $post->ID, 'humble_lms_cert_heading', true );

  echo '<input class="widefat" name="humble_lms_cert_heading" id="humble_lms_cert_heading" type="text" value="' . $heading . '">';
} 

// Sub-heading
function humble_lms_cert_subheading_mb()
{
  global $post;

  $subheading = get_post_meta( $post->ID, 'humble_lms_cert_subheading', true );

  echo '<input class="widefat" name="humble_lms_cert_subheading" id="humble_lms_cert_subheading" type="text" value="' . $subheading . '">';
}

// Date format
function humble_lms_cert_date_format_mb()
{
  global $post;

  $format = get_post_meta( $post->ID, 'humble_lms_cert_date_format', true );

  echo '<select id="humble_lms_cert_date_format" name="humble_lms_cert_date_format">';
    $selected_german = $format === 'd.F Y' ? 'selected' : '';
    $selected_english = $format === 'F j, Y' ? 'selected' : '';
    echo '<option value="d.F Y" ' . $selected_german . '>'. __('German (20. Februar 2020)', 'humble-lms') . '</option>';
    echo '<option value="F j, Y" ' . $selected_english . '>'. __('English (March 10, 2001)', 'humble-lms') . '</option>';
  echo '</select>';
}

// Email message
function humble_lms_cert_content_mb()
{
  global $post;

  $content = get_post_meta( $post->ID, 'humble_lms_cert_content', true );

  echo '<textarea class="widefat" id="humble_lms_cert_content" name="humble_lms_cert_content" rows="10">' . $content . '</textarea>';

  echo '<p>You can use the following strings to include specific information in your certificates:</p>';
  echo '<ul>';
    echo '<li>' . __('Student name', 'humble-lms') . ': <strong>STUDENT_NAME</strong></li>';
    echo '<li>' . __('Student first name', 'humble-lms') . ': <strong>STUDENT_FIRST_NAME</strong></li>';
    echo '<li>' . __('Student last name', 'humble-lms') . ': <strong>STUDENT_LAST_NAME</strong></li>';
    echo '<li>' . __('Current date', 'humble-lms') . ': <strong>CURRENT_DATE</strong></li>';
    echo '<li>' . __('Website name', 'humble-lms') . ': <strong>WEBSITE_NAME</strong></li>';
    echo '<li>' . __('Website URL', 'humble-lms') . ': <strong>WEBSITE_URL</strong></li>';
  echo '</ul>';

  $allowed_html = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'br', 'em', 'strong', 'img' );
  echo '<p>' . __('You can use the following tags in the content of your certificate') . ':</p>';
  echo '<strong>' . implode('</strong>, <strong>', $allowed_html ) . '</strong>';
}

// Orientation
function humble_lms_cert_orientation_mb()
{
  global $post;

  $orientation = get_post_meta( $post->ID, 'humble_lms_cert_orientation', true );

  if( ! $orientation ) {
    $orientation = 'portrait';
  }

  echo '<select id="humble_lms_cert_orientation" name="humble_lms_cert_orientation">';
    $selected = $orientation === 'portrait' ? 'selected' : '';
    echo '<option value="portrait" ' . $selected . '>Portrait</option>';
    $selected = $orientation === 'landscape' ? 'selected' : '';
    echo '<option value="landscape" ' . $selected . '>Landscape</option>';
  echo '</select>';
}

// Templates
function humble_lms_cert_template_mb()
{
  global $post;

  $formats = ['default'];
  $format = get_post_meta( $post->ID, 'humble_lms_cert_template', true );
  $custom_formats = glob( get_stylesheet_directory() . '/humble-lms/certificate/' . '*.css' );

  if( $custom_formats ) {
    foreach( $custom_formats as $custom_format ) {
      array_push( $formats, basename( $custom_format, '.css' ) );
    }
  }

  if( ! in_array( $format, $formats ) ) {
    $format = 'default';
  }

  echo '<select id="humble_lms_cert_template" name="humble_lms_cert_template">';
    foreach( $formats as $f ) {
      $selected = $f === $format ? 'selected' : '';
      echo '<option value="' . esc_html( strtolower( $f ) ) . '" ' . $selected . '>' . ucfirst( esc_html( $f ) ) . '</option>';
    }
  echo '</select> <a href="' . esc_url( get_permalink( $post->ID ) ) . '" target="_certificate_preview" class="button" id="humble-lms-preview-certificate">' . __('Preview certificate', 'humble-lms') . '</a>';
}

// Save metabox data

function humble_lms_save_cert_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_cert' ) {
    return false;
  }

  // Let's save some data!
  $allowed_html = array(
    'p' => array(
      'style' => array(),
    ),
    'h1' => array(
      'style' => array(),
    ),
    'h2' => array(
      'style' => array(),
    ),
    'h3' => array(
      'style' => array(),
    ),
    'a' => array(
      'href' => array(),
      'title' => array(),
      'style' => array(),
    ),
    'br' => array(),
    'em' => array(),
    'strong' => array(),
    'img' => array(
      'src' => array(),
      'alt' => array(),
      'title' => array(),
      'style' => array(),
    ),
  );

  $cert_meta['humble_lms_cert_heading'] = isset( $_POST['humble_lms_cert_heading'] ) ? sanitize_text_field( $_POST['humble_lms_cert_heading'] ) : '';
  $cert_meta['humble_lms_cert_subheading'] = isset( $_POST['humble_lms_cert_subheading'] ) ? sanitize_text_field( $_POST['humble_lms_cert_subheading'] ) : '';
  $cert_meta['humble_lms_cert_date_format'] = isset( $_POST['humble_lms_cert_date_format'] ) ? sanitize_text_field( $_POST['humble_lms_cert_date_format'] ) : 'F j, Y';
  $cert_meta['humble_lms_cert_content'] = isset( $_POST['humble_lms_cert_content'] ) ? wp_kses( $_POST['humble_lms_cert_content'], $allowed_html ) : '';
  $cert_meta['humble_lms_cert_orientation'] = isset( $_POST['humble_lms_cert_orientation'] ) ? sanitize_text_field( $_POST['humble_lms_cert_orientation'] ) : 'portrait';
  $cert_meta['humble_lms_cert_template'] = isset( $_POST['humble_lms_cert_template'] ) ? sanitize_text_field( $_POST['humble_lms_cert_template'] ) : 'default';

  if( ! empty( $cert_meta ) && sizeOf( $cert_meta ) > 0 )
  {
    foreach ($cert_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_cert_meta_boxes', 1, 2);
