<?php

/**
 * The base class for all controller components
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );


if ( ! class_exists('MOK_Controller') ) :
	/**
	 * This abstract component is the base for all Controllers.
	 *
	 * @since 1.0
	 */
	class MOK_Controller extends MOK_Component {

		/**
		 * Returns the real class-name of the controler
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the controller
		 * @return string The real classname of the controller, or NULL when the controller does not exist
		 */
		private static function get_controller_classname( $name ) {
			$core_name = 'MOK_Controller' . $name;
			$ext_name = 'MOK_C' . $name;

			if ( class_exists($ext_name) ) return $ext_name;
			elseif ( class_exists($core_name) ) return $core_name;
			return null;
		}


		/**
		 * Checks if the specified controller exists
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the controller
		 * @return boolean
		 */
		public static function has_controller( $name ) {
			$classname = self::get_controller_classname( $name );

			if ( is_string($classname) And class_exists($classname) ) return true;
			return false;
		}


		/**
		 * Returns the singleton instance of the specified controller
		 *
		 * @since 1.0
		 *
		 * @param string $name Name of the controller
		 * @return MOK_Controller The instance
		 */
		public static function get_controller( $name ) {
			$classname = self::get_controller_classname( $name );

			if ( ! class_exists($classname) )
				throw new MOK_Exception(__FUNCTION__ . ': The controller for "' . $name . '" was not found');

			return self::instance_of($classname);
		}


		/**
		 * Executed the specified action in the current controller
		 *
		 * @since 1.0
		 *
		 * @param string $action The action that should be executed
		 * @param string $params Optional parameters that are passed to the action method
		 */
		public function execute_action( $action, $params=array() ) {
			$method_name = 'action_' . $action;

			if ( ! method_exists($this, $method_name) )
				throw new MOK_Exception(__FUNCTION__ . ': The controller ' . get_called_class() . ' does not have an action handler for "' . $action . '"');

			call_user_func( array($this, $method_name), $params );
		}
	};


endif;
