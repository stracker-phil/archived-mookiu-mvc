<?php

/**
 * Item for Posts (wp_posts items)
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ItemPost') ) :
	/**
	 * This is the Item class to handle WordPress posts (specifially post-type "post")
	 *
	 * @since 1.0
	 */
	class MOK_ItemPost extends MOK_ItemBlog {

		/**
		 * Initialize the new instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Defines, which model is associated with this item
			$this->_model = 'posts';

			parent::setup();
		}

	};


endif;
