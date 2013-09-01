<?php

/**
 * Plugin Dependency
 *
 * The purpose of the following hooks is to mimic the behavior of something
 * called 'plugin dependency' which enables a plugin to have plugins of their
 * own in a safe and reliable way.
 *
 * We do this in MookiuMVC by mirroring existing WordPress hooks in many places
 * allowing dependant plugins to hook into the MVC specific ones, thus
 * guaranteeing proper code execution only when MVC is active.
 *
 * The following functions are wrappers for hooks, allowing them to be
 * manually called and/or piggy-backed on top of other hooks if needed.
 */


if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

/**
 * Register routes for Request Parsing
 */
function mok_routes() {
	do_action( 'mok_routes' );
}


/**
 * Parse the current request and prepare the display of the correct view
 */
function mok_handle_request() {
	do_action( 'mok_handle_request' );
}




