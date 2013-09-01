<?php

/**
 * Mookiu MVC Core processor for views
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_View') ) :
	/**
	 * Base class to handle Mookiu MVC Views.
	 *
	 * @since 1.0
	 */
	class MOK_View extends MOK_Component {

		/**
		 * Setup the new component (exectuted by singleton implementation)
		 *
		 * @since 1.0
		 */
		protected function setup() {
			self::filters();
			self::set_globals();
		}


		/**
		 * Attach the filters
		 *
		 * @since 1.0
		 */
		protected static function filters() {
			add_filter( 'mok_set_view',			array( 'MOK_View', 'set_the_view' ) );
		}


		/**
		 * Setup the configuration
		 *
		 * @since 1.0
		 */
		protected static function set_globals() {
			mok_add_view_path( get_stylesheet_directory() ); // look in current child-theme folder
			mok_add_view_path( get_template_directory() ); // look in the parent-child folder
			mok_add_view_path( trailingslashit( MOK_PLUGIN_DIR ) . 'views' ); // look in this plugins view folder
		}


		/**
		 * Add a new path to the view-paths array
		 *
		 * @since 1.0
		 */
		public static function add_view_path( $path ) {
			$paths = mok_get_config_value( 'view_paths' );
			$path = trailingslashit( trim( $path ) );

			if ( !is_array( $paths ) )
				$paths = array();

			if ( ! in_array( $path, $paths ) )
				$paths[] = $path;

			mok_set_config_value( 'view_paths', $paths );
		}


		/**
		 * Add a new path to the view-paths array
		 *
		 * @since 1.0
		 */
		public static function remove_view_path( $path ) {
			$paths = mok_get_config_value( 'view_paths' );
			$path = trailingslashit( trim( $path ) );

			if ( !is_array( $paths ) )
				$paths = array();

			$path_key = array_keys( $paths, $path );
			foreach( $path_key as $k ) {
				unset( $paths[$k] );
			}

			mok_set_config_value( 'view_paths', $paths );
		}


		/**
		 * Schedule the specified view to be displayed at the end of the request.
		 * Whent he view is false, then no view will be displayed
		 * Empty string means, means that we will display the template suggested by WordPress
		 *
		 * @since 1.0
		 *
		 * @param string $view Name of the view to be displayed. Or false
		 */
		public static function set_view( $view ) {
			if ( $view === false ) {
				mok_set_config_value( 'view', false );
			} else {
				$paths = mok_get_config_value( 'view_paths' );
				$new_view = '';

				// Add the ".php" extension to the view if it is missing
				if ( substr( $view, -4) !== '.php' )
					$view .= '.php';

				// Make sure the view is a full path and that the file exists
				if ( realpath( $view ) === $view ) {
					if ( file_exists( $view ) )
						$new_view = $view;
				} else {
					foreach ( $paths as $path_base ) {
						$view_path = trailingslashit( $path_base ) . $view;
						if ( file_exists( $view_path) ) {
							$new_view = $view_path;
							break;
						}
					}
				}
				// Save the view to the config
				mok_set_config_value( 'view', $new_view );
			}
		}


		/**
		 * Returns the "has no view" flag.
		 * When this is true it means that no view should be loaded (i.e. the request produces no output)
		 * By default the method returns FALSE (meaning, that the default view is displayed)
		 *
		 * @since 1.0
		 *
		 * @return boolean True, if there was explicit request to display no view
		 */
		public static function has_no_view() {
			$view = mok_get_config_value( 'view' );
			return ($view === false Or is_null( $view ));
		}


		/**
		 * Filter that is called right before the view is finally displayed
		 * This is the last (and for us only) chance to say which file we want to display
		 *
		 * @since 1.0
		 * @filter mok_set_view
		 *
		 * @param string $suggested_view The view which is suggested by WordPress
		 */
		public static function set_the_view( $suggested_view ) {
			if ( mok_has_no_view() )
				exit();

			$view = mok_get_config_value( 'view' );
			if ( ! empty( $view ) )
				return $view;
			else
				return $suggested_view;
		}


		/**
		 * Loads the specified view and returns the parsed HTML code
		 *
		 * @since  1.0
		 * @param  string $view Name of the view (without file exitension)
		 * @return string       Parsed HTML code of the view
		 */
		public static function get_parsed_view( $view ) {
			$path = mookiumvc()->plugin_dir . '/views/' . $view . '.php';

			ob_start();
			require $path;
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
	};


endif;
