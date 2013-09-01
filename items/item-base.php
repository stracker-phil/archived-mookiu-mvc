<?php

/**
 * Mookiu MVC base class for all active record items
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_Item') ) :
	/**
	 * Base class for all Mookiu MVC models.
	 * Notice that the MOK_Item does not extend the MOK_Component class!
	 * Reason for this is, that the MOK_Component is spezialized for singleton usage
	 * However, there need to be many Items of the same type possible
	 *
	 * @since 1.0
	 */
	class MOK_Item extends MOK_Base {

		/**
		 * The ID (primary key) of the current item.
		 * This should stay private, that's why it is not declared in the $property array
		 *
		 * @var scalar
		 * @since 1.0
		 */
		private $_id;


		/**
		 * Item values are stored in this array.
		 * @see popuplate(), get(), set()
		 *
		 * @var array
		 * @since 1.0
		 */
		private $_values;


		/**
		 * Item meta data, which are stored in a related meta-table.
		 *
		 * @var array
		 * @since 1.0
		 */
		private $_meta;


		/**
		 * A list of meta-fields that were changed after they were fetched from the database.
		 *
		 * @var array
		 * @since 1.0
		 */
		private $_meta_changed;


		/**
		 * Defines the model, whichis associated with this item
		 * Needs to be protected, so it can be overridden in the child class
		 *
		 * @var string
		 * @since 1.0
		 */
		protected $_model = '';


		/**
		 * Initialize the instance
		 *
		 * @since 1.0
		 */
		protected function setup() {
			$this->properties[] = 'is_dirty';
			$this->is_dirty = false;
		}


		/**
		 * Delete current record from database
		 *
		 * @since 1.0
		 * @return boolean Returns TRUE when the item was deleted
		 */
		public function delete() {
			$this->model()->delete( $this->_id );
		}


		/**
		 * Save current record to database.
		 * The record is only saved when it is in a valid state (@see $this->validate())
		 *
		 * @since 1.0
		 * @return boolean Returns TRUE when the record was saved
		 */
		public function save() {
			if ( $this->validate( $this->_values ) ) {
				// Save the item when it is dirty
				if ( $this->is_dirty ) {
					$id = $this->model()->save( $this->_id, $this->_values );
					if ( $id != $this->_id ) {
						$this->add_error( 'There was an error when saving the item' );
						return false;
					}
					$this->is_dirty = false;
				}
				// Save metadata when they are dirty
				if ( count( $this->_meta_changed ) > 0 ) {
					$this->save_metadata();
				}
				return true;
			} else {
				$this->add_error( 'Validation failed. Cannot save the item' );
				return false;
			}
		}


		/**
		 * Validate the state of the Item
		 * i.e. make sure all fields have correct values
		 *
		 * @since 1.0
		 *
		 * @param array $data The data container to validate.
		 *    When this is NULL then the values in $this->_values are validated.
		 * @return boolean Returns TRUE when the Item is in valid state
		 */
		public function validate( $data=null ) {
			return true;
		}


		/**
		 * Return the primary key of current record
		 *
		 * @since 1.0
		 * @return scalar The Primary-Key of the current record
		 */
		public function get_id() {
			return $this->_id;
		}


		/**
		 * Creates a new Item object with same details as the current record
		 * The new item will get a new primary key, but all other details will be exact replica of the current record
		 *
		 * @since 1.0
		 *
		 * @return MOK_Item The Item of the new item is returned (or FALSE on error)
		 */
		public function duplicate() {
			$data = $this->_values;
			if ( $this->validate( $data ) ) {
				// Copy the item
				$id = $this->model()->save( -1, $data );
				if ( empty( $id ) ) {
					$this->add_error( 'There was an error when saving the new item' );
					return false;
				}
				$item = $this->model()->load( $id );

				// Copy the meta data
				foreach ( $this->_meta as $key=>$val )
					$item->set_meta( $key, $val );
				$item->save_metadata();
				return $item;
			} else {
				$this->add_error( 'Validation failed. Cannot save the item' );
				return false;
			}
			return false;
		}


		/**
		 * Returns the model associated with the Item
		 *
		 * @since 1.0
		 *
		 * @return MOK_Model The model instance associated with this Item
		 */
		public function model() {
			if ( strlen( $this->_model ) == 0 )
				throw new MOK_Exception( 'The model-property needs to be defined!' );
			else
				return mok_get_model( $this->_model );
		}


		/**
		 * Returns the value of the specified database field.
		 *
		 * @since 1.0
		 * @param string $field The name of the field
		 * @return string Value of the field
		 */
		public function get( $field ) {
			$schema = $this->model()->get_schema();
			if ( array_key_exists( $field, $schema->fields ) ) {
 				if ( isset( $this->_values[$field] ) )
					return $this->_values[$field];
				else
					return $schema->fields[$field];
			}
			return null;
		}


		/**
		 * Set the value of the specified database field. The change is not automatically saved to database!
		 *
		 * @since 1.0
		 * @param string $field The name of the field
		 * @param string $value The new value of the field
		 */
		public function set( $field, $value ) {
			$schema = $this->model()->get_schema();
			if ( array_key_exists( $field, $schema->fields ) ) {
				$this->_values[$field] = $value;
				$this->is_dirty = true;
			}
		}


		/**
		 * Returns the value of the specified meta-field.
		 *
		 * @since 1.0
		 * @param string $field The name of the meta-field
		 * @return string Value of the meta-field
		 */
		public function get_meta( $field ) {
			$this->get_metadata();
			if ( isset( $this->_meta[$field] ) )
				return $this->_meta[$field];
			return null;
		}


		/**
		 * Returns true, when the current item has the defined meta-data field.
		 *
		 * @since 1.0
		 * @param string $field The name of the meta-field
		 * @return boolean True means that the meta-field exists
		 */
		public function has_meta( $field ) {
			$this->get_metadata();
			return isset( $this->_meta[$field] );
		}


		/**
		 * Set the value of the specified meta-field. The change is not automatically saved to database!
		 *
		 * @since 1.0
		 * @param string $field The name of the meta-field
		 * @param string $value The new value of the field
		 */
		public function set_meta( $field, $value ) {
			$this->_meta[$field] = $value;
			$this->_meta_changed[$field] = 'dirty';
		}


		/**
		 * Removes the specified meta field from the item.
		 *
		 * @since 1.0
		 * @param string $field The name of the meta-field
		 */
		public function clear_meta( $field ) {
			$this->_meta_changed[$field] = 'clear';
		}


		/**
		 * Populates the current record with the specified attribute values.
		 *
		 * @since 1.0
		 *
		 * @param array/object $attributes An array containing the data which should be stored in the Item.
		 */
		public function populate( $attributes ) {
			if ( is_object( $attributes) ) $attributes = (array) $attributes;
			if ( ! is_array( $attributes ) )
				throw new MOK_Exception( 'Invalid argument passed to the populate() method. Array or Object expected.' );

			$data = array();
			$schema = $this->model()->get_schema();

			foreach ( $schema->fields as $field=>$default ) {
				$value = array_key_exists( $field, $attributes ) ? $attributes[$field] : $default;
				$data[$field] = $value;
			}

			if ( $this->validate( $data) ) {
				$this->_values = $data;
				$this->_id = $data[$schema->primary];
				$this->is_dirty = false;

				return true;
			} else {
				$this->add_error( 'Validation failed, object was not initialized' );
				return false;
			}
		}


		/**
		 * Reads the metadata from the meta_table and stores it in the $_meta property
		 *
		 * @since 1.0
		 */
		protected function get_metadata() {
			$values = $this->model()->get_meta_for( $this->_id );

			foreach ( $values as $key=>$val ) {
				if ( ! isset( $this->_meta_changed[$key] ) )
					$this->_meta[$key] = $val;
			}
		}


		/**
		 * Saves the changed meta-fields to the database
		 *
		 * @since 1.0
		 * @param  boolean $only_changed Defines, if only the items should be saved that were changed
		 *    via set_meta() or all meta-fields.
		 */
		protected function save_metadata( $only_changed=true ) {
			$fields = $only_changed ? array_keys( $this->_meta_changed ) : array_keys( $this->_meta );

			foreach ( $fields as $field ) {
				if ( isset( $this->_meta_changed[$field] ) And $this->_meta_changed[$field] === 'clear' ) {
					// Meta field is marked to be cleared (= deleted)
					$this->model()->clear_meta_for( $this->_id, $field );
					unset( $this->_meta[$field] );
				} else {
					// Overwrite the meta value in the database
					$this->model()->set_meta_for( $this->_id, $field, $this->_meta[$field] );
				}
			}

			// Reset the "dirty"-marker
			$this->_meta_changed = array();
		}


		/**
		 * Returns the real class-name of the Item
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the Item
		 * @return string Class-Name of the Item (or NULL if Item does not exist)
		 */
		private static function get_activerec_classname( $name ) {
			$core_name = 'MOK_Item' . $name;
			$ext_name = 'MOK_I' . $name;

			if ( class_exists($ext_name) ) return $ext_name;
			elseif ( class_exists($core_name) ) return $core_name;
			return null;
		}


		/**
		 * Returns a new instance of the requested Item type
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the Item
		 * @return MOK_Item The new instance
		 */
		public static function new_instance( $name ) {
			$classname = self::get_activerec_classname( $name );

			if ( ! class_exists($classname) )
				throw new MOK_Exception(__FUNCTION__ . ': The Item class "' . $name . '" was not found');

			return new $classname();
		}

	};


endif;
