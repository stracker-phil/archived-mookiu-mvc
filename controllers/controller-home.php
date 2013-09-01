<?php

/**
 * Default Home-handler
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_HandlerItem') ) :
	/**
	 * Handler for the item actions
	 *
	 * @since 1.0
	 */
	class MOK_ControllerHome extends MOK_Controller {

		public function action_index( $args ) {
			mok_set_view( '' ); // display default WordPress view
		}

	};


endif;