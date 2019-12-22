<?php

$labels = array(
  'name'                  => _x( 'Awards', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Award', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Awards', 'humble_lms' ),
  'name_admin_bar'        => __( 'Awards', 'humble_lms' ),
  'archives'              => __( 'Award Archives', 'humble_lms' ),
  'attributes'            => __( 'Award Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Award:', 'humble_lms' ),
  'all_items'             => __( 'All Awards', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Award', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Award', 'humble_lms' ),
  'edit_item'             => __( 'Edit Award', 'humble_lms' ),
  'update_item'           => __( 'Update Award', 'humble_lms' ),
  'view_item'             => __( 'View Awards', 'humble_lms' ),
  'view_items'            => __( 'View Awards', 'humble_lms' ),
  'search_items'          => __( 'Search Award', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into award', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this award', 'humble_lms' ),
  'items_list'            => __( 'Awards list', 'humble_lms' ),
  'items_list_navigation' => __( 'Awards list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter awards list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('awards', 'humble-lms'),
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Award', 'humble_lms' ),
  'description'           => __( 'Award', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'thumbnail', 'revisions' ),
  'show_in_rest'          => true,
  'taxonomies'            => array(),
  'hierarchical'          => false,
  'public'                => false,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-awards',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => true,
  'can_export'            => true,
  'has_archive'           => false,
  'exclude_from_search'   => true,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_award', $args );

function humble_lms_award_add_meta_boxes()
{
  remove_meta_box( 'postimagediv', 'post_type', 'side' );
  add_meta_box('postimagediv', __('Award image (300x300)'), 'post_thumbnail_meta_box', 'humble_lms_award', 'normal', 'high');
}

add_action( 'add_meta_boxes', 'humble_lms_award_add_meta_boxes' );
