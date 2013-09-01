<?php

/**
 * Item for Pages (wp_posts items)
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ItemPage') ) :
	/**
	 * This is the Item class to handle WordPress pages (specifially post-type "page")
	 *
	 * @since 1.0
	 */
	class MOK_ItemPage extends MOK_ItemBlog {

		/**
		 * Initialize the new instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Defines, which model is associated with this item
			$this->_model = 'pages';

			parent::setup();
		}

	};


endif;
