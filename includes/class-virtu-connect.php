<?php
/**
 * Core plugin class that initializes all components.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Connect
 *
 * Singleton class that bootstraps all plugin components.
 */
class Virtu_Connect {

	/**
	 * Single instance of this class.
	 *
	 * @var Virtu_Connect|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Virtu_Connect
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — load all components.
	 */
	private function __construct() {
		$this->init_components();
	}

	/**
	 * Prevent cloning of the singleton.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the singleton.
	 *
	 * @throws \Exception Always.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Initialize all plugin components.
	 */
	private function init_components() {
		// AJAX handler.
		new Virtu_Ajax();

		// Shortcode handler.
		new Virtu_Shortcode();

		// Public-facing functionality.
		new Virtu_Public();

		// Admin-only components.
		if ( is_admin() ) {
			new Virtu_Admin();
			new Virtu_Settings();
		}
	}
}
