<?php

/**
 * Item for Users (wp_users items)
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ItemUser') ) :
	/**
	 * This is the Item class to handle WordPress users
	 *
	 * @since 1.0
	 */
	class MOK_ItemUser extends MOK_Item {

		/**
		 * Initialize the new instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Defines, which model is associated with this item
			$this->_model = 'users';

			parent::setup();
		}

	};


endif;
