<?php

$labels = array(
  'name'                  => _x( 'Lessons', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Lesson', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Lessons', 'humble_lms' ),
  'name_admin_bar'        => __( 'Lessons', 'humble_lms' ),
  'archives'              => __( 'Lesson Archives', 'humble_lms' ),
  'attributes'            => __( 'Lesson Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Lesson:', 'humble_lms' ),
  'all_items'             => __( 'All Lessons', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Lesson', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Lesson', 'humble_lms' ),
  'edit_item'             => __( 'Edit Lesson', 'humble_lms' ),
  'update_item'           => __( 'Update Lesson', 'humble_lms' ),
  'view_item'             => __( 'View Lesson', 'humble_lms' ),
  'view_items'            => __( 'View Lessons', 'humble_lms' ),
  'search_items'          => __( 'Search Lesson', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into lesson', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this lesson', 'humble_lms' ),
  'items_list'            => __( 'Lessons list', 'humble_lms' ),
  'items_list_navigation' => __( 'Lessons list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter lessons list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => 'lesson',
  'with_front'            => true,
  'pages'                 => true,
  'feeds'                 => true,
);

$args = array(
  'label'                 => __( 'Lesson', 'humble_lms' ),
  'description'           => __( 'Lesson', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'editor', 'revisions', 'post-formats' ),
  'show_in_rest'          => true,
  'taxonomies'            => array( 'category', 'post_tag' ),
  'hierarchical'          => false,
  'public'                => true,
  'show_ui'               => true,
  'show_in_menu'          => true,
  'menu_position'         => 5,
  'menu_icon'             => 'dashicons-welcome-learn-more',
  'show_in_admin_bar'     => true,
  'show_in_nav_menus'     => true,
  'can_export'            => true,
  'has_archive'           => true,
  'exclude_from_search'   => false,
  'publicly_queryable'    => true,
  'rewrite'               => $rewrite,
  'capability_type'       => 'page',
);

register_post_type( 'humble_lms_lesson', $args );
