<?php

/**
 * Mookiu MVC base class for all model
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_Model') ) :
	/**
	 * Base class for all Mookiu MVC model.
	 * A collection is the data-provider which handles the ActiveRecord items
	 *
	 * For example is the collection responsible for finding an ActiveRecord item or to create a new item
	 * The ActiveRecord itself handles tasks that are specifically limited to the item it holds
	 *
	 * @since 1.0
	 */
	class MOK_Model extends MOK_Database {


		/**
		 * Use this const as first param for the save() method
		 */
		const NEW_ITEM = -1;


		/**
		 * The handle to the DB connection
		 *
		 * @var object
		 * @since 1.0
		 */
		private static $db_connection = null;


		/**
		 * NEEDS TO BE OVERRIDDEN
		 * Defines the name of the Item-type which is returned by this Model (item = active record)
		 *
		 * @var string
		 * @since 1.0
		 */
		protected $itemname = '';


		/**
		 * Initialize the component
		 *
		 * @since 1.0
		 */
		protected function setup() {
			$this->get_schema();
		}


		/**
		 * Delete item with the specified ID
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the record
		 * @return integer The number of deleted records
		 */
		public function delete( $id ) {
			// First filter the ID, allowing the user to set it to 0/false
			$id = apply_filters( 'mok_delete_' . $this->tablename, $id );
			if ( empty( $id ) ) return;

			#do_action( 'mok_before_delete_' . $this->tablename, $id );

			// Prepare the DELETE condition
			$args = array();
			$args[$this->primaryfield()] = $id;
			$args = apply_filters( 'mok_before_args_' . $this->tablename, $args, $id );

			// Delete the item
			$num = $this->db_delete( $this->tablename, $args );

			#if ( $num > 0 )
			#	do_action( 'mok_after_delete_' . $this->tablename, $id );

			return $num;
		}


		/**
		 * Return an ActiveRecord item for the specified item
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the record
		 * @return MOK_Item A single ActiveRecord instance
		 */
		public function load( $id ) {
			// First filter the ID, allowing the user to set it to 0/false
			$id = apply_filters( 'mok_prepare_id_' . $this->tablename, $id, 'load' );
			if ( empty( $id ) ) return;

			$item = $this->get_new();
			#$item = apply_filters( 'mok_before_load_' . $this->tablename, $item, $id );

			// Prepare the SELECT condition
			$condition = $this->cond_primary( $id );
			$condition = apply_filters( 'mok_prepare_condition_' . $this->tablename, $condition, $id );

			// Load data and populate the item
			$sql = 'SELECT * FROM ' . $this->tablename() . ' WHERE ' . $condition;
			$data = $this->db_get_row( $sql );
			if ( ! is_object($data) ) return false;

			$item->populate( $data );

			#$item = apply_filters( 'mok_after_load_' . $this->tablename, $item, $id );
			return $item;
		}


		/**
		 * Inserts or updates the provided attributes for the specified record.
		 * Response is the ID (primary key) of the modified or inserted database row.
		 *
		 * For UPDATE statements the reponse should be identical to the $id parameter
		 * For INSERT statements the response is the primary key of the new item
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the record
		 * @param array $attributes Array containing all fields to update (array keys are the field names)
		 * @return scalar Returns the ID of the inserted/updated DB item
		 */
		public function save( $id, $attributes ) {
			$response = false;
			if ( is_object( $attributes ) ) $attributes = (array) $attributes;
			if ( ! is_array( $attributes ) )
				throw new MOK_Exception( 'The attributes provided are invalid. They should be an array or object.' );

			// First filter the ID, allowing the user to set it to 0/false
			$id = apply_filters( 'mok_prepare_id_' . $this->tablename, $id, 'save' );
			if ( empty( $id ) ) return;

			// Remove the primary key from the $attributes array - the primary key cannot be updated
			unset( $attributes[$this->primaryfield()] );

			// Find the type: INSERT or UPDATE
			$insert = self::NEW_ITEM == $id;
			if ( ! $insert ) {
				// Prepare the SELECT condition
				$condition = $this->cond_primary( $id );
				$condition = apply_filters( 'mok_prepare_condition_' . $this->tablename, $condition, $id );
				$sql = 'SELECT COUNT(1) FROM ' . $this->tablename() . ' WHERE ' . $condition;
				$existing = $this->db_get_var( $sql );
				// The item to modify does not exist
				if ( 0 == $existing ) return;
			}

			if ( $insert )
				$task = 'insert';
			else
				$task = 'update';

			// Allow user to validate or modify the attributes
			$attributes = apply_filters( 'mok_prepare_args_' . $this->tablename, $attributes, $id, $task );
			if ( ! is_array( $attributes ) ) return 0;

			#do_action( 'mok_before_save_' . $this->tablename, $id, $attributes, $task );

			if ( $insert ) {
				$num = $this->db_insert( $this->tablename, $attributes );
				$new_id = $this->get_db_connection()->insert_id;
			} else {
				// Prepare the UPDATE condition
				$args = array();
				$args[$this->primaryfield()] = $id;
				$args = apply_filters( 'mok_prepare_condition_args_' . $this->tablename, $args, $id, $task );

				$num = $this->db_update( $this->tablename, $attributes, $args );
			}

			if ( $num > 0 ) {
				if ( $insert )
					$id = $new_id;

				#do_action( 'mok_after_save_' . $this->tablename, $id, $attributes, $task );
			}

			return $id;
		}


		/**
		 * A shortcut function to the save() method which uses NEW_ITEM const as first param
		 *
		 * @since 1.0
		 *
		 * @param array $attributes Array containing all fields to update (array keys are the field names)
		 * @return scalar Returns the ID of the inserted/updated DB item
		 */
		public function add( $attributes ) {
			return $this->save ( self::NEW_ITEM, $attributes );
		}


		/**
		 * Returns an array of all ActiveRecord items that match the specified condition
		 * The condition is a string containting a SQL WHERE condition. This condition may contain placeholders
		 * like "post_type=:type". The placeholder must be defined in the $params array, like "array('type'=>'page')"
		 *
		 * @since 1.0
		 *
		 * @param string $condition SQL WHERE condition
		 * @param array $params Optional array containing placeholders to complete into the condition
		 * @return array(MOK_Item) An Array of all matching ActiveRecords
		 */
		public function find( $condition='1=1', array $params=array() ) {
			$items = array();

			// Prepare the filter condition
			$condition = apply_filters( 'mok_prepare_condition_' . $this->tablename, $condition, $params );

			// Load data and populate the item
			$sql = 'SELECT * FROM ' . $this->tablename() . ' WHERE ' . $condition;
			if ( ! empty( $params ) )
				$sql = $this->db_prepare( $sql, $params );
			$data = $this->db_get_results( $sql );

			// Create new item-instances for all results
			foreach ( $data as $row ) {
				$item = $this->get_new();
				$item->populate( $row );
				$items[] = $item;
			}

			#$items = apply_filters( 'mok_after_find_' . $this->tablename, $items, $condition, $params );
			return $items;
		}


		/**
		 * Returns a new/empty ActiveRecord item
		 *
		 * @since 1.0
		 *
		 * @return MOK_Item A new/empty ActiveRecord
		 */
		public function get_new() {
			if ( strlen( $this->itemname ) == 0 )
				throw new MOK_Exception( 'The item name is not defined, cannot create new object.' );
			else
				return mok_new_dbitem( $this->itemname );
		}


		/**
		 * Returns the real class-name of the model
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the model
		 * @return string Class-Name of the model (or NULL if model does not exist)
		 */
		private static function get_model_classname( $name ) {
			$core_name = 'MOK_Model' . $name;
			$ext_name = 'MOK_M' . $name;

			if ( class_exists($ext_name) ) return $ext_name;
			elseif ( class_exists($core_name) ) return $core_name;
			return null;
		}


		/**
		 * Checks if the specified model exists
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the model
		 * @return boolean
		 */
		public static function has_model( $name ) {
			$classname = self::get_model_classname( $name );

			if ( is_string($classname) And class_exists($classname) ) return true;
			return false;
		}


		/**
		 * Returns the singleton instance of the specified model
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the model
		 * @return MOK_Model The instance
		 */
		public static function get_model( $name ) {
			$classname = self::get_model_classname( $name );

			if ( ! class_exists($classname) )
				throw new MOK_Exception(__FUNCTION__ . ': The model for "' . $name . '" was not found');

			return self::instance_of($classname);
		}

	};


endif;
