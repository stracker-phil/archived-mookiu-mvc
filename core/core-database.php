<?php

/**
 * Mookiu MVC database layer
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_Database') ) :
	/**
	 * Database "abstraction" layer for Mookiu MVC.
	 * This is the only component the should directly interact with the database
	 *
	 * Components that need DB interaction should either call the functions
	 * or extend this component. All DB functions are static, so both solutions are very similar
	 *
	 * @since 1.0
	 */
	class MOK_Database extends MOK_Component {

		/**
		 * The handle to the DB connection
		 *
		 * @var object
		 * @since 1.0
		 */
		private static $_db_connection = null;


		/**
		 * Schema information on the table, mainly the available columns.
		 * This object is private. {@see get_schema()}
		 *
		 * @since 1.0
		 */
		private $_schema = null;


		/**
		 * Specifies in which table stores the item meta information
		 * The meta_info details are set via {@see metatable()}
		 *
		 * @var array
		 * @since 1.0
		 */
		private $_meta_info = array(
			'has_meta' => false,  	// Flag to quickly check if meta-information was provided
			'table' => '',   		// Which tables holds the meta information?
			'ref_col' => '', 		// Which column links the meta information to the item?
			'key_col' => '', 		// Which column defines the meta field-names?
			'val_col' => '', 		// Which column holds the meta-values?
		);


		/**
		 * NEEDS TO BE OVERRIDDEN
		 * Specifies in which table the items are stored in
		 *
		 * @var string
		 * @since 1.0
		 */
		protected $tablename = '';


		/**
		 * Return handle to the DB connection
		 *
		 * @since 1.0
		 *
		 * @return Object Database connection object
		 */
		public static function get_db_connection() {
			if (self::$_db_connection === null) {
				global $wpdb;
				self::$_db_connection = $wpdb;
			}
			return self::$_db_connection;
		}


		/**
		 *  Fields with a "db_" prefix are searched in self::$_db_connection
		 *
		 * @since 1.0
		 */
		public function __isset( $key ) {
			if ( strlen( $key ) > 3 And substr( $key, 0, 3 ) == 'db_' ) {
				$new_key = substr( $key, 3 );
				$db_obj = self::get_db_connection();
				return isset( $db_obj->$new_key );
			} else {
				return parent::__isset( $key );
			}
		}


		/**
		 * Fields with a "db_" prefix are searched in self::$_db_connection
		 *
		 * @since 1.0
		 */
		public function __get( $key ) {
			if ( strlen( $key ) > 3 And substr( $key, 0, 3 ) == 'db_' ) {
				$new_key = substr( $key, 3 );
				$db_obj = self::get_db_connection();
				if ( isset( $db_obj->$new_key ) ) {
					return $db_obj->$new_key;
				} else {
					throw new MOK_Exception( 'Cannot return value of undeclared DB property: "' . $new_key . '"' );
				}
			} else {
				return parent::__get( $key );
			}
		}


		/**
		 * Fields with a "db_" prefix are searched in self::$_db_connection
		 *
		 * @since 1.0
		 */
		public function __set( $key, $value ) {
			if ( strlen( $key ) > 3 And substr( $key, 0, 3 ) == 'db_' ) {
				$new_key = substr( $key, 3 );
				throw new MOK_Exception( 'Cannot modify DB properties: "' . $new_key . '"' );
			} else {
				return parent::__set( $key, $value );
			}
		}


		/**
		 * Simply maps the instance "__call" method to the static "__callStatic" method
		 *
		 * @since 1.0
		 */
		public function __call( $name = '', $args = array() ) {
			return self::__callStatic( $name, $args );
		}


		/**
		 * Methods with a "db_" prefix are executed against self::$_db_connection
		 *
		 * @since 1.0
		 */
		public static function __callStatic( $name = '', $args = array() ) {
			if ( strlen( $name ) > 3 And substr( $name, 0, 3 ) == 'db_' ) {
				$methodname = substr( $name, 3 );
				if ( method_exists ( self::get_db_connection(), $methodname ) ) {
					return call_user_func_array( array(self::get_db_connection(), $methodname), $args );
				}
			}
			throw new MOK_Exception( 'The requested method does not exist or is not public: ' . $name );
		}


		/**
		 * Returns the table name where the ActiveRecords are stored/read from
		 *
		 * @since 1.0
		 *
		 * @return string Table-name
		 */
		public function tablename() {
			return $this->esc_field( $this->tablename );
		}


		/**
		 * SQL escapes a fieldname/tablename (by wrapping it in `)
		 *
		 * @since 1.0
		 *
		 * @param string $field The field name
		 * @return string The wrapped fieldname
		 */
		public function esc_field( $field ) {
			return '`' . $field . '`';
		}


		/**
		 * Returns the name of the primary key column
		 *
		 * @since 1.0
		 * @return string The name of the primary column
		 */
		public function primaryfield() {
			return $this->get_schema()->primary;
		}


		/**
		 * SQL expression "primary=value"
		 *
		 * @since 1.0
		 *
		 * @param scalar $id Primary key value
		 * @return string The SQL expression
		 */
		public function cond_primary( $id ) {
			return ' ' . $this->esc_field( $this->primaryfield() ) . '=' . $this->esc( $id ) . ' ';
		}


		/**
		 * SQL condition " WHERE primary=value "
		 *
		 * @since 1.0
		 *
		 * @param scalar $id Primary key value
		 * @return string SQL condition, including the WHERE clause
		 */
		public function where_primary( $id ) {
			return ' WHERE ' . $this->cond_primary( $id );
		}


		/**
		 * Read the table schema data and store it in the $_schema property
		 * This method will find out, which columns exist in the model-table
		 *
		 * @since 1.0
		 */
		public function get_schema() {
			if ( $this->_schema === null ) {
				$sql = 'SHOW COLUMNS FROM ' . $this->tablename();
				$result = self::db_get_results($sql);

				$this->_schema = (object) array(
					'primary' => '',
					'fields' => array(),
					'nullable' => array(),
					'type' => array(),
				);

				foreach ($result as $field) {
					// Add the column to the fields-list
					$this->_schema->fields[$field->Field] = $field->Default;
					// Store the DB default value
					$this->_schema->type[] = $field->Type;
					// Check if the column is primary key. NOTE: Only 1 column can be primary key!
					if ( $field->Key === 'PRI' ) {
						if ( strlen( $this->_schema->primary ) > 0 And $this->_schema->primary !== $field->Field )
							throw new MOK_Exception( 'Not supported: Table has primary key over multiple columns! (' . $this->tablename . ')' );
						$this->_schema->primary = $field->Field;
					}
					// Check if column can be NULL
					if ( $field->Null !== 'NO' )
						$this->_schema->nullable[] = $field->Field;
				}
			}

			return $this->_schema;
		}


		/**
		 * Returns an array with all meta-information that belongs to the specified item
		 * The meta information is fetched from the $meta_table
		 * When no meta_table is defined, an empty array is returned
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the associated item
		 * @return array An associative array of meta-information
		 */
		public function get_meta_for( $id ) {
			if ( $this->metafield('has_meta') != true )
				return array();

			$sql =
				'SELECT ' .
					$this->metafield('key_col') . ' AS m_key, ' .
					$this->metafield('val_col') . ' AS m_val ' .
				' FROM ' .
					$this->metafield('table') .
				' WHERE ' .
					$this->metafield('ref_col') . '=' . $this->esc( $id );
			$results = $this->db_get_results( $sql );

			if ( ! is_array( $results ) ) return array();

			$fields = array();
			foreach ( $results as $row ) {
				$fields[$row->m_key] = $row->m_val;
			}

			return $fields;
		}


		/**
		 * Saves a single meta field to the database.
		 * When no meta_table was defined then an Exception is thrown
		 * We do a check to find out if we need to update or insert the meta information in database
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the associated item
		 * @param string $field Name of the meta-field
		 * @param string $value Value of the meta-field
		 */
		public function set_meta_for( $id, $field, $value ) {
			if ( $this->metafield('has_meta') != true )
				throw new MOK_Exception( 'Cannot save meta field: No meta table defined' );

			$upd_cond =
				' WHERE ' .
					$this->metafield('ref_col') . '=' . $this->esc( $id ) .
					' AND ' . $this->metafield('key_col') . '=' . $this->esc( $field );

			$check_sql = 'SELECT COUNT(1) FROM ' . $this->metafield('table') . $upd_cond;
			$existing = $this->db_get_var( $check_sql );

			if ( $existing ) {
				$sql =
					'UPDATE ' .
						$this->metafield('table') .
					' SET ' .
						$this->metafield('val_col') . '=' . $this->esc( $value ) .
					$upd_cond;
			} else {
				$sql =
					'INSERT INTO ' .
						$this->metafield('table') .
					'(' .
						$this->metafield('ref_col') . ',' .
						$this->metafield('key_col') . ',' .
						$this->metafield('val_col') .
					') VALUES (' .
						$this->esc( $id ) . ',' .
						$this->esc( $field ) . ',' .
						$this->esc( $value ) .
					')';
			}
			$result = $this->db_query( $sql );
		}


		/**
		 * Removes a single meta-field from the database
		 * When no meta_table was defined then an Exception is thrown
		 *
		 * @since 1.0
		 *
		 * @param scalar $id The Primary-Key of the associated item
		 * @param string $field Name of the meta-field
		 */
		public function clear_meta_for( $id, $field ) {
			if ( $this->metafield('has_meta') != true )
				throw new MOK_Exception( 'Cannot clear meta field: No meta table defined' );

			$sql =
				'DELETE FROM ' .
					$this->metafield('table') .
				' WHERE ' .
					$this->metafield('ref_col') . '=' . $this->esc( $id ) .
					' AND ' . $this->metafield('key_col') . '=' . $this->esc( $field );
			$result = $this->db_query( $sql );
		}


		/**
		 * Sets the details for the meta-field lookup
		 *
		 * @since 1.0
		 *
		 * @param string $table Which tables holds the meta information?
		 * @param string $refcol Which column links the meta information to the item?
		 * @param string $keycol Which column defines the meta field-names?
		 * @param string $valuecol Which column holds the meta-values?
		 */
		public function metatable( $table = '', $refcol = '', $keycol = '', $valuecol = '' ) {
			if ( empty( $table ) ) {
				$this->_meta_info['has_meta'] = true;
			} else {
				$this->_meta_info['table'] = $this->esc_field( $table );
				$this->_meta_info['ref_col'] = $this->esc_field( $refcol );
				$this->_meta_info['key_col'] = $this->esc_field( $keycol );
				$this->_meta_info['val_col'] = $this->esc_field( $valuecol );
				$this->_meta_info['has_meta'] = true;
			}
		}


		/**
		 * Returns a value from the $_meta_info array
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the requested field
		 * @return string The field value
		 */
		private function metafield( $name ) {
			if ( isset( $this->_meta_info[$name] ) )
				return $this->_meta_info[$name];
			else
				throw new MOK_Exception( 'Meta field is not defined: ' . $name );
		}


		/**
		 * Escapes a string, so it is save to use it in a SQL string
		 *
		 * @since 1.0
		 *
		 * @param string $value The value which should be escaped
		 * @return string The escaped string value
		 */
		public function esc( $value ) {
			return "'" . self::db__real_escape( $value ) . "'";
		}

	};


endif;
