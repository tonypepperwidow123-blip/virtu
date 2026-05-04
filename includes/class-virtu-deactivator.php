<?php
/**
 * Fired during plugin deactivation.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Deactivator
 *
 * Handles tasks that run on plugin deactivation.
 */
class Virtu_Deactivator {

	/**
	 * Run deactivation routines.
	 *
	 * Currently a placeholder for any cleanup tasks needed on deactivation.
	 * Data is preserved; full cleanup happens in uninstall.php.
	 */
	public static function deactivate() {
		// Flush rewrite rules in case we added any.
		flush_rewrite_rules();
	}
}
