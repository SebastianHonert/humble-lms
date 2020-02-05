<?php

$labels = array(
  'name'                  => _x( 'Questions', 'Post Type General Name', 'humble-lms' ),
  'singular_name'         => _x( 'Question', 'Post Type Singular Name', 'humble-lms' ),
  'menu_name'             => __( 'Quiz Questions', 'humble-lms' ),
  'name_admin_bar'        => __( 'Questions', 'humble-lms' ),
  'archives'              => __( 'Question Archives', 'humble-lms' ),
  'attributes'            => __( 'Question Attributes', 'humble-lms' ),
  'parent_item_colon'     => __( 'Parent Question:', 'humble-lms' ),
  'all_items'             => __( 'All Questions', 'humble-lms' ),
  'add_new_item'          => __( 'Add New Question', 'humble-lms' ),
  'add_new'               => __( 'Add New', 'humble-lms' ),
  'new_item'              => __( 'New Question', 'humble-lms' ),
  'edit_item'             => __( 'Edit Question', 'humble-lms' ),
  'update_item'           => __( 'Update Question', 'humble-lms' ),
  'view_item'             => __( 'View Question', 'humble-lms' ),
  'view_items'            => __( 'View Questions', 'humble-lms' ),
  'search_items'          => __( 'Search Question', 'humble-lms' ),
  'not_found'             => __( 'Not found', 'humble-lms' ),
  'not_found_in_trash'    => __( 'Not found in Trash', 'humble-lms' ),
  'featured_image'        => __( 'Featured Image', 'humble-lms' ),
  'set_featured_image'    => __( 'Set featured image', 'humble-lms' ),
  'remove_featured_image' => __( 'Remove featured image', 'humble-lms' ),
  'use_featured_image'    => __( 'Use as featured image', 'humble-lms' ),
  'insert_into_item'      => __( 'Insert into question', 'humble-lms' ),
  'uploaded_to_this_item' => __( 'Uploaded to this question', 'humble-lms' ),
  'items_list'            => __( 'Questions list', 'humble-lms' ),
  'items_list_navigation' => __( 'Questions list navigation', 'humble-lms' ),
  'filter_items_list'     => __( 'Filter questions list', 'humble-lms' ),
);

$rewrite = array(
  'slug'                  => __('questions', 'humble-lms'),
  'with_front'            => false,
  'pages'                 => false,
  'feeds'                 => false,
);

$args = array(
  'label'                 => __( 'Question', 'humble-lms' ),
  'description'           => __( 'Question', 'humble-lms' ),
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
  add_meta_box( 'humble_lms_question_type_mb', __('Question type', 'humble-lms'), 'humble_lms_question_type_mb', 'humble_lms_question', 'normal', 'default' );
  add_meta_box( 'humble_lms_question_mb', __('Question', 'humble-lms'), 'humble_lms_question_mb', 'humble_lms_question', 'normal', 'default' );
  add_meta_box( 'humble_lms_answers_mb', __('Answers to this question', 'humble-lms'), 'humble_lms_answers_mb', 'humble_lms_question', 'normal', 'default' );
}

add_action( 'add_meta_boxes', 'humble_lms_question_add_meta_boxes' );

// Meta box question type
function humble_lms_question_type_mb() {
  global $post;

  $question_type = get_post_meta( $post->ID, 'humble_lms_question_type', false );
  $question_type = isset( $question_type[0] ) && ! empty( $question_type[0] ) ? $question_type[0] : 'multiple_choice'; 

  $selected_multiple_choice = $question_type === 'multiple_choice' ? 'selected' : '';

  echo '<select name="humble_lms_question_type" class="humble_lms_question_type">
    <option value="multiple_choice" ' . $selected_multiple_choice . '>Single / Multiple Choice</option>
  </select>';
}

// Meta box question
function humble_lms_question_mb() {
  global $post;

  wp_nonce_field('humble_lms_meta_nonce', 'humble_lms_meta_nonce');
  
  $question = get_post_meta( $post->ID, 'humble_lms_question', true );

  echo '<input name="humble_lms_question" class="widefat" value="' . $question . '">'; 
}

// Meta box answers
function humble_lms_answers_mb() {
  global $post;

  $question = get_post_meta( $post->ID, 'humble_lms_question', false );
  $question_type = get_post_meta( $post->ID, 'humble_lms_question_type', false );
  $answers = get_post_meta( $post->ID, 'humble_lms_question_answers', true );
  $answers = maybe_unserialize( $answers );

  $answers = ! isset( $answers ) || ! isset( $answers[0]['answer'] ) || empty( $answers[0]['answer'] ) ? array(['answer' => __('Please provide at least one answer.', 'humble-lms'), 'correct' => 0]) : $answers;

  ?>

  <p><?php _e('Select more than on correct answer for a <strong>multiple choice</strong> question, and only one correct answer for a <strong>single choice</strong> question.', 'humble-lms'); ?></p>

  <div class="humble-lms-answers">
    <?php foreach( $answers as $key => $answer ): ?>
      <?php $checked = isset( $answer['correct'] ) && $answer['correct'] === 1 ? 'checked' : ''; ?>
      <div class="humble-lms-answer">
        <input type="text" name="humble_lms_question_answers[<?php echo $key; ?>][answer]" data-key="<?php echo $key; ?>" class="humble-lms-answer-text widefat" value="<?php echo $answers[$key]['answer']; ?>">
        <div class="humble-lms-correct-answer-wrapper">
          <span>
            <input type="checkbox" name="humble_lms_question_answers[<?php echo $key; ?>][correct]" data-key="<?php echo $key; ?>" class="humble-lms-answer-correct" value="1" <?php echo $checked; ?>></span>
            <span><?php echo __('Correct answer', 'humble-lms'); ?>?</span>
        </div>
        <a class="button humble-lms-remove-answer"><?php _e('Delete answer', 'humble-lms'); ?></a>
      </div>
    <?php endforeach; ?>
  </div>

  <p><a class="button button-primary humble-lms-repeater" data-element=".humble-lms-answer" data-target=".humble-lms-answers">+ <?php _e('Add answer', 'humble-lms'); ?></a></p>

  <?php
}

// Save metabox data

function humble_lms_save_question_meta_boxes( $post_id, $post )
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
  
  if ( ( ! $post_id ) || get_post_type( $post_id ) !== 'humble_lms_question' ) {
    return false;
  }

  $allowed_question_types = array(
    'multiple_choice'
  );

  $question_meta['humble_lms_question_type'] = ! empty( $_POST['humble_lms_question_type'] ) && in_array( $_POST['humble_lms_question_type'], $allowed_question_types ) ? $_POST['humble_lms_question_type'] : 'multiple_choice';
  $question_meta['humble_lms_question'] = ! empty( $_POST['humble_lms_question'] ) ? esc_attr( $_POST['humble_lms_question'] ) : __('Please enter a question.', 'humble-lms');
  
  if( is_array( $_POST['humble_lms_question_answers'] ) ) {
    foreach( $_POST['humble_lms_question_answers'] as $key => $answer ) {
      if( empty( $answer['answer'] ) ) continue;

      $question_meta['humble_lms_question_answers'][$key]['answer'] = esc_attr( $answer['answer'] );
      $question_meta['humble_lms_question_answers'][$key]['correct'] = $answer['correct'] ? 1 : 0 ;
    }

    $question_meta['humble_lms_question_answers'] = serialize( $question_meta['humble_lms_question_answers'] );
  }

  if( ! empty( $question_meta ) && sizeOf( $question_meta ) > 0 )
  {
    foreach ($question_meta as $key => $value)
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

add_action('save_post', 'humble_lms_save_question_meta_boxes', 1, 2);
