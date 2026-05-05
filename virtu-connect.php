<?php
/**
 * Plugin Name: VirtuConnect for WooCommerce
 * Plugin URI:  https://example.com/virtu-connect
 * Description: Add Video Call scheduling and WhatsApp chat widgets to WooCommerce product pages with lead capture and email notifications.
 * Version:     1.0.0
 * Author:      VirtuConnect
 * License:     GPL-2.0+
 * Text Domain: virtu-connect
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 */

/*
 * EMERGENCY SAFE MODE — Plugin temporarily disabled for diagnostics.
 * All functionality is paused. No classes are loaded.
 * Restore virtu-connect.php from git history to re-enable.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Safe mode: show admin notice only.
add_action(
	'admin_notices',
	function () {
		echo '<div class="notice notice-warning"><p>'
			. '<strong>VirtuConnect:</strong> Plugin is in <strong>SAFE MODE</strong> for diagnostics. '
			. 'All functionality is paused. Contact your developer to restore.</p></div>';
	}
);
