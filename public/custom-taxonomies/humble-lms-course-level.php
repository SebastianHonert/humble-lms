<?php

$labels = array(
  'name'                       => _x( 'Levels of difficulty', 'Taxonomy General Name', 'humble_lms' ),
  'singular_name'              => _x( 'Level of difficulty', 'Taxonomy Singular Name', 'humble_lms' ),
  'menu_name'                  => __( 'Level of difficulty', 'humble_lms' ),
  'all_items'                  => __( 'All Items', 'humble_lms' ),
  'parent_item'                => __( 'Parent Item', 'humble_lms' ),
  'parent_item_colon'          => __( 'Parent Item:', 'humble_lms' ),
  'new_item_name'              => __( 'New Item Name', 'humble_lms' ),
  'add_new_item'               => __( 'Add New Item', 'humble_lms' ),
  'edit_item'                  => __( 'Edit Item', 'humble_lms' ),
  'update_item'                => __( 'Update Item', 'humble_lms' ),
  'view_item'                  => __( 'View Item', 'humble_lms' ),
  'separate_items_with_commas' => __( 'Separate items with commas', 'humble_lms' ),
  'add_or_remove_items'        => __( 'Add or remove items', 'humble_lms' ),
  'choose_from_most_used'      => __( 'Choose from the most used', 'humble_lms' ),
  'popular_items'              => __( 'Popular Items', 'humble_lms' ),
  'search_items'               => __( 'Search Items', 'humble_lms' ),
  'not_found'                  => __( 'Not Found', 'humble_lms' ),
  'no_terms'                   => __( 'No items', 'humble_lms' ),
  'items_list'                 => __( 'Items list', 'humble_lms' ),
  'items_list_navigation'      => __( 'Items list navigation', 'humble_lms' ),
);
$rewrite = array(
  'slug'                       => 'level',
  'with_front'                 => true,
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

register_taxonomy( 'humble_lms_course_level', array( 'humble_lms_course', 'humble_lms_lesson' ), $args );
