<?php
/*
 * Copyright 2011 Ian Dunn (email : ian@iandunn.name)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) die( 'Access denied.' );


if( !class_exists( 'MOK_AdminNotice' ) ) :
	/**
	 * This class provides a way to display messages in the WordPress admin section
	 *
	 * @since 1.0
	 */
	class MOK_AdminNotice extends MOK_Controller {
		// Variables that are edited by reference (e.g. array_push($var, $arr)) cannot be magic variables...
		private $notices;
		private $user_notice_count;


		const NAME		= 'MOK_AdminNotice';
		const PREFIX	= 'mok_';


		/**
		 * Setup the AdminNotice controller
		 *
		 * @since 1.0
		 */
		protected function setup() {
			$this->init_done = false;
			$this->message_printed = false;
		}


		/**
		 * Register the actions that are used by this module
		 *
		 * @since 1.0
		 */
		private function setup_actions() {
			// NOTE: Make sure you update the did_action() parameter in the corresponding callback method when changing the hooks here
			add_action( 'admin_init',		array( $this, 'init' ) );	// needs to run before other plugin's init callbacks so that they can enqueue messages in their init callbacks
			add_action( 'admin_notices',	array( $this, 'print_messages' ) );
			add_action( 'shutdown',			array( $this, 'shutdown' ) );
		}


		/**
		 * Initializes variables
		 *
		 * @since 1.0
		 * @action 'admin_init'
		 */
		public function init() {
			if( $this->init_done )
				return;

			$default_notices 				= array( 'updates' => array(), 'errors' => array() );

			$this->notices					= array_merge( $default_notices, get_option( self::PREFIX . 'notices', array() ) );
			$this->user_notice_count		= array( 'updates' => count( $this->notices[ 'updates' ] ), 'errors' => count( $this->notices[ 'errors' ] )	);		// @todo - don't you need to check if the messages are 'user' mode or not?
			$this->updated_notices			= false;
			$this->accessible_private_vars	= array( 'debug_mode' );
			$this->debug_mode				= false;
			$this->init_done 				= true;
		}


		/**
		 * Queues up a message to be displayed to the user
		 * NOTE: In order to allow HTML in the output, any unsafe variables in $message need to be escaped before they're passed in, instead of escaping here.
		 *
		 * @since 1.0
		 * @param string $message The text to show the user
		 * @param string $type 'update' for a success or notification message, or 'error' for an error message
		 * @param string $mode 'user' if it's intended for the user, or 'debug' if it's intended for the developer
		 */
		public function enqueue( $message, $type = 'update', $mode = 'user' ) {
			if ( ! $this->init_done )
				$this->init();

			$message = apply_filters( self::PREFIX . 'enqueue-message', $message );

			if ( ! is_string( $message ) )
				return false;

			if ( ! isset( $this->notices[ $type .'s' ] ) )
				return false;

			array_push( $this->notices[ $type .'s' ], array(
				'message'	=> $message,
				'type'		=> $type,
				'mode'		=> $mode
			) );

			if ( $mode == 'user' )
				$this->user_notice_count[ $type . 's' ] += 1;

			$this->updated_notices = true;

			return true;
		}


		/**
		 * Displays updates and errors
		 *
		 * @since 1.0
		 * @action 'print_messages'
		 */
		public function print_messages() {
			if ( $this->message_printed )
				return;

			foreach ( array( 'updates', 'errors' ) as $type ) {
				if ( $this->notices[ $type ] && ( $this->debug_mode || $this->user_notice_count[ $type ] ) ) {
					$message = '';
					$class = $type == 'updates' ? 'updated' : 'error';
					$path = dirname( dirname( __FILE__ ) ) . '/views/admin-notice.php';

					require $path;

					$this->notices[ $type ] = array();
					$this->updated_notices = true;
					$this->user_notice_count[ $type ] = 0;
				}
			}
			$this->message_printed = true;
		}


		/**
		 * Writes notices to the database
		 *
		 * @since 1.0
		 * @action 'shutdown'
		 */
		public function shutdown() {
			if ( did_action( 'shutdown' ) !== 1 )
				return;

			if ( $this->updated_notices  )
				update_option( self::PREFIX . 'notices', $this->notices );
		}
	}


endif;

