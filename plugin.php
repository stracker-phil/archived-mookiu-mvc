<?php
/*
 * Plugin Name: Mookiu MVC Framework
 * Plugin URI:  http://github.com/stracker-phil/mookiu-mvc
 * Description: MVC Framework for WordPress
 * Version:     0.1
 * Author:      Philipp Stracker
 * Author URI:  http://www.stracker.net
 * Text Domain: mookiu-mvc
 * Domain Path: /mookiu-mvc/
 * License:     GPL (or later)
 * Provides:    mookiu-mvc
 */

/**
 * Copyright (c) 2013 Philipp Stracker. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

/**
 * Most components (including this one) build upon the core-component
 * So this is the first file we will include
 */
require( 'core/core-base.php' );
require( 'core/core-component.php' );


if ( ! class_exists('MookiuMVC') ) :
	/**
	 * Base Mookiu MVC class.
	 * It prepares the stage for everything else to come
	 *
	 * @since 1.0
	 */
	class MookiuMVC extends MOK_Component {


		/**
		 * List of public properties
		 *
		 * @since 1.0
		 */
		protected $properties = array(
			'config', 					// Configuration object
		);


		/**
		 * This setup-function is called by the singleton implementation
		 * It is executed only once.
		 * Do all the voodoo stuff to breath live into the component!
		 *
		 * @since 1.0
		 */
		protected function setup() {
			$this->constants();
			$this->includes();
			$this->setup_globals();
			$this->start();
		}


		/**
		 * Checks if the system requirements are met
		 *
		 * @since 1.0
		 * @return bool True if system requirements are met, false if not
		 */
		private function check_requirements() {
			global $wp_version;

			if ( version_compare( PHP_VERSION, MOK_PHP_REQUIRED, '<' ) )
				return false;

			if ( version_compare( $wp_version, MOK_WP_REQUIRED, '<' ) )
				return false;

			return true;
		}


		/**
		 * Prints an error that the system requirements weren't met.
		 *
		 * @since 1.0
		 */
		private function requirements_error() {
			require( MOK_PLUGIN_DIR . 'controllers/admin-notice.php'    );

			$view = 'requirement-error';
			$class = 'error';
			$message = mok_get_parsed_view( $view );
			MOK_AdminNotice::instance()->enqueue( $message, $class );
		}


		/**
		 * Bootstrap constants
		 *
		 * @since 1.0
		 */
		private function constants() {
			// Plugin name
			if ( ! defined( 'MOK_PLUGIN_NAME' ) ) {
				define( 'MOK_PLUGIN_NAME', 'Mookiu MVC' );
			}

			// Prefix for DB keys
			if ( ! defined( 'MOK_PREFIX' ) ) {
				define( 'MOK_PREFIX', '_mmvc_' );
			}

			// Plugin version (major.minor)
			if ( ! defined( 'MOK_PLUGIN_VERSION' ) ) {
				define( 'MOK_PLUGIN_VERSION', '1.0' );
			}

			// Plugin-DB version (integer only) - the DB version can update several times without the plugin version changing
			if ( ! defined( 'MOK_DB_VERSION' ) ) {
				define( 'MOK_DB_VERSION', '1' );
			}

			// Minimum PHP version required for this plugin
			if ( ! defined( 'MOK_PHP_REQUIRED' ) ) {
				define( 'MOK_PHP_REQUIRED', '5.3' ); // because of get_called_class()
			}

			// Minimum WordPress version required for this plugin
			if ( ! defined( 'MOK_WP_REQUIRED' ) ) {
				define( 'MOK_WP_REQUIRED', '3.1' ); // because of esc_textarea()
			}

			// Path and URL
			if ( ! defined( 'MOK_PLUGIN_DIR' ) ) {
				define( 'MOK_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/mookiu_mvc' ) );
			}

			// The absolute URL to the plugin directory (e.g. for including css, images, etc)
			if ( ! defined( 'MOK_PLUGIN_URL' ) ) {
				$plugin_url = plugin_dir_url( __FILE__ );

				// If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
				if ( is_ssl() )
					$plugin_url = str_replace( 'http://', 'https://', $plugin_url );

				define( 'MOK_PLUGIN_URL', $plugin_url );
			}

			// Absolute Site URL
			if ( ! defined( 'SITE_URL' ) ) {
				define( 'SITE_URL',	get_bloginfo( 'wpurl' ) );
			}
		}


		/**
		 * Component global variables
		 *
		 * @since 1.0
		 */
		private function setup_globals() {
			$this->config = MOK_Config::instance();
		}


		/**
		 * Include required files
		 *
		 * @since 1.0
		 */
		private function includes() {
			// core
			require( MOK_PLUGIN_DIR . 'core/core-exceptions.php'        );
			require( MOK_PLUGIN_DIR . 'core/core-configuration.php'     );
			require( MOK_PLUGIN_DIR . 'core/core-functions.php'         );
			require( MOK_PLUGIN_DIR . 'core/core-dependency.php'        );
			require( MOK_PLUGIN_DIR . 'core/core-request.php'           );
			require( MOK_PLUGIN_DIR . 'core/core-database.php'          );
			// controllers
			require( MOK_PLUGIN_DIR . 'controllers/controller-base.php' );
			require( MOK_PLUGIN_DIR . 'controllers/controller-home.php' );
			require( MOK_PLUGIN_DIR . 'controllers/view-base.php'       );
			// models
			require( MOK_PLUGIN_DIR . 'models/model-base.php'           );
			require( MOK_PLUGIN_DIR . 'models/model-blog.php'           );
			require( MOK_PLUGIN_DIR . 'models/model-posts.php'          );
			require( MOK_PLUGIN_DIR . 'models/model-pages.php'          );
			require( MOK_PLUGIN_DIR . 'models/model-users.php'          );
			require( MOK_PLUGIN_DIR . 'models/model-comments.php'       );
			// Items (i.e. active records)
			require( MOK_PLUGIN_DIR . 'items/item-base.php'             );
			require( MOK_PLUGIN_DIR . 'items/item-blog.php'             );
			require( MOK_PLUGIN_DIR . 'items/item-post.php'             );
			require( MOK_PLUGIN_DIR . 'items/item-page.php'             );
			require( MOK_PLUGIN_DIR . 'items/item-user.php'             );
			require( MOK_PLUGIN_DIR . 'items/item-comment.php'          );
		}


		/**
		 * Initialize the framework
		 *
		 * @since 1.0
		 */
		private function start() {
			/**
			 * Check requirements and load main class
			 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
			 */
			if ( ! is_admin() Or $this->check_requirements() ) {
				$this->actions();

				// Initialize the View-Controller
				MOK_View::instance();
			} else {
				$this->requirements_error();
			}
		}


		/**
		 * Register the actions to connect the framework with WordPress
		 *
		 * @since 1.0
		 */
		private function actions() {
			// NOTE: Make sure you update the did_action() parameter in the corresponding callback method when changing the hooks here
			register_activation_hook( __FILE__,		array( $this, 'action_activate' ) );
			register_deactivation_hook( __FILE__,	array( $this, 'action_deactivate' ) );
			add_action( 'activated_plugin', 		array( $this, 'load_this_first' ) );
		}


		/**
		 * Action-Hook that is called when the plugin is actiavted
		 *
		 * @since 1.0
		 */
		public function action_activate() {
			$db_version_key = MOK_PREFIX . 'version';

			if ( get_option( $db_version_key ) != MOK_DB_VERSION ) {
				$this->update_database();
				update_option( $db_version_key, MOK_DB_VERSION );
			}
		}


		/**
		 * Action-Hook that is called when the plugin is deactiavted
		 *
		 * @since 1.0
		 */
		public function action_deactivate() {
		}


		/**
		 * Updates the WordPress database and adds/updates tables that are required by this plugin
		 *
		 * @since 1.0
		 */
		private function update_database() {
			/*
			global $wpdb;
			$table = $wpdb->prefix . 'table_name';

			$sql = "CREATE TABLE " . $table . " (
				id INT NOT NULL AUTO_INCREMENT,
				name VARCHAR(250) NOT NULL DEFAULT '', // Bigger name column
				email VARCHAR(100) NOT NULL DEFAULT '',
				UNIQUE KEY id (id)
			);";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			*/
		}


		/**
		 * Modifies the plugin-loading chain in WordPress
		 * The Mookiu-MVC framework should be loaded before all other plugins, as they may depend on it
		 *
		 * @since 1.0
		 * @action 'activated_plugin'
		 */
		public function load_this_first() {
			$path = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
			if ( $plugins = get_option( 'active_plugins' ) ) {
				if ( $key = array_search( $path, $plugins ) ) {
					array_splice( $plugins, $key, 1 );
					array_unshift( $plugins, $path );
					update_option( 'active_plugins', $plugins );
				}
			}
		}
	};


	/**
	 * Debugging function: Output all parameters to the function
	 * Can receive multiple arguments, e.g. describe('User', $user_id, $user_obj);
	 *
	 * @since 1.0
	 * @param anything $statement The content of the variable is dumped as debug output
	 */
	function describe( $statement ) {
		if (WP_DEBUG === true) {
			// Stop all output buffering... Current buffer content is displayed (and not discarded)
			echo '<pre style="color:#A00">';

			if ( is_string( $statement ) ) {
				echo '<strong>' . htmlspecialchars( $statement ) . '</strong><br />';
				$arg_start = 1; // first argument is a string = the title
			} else {
				$arg_start = 0; // first argument is no string, so we also dump it
			}

			$num_args = func_num_args();
			if ($num_args > $arg_start) {
				$arg_list = func_get_args();
				for ( $i = $arg_start; $i < $num_args; $i += 1 ) {
					echo "object $i: ";
					//echo htmlspecialchars( var_export ( $arg_list[$i], true ) ); // PHP Code, but no functions
					//echo htmlspecialchars( print_r ( $arg_list[$i], true ) ); // Readable, but not possible to distinguish NULL/Boolean/Empty string
					var_dump ( $arg_list[$i] ); // not so readable but complete
					echo "<br />";
				}
			}
			echo '<hr />';
			echo '</pre>';
		}
	}


	/**
	 * Easy handle to the MookiuMVC framework class!
	 * Does not need any global declaration
	 *
	 * Use like:
	 *   $mok = mookiumvc();
	 *   $mok->do_something();
	 *
	 * @since 1.0
	 * @return MookiuMVC The one-and-only instance to the MookiuMVC framework
	 */
	function mookiumvc() {
		return MookiuMVC::instance();
	}


	/**
	 * Another cool shortcut to the Framework :-)
	 *
	 * This assignment will also kick off the whole show!
	 *
	 * Use like:
	 *   global $mok;
	 *   $mok->do_something()
	 */
	$GLOBALS['mok'] = mookiumvc();


endif;