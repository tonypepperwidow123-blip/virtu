<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Drops the custom database table and removes all plugin options.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}virtu_leads" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

$options = array(
	'virtu_enabled',
	'virtu_display_hook',
	'virtu_display_priority',
	'virtu_section_title',
	'virtu_vc_enabled',
	'virtu_vc_title',
	'virtu_vc_description',
	'virtu_vc_button_text',
	'virtu_form_show_date',
	'virtu_form_show_time',
	'virtu_form_show_message',
	'virtu_popup_title',
	'virtu_popup_subtitle',
	'virtu_admin_email',
	'virtu_email_subject',
	'virtu_auto_reply',
	'virtu_auto_reply_subject',
	'virtu_auto_reply_message',
	'virtu_wa_enabled',
	'virtu_wa_number',
	'virtu_wa_title',
	'virtu_wa_description',
	'virtu_wa_button_text',
	'virtu_wa_message_template',
	'virtu_vc_box_bg',
	'virtu_wa_box_bg',
	'virtu_vc_btn_bg',
	'virtu_vc_btn_text_color',
	'virtu_wa_btn_bg',
	'virtu_wa_btn_text_color',
	'virtu_box_border_radius',
	'virtu_btn_border_radius',
	'virtu_section_font_size',
	'virtu_db_version',
);

foreach ( $options as $opt ) {
	delete_option( $opt );
}
