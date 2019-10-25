<?php
/*
   Plugin Name: WP & Woo & AJAX
   Version: 0.0.1
   Author: Gabriel Maldonado
   Author URI: https://tilcode.blog
   Description: Learning exercise using AJAX in WordPress and WooCommerce. This plugin adds a like button to the product page that is updated without page reload.
   Text Domain: gma-woo-ajax-learning
   License: GPLv3
*/

// Get out if direct access.
if (! defined( 'ABSPATH' ) ) {
   	exit;
}

// Display Likes after the "add to cart" button.
add_action( 'woocommerce_after_add_to_cart_button', 'gma_add_content_after_addtocart_button' );
// Enqueue frontend scripts
add_action( 'wp_enqueue_scripts', 'gma_frontend_scripts' );
// Ajax handler for custom AJAX endpoints wp_ajax_{action} as well as wp_ajax_nopriv_{action} for non-logged users. More: https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)
// TODO: Add a back-end setting so we can select to allow or disallow non-logged users
add_action( 'wp_ajax_gma_add_like', 'gma_add_like' );
add_action( 'wp_ajax_nopriv_gma_add_like', 'gma_add_like' );

/**
* If option does not exist in the DB then create it, empty() checks for both empty or null.
*/
if ( !empty( $gma_likes ) ) {
	$gma_likes = get_option( 'gma_likes' );
} else {
	add_option( 'gma_likes', 0 );
	$gma_likes = 0;
}

/**
* Enqueues main JS scripts and AJAX via wp_localize_script()
*/
function gma_frontend_scripts(){

	// Enqueue main JS script
  	wp_enqueue_script( 
  		'gma-frontend-js', 
  		plugins_url( 'gma-main.js', __FILE__ ),
  		['jquery'],
  		time(), // So we're getting the last file version
  		true 
  	);

  	/**
  	* Passes data between JS and PHP via localize scripts
  	* 
  	* ajax_url: Locates the correct path to admin-ajax.php
  	* action: The method that contains the basic functionality
  	* gma_total_likes: Get/Set total likes from DB options
  	* nonce: Named after the specific action we're using it for, so is easier to track later
  	*
  	*/
  	wp_localize_script(
  		'gma-frontend-js',
  		'gma_globals',
  		[
  			'ajax_url'			=> admin_url( 'admin-ajax.php' ),
  			'action'			=> 'gma_add_like',
  			'gma_total_likes'	=> get_option( 'gma_likes' ),
  			'nonce'				=> wp_create_nonce( 'gma_likes_nonce' )
  		]
  	);
}

/**
* Front-end HTML output. Contains the class we'll be targetting via jQuery
*/
function gma_add_content_after_addtocart_button() {

        echo '<div>Please spam the "Like Site" button:</div>';
        echo '<p><a href="#like" class="btn gma-like">Like this Site</a> <span class="gma-count"></span> Likes</p>';
}

/**
* Main method to add +1 like when the button is clicked
*/
function gma_add_like(){
	
	// If the nonce does not match, we'll get a 403 forbidden
	check_ajax_referer( 'gma_likes_nonce' );

	$gma_likes = intval( get_option( 'gma_likes' ) );
	$new_gma_likes = $gma_likes +1;
	$success = update_option( 'gma_likes', $new_gma_likes );

	// If the response is successful (data is saved via update_option() ), then update the variable and kick back the data
	if( true == $success ) {
		$response['gma_total_likes'] = $new_gma_likes;
		$response['type'] = 'success';
	}

	$response = json_encode( $response );
	echo $response;
	die();

}

// TODO: Add a reset likes method
