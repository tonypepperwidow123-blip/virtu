<?php
/**
 * Admin menu pages and functionality.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Admin
 *
 * Handles admin menu registration, page rendering, asset enqueueing, and CSV export.
 */
class Virtu_Admin {

	/**
	 * Constructor — register admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_csv_export' ) );
	}

	/**
	 * Add menu items under the WooCommerce menu.
	 */
	public function add_admin_menu() {
		// Main menu page under WooCommerce.
		add_submenu_page(
			'woocommerce',
			__( 'VirtuConnect Settings', 'virtu-connect' ),
			__( 'VirtuConnect', 'virtu-connect' ),
			'manage_woocommerce',
			'virtu-connect-settings',
			array( $this, 'render_settings_page' )
		);

		// Leads submenu.
		add_submenu_page(
			'woocommerce',
			__( 'VirtuConnect Leads', 'virtu-connect' ),
			__( 'VirtuConnect Leads', 'virtu-connect' ),
			'manage_woocommerce',
			'virtu-connect-leads',
			array( $this, 'render_leads_page' )
		);
	}

	/**
	 * Render the settings page with tabbed navigation.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'virtu-connect' ) );
		}

		$tabs = array(
			'general'   => __( 'General', 'virtu-connect' ),
			'videocall' => __( 'Video Call', 'virtu-connect' ),
			'email'     => __( 'Email', 'virtu-connect' ),
			'whatsapp'  => __( 'WhatsApp', 'virtu-connect' ),
			'design'    => __( 'Design', 'virtu-connect' ),
		);

		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! array_key_exists( $current_tab, $tabs ) ) {
			$current_tab = 'general';
		}

		?>
		<div class="wrap virtu-admin-wrap">
			<h1><?php esc_html_e( 'VirtuConnect Settings', 'virtu-connect' ); ?></h1>

			<nav class="nav-tab-wrapper virtu-tabs">
				<?php foreach ( $tabs as $tab_slug => $tab_label ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=virtu-connect-settings&tab=' . $tab_slug ) ); ?>"
					   class="nav-tab <?php echo $current_tab === $tab_slug ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="virtu-tab-content">
				<?php
				$view_file = VIRTU_PATH . 'admin/views/settings-' . $current_tab . '.php';
				if ( file_exists( $view_file ) ) {
					include $view_file;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the leads management page.
	 */
	public function render_leads_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'virtu-connect' ) );
		}

		include VIRTU_PATH . 'admin/views/leads-page.php';
	}

	/**
	 * Enqueue admin CSS and JS on plugin pages only.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		$plugin_pages = array(
			'woocommerce_page_virtu-connect-settings',
			'woocommerce_page_virtu-connect-leads',
		);

		if ( ! in_array( $hook_suffix, $plugin_pages, true ) ) {
			return;
		}

		// WordPress color picker.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Plugin admin styles.
		wp_enqueue_style(
			'virtu-admin-css',
			VIRTU_URL . 'admin/assets/admin.css',
			array( 'wp-color-picker' ),
			VIRTU_VERSION
		);

		// Plugin admin scripts.
		wp_enqueue_script(
			'virtu-admin-js',
			VIRTU_URL . 'admin/assets/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			VIRTU_VERSION,
			true
		);

		wp_localize_script( 'virtu-admin-js', 'virtuAdmin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'virtu_leads_nonce' ),
			'strings'  => array(
				'status_updated' => __( 'Status updated.', 'virtu-connect' ),
				'status_error'   => __( 'Failed to update status.', 'virtu-connect' ),
			),
		) );
	}

	/**
	 * Handle CSV export request on admin_init.
	 */
	public function handle_csv_export() {
		if ( ! isset( $_GET['virtu_export'] ) || 'csv' !== $_GET['virtu_export'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'virtu_export_csv' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'virtu-connect' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'virtu-connect' ) );
		}

		Virtu_Leads::export_csv();
	}
}
