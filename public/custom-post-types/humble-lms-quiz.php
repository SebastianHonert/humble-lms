<?php

$labels = array(
  'name'                  => _x( 'Quizzes', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Quiz', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Quizzes', 'humble_lms' ),
  'name_admin_bar'        => __( 'Quizzes', 'humble_lms' ),
  'archives'              => __( 'Quiz Archives', 'humble_lms' ),
  'attributes'            => __( 'Quiz Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Quiz:', 'humble_lms' ),
  'all_items'             => __( 'All Quizzes', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Quiz', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Quiz', 'humble_lms' ),
  'edit_item'             => __( 'Edit Quiz', 'humble_lms' ),
  'update_item'           => __( 'Update Quiz', 'humble_lms' ),
  'view_item'             => __( 'View Quiz', 'humble_lms' ),
  'view_items'            => __( 'View Quizzes', 'humble_lms' ),
  'search_items'          => __( 'Search Quiz', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into quiz', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this quiz', 'humble_lms' ),
  'items_list'            => __( 'Quizzes list', 'humble_lms' ),
  'items_list_navigation' => __( 'Quizzes list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter quizzes list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('quizzes', 'humble-lms'),
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Quiz', 'humble_lms' ),
  'description'           => __( 'Quiz', 'humble_lms' ),
  'labels'                => $labels,
  'supports'              => array( 'title', 'revisions'),
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

register_post_type( 'humble_lms_quiz', $args );

// Quiz meta boxes

function humble_lms_quiz_add_meta_boxes()
{
  add_meta_box( 'humble_lms_quiz_questions_mb', __('Questions in this quiz', 'humble-lms'), 'humble_lms_quiz_questions_mb', 'humble_lms_quiz', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_quiz_add_meta_boxes' );

// Meta box questions
function humble_lms_quiz_questions_mb() {
  global $post;

  $questions = get_post_meta( $post->ID, 'humble_lms_quiz_questions', false );

  echo 'Coming soon...';
}
