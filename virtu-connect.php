<?php
/**
 * Plugin Name: VirtuConnect for WooCommerce
 * Plugin URI:  https://example.com/virtu-connect
 * Description: Add Video Call scheduling and WhatsApp chat widgets to WooCommerce product pages with lead capture and email notifications.
 * Version:     1.0.1
 * Author:      VirtuConnect
 * License:     GPL-2.0+
 * Text Domain: virtu-connect
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VIRTU_VERSION', '1.0.1' );
define( 'VIRTU_PATH', plugin_dir_path( __FILE__ ) );
define( 'VIRTU_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WooCommerce is active.
 */
function virtu_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'virtu_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice when WooCommerce is not active.
 */
function virtu_woocommerce_missing_notice() {
	echo '<div class="notice notice-error is-dismissible"><p>';
	echo wp_kses_post( __( '<strong>VirtuConnect for WooCommerce</strong> requires WooCommerce to be installed and active.', 'virtu-connect' ) );
	echo '</p></div>';
}

/**
 * Load all required class files safely.
 * Each file is loaded individually so a single error is isolated.
 */
function virtu_load_classes() {
	$files = array(
		'includes/class-virtu-activator.php',
		'includes/class-virtu-deactivator.php',
		'includes/class-virtu-leads.php',
		'includes/class-virtu-email.php',
		'includes/class-virtu-ajax.php',
		'includes/class-virtu-shortcode.php',
		'includes/class-virtu-connect.php',
	);

	foreach ( $files as $file ) {
		$path = VIRTU_PATH . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	if ( is_admin() ) {
		$admin_files = array(
			'admin/class-virtu-admin.php',
			'admin/class-virtu-settings.php',
			'admin/class-virtu-leads-table.php',
		);
		foreach ( $admin_files as $file ) {
			$path = VIRTU_PATH . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	$public_file = VIRTU_PATH . 'public/class-virtu-public.php';
	if ( file_exists( $public_file ) ) {
		require_once $public_file;
	}
}

/**
 * Initialize the plugin — fully wrapped in try-catch to prevent site crashes.
 */
function virtu_init_plugin() {
	try {

		if ( ! virtu_check_woocommerce() ) {
			return;
		}

		virtu_load_classes();

		if ( class_exists( 'Virtu_Connect' ) ) {
			Virtu_Connect::get_instance();
		}
	} catch ( \Throwable $e ) {
		// Log the error to a file inside the plugin folder.
		$log_entry = '[' . gmdate( 'Y-m-d H:i:s' ) . '] '
			. $e->getMessage()
			. ' in ' . $e->getFile()
			. ' on line ' . $e->getLine()
			. PHP_EOL;

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@file_put_contents( VIRTU_PATH . 'virtu-error.log', $log_entry, FILE_APPEND | LOCK_EX );

		// Show a safe admin notice instead of crashing.
		add_action(
			'admin_notices',
			function () use ( $e ) {
				echo '<div class="notice notice-error"><p>'
					. '<strong>VirtuConnect Error:</strong> '
					. esc_html( $e->getMessage() )
					. ' &mdash; Check <code>virtu-error.log</code> in the plugin folder.'
					. '</p></div>';
			}
		);
	}
}
add_action( 'plugins_loaded', 'virtu_init_plugin' );

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, 'virtu_activate_plugin' );

function virtu_activate_plugin() {
	try {
		require_once VIRTU_PATH . 'includes/class-virtu-activator.php';
		Virtu_Activator::activate();
	} catch ( \Throwable $e ) {
		$log = gmdate( 'Y-m-d H:i:s' ) . ' - Activation Error: ' . $e->getMessage() . PHP_EOL;
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@file_put_contents( VIRTU_PATH . 'virtu-error.log', $log, FILE_APPEND | LOCK_EX );
		wp_die( 'VirtuConnect activation failed: ' . esc_html( $e->getMessage() ) );
	}
}

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, 'virtu_deactivate_plugin' );

function virtu_deactivate_plugin() {
	if ( file_exists( VIRTU_PATH . 'includes/class-virtu-deactivator.php' ) ) {
		require_once VIRTU_PATH . 'includes/class-virtu-deactivator.php';
		Virtu_Deactivator::deactivate();
	}
}

/**
 * Load text domain.
 */
function virtu_load_textdomain() {
	load_plugin_textdomain( 'virtu-connect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'virtu_load_textdomain' );
