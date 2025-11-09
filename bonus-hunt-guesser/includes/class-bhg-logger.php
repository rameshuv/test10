<?php
/**
 * Simple logger wrapper for Bonus Hunt Guesser.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper around the bhg_log() helper.
 */
class BHG_Logger {

	/**
	 * Log an informational message.
	 *
	 * @param string $message Message to log.
	 *
	 * @return void
	 */
	public static function info( $message ) {
		bhg_log( $message );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message Message to log.
	 *
	 * @return void
	 */
	public static function error( $message ) {
		bhg_log( 'ERROR: ' . $message );
	}
}
