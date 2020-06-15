<?php

get_header();

if (have_posts()):
  
  while (have_posts()): the_post();

    echo '<section id="humble-lms-course-content">';

      do_action( 'humble_lms_before_course_thumbnail' );

      $show_featured_image = get_post_meta( $post->ID, 'humble_lms_course_show_featured_image', true );

      if( $show_featured_image === '1' )
        the_post_thumbnail('post-thumbnail', ['class' => 'humble-lms-featured-image', 'title' => 'Featured image']);
      
      do_action( 'humble_lms_before_course_title' );
      
      echo '<h1 class="humble-lms-course-single-title">' . get_the_title() . '</h1>';

      the_content();

      do_action( 'humble_lms_after_course_content' );

    echo '</section>';

    echo do_shortcode('[humble_lms_paypal_buttons_single_item]');

    echo do_shortcode('[humble_lms_syllabus]');

  endwhile;

endif;

get_footer();

// Compare prices on server – cancel transaction if different
$post_id = get_the_ID();
$is_for_sale = get_post_meta( $post_id, 'humble_lms_is_for_sale', true );

echo $is_for_sale;

if( (int)$is_for_sale !== 1 ) {
  echo 'NOT FOR SALE';
}

$price_frontend = sanitize_text_field( 23.79 );
$price_backend = Humble_LMS_Content_Manager::get_price( $post_id, true );

if( $price_frontend !== $price_backend ) {
  echo 'PRICES DO NOT MATCH';
}
