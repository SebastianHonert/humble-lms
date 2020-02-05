<?php

$labels = array(
  'name'                       => _x( 'Difficulty', 'Taxonomy General Name', 'humble-lms' ),
  'singular_name'              => _x( 'Difficulty', 'Taxonomy Singular Name', 'humble-lms' ),
  'menu_name'                  => __( 'Difficulty', 'humble-lms' ),
  'all_items'                  => __( 'All Items', 'humble-lms' ),
  'parent_item'                => __( 'Parent Item', 'humble-lms' ),
  'parent_item_colon'          => __( 'Parent Item:', 'humble-lms' ),
  'new_item_name'              => __( 'New Item Name', 'humble-lms' ),
  'add_new_item'               => __( 'Add New Item', 'humble-lms' ),
  'edit_item'                  => __( 'Edit Item', 'humble-lms' ),
  'update_item'                => __( 'Update Item', 'humble-lms' ),
  'view_item'                  => __( 'View Item', 'humble-lms' ),
  'separate_items_with_commas' => __( 'Separate items with commas', 'humble-lms' ),
  'add_or_remove_items'        => __( 'Add or remove items', 'humble-lms' ),
  'choose_from_most_used'      => __( 'Choose from the most used', 'humble-lms' ),
  'popular_items'              => __( 'Popular Items', 'humble-lms' ),
  'search_items'               => __( 'Search Items', 'humble-lms' ),
  'not_found'                  => __( 'Not Found', 'humble-lms' ),
  'no_terms'                   => __( 'No items', 'humble-lms' ),
  'items_list'                 => __( 'Items list', 'humble-lms' ),
  'items_list_navigation'      => __( 'Items list navigation', 'humble-lms' ),
);
$rewrite = array(
  'slug'                       => 'level',
  'with_front'                 => false,
  'hierarchical'               => false,
);
$args = array(
  'labels'                     => $labels,
  'hierarchical'               => true,
  'public'                     => true,
  'show_ui'                    => true,
  'show_admin_column'          => true,
  'show_in_nav_menus'          => false,
  'show_tagcloud'              => false,
  'show_in_rest'               => true,
  'rewrite'                    => $rewrite,
);

register_taxonomy(
  'humble_lms_course_level',
  array(
    'humble_lms_track',
    'humble_lms_course',
    'humble_lms_lesson',
    'humble_lms_quiz',
    'humble_lms_question',
  ), $args
);
