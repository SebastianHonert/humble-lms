<?php

$labels = array(
  'name'                  => _x( 'Questions', 'Post Type General Name', 'humble_lms' ),
  'singular_name'         => _x( 'Question', 'Post Type Singular Name', 'humble_lms' ),
  'menu_name'             => __( 'Quiz Questions', 'humble_lms' ),
  'name_admin_bar'        => __( 'Questions', 'humble_lms' ),
  'archives'              => __( 'Question Archives', 'humble_lms' ),
  'attributes'            => __( 'Question Attributes', 'humble_lms' ),
  'parent_item_colon'     => __( 'Parent Question:', 'humble_lms' ),
  'all_items'             => __( 'All Questions', 'humble_lms' ),
  'add_new_item'          => __( 'Add New Question', 'humble_lms' ),
  'add_new'               => __( 'Add New', 'humble_lms' ),
  'new_item'              => __( 'New Question', 'humble_lms' ),
  'edit_item'             => __( 'Edit Question', 'humble_lms' ),
  'update_item'           => __( 'Update Question', 'humble_lms' ),
  'view_item'             => __( 'View Question', 'humble_lms' ),
  'view_items'            => __( 'View Questions', 'humble_lms' ),
  'search_items'          => __( 'Search Question', 'humble_lms' ),
  'not_found'             => __( 'Not found', 'humble_lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble_lms' ),
  'featured_image'        => __( 'Featured Image', 'humble_lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble_lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble_lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble_lms' ),
  'insert_into_item'      => __( 'Insert into question', 'humble_lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this question', 'humble_lms' ),
  'items_list'            => __( 'Questions list', 'humble_lms' ),
  'items_list_navigation' => __( 'Questions list navigation', 'humble_lms' ),
  'filter_items_list'     => __( 'Filter questions list', 'humble_lms' ),
);

$rewrite = array(
  'slug'                  => __('questions', 'humble-lms'),
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Question', 'humble_lms' ),
  'description'           => __( 'Question', 'humble_lms' ),
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

register_post_type( 'humble_lms_question', $args );

// Question meta boxes

function humble_lms_question_add_meta_boxes()
{
  add_meta_box( 'humble_lms_question_mb', __('Please select a question type.', 'humble-lms'), 'humble_lms_question_mb', 'humble_lms_question', 'normal', 'default' );
  add_meta_box( 'humble_lms_answers_mb', __('Answers to this question', 'humble-lms'), 'humble_lms_answers_mb', 'humble_lms_question', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_question_add_meta_boxes' );

// Meta box question
function humble_lms_question_mb() {
  global $post;

  $question = get_post_meta( $post->ID, 'humble_lms_question', false );
  $question_type = isset( $question[0]['type'] ) && ! empty( $question[0]['type'] ) ? $question[0]['type'] : 'single-choice'; 

  $selected_single_choice = $question_type === 'single_choice' ? 'selected' : '';
  $selected_multiple_choice = $question_type === 'multiple_choice' ? 'selected' : '';
  echo '<select name="humble-lms-question-type" class="humble-lms-question-type">
    <option value="single-choice" ' . $selected_single_choice . '>Single Choice</option>
    <option value="single-choice" ' . $selected_single_choice . '>Multiple Choice</option>
  </select>';
}

// Meta box answers
function humble_lms_answers_mb() {
  global $post;

  $question = get_post_meta( $post->ID, 'humble_lms_question', false );
  $answers = isset( $question[0]['answers'] ) && ! empty( $question[0]['answers'] ) ? $question[0]['answers'] : ['coming soon'];

  ?>

  <div class="humble-lms-answers">
    <div class="humble-lms-answer">
      <input type="text" class="humble-lms-answer-text widefat" value="">
      <a class="button humble-lms-remove-answer"><?php _e('Delete answer', 'humble-lms'); ?></a>
    </div>
  </div>

  <p><a class="button humble-lms-repeater" data-element=".humble-lms-answer:last" data-target=".humble-lms-answers">+ <?php _e('Add answer', 'humble-lms'); ?></a></p>

  <?php
}
