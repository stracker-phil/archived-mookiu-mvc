<?php

/**
 * The Post - access WordPress wp_post items
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ModelBlog') ) :
	/**
	 * The Post model provides interface to the WordPress posts.
	 * This are all items in the wp_posts table, regardless of post_type...
	 *
	 * @since 1.0
	 */
	class MOK_ModelBlog extends MOK_Model {

		/**
		 * Specifies which post-type is accessed from the wp_posts table
		 * (Empty value means, that all post-types are handled)
		 *
		 * @var string
		 * @since 1.0
		 */
		protected $posttype = '';


		/**
		 * Setup the blog model
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Specifies, which Item is returned by the model (item = active record)
			$this->itemname = 'post';

			// This defines which database table is queried
			$this->tablename = self::get_db_connection()->posts;

			// Link the posts table with the postmeta table to easily fetch metainformation for post-items
			$this->metatable( self::get_db_connection()->postmeta, 'post_id', 'meta_key', 'meta_value' );

			parent::setup();
		}


		/**
		 * Attach filters that are used by this class
		 *
		 * @since 1.0
		 */
		protected function setup_filters() {
			add_filter( 'mok_prepare_condition_' . $this->tablename,      array( $this, 'prepare_where' ) ); // Modify WHERE condition for SELECT (single, multi)
			add_filter( 'mok_prepare_args_' . $this->tablename,           array( $this, 'prepare_args' ) );  // Modify data-attributes (INSERT, UPDATE)
			add_filter( 'mok_prepare_condition_args_' . $this->tablename, array( $this, 'prepare_args' ) );  // Modify WHERE-attributes (UPDATE, DELETE)
		}


		/**
		 * Prepare the WHERE condition used by the SELECT queries
		 *
		 * @since 1.0
		 * @filter 'mok_prepare_load_wp_posts'
		 * @filter 'mok_prepare_find_wp_posts'
		 *
		 * @param string $condition The WHERE condition that is used in the SQL
		 * @return string The modfied WHERE condition
		 */
		public function prepare_where( $condition ) {
			if ( strlen( $this->posttype ) > 0 ) {
				$condition = trim( $condition );

				if ( strlen( $condition ) > 0 )
					$condition .= ' AND ';

				$condition .= $this->esc_field( 'post_type' ) . '=' . $this->esc( $this->posttype ) . ' ';
			}
			return $condition;
		}


		/**
		 * Prepare the WHERE attributes used by the DELETE queries
		 *
		 * @since 1.0
		 * @filter 'mok_prepare_delete_wp_posts'
		 * @filter 'mok_before_insert_wp_posts'
		 * @filter 'mok_before_update_wp_posts'
		 * @filter 'mok_prepare_save_wp_posts'
		 *
		 * @param array $attributes The attributes that are used to build the WHERE condition
		 * @return array The updated attributes array
		 */
		public function prepare_args( $attributes ) {
			if ( strlen( $this->posttype ) > 0 ) {
				if ( is_object( $attributes ) )
					$attributes->post_type = $this->posttype;
				elseif ( is_array( $attributes ) )
					$attributes['post_type'] = $this->posttype;
			}
			return $attributes;
		}


	};


endif;
