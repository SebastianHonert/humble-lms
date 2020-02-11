<?php

$labels = array(
  'name'                  => _x( 'Awards', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Award', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Awards', 'humble-lms' ),
  'name_admin_bar'        => __( 'Awards', 'humble-lms' ),
  'archives'              => __( 'Award Archives', 'humble-lms' ),
  'attributes'            => __( 'Award Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Award:', 'humble-lms' ),
  'all_items'             => __( 'All Awards', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Award', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Award', 'humble-lms' ),
  'edit_item'             => __( 'Edit Award', 'humble-lms' ),
  'update_item'           => __( 'Update Award', 'humble-lms' ),
  'view_item'             => __( 'View Awards', 'humble-lms' ),
  'view_items'            => __( 'View Awards', 'humble-lms' ),
  'search_items'          => __( 'Search Award', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into award', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this award', 'humble-lms' ),
  'items_list'            => __( 'Awards list', 'humble-lms' ),
  'items_list_navigation' => __( 'Awards list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter awards list', 'humble-lms' ),
);

$rewrite = array(
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Award', 'humble-lms' ),
  'description'           => __( 'Award', 'humble-lms' ),
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
  'show_in_admin_bar'     => false,
  'show_in_nav_menus'     => false,
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
  add_meta_box('postimagediv', __('Award image (256x256)', 'humble-lms'), 'post_thumbnail_meta_box', 'humble_lms_award', 'normal', 'high');
}

add_action( 'add_meta_boxes', 'humble_lms_award_add_meta_boxes' );
