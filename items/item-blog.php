<?php

/**
 * Item for Posts (wp_posts items)
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ItemBlog') ) :
	/**
	 * This is the Item class to handle WordPress posts
	 * A post is any item from the wp_posts table, regardless of the actual post_type...
	 *
	 * @since 1.0
	 */
	class MOK_ItemBlog extends MOK_Item {

		/**
		 * Initialize the new instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Defines, which model is associated with this item
			$this->_model = 'blog';

			parent::setup();
		}

	};


endif;
