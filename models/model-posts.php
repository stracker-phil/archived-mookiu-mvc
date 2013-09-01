<?php

/**
 * The Post - access WordPress wp_post items
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ModelPosts') ) :
	/**
	 * The Posts model provides interface to the WordPress posts (specifically to post_type "post")
	 *
	 * @since 1.0
	 */
	class MOK_ModelPosts extends MOK_ModelBlog {

		/**
		 * Setup the posts model
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Limit the Model functions to post-type
			$this->posttype = 'post';

			parent::setup();
		}

	};


endif;
