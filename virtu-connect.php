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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version constant.
 */
define( 'VIRTU_VERSION', '1.0.0' );

/**
 * Plugin directory path constant.
 */
define( 'VIRTU_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL constant.
 */
define( 'VIRTU_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WooCommerce is active. If not, display an admin notice and bail.
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
	?>
	<div class="notice notice-error is-dismissible">
		<p>
			<?php
			echo wp_kses_post(
				__( '<strong>VirtuConnect for WooCommerce</strong> requires WooCommerce to be installed and active. Please install and activate WooCommerce.', 'virtu-connect' )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Load all required class files.
 */
function virtu_load_classes() {
	require_once VIRTU_PATH . 'includes/class-virtu-activator.php';
	require_once VIRTU_PATH . 'includes/class-virtu-deactivator.php';
	require_once VIRTU_PATH . 'includes/class-virtu-connect.php';
	require_once VIRTU_PATH . 'includes/class-virtu-ajax.php';
	require_once VIRTU_PATH . 'includes/class-virtu-email.php';
	require_once VIRTU_PATH . 'includes/class-virtu-leads.php';
	require_once VIRTU_PATH . 'includes/class-virtu-shortcode.php';
	if ( is_admin() ) {
		require_once VIRTU_PATH . 'admin/class-virtu-admin.php';
		require_once VIRTU_PATH . 'admin/class-virtu-settings.php';
		require_once VIRTU_PATH . 'admin/class-virtu-leads-table.php';
	}
	require_once VIRTU_PATH . 'public/class-virtu-public.php';
}

/**
 * Initialize the plugin.
 */
function virtu_init_plugin() {
	if ( ! virtu_check_woocommerce() ) {
		return;
	}

	virtu_load_classes();

	Virtu_Connect::get_instance();
}
add_action( 'plugins_loaded', 'virtu_init_plugin' );

/**
 * Register activation hook.
 */
register_activation_hook( __FILE__, 'virtu_activate_plugin' );

/**
 * Activation callback.
 */
function virtu_activate_plugin() {
	try {
		require_once VIRTU_PATH . 'includes/class-virtu-activator.php';
		Virtu_Activator::activate();
	} catch ( Exception $e ) {
		// Log the exact error to a file in the plugin folder.
		$error_message = date( 'Y-m-d H:i:s' ) . " - Activation Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
		file_put_contents( VIRTU_PATH . 'virtu-error.log', $error_message, FILE_APPEND );
		
		// Still let WordPress know it failed, but gracefully.
		die( 'VirtuConnect Activation Error: ' . $e->getMessage() . '. Please check virtu-error.log.' );
	}
}

/**
 * Register deactivation hook.
 */
register_deactivation_hook( __FILE__, 'virtu_deactivate_plugin' );

/**
 * Deactivation callback.
 */
function virtu_deactivate_plugin() {
	require_once VIRTU_PATH . 'includes/class-virtu-deactivator.php';
	Virtu_Deactivator::deactivate();
}

/**
 * Load plugin text domain for translations.
 */
function virtu_load_textdomain() {
	load_plugin_textdomain( 'virtu-connect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'virtu_load_textdomain' );
