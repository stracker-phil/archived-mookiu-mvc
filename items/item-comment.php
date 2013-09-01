<?php

/**
 * Item for Comments (wp_comments items)
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ItemComment') ) :
	/**
	 * This is the Item class to handle WordPress comments
	 *
	 * @since 1.0
	 */
	class MOK_ItemComment extends MOK_Item {

		/**
		 * Initialize the new instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Defines, which model is associated with this item
			$this->_model = 'comments';

			parent::setup();
		}

	};


endif;
