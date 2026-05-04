<?php
/**
 * Settings registration using WordPress Settings API.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Settings
 *
 * Registers all settings groups, sections, and fields.
 */
class Virtu_Settings {

	/**
	 * Constructor — register settings on admin_init.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register all plugin settings.
	 */
	public function register_settings() {
		$this->register_general_settings();
		$this->register_videocall_settings();
		$this->register_email_settings();
		$this->register_whatsapp_settings();
		$this->register_design_settings();
	}

	/**
	 * Register General tab settings.
	 */
	private function register_general_settings() {
		register_setting( 'virtu_general', 'virtu_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_general', 'virtu_display_hook', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_general', 'virtu_display_priority', array( 'sanitize_callback' => 'absint' ) );
		register_setting( 'virtu_general', 'virtu_section_title', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		add_settings_section( 'virtu_general_section', __( 'General Settings', 'virtu-connect' ), '__return_false', 'virtu_general' );

		add_settings_field( 'virtu_enabled', __( 'Enable Plugin', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_general', 'virtu_general_section', array( 'id' => 'virtu_enabled', 'label' => __( 'Enable VirtuConnect on product pages', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_display_hook', __( 'Display Position', 'virtu-connect' ), array( $this, 'render_select' ), 'virtu_general', 'virtu_general_section', array( 'id' => 'virtu_display_hook', 'options' => array( 'woocommerce_single_product_summary' => __( 'Product Summary', 'virtu-connect' ), 'woocommerce_before_add_to_cart_form' => __( 'Before Add to Cart Form', 'virtu-connect' ), 'woocommerce_after_add_to_cart_form' => __( 'After Add to Cart Form', 'virtu-connect' ), 'woocommerce_product_meta_start' => __( 'Before Product Meta', 'virtu-connect' ), 'woocommerce_product_meta_end' => __( 'After Product Meta', 'virtu-connect' ), 'woocommerce_after_single_product_summary' => __( 'After Product Summary', 'virtu-connect' ), 'shortcode_only' => __( 'Shortcode Only', 'virtu-connect' ) ) ) );
		add_settings_field( 'virtu_display_priority', __( 'Display Priority', 'virtu-connect' ), array( $this, 'render_number' ), 'virtu_general', 'virtu_general_section', array( 'id' => 'virtu_display_priority', 'min' => 1, 'max' => 100 ) );
		add_settings_field( 'virtu_section_title', __( 'Section Title', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_general', 'virtu_general_section', array( 'id' => 'virtu_section_title' ) );
	}

	/**
	 * Register Video Call tab settings.
	 */
	private function register_videocall_settings() {
		$fields = array(
			'virtu_vc_enabled'        => 'sanitize_text_field',
			'virtu_vc_title'          => 'sanitize_text_field',
			'virtu_vc_description'    => 'sanitize_text_field',
			'virtu_vc_button_text'    => 'sanitize_text_field',
			'virtu_form_show_date'    => 'sanitize_text_field',
			'virtu_form_show_time'    => 'sanitize_text_field',
			'virtu_form_show_message' => 'sanitize_text_field',
			'virtu_popup_title'       => 'sanitize_text_field',
			'virtu_popup_subtitle'    => 'sanitize_text_field',
		);

		foreach ( $fields as $key => $cb ) {
			register_setting( 'virtu_videocall', $key, array( 'sanitize_callback' => $cb ) );
		}

		add_settings_section( 'virtu_vc_section', __( 'Video Call Settings', 'virtu-connect' ), '__return_false', 'virtu_videocall' );

		add_settings_field( 'virtu_vc_enabled', __( 'Enable Video Call Box', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_videocall', 'virtu_vc_section', array( 'id' => 'virtu_vc_enabled', 'label' => __( 'Show video call box on product pages', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_vc_title', __( 'Box Title', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_videocall', 'virtu_vc_section', array( 'id' => 'virtu_vc_title' ) );
		add_settings_field( 'virtu_vc_description', __( 'Box Description', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_videocall', 'virtu_vc_section', array( 'id' => 'virtu_vc_description' ) );
		add_settings_field( 'virtu_vc_button_text', __( 'Button Text', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_videocall', 'virtu_vc_section', array( 'id' => 'virtu_vc_button_text' ) );

		add_settings_section( 'virtu_vc_form_section', __( 'Popup Form Fields', 'virtu-connect' ), '__return_false', 'virtu_videocall' );

		add_settings_field( 'virtu_form_show_date', __( 'Show Date Field', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_videocall', 'virtu_vc_form_section', array( 'id' => 'virtu_form_show_date', 'label' => __( 'Show preferred date field in popup', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_form_show_time', __( 'Show Time Field', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_videocall', 'virtu_vc_form_section', array( 'id' => 'virtu_form_show_time', 'label' => __( 'Show preferred time field in popup', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_form_show_message', __( 'Show Message Field', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_videocall', 'virtu_vc_form_section', array( 'id' => 'virtu_form_show_message', 'label' => __( 'Show message textarea in popup', 'virtu-connect' ) ) );

		add_settings_section( 'virtu_vc_popup_section', __( 'Popup Appearance', 'virtu-connect' ), '__return_false', 'virtu_videocall' );

		add_settings_field( 'virtu_popup_title', __( 'Popup Title', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_videocall', 'virtu_vc_popup_section', array( 'id' => 'virtu_popup_title' ) );
		add_settings_field( 'virtu_popup_subtitle', __( 'Popup Subtitle', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_videocall', 'virtu_vc_popup_section', array( 'id' => 'virtu_popup_subtitle' ) );
	}

	/**
	 * Register Email tab settings.
	 */
	private function register_email_settings() {
		register_setting( 'virtu_email', 'virtu_admin_email', array( 'sanitize_callback' => 'sanitize_email' ) );
		register_setting( 'virtu_email', 'virtu_email_subject', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_email', 'virtu_auto_reply', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_email', 'virtu_auto_reply_subject', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_email', 'virtu_auto_reply_message', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );

		add_settings_section( 'virtu_email_section', __( 'Email Notification Settings', 'virtu-connect' ), '__return_false', 'virtu_email' );

		add_settings_field( 'virtu_admin_email', __( 'Admin Email', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_email', 'virtu_email_section', array( 'id' => 'virtu_admin_email', 'type' => 'email', 'desc' => __( 'Email address to receive lead notifications.', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_email_subject', __( 'Email Subject', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_email', 'virtu_email_section', array( 'id' => 'virtu_email_subject', 'desc' => __( 'Variables: {product_name}, {customer_name}, {date}', 'virtu-connect' ) ) );

		add_settings_section( 'virtu_auto_reply_section', __( 'Auto-Reply Settings', 'virtu-connect' ), '__return_false', 'virtu_email' );

		add_settings_field( 'virtu_auto_reply', __( 'Enable Auto-Reply', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_email', 'virtu_auto_reply_section', array( 'id' => 'virtu_auto_reply', 'label' => __( 'Send automatic reply to customers', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_auto_reply_subject', __( 'Auto-Reply Subject', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_email', 'virtu_auto_reply_section', array( 'id' => 'virtu_auto_reply_subject' ) );
		add_settings_field( 'virtu_auto_reply_message', __( 'Auto-Reply Message', 'virtu-connect' ), array( $this, 'render_textarea' ), 'virtu_email', 'virtu_auto_reply_section', array( 'id' => 'virtu_auto_reply_message', 'desc' => __( 'Available variables: {customer_name}, {product_name}, {product_url}, {preferred_date}, {preferred_time}', 'virtu-connect' ) ) );
	}

	/**
	 * Register WhatsApp tab settings.
	 */
	private function register_whatsapp_settings() {
		register_setting( 'virtu_whatsapp', 'virtu_wa_enabled', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_whatsapp', 'virtu_wa_number', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_whatsapp', 'virtu_wa_title', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_whatsapp', 'virtu_wa_description', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_whatsapp', 'virtu_wa_button_text', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'virtu_whatsapp', 'virtu_wa_message_template', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );

		add_settings_section( 'virtu_wa_section', __( 'WhatsApp Settings', 'virtu-connect' ), '__return_false', 'virtu_whatsapp' );

		add_settings_field( 'virtu_wa_enabled', __( 'Enable WhatsApp Box', 'virtu-connect' ), array( $this, 'render_checkbox' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_enabled', 'label' => __( 'Show WhatsApp box on product pages', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_wa_number', __( 'WhatsApp Number', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_number', 'desc' => __( 'Format without + or spaces, e.g. 919876543210', 'virtu-connect' ) ) );
		add_settings_field( 'virtu_wa_title', __( 'Box Title', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_title' ) );
		add_settings_field( 'virtu_wa_description', __( 'Box Description', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_description' ) );
		add_settings_field( 'virtu_wa_button_text', __( 'Button Text', 'virtu-connect' ), array( $this, 'render_text' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_button_text' ) );
		add_settings_field( 'virtu_wa_message_template', __( 'Message Template', 'virtu-connect' ), array( $this, 'render_textarea' ), 'virtu_whatsapp', 'virtu_wa_section', array( 'id' => 'virtu_wa_message_template', 'desc' => __( 'Available variables: {product_name}, {product_price}, {product_url}', 'virtu-connect' ) ) );
	}

	/**
	 * Register Design tab settings.
	 */
	private function register_design_settings() {
		$color_fields = array( 'virtu_vc_box_bg', 'virtu_wa_box_bg', 'virtu_vc_btn_bg', 'virtu_vc_btn_text_color', 'virtu_wa_btn_bg', 'virtu_wa_btn_text_color' );
		foreach ( $color_fields as $field ) {
			register_setting( 'virtu_design', $field, array( 'sanitize_callback' => 'sanitize_hex_color' ) );
		}

		$number_fields = array( 'virtu_box_border_radius', 'virtu_btn_border_radius', 'virtu_section_font_size' );
		foreach ( $number_fields as $field ) {
			register_setting( 'virtu_design', $field, array( 'sanitize_callback' => 'absint' ) );
		}

		add_settings_section( 'virtu_design_colors', __( 'Colors', 'virtu-connect' ), '__return_false', 'virtu_design' );

		add_settings_field( 'virtu_vc_box_bg', __( 'Video Call Box Background', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_vc_box_bg' ) );
		add_settings_field( 'virtu_wa_box_bg', __( 'WhatsApp Box Background', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_wa_box_bg' ) );
		add_settings_field( 'virtu_vc_btn_bg', __( 'Video Call Button Background', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_vc_btn_bg' ) );
		add_settings_field( 'virtu_vc_btn_text_color', __( 'Video Call Button Text', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_vc_btn_text_color' ) );
		add_settings_field( 'virtu_wa_btn_bg', __( 'WhatsApp Button Background', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_wa_btn_bg' ) );
		add_settings_field( 'virtu_wa_btn_text_color', __( 'WhatsApp Button Text', 'virtu-connect' ), array( $this, 'render_color' ), 'virtu_design', 'virtu_design_colors', array( 'id' => 'virtu_wa_btn_text_color' ) );

		add_settings_section( 'virtu_design_sizes', __( 'Sizing', 'virtu-connect' ), '__return_false', 'virtu_design' );

		add_settings_field( 'virtu_box_border_radius', __( 'Box Border Radius', 'virtu-connect' ), array( $this, 'render_number' ), 'virtu_design', 'virtu_design_sizes', array( 'id' => 'virtu_box_border_radius', 'min' => 0, 'max' => 50, 'suffix' => 'px' ) );
		add_settings_field( 'virtu_btn_border_radius', __( 'Button Border Radius', 'virtu-connect' ), array( $this, 'render_number' ), 'virtu_design', 'virtu_design_sizes', array( 'id' => 'virtu_btn_border_radius', 'min' => 0, 'max' => 50, 'suffix' => 'px' ) );
		add_settings_field( 'virtu_section_font_size', __( 'Section Font Size', 'virtu-connect' ), array( $this, 'render_number' ), 'virtu_design', 'virtu_design_sizes', array( 'id' => 'virtu_section_font_size', 'min' => 10, 'max' => 30, 'suffix' => 'px' ) );
	}

	/**
	 * Render a text input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text( $args ) {
		$value = get_option( $args['id'], '' );
		$type  = isset( $args['type'] ) ? $args['type'] : 'text';
		?>
		<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php if ( ! empty( $args['desc'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['desc'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a textarea field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_textarea( $args ) {
		$value = get_option( $args['id'], '' );
		?>
		<textarea id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( ! empty( $args['desc'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['desc'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox( $args ) {
		$value = get_option( $args['id'], 'yes' );
		?>
		<label>
			<input type="hidden" name="<?php echo esc_attr( $args['id'] ); ?>" value="no" />
			<input type="checkbox" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="yes" <?php checked( $value, 'yes' ); ?> />
			<?php echo isset( $args['label'] ) ? esc_html( $args['label'] ) : ''; ?>
		</label>
		<?php
	}

	/**
	 * Render a select dropdown.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_select( $args ) {
		$value = get_option( $args['id'], '' );
		?>
		<select id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>">
			<?php foreach ( $args['options'] as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render a number input field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_number( $args ) {
		$value  = get_option( $args['id'], '' );
		$min    = isset( $args['min'] ) ? $args['min'] : 0;
		$max    = isset( $args['max'] ) ? $args['max'] : 100;
		$suffix = isset( $args['suffix'] ) ? $args['suffix'] : '';
		?>
		<input type="number" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" class="small-text" />
		<?php if ( $suffix ) : ?>
			<span class="description"><?php echo esc_html( $suffix ); ?></span>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a color picker field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_color( $args ) {
		$value = get_option( $args['id'], '' );
		?>
		<input type="text" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="virtu-color-picker" data-default-color="<?php echo esc_attr( $value ); ?>" />
		<?php
	}
}
