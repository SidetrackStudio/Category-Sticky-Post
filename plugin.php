<?php
/*
Plugin Name: Category Sticky Post
Plugin URI: http://tommcfarlin.com/category-sticky-post/
Description: Mark a post to be placed at the top of a specified category archive. It's sticky posts specifically for categories.
Version: 1.2.1
Author: Tom McFarlin
Author URI: http://tommcfarlin.com
Author Email: tom@tommcfarlin.com
License:

  Copyright 2012 - 2013 Tom McFarlin (tom@tommcfarlin.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Category_Sticky_Post {

	/*--------------------------------------------*
	 * Attributes
	 *--------------------------------------------*/

	 /** A static reference to track the single instance of this class. */
	 private static $instance = null;

	 /** A boolean used to track whether or not the sticky post has been marked */
	 private $is_sticky_post;

	/*--------------------------------------------*
	 * Singleton Implementation
	 *--------------------------------------------*/
	
	/**
	 * Method used to provide a single instance of this 
	 */
	public function getInstance() {
		
		if( null == self::$instance ) {
			self::$instance = new Category_Sticky_Post();
		} // end if
		
		return self::$instance;
		
	} // end getInstance
	
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	private function __construct() {

		// Initialize the count of the sticky post
		$this->is_sticky_post = false;

		// Category Meta Box actions
		add_action( 'add_meta_boxes', array( $this, 'add_category_sticky_post_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_category_sticky_post_data' ) );
		add_action( 'wp_ajax_is_category_sticky_post', array( $this, 'is_category_sticky_post' ) );
				
		// Filters for displaying the sticky category posts
		add_filter( 'the_posts', array( $this, 'reorder_category_posts' ) );
		add_filter( 'post_class', array( $this, 'set_category_sticky_class' ) );
		
		// Stylesheets
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_styles_and_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );

	} // end constructor
	
	/*---------------------------------------------*
	 * Action Functions
	 *---------------------------------------------*/

	/**
	 * Renders the meta box for allowing the user to select a category in which to stick a given post.
	 */
	public function add_category_sticky_post_meta_box() {
		
		add_meta_box(
			'post_is_sticky',
			__( 'Category Sticky', 'category-sticky-post' ),
			array( $this, 'category_sticky_post_display' ),
			'post',
			'side',
			'low'
		);
		
	} // end add_category_sticky_post_meta_box
	
	/**
	 * Renders the select box that allows users to choose the category into which to stick the 
	 * specified post.
	 *
	 * @param	object	$post	The post to be marked as sticky for the specified category.
	 */
	public function category_sticky_post_display( $post ) {
		
		// Set the nonce for security
		wp_nonce_field( plugin_basename( __FILE__ ), 'category_sticky_post_nonce' );

		// First, read all the categories
		$categories = get_categories();
	
		// Build the HTML that will display the select box
		$html = '<select id="category_sticky_post" name="category_sticky_post">';
			$html .= '<option value="0">' . __( 'Select a category...', 'category-sticky-post' ) . '</option>';
			foreach( $categories as $category ) {
				$html .= '<option value="' . $category->cat_ID . '" ' . selected( get_post_meta( $post->ID, 'category_sticky_post', true ), $category->cat_ID, false ) . ( $this->category_has_sticky_post( $category->cat_ID ) ? ' disabled ' : '' ) . '>';
					$html .= $category->cat_name;
				$html .= '</option>';	
			} // end foreach
		$html .= '</select>';
		
		echo $html;
		
	} // end category_sticky_post_display
	
	/**
	 * Set the custom post meta for marking a post as sticky.
	 *
	 * @param	int	$post_id	The ID of the post to which we're saving the post meta
	 */
	public function save_category_sticky_post_data( $post_id ) {
	
		if( isset( $_POST['category_sticky_post_nonce'] ) && isset( $_POST['post_type'] ) && $this->user_can_save( $post_id, 'category_sticky_post_nonce' ) ) {
		
			// Read the ID of the category to which we're going to stick this post
			$category_id = '';
			if( isset( $_POST['category_sticky_post'] ) ) {
				$category_id = esc_attr( $_POST['category_sticky_post'] );
			} // end if
	
			// If the value exists, delete it first. I don't want to write extra rows into the table.
			if ( 0 == count( get_post_meta( $post_id, 'category_sticky_post' ) ) ) {
				delete_post_meta( $post_id, 'category_sticky_post' );
			} // end if
	
			// Update it for this post.
			update_post_meta( $post_id, 'category_sticky_post', $category_id );
				
		} // end if
	
	} // end save_category_sticky_post_data
	
	/**
	 * Register and enqueue the stylesheets and JavaScript dependencies for styling the sticky post.
	 */
	public function add_admin_styles_and_scripts() {
	
		// Only register the stylesheet for the post page
		$screen = get_current_screen();
		if( 'post' == $screen->id ) { 
	
			// admin stylesheet
			wp_enqueue_style( 'category-sticky-post', plugins_url( '/category-sticky-post/css/admin.css' ) );

			// post editor javascript
			wp_enqueue_script( 'category-sticky-post-editor', plugins_url( '/category-sticky-post/js/editor.min.js' ), array( 'jquery' ) );
		
		// And only register the JavaScript for the post listing page
		} elseif( 'edit-post' == $screen->id ) {
		
			wp_enqueue_script( 'category-sticky-post', plugins_url( '/category-sticky-post/js/admin.min.js' ), array( 'jquery' ) );
		
		} // end if
		
	} // end add_admin_styles_and_scripts
	
	/**
	 * Register and enqueue the stylesheets for styling the sticky post, but only do so on an archives page.
	 */
	public function add_styles() {

		if( is_archive() ) {
			wp_enqueue_style( 'category-sticky-post', plugins_url( '/category-sticky-post/css/plugin.css' ) );			
		} // end if
		
	} // end add_styles
	
	/**
	 * Ajax callback function used to decide if the specified post ID is marked as a category
	 * sticky post.
	 *
	 * TODO:	Eventually, I want to do this all server side.
	 */
	public function is_category_sticky_post() {
	
		if( isset( $_GET['post_id'] ) ) {
		
			$post_id = trim ( $_GET['post_id'] );
			if( 0 == get_post_meta( $post_id, 'category_sticky_post', true ) ) {
				die( '0' );
			} else {
				die( _e( ' - Category Sticky Post', 'category-sticky-post' ) );
			} // end if/else
		
		} // end if
		
	} // end is_category_sticky_post
	
	/*---------------------------------------------*
	 * Filter Functions
	 *---------------------------------------------*/
	 
	 /**
	  * Adds a CSS class to make it easy to style the sticky post.
	  * 
	  * @param		array	$classes	The array of classes being applied to the given post
	  * @return		array				The updated array of classes for our posts
	  */
	 public function set_category_sticky_class( $classes ) {
	 
	 	// If we've not set the category sticky post...
	 	if( false == $this->is_sticky_post && $this->is_sticky_post() ) {
	 
		 	// ...append the class to the first post (or the first time this event is raised)
			$classes[] = 'category-sticky';
			
			// ...and indicate that we've set the sticky post
			$this->is_sticky_post = true;
		 
		} // end if
		 
		return $classes;
		 
	 } // end set_category_sticky_class
	 
	 /**
	  * Places the sticky post at the top of the list of posts for the category that is being displayed.
	  *
	  * @param	array	$posts	The lists of posts to be displayed for the given category
	  * @return	array			The updated list of posts with the sticky post set as the first titem
	  */
	 public function reorder_category_posts( $posts ) {

	 	// We only care to do this for the first page of the archives
	 	if( is_archive() && 0 == get_query_var( 'paged' ) && '' != get_query_var( 'cat' ) ) {
	 
		 	// Read the current category to find the sticky post
		 	$category = get_category( get_query_var( 'cat' ) );
		 	
		 	// Query for the ID of the post
		 	$sticky_query = new WP_Query(
		 		array(
			 		'fields'			=>	'ids',
			 		'post_type'			=>	'post',
			 		'posts_per_page'	=>	'1',
			 		'tax_query'			=> array(
			 			'terms'				=> 	null,
			 			'include_children'	=>	false
			 		),
			 		'meta_query'		=>	array(
			 			array(
				 			'key'		=>	'category_sticky_post',
				 			'value'		=>	$category->cat_ID,
				 		)
			 		)
		 		)
		 	);
		 	
		 	// If there's a post, then set the post ID
		 	$post_id = ( ! isset ( $sticky_query->posts[0] ) ) ? -1 : $sticky_query->posts[0];
		 	wp_reset_postdata();
		 	
		 	// If the query returns an actual post ID, then let's update the posts
		 	if( -1 < $post_id ) {

		 		// Store the sticky post in an array
			 	$new_posts = array( get_post( $post_id ) );

			 	// Look to see if the post exists in the current list of posts.
			 	foreach( $posts as $post_index => $post ) {
			 	
			 		// If so, then remove it so we don't duplicate its display
			 		if( $post_id == $posts[ $post_index ]->ID ) {
				 		unset( $posts[ $post_index ] );
			 		} // end if
				 	
			 	} // end foreach
			 	
			 	// Merge the existing array (with the sticky post first and the original posts second)
			 	$posts = array_merge( $new_posts, $posts );
			 	
		 	} // end if
		 	
	 	} // end if

	 	return $posts;
	 	
	 } // end reorder_category_posts
	 
	/*---------------------------------------------*
	 * Helper Functions
	 *---------------------------------------------*/
	
	/**
	 * Determines if the given category already has a sticky post.
	 * 
	 * @param	int		$category_id	The ID of the category to check
	 * @return	boolean					Whether or not the category has a sticky post
	 */
	private function category_has_sticky_post( $category_id ) {
	
		$has_sticky_post = false;
		
		$q = new WP_Query( 'meta_key=category_sticky_post&meta_value=' . $category_id );	
		$has_sticky_post = $q->have_posts();
		wp_reset_query();
		
		return $has_sticky_post;

	} // end category_has_sticky_post
	
	/**
	 * Determines whether or not the current post is a sticky post for the current category.
	 *
	 * @return	boolean	Whether or not the current post is a sticky post for the current category.
	 */
	private function is_sticky_post() {
		
		global $post;
		return get_query_var( 'cat' ) == get_post_meta( $post->ID, 'category_sticky_post', true );
		
	} // end is_sticky_post
	
	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	*/
	private function user_can_save( $post_id, $nonce ) {
		
	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );
	    
	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;
	 
	} // end user_can_save
	
} // end class

function Category_Sticky_Post() {
	Category_Sticky_Post::getInstance();
} // end Category_StickyPost
add_action( 'plugins_loaded', 'Category_Sticky_Post' );