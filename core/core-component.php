<?php

/**
 * The base class for all Mookiu MVC components
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );


if ( ! class_exists('MOK_Component') ) :
	/**
	 * This is the base class for all Mookiu MVC components.
	 * A component is a "static" object - i.e. a singleton.
	 *
	 * @since 1.0
	 */
	class MOK_Component extends MOK_Base {

		private static $instances;


		/**
		 * Singleton accessor to make sure that there is only one instance of each component
		 * The method get_called_class() required PHP 5.3
		 *
		 * @see http://www.guzaba.org/page/singleton-implementation-php53.html
		 *
		 * @since 1.0
		 */
		public static function instance() {
			$c = get_called_class();

			if ( ! isset( self::$instances[$c] ) ) {
				$args = func_get_args();
				$reflection_obj = new ReflectionClass( $c );
				self::$instances[$c] = $reflection_obj->newInstanceArgs( $args );
			}

			return self::$instances[$c];
		}


		/**
		 * A factory pattern that will return the singleton instance of the specified class
		 * This is useful when the class-name is variable (e.g. get the controller-instance based on URL param)
		 *
		 * @since 1.0
		 */
		public static function instance_of( $class_name ) {
			if ( ! isset( self::$instances[$class_name] ) ) {
				$reflection_obj = new ReflectionClass( $class_name );
				self::$instances[$class_name] = $reflection_obj->newInstanceArgs( array() );
			}
			return self::$instances[$class_name];
		}


		/**
		 * A dummy constructor to prevent component from being loaded more than once.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$c = get_class($this);
			if ( isset( self::$instances[$c] ) ) {
				_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mookiumvc' ), '1.0' );
			} else {
				self::$instances[$c] = $this;
				$this->setup();
				$this->setup_includes();
				$this->setup_actions();
				$this->setup_filters();
			}
		}


		/**
		 * A dummy magic method to prevent component from being cloned
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mookiumvc' ), '1.0' );
		}


		/**
		 * A dummy magic method to prevent component from being unserialized
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mookiumvc' ), '1.0' );
		}

	};


endif;
