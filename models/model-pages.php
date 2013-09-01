<?php

/**
 * The Pages - access WordPress wp_post items
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ModelPages') ) :
	/**
	 * The Pages model provides interface to the WordPress pages (specifically to post_type "page")
	 *
	 * @since 1.0
	 */
	class MOK_ModelPages extends MOK_ModelBlog {

		/**
		 * Setup the pages model
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Limit the Model functions to post-type
			$this->posttype = 'page';

			parent::setup();
		}

	};


endif;
