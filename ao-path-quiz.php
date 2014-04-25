<?php
/**
 * Plugin Name: Aaron's Path-Based Quiz Plugin
 * Description: Plugin for embedding path-based quizzes in posts.
 * Version: 1.0.0
 * Author: Aaron Olkin
 * License: MIT
 * 
 * Path Quiz Plugin
 * Copyright (C) 2014, Aaron Olkin
 * 
 */


wp_register_style( 'ao-path-quiz-style', plugins_url( 'quiz.css', __FILE__ ) );
wp_enqueue_style( 'ao-path-quiz-style' );
wp_enqueue_script( 'ao-path-quiz', plugins_url( 'quiz.js', __FILE__ ) );

add_shortcode('pathquiz', 'ao_path_quiz_processor');

$ao_path_quiz_id = 0;

function ao_path_quiz_processor( $attributes, $content = null ) {
  global $post;
  extract( shortcode_atts( array( 'url' => '' ), $attributes ) );
  
  $content = get_post_meta( $post->ID, '_ao_path_quiz_json', true );
  if ($content == "") { $content = "null"; }

  return '<div id="ao-path-quiz-' . $ao_path_quiz_id . '"></div><script>jQuery(function(){var ao_path_quiz = new AOPathQuiz("#ao-path-quiz-' . $ao_path_quiz_id . '",' . $content . ',"' . $url . '");});</script>';
  $ao_path_quiz_id++;
}



/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function ao_path_quiz_add_meta_box() {

  $screens = array( 'post', 'page' );

  foreach ( $screens as $screen ) {

    add_meta_box('ao_path_quiz_jsondata',
		 'Path Quiz JSON Data for Post',
		 'ao_path_quiz_meta_box_callback',
		 $screen,'advanced','low');
  }
}
add_action( 'add_meta_boxes', 'ao_path_quiz_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function ao_path_quiz_meta_box_callback( $post ) {

  // Add an nonce field so we can check for it later.
  wp_nonce_field( 'ao_path_quiz_meta_box', 'ao_path_quiz_meta_box_nonce' );

  /*
   * Use get_post_meta() to retrieve an existing value
   * from the database and use the value for the form.
   */
  $value = get_post_meta( $post->ID, '_ao_path_quiz_json', true );

  echo '<textarea id="ao_path_quiz_json_data" name="ao_path_quiz_json_data" value="' . esc_attr( $value ) . '" cols="80" rows="10">'.$value.'</textarea>';
  //  echo '<input type="submit" value="Save" />';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function ao_path_quiz_save_meta_box_data( $post_id ) {

  /*
   * We need to verify this came from our screen and with proper authorization,
   * because the save_post action can be triggered at other times.
   */

  // Check if our nonce is set.
  if ( ! isset( $_POST['ao_path_quiz_meta_box_nonce'] ) ) {
    return;
  }

  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $_POST['ao_path_quiz_meta_box_nonce'], 'ao_path_quiz_meta_box' ) ) {
    return;
  }

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  // Check the user's permissions.
  if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

    if ( ! current_user_can( 'edit_page', $post_id ) ) {
      return;
    }

  } else {

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
      return;
    }
  }

  /* OK, its safe for us to save the data now. */
  
  // Make sure that it is set.
  if ( ! isset( $_POST['ao_path_quiz_json_data'] ) ) {
    return;
  }

  // Sanitize user input.
  $my_data = sanitize_text_field( $_POST['ao_path_quiz_json_data'] );

  // Update the meta field in the database.
  update_post_meta( $post_id, '_ao_path_quiz_json', $my_data );
}
add_action( 'save_post', 'ao_path_quiz_save_meta_box_data' );