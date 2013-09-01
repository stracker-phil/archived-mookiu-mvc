<?php

/**
 * Access to comments from wp_comments table
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ModelComments') ) :
	/**
	 * The Comments model provides interface to the WordPress comments.
	 *
	 * @since 1.0
	 */
	class MOK_ModelComments extends MOK_Model {

		/**
		 * Setup the comments model
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Specifies, which Item is returned by the model (item = active record)
			$this->itemname = 'comment';

			// This defines which database table is queried
			$this->tablename = self::get_db_connection()->comments;

			// Link the comment table with the commentmeta table to easily fetch metainformation for comments
			$this->metatable( self::get_db_connection()->commentmeta, 'comment_id', 'meta_key', 'meta_value' );

			parent::setup();
		}

	};


endif;
