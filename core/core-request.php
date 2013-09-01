<?php

/**
 * Mookiu MVC Core request/URL processor
 */


if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_Request') ) :
	/**
	 * The request class which acts as the fundament for the controler layer.
	 *
	 * @since 1.0
	 */
	class MOK_Request extends MOK_Component {


		/**
		 * List of public properties
		 *
		 * @since 1.0
		 */
		protected $properties = array(
			'routes',			// Array of registered routes
		);


		/**
		 * Register the actions to connect the framework with WordPress
		 *
		 * @since 1.0
		 */
		protected function setup_actions() {
			// Core WordPress actions
			add_action( 'wp_loaded',					'mok_routes' );
			add_action( 'template_redirect',			'mok_handle_request' );

			// Custom MookiuMVC actions
			add_action( 'mok_routes',					array( $this, 'setup_routes' ) );
			add_action( 'mok_handle_request',			array( $this, 'parse_request' ) );
			add_action( 'mok_routing_error',			array( $this, 'routing_error' ) );
		}


		/**
		 * Register the filters used by this component
		 *
		 * @since 1.0
		 */
		protected function setup_filters() {
			// Core WordPress filters
			add_filter( 'template_include',				array( $this, 'request_end' ) );
			add_filter( 'status_header',				array( $this, 'status_header' ), 10, 4 );

			// Custom MookiuMVC filters
			add_filter( 'mok_register_routes',			array( $this, 'register_core_routes' ) );
			add_filter( 'mok_request_params',			array( $this, 'get_params' ) );
		}


		/**
		 * Returns the complete URL to the current page
		 * @return string Current page URL
		 */
		public function current_url() {
			$page_url = 'http';

			if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
				$page_url .= "s";
			$page_url .= "://";

			if ( $_SERVER["SERVER_PORT"] != "80" )
				$page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
			else
				$page_url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

			return $page_url;
		}


		/**
		 * Extract the path from the current URL.
		 * The Path is the part of the URL which comes after the Site-URL
		 *
		 * Example:
		 *   Site URL is "http://www.mysite.com/app/"
		 *   Current URL is "http://www.mysite.com/app/company/info?id=12"
		 *   The path is "company/info"
		 *   Note: The Parameter "?id=12" is not part of the Path!
		 *
		 * @since 1.0
		 * @return array The path elements of the URL
		 */
		public function get_path() {
			$url = mok_get_request_url();
			$base_url = mok_get_base_url();

			$current_path = parse_url( $url, PHP_URL_PATH );
			$base_path = parse_url( $base_url, PHP_URL_PATH );
			$path = trim( substr( $current_path, strlen( $base_path )), '/' );

			// The following line would return the path including the URL parameters
			//$path = trim(substr($url, strlen($base_url)), '/');
			return $path;
		}


		/**
		 * Setup the routing array.
		 * The routes are evaluated in the order in which they appear in the $this->routes array
		 *
		 * @since 1.0
		 * @action 'mok_routes'
		 */
		public function setup_routes() {
			$this->routes = apply_filters( 'mok_register_routes', array() );
		}


		/**
		 * Register the core/system routes
		 * Returns an array of routes that are recognized/handled
		 *
		 * Example
		 *    array(
		 *      //     v-- route                    v-- controler/action
		 *      array('posts'                    , 'posts/list'), // $posts->list
		 *      array('posts/<id:\d+>'           , 'posts/view'), // $posts->view( array('id'=>$id) )
		 *      array('posts/<year:\d+>/<title>' , 'posts/view'), // $posts->view( array('year'=>$year, 'title'=>$title) )
		 *      array('<contr:\w+>/<action:\w+>' , '<contr>/<action>'), // a generic standard rule
		 *    )
		 *
		 * @since 1.0
		 * @filter 'mok_register_routes'
		 */
		public function register_core_routes( $routes ) {
			$routes[] = array( '<controller:\w+>/<action:\w+>', '<controller>/<action>' );
			$routes[] = array( '', 'home/index' );
			return $routes;
		}


		/**
		 * This is the main controler function:
		 * It parses the currnt URL and calls the associated controler function.
		 *
		 * @since 1.0
		 * @action 'mok_handle_request'
		 */
		public function parse_request() {
			$path = mok_get_request_path();
			$handler = $this->get_handler_from_path( $path );

			if ( isset( $handler->params ) )
				$handler->params = apply_filters( 'mok_request_params', $handler->params );

			if ( isset( $handler->controller ) And isset( $handler->action ) And mok_has_controller( $handler->controller ) ) {
				$controller = mok_get_controller( $handler->controller );
				$controller->execute_action( $handler->action, $handler->params );
				mok_set_config_value( 'handler', $handler );
			}
		}


		/**
		 * Parses the path and returns a the handler/action value that should process the path
		 *
		 * @since 1.0
		 *
		 * @param string $path The path which should be parsed
		 * @return object An object containing the properties "controller", "action", "params"
		 */
		private function get_handler_from_path( $path ) {
			$result = (object) array(
				'controller' => '',
				'action' => '',
				'params' => array(),
			);

			foreach ( $this->routes as $route ) {
				if ( count( $route ) != 2 ) do_action( 'mok_routing_error', 'Invalid Route config. Each route config must have the form "array($route, $path)".' );

				/*
				 * Prepare the rule, so it is a valid regular expression
				 */
				$rule = $route[0];
				$rule = preg_replace('/\<(\w+)\>/i', '(?<\1>.*)', $rule);
				$rule = preg_replace('/\<(\w+):(.*?)\>/i', '(?<\1>\2)', $rule);
				$rule = str_replace('!', '\!', $rule);

				/*
				 * Test the rule
				 */
				if ( preg_match( '!^' . $rule . '$!i', $path, $match ) ) {
					/*
					 * This anonymous callback-function is used to replace variable-names in the handler
					 */
					$callback = function( $var ) use ( &$match ) {
						$key = $var[1];
						$val = $match[$key];
						unset( $match[$key] );
						return $val;
					};

					/*
					 * When the rule matches, then fetch and update the handler
					 */
					$handler = $route[1];
					$handler = preg_replace_callback( '/<(\w+)>/i', $callback, $handler );
					$handler_parts = explode( '/', $handler );
					if ( count( $handler_parts ) != 2 ) do_action( 'mok_routing_error', 'Invalid route path. The path must have the form "controler/action".' );
					$result->controller = trim( strtolower( $handler_parts[0] ) );
					$result->action = trim( strtolower( $handler_parts[1] ) );

					/*
					 * Remove the numeric indexes from the matches-array
					 */
					foreach($match as $key=>$val) {
						if ( is_int( $key ) ) unset ( $match[$key] );
					}
					$result->params = $match;
					return $result;
				}
			}

			do_action( 'mok_routing_error', 'No route defined for "' . $path . '".' );
		}


		/**
		 * Handles routing errors (i.e. no route specified, route-path is invalid)
		 *
		 * @since 1.0
		 *
		 * @param string $message The error message
		 */
		public function routing_error( $message ) {
			$routing_error = mok_get_config_value( 'routing_error' );

			// NEVER - do nothing
			if ( $routing_error == 'never' )
				return;

			// ALWAYS - display error message
			if ( $routing_error == 'always' ) {
				throw new MOK_Exception( 'ROUTING ERROR ' . $message );
			} else {
				// 404 - only display error when WordPress reports 404
				if ( $routing_error == '404' And is_404() ) {
					throw new MOK_Exception( 'ROUTING ERROR ' . $message );
				}
			}

		}


		/**
		 * Prepares the parameters that are passed to the current controller/action method
		 * As input we receive an array of parameters extracted from the URL path.
		 * We want to add URL parameters passed via query-string
		 *
		 * @since 1.0
		 * @filter 'mok_request_params'
		 *
		 * @param array $params The parameters which were extracted from the URL path
		 * @return array The full array containing all parameters which should be passed to the action-method
		 */
		public function get_params( $params ) {
			// Add the query-string parameters to the param list
			foreach ( $_GET as $key=>$val ) {
				if ( ! isset( $params[$key] ) )
					$params[$key] = $val;
			}

			return $params;
		}


		/**
		 * Set a new status code for the http response.
		 * This can be used for example for API responses to set 401 (unauthorized) or other meaningful codes.
		 * The code can be set to FALSE to keep the default code suggested by WordPress
		 *
		 * @since 1.0
		 *
		 * @param integer $code The reponse code to set
		 */
		public static function set_status( $code ) {
			if ( $view === false Or (is_numeric( $code ) And intval($code) > 0 ) ) {
				mok_set_config_value( 'status_code', intval( $code ) );
			}
		}


		/**
		 * Return the currently defined reponse status-code.
		 * Note that this will return the Code that is used by Mookiu to override the WordPress default code.
		 * The WordPress code is ignored by this method
		 *
		 * @since 1.0
		 *
		 * @return integer The reponse code (or FALSE when the code will not be overridden)
		 */
		public static function get_status() {
			return mok_get_config_value( 'status_code' );
		}


		/**
		 * This function sets a custom view-file that will be included.
		 * The view-file can/should be defined by the handler function called by parse_request()
		 *
		 * @since 1.0
		 * @filter 'template_include'
		 *
		 * @param string $wordpress_choice The view which was selected by WordPress
		 * @return string The filename of the effective view to display
		 */
		public function request_end( $wordpress_choice ) {
			if ( mok_has_no_view() ) {
				exit();
			} else {
				$the_view = mok_get_config_value( 'view' );

				// If there is no view defined then stick to the WordPress Choice
				if ( strlen( $the_view ) == 0 )
					$the_view = $wordpress_choice;

				return apply_filters( 'mok_set_view', $the_view );
			}
		}


		/**
		 * Sets the HTTP Status header that will be returned with the HTTP response
		 *
		 * @since 1.0
		 * @filter 'status_header'
		 *
		 * @param string $status_header The suggested status header (e.g. "HTTP 200 OK")
		 * @param integer $header The suggested status code (e.g. 200)
		 * @param string $text The descriptive text for the status code (e.g. "OK")
		 * @param string $protocol The request protocol (e.g. "HTTP")
		 * @return string The final status header
		 */
		public function status_header( $status_header, $header, $text, $protocol ) {
			$status = mok_get_response_status();

			if ( $status != false And is_numeric( $status ) ) {
				$status_text = get_status_header_desc( $status ); // This is a WordPress function, since 2.3
				$status_header = "$protocol $status $status_text";
			}

			return $status_header;
		}
	};


	/**
	 * Convenient accessor to the MOK_Views instance
	 *
	 * @since 1.0
	 * @return MOK_Views The instance to MOK_Views
	 */
	function mok_request() {
		return MOK_Request::instance();
	}


	/**
	 * Initialize the request instance to hook up all actions and filters
	 */
	mok_request();


endif;
