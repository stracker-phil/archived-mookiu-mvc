<?php

/**
 * Mookiu MVC Configuration collection
 *
 * To change the configuration use this workflow:
 *   $config = mok_get_config();
 *   $config->routing_error = 'always'; // change the settings
 *   mok_set_config($config);
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_Config') ) :
	/**
	 * Simple class that is used to store configuration settings for mookiu MVC.
	 *
	 * @since 1.0
	 */
	class MOK_Config extends MOK_Component {

		/**
		 * This is the default view which will be displayed when no other view is specified
		 * Empty string means: Let WordPress decide
		 * False means: Do not display any view
		 */
		public static $view = '';


		/**
		 * This status code is used for the HTTP response (200, 404, ...)
		 * It can be set via {@see mok_set_response_status()}
		 * False means: Use the default WordPress response status
		 */
		public static $status_code = false;


		/**
		 * This array holds the paths where view-files are stored
		 * When a view is set, then each path is searched in the order in which they are set
		 * The first path that contains the specified view will be used
		 * Note: The view can also be an absolute path. In this case the view_paths array is not used
		 */
		public static $view_paths = array();


		/**
		 * Decides if an error should be displayed when no route is defined for the current request
		 * 'never' - Never display error, simply fall-back to WordPress theme/404 page
		 * 'always' - Always show an error when no route is defined
		 * '404' - Display routing error only when WordPress also reports an 404 error
		 */
		public static $routing_error = '404';


		/**
		 * Stores the request-handling information.
		 * This information is set by the MOK_Request object
		 */
		public static $handler = array();


		/**
		 * Assign a new value to a single configuration option
		 *
		 * @since 1.0
		 *
		 * @param string $key The option that should be updated
		 * @param string $value The new value
		 */
		public static function set_value( $key, $value ) {
			if ( isset( self::$$key ) ) self::$$key = $value;
		}


		/**
		 * Returns the value to a single configuration option
		 *
		 * @since 1.0
		 *
		 * @param string $key The option that should be updated
		 * @return anything The value of the option, or NULL if option does not exist
		 */
		public static function get_value( $key ) {
			if ( isset( self::$$key ) ) return self::$$key;
			return null;
		}

	};


endif;
