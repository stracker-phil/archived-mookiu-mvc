<?php

/**
 * Exception handling functions
 */

if ( ! class_exists('MOK_Exception') ) :

	/**
	 * The MOK_Exception class simply allows us to recognize the MOK Framework Exceptions
	 */
	class MOK_Exception extends Exception {
		// Nothing special here
	}

endif;


/**
 * This method handles uncaught exceptions
 *
 * @since 1.0
 *
 * @param Exception $exception The exception-object which was not handled
 */
function mok_exception_handler($exception) {
	if (is_a($exception, 'MOK_Exception')) {
		$message = $exception->getMessage();
		$file = $exception->getFile();
		$line = $exception->getLine();
		$trace = $exception->getTrace();
		$output = '';

		$output .= '<h1>Exception</h1>';
		$output .= '<p><strong>Message</strong>: ' . $message;
		$output .= '<br><strong>File</strong>: ' . $file;
		$output .= '<br><strong>Line</strong>: ' . $line . '</p>';
		$output .= '<h1>Trace</h1><ol>';
		for ( $i = 0; $i < count($trace); $i += 1 ) {
			$entry = '<tt>' . ( isset($trace[$i]['class']) ? $trace[$i]['class'] : '' ) . ( isset($trace[$i]['type']) ? $trace[$i]['type'] : '' ) . '<strong>' . $trace[$i]['function'] . '()</strong></tt>';
			if ( isset($trace[$i]['file']) ) $entry .= '<br><small>' . $trace[$i]['file'] . ' (line ' . $trace[$i]['line'] . ')</small>';
			$output .= '<li>' . $entry . '<br>&nbsp;</li>';
		}
		$output .= '</ol>';

		wp_die($output);
	}
}


/**
 * Register the Mookiu MVC Exception handler
 */
set_exception_handler('mok_exception_handler');

