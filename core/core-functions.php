<?php

/**
 * Mookiu MVC Common Functions
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

/**
 * Returns the Base-URL of the Mookiu-MVC plugin
 *
 * @since 1.0
 */
function mok_base_url() {
	echo mok_get_base_url();
}

	function mok_get_base_url() {
		return SITE_URL;
	}


/**
 * Returns the full request-URL of current page
 *
 * @since 1.0
 */
function mok_request_url() {
	echo mok_get_request_url();
}

	function mok_get_request_url() {
		return mok_request()->current_url();
	}


/**
 * Returns the request path of the current page
 *
 * @since 1.0
 */
function mok_request_path() {
	echo mok_get_request_path();
}

	function mok_get_request_path() {
		return mok_request()->get_path();
	}


/**
 * Returns the fully parsed view
 *
 * @since 1.0
 */
function mok_parsed_view( $view ) {
	echo mok_get_parsed_view( $view );
}

	function mok_get_parsed_view( $view ) {
		return MOK_View::get_parsed_view( $view );
	}


/**
 * Returns the singleton instance of the specified controller
 *
 * @since 1.0
 */
function mok_get_controller( $name ) {
	return MOK_Controller::get_controller( $name );
}


/**
 * Checks if the specified controller exists, returns true/false
 *
 * @since 1.0
 */
function mok_has_controller( $name ) {
	return MOK_Controller::has_controller( $name );
}



/**
 * Returns true, when the current request does not need to load a view.
 * By default this is false
 *
 * @since 1.0
 */
function mok_has_no_view() {
	return MOK_View::has_no_view();
}


/**
 * Schedule the specified view for display. Set to "false" to load no view (@see mok_has_no_view())
 *
 * @since 1.0
 */
function mok_set_view( $view ) {
	return MOK_View::set_view( $view );
}


/**
 * Overwrites the status code for the http response
 *
 * @since 1.0
 */
function mok_set_response_status( $code ) {
	return mok_request()->set_status( $code );
}


/**
 * Return the currently specified response stautus code
 *
 * @since 1.0
 */
function mok_get_response_status() {
	return mok_request()->get_status();
}


/**
 * Add a new path to search views in
 *
 * @since 1.0
 */
function mok_add_view_path( $path ) {
	return MOK_View::add_view_path( $path );
}


/**
 * Remove path to search views in
 *
 * @since 1.0
 */
function mok_remove_view_path( $path ) {
	return MOK_View::remove_view_path( $path );
}


/**
 * Sets the mookiu MVC configuration object
 *
 * @since 1.0
 */
function mok_set_config( MOK_Config $config ) {
	mookiumvc()->config = $config;
}


	/**
	 * Sets a single configuration option
	 *
	 * @since 1.0
	 */
	function mok_set_config_value( $key, $value ) {
		mookiumvc()->config->set_value( $key, $value );
	}

/**
 * Returns the mookiu MVC configuration object
 *
 * @since 1.0
 */
function mok_get_config() {
	return mookiumvc()->config;
}


	/**
	 * Returns a single configuration option
	 *
	 * @since 1.0
	 */
	function mok_get_config_value( $key ) {
		return mookiumvc()->config->get_value( $key );
	}


/**
 * Returns the defined model instance
 *
 * @since 1.0
 *
 * @param string $name Name of the model
 * @return MOK_model The model instance
 */
function mok_get_model( $name ) {
	return MOK_Model::get_model( $name );
}


/**
 * Checks if the specified model exists, returns true/false
 *
 * @since 1.0
 *
 * @param string $name Name of the model
 * @return boolean True if the model exists
 */
function mok_has_model( $name ) {
	return MOK_Model::has_model( $name );
}


/**
 * Creates a new ActiveRecord instance of the specified type and returns it
 *
 * @since 1.0
 *
 * @param string $name Name of the ActiveRecord
 * @return MOK_ActiveRecord
 */
function mok_new_dbitem( $name ) {
	return MOK_Item::new_instance( $name );
}


