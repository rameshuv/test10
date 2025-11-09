<?php
/**
 * Tournaments controller for Bonus Hunt Guesser.
 *
 * Previously applied default tournament settings during creation. The default
 * period logic has been removed since start and end dates define scope.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
				exit;
}

/**
 * Handles tournament-related hooks and logic.
 */
class BHG_Tournaments_Controller {
				/**
				 * Initialize hooks.
				 *
				 * @return void
				 */
	public static function init() {
			// Default period logic removed; start and end dates define scope.
	}
}
