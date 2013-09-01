<?php

/**
 * The base class is (as the name suggest) the base of all other components used in the Mookiu MVC Framework
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );


if ( ! class_exists('MOK_Base') ) :
	/**
	 * This is the base class for all other Mookiu MVC classes.
	 * It provides a very cool fundament of basic features like magic-get/set encapsulation, etc
	 *
	 * @since 1.0
	 */
	abstract class MOK_Base {

		/** Magic *****************************************************************/

		/**
		 * We store MookiuMVC variables in this private member. Access to the data
		 * is granted via the magic functions defined later (requires PHP 5.2+)
		 *
		 * @since 1.0
		 * @var array
		 */
		private $data;


		/**
		 * List of allowed properties: The $data array above will only get items that are mentioned in
		 * the $properties array.
		 *
		 * Example:
		 *   $properties = array('name');
		 *   echo $this->name; // This works
		 *   echo $this->email; // Error: Unknown property (unless of course there is declaration like "private $email")
		 *
		 * @since 1.0
		 * @var array
		 */
		protected $properties = array();

		/** Not Magic *************************************************************/


		/**
		 * Error messages
		 */
		private $_errors = array();


		/**
		 * A dummy constructor to prevent component from being loaded more than once.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$this->setup();
			$this->setup_includes();
			$this->setup_actions();
			$this->setup_filters();
		}


		/**
		 * Magic method for checking the existence of a certain custom field
		 *
		 * @since 1.0
		 */
		public function __isset( $key ) {
			$getter = 'get_' . $key;
			if ( in_array( $key, $this->properties ) ) return true;
			if ( method_exists( $this, $getter) ) return true;
			return false;
		}


		/**
		 * Magic method for getting component varibles
		 *
		 * @since 1.0
		 */
		public function __get( $key ) {
			$getter = 'get_' . $key;

			if ( method_exists( $this, $getter) )
				return $this->$getter();
			elseif ( in_array( $key, $this->properties ) ) {
				if ( isset( $this->data[$key] ) ) return $this->data[$key];
				else return '';
			}
			else
				throw new MOK_Exception( 'Cannot return value of undeclared property: "' . $key . '"' );
		}


		/**
		 * Magic method for setting component varibles
		 *
		 * @since 1.0
		 */
		public function __set( $key, $value ) {
			$setter = 'set_' . $key;
			$getter = 'get_' . $key;

			if ( method_exists( $this, $setter) )
				return $this->$setter( $value );
			elseif ( in_array( $key, $this->properties ) )
				$this->data[$key] = $value;
			elseif ( method_exists( $this, $getter) )
				throw new MOK_Exception( 'Cannot set value of read-only property: "' . $key . '"' );
			else
				throw new MOK_Exception( 'Cannot set value of undeclared property: "' . $key . '"' );
		}


		/**
		 * Magic method for unsetting component variables
		 *
		 * @since 1.0
		 */
		public function __unset( $key ) {
			$getter = 'get_' . $key;
			if ( method_exists( $this, $getter) )
				throw new MOK_Exception( 'Cannot unset static property: "' . $key . '"' );
			if ( in_array( $key, $this->properties ) )
				if ( isset( $this->data[$key] ) ) unset( $this->data[$key] );
			else
				throw new MOK_Exception( 'Cannot unset undeclared property: "' . $key . '"' );
		}


		/**
		 * Magic method to prevent notices and errors from invalid method calls
		 *
		 * @since 1.0
		 */
		public function __call( $name = '', $args = array() ) {
			throw new MOK_Exception( 'The requested method does not exist or is not public: ' . $name );
		}


		/**
		 * Do the initialization before filters and actions are registered
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// This is the function you want to override in each component to add initialization code!
		}


		/**
		 * Register the actions to connect the framework with WordPress
		 *
		 * @since 1.0
		 */
		protected function setup_actions() {
			// This is the function you want to override in each component to add initialization code!
		}


		/**
		 * Register the filters to connect the framework with WordPress
		 *
		 * @since 1.0
		 */
		protected function setup_filters() {
			// This is the function you want to override in each component to add initialization code!
		}


		/**
		 * Load files that are required by the module
		 *
		 * @since 1.0
		 */
		protected function setup_includes() {
			// This is the function you want to override in each component to add initialization code!
		}


		/**
		 * Adds a new error message to the errors-array
		 *
		 * @since 1.0
		 * @param string $message The error message to add
		 */
		protected function add_error( $message ) {
			$this->_errors[] = $message;
		}


		/**
		 * Returns TRUE when the component reported errors since the last get_errors() check
		 *
		 * @since 1.0
		 * @return boolean TRUE means that there are new errors
		 */
		public function has_error() {
			return 0 === count($this->_errors);
		}


		/**
		 * Returns all errors that are stored in the errors-array
		 * When this method is called the errors-array will be reset, so the has_errors() method will return false
		 *
		 * @since 1.0
		 * @return array Array containing all error messages
		 */
		public function get_errors() {
			$errors = $this->_errors;
			$this->_errors = array();
			return $errors;
		}

	};


endif;
