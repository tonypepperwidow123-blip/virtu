<?php
/**
 * Leads list table for admin.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Virtu_Leads_Table
 *
 * Extends WP_List_Table to display leads in the admin.
 */
class Virtu_Leads_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'lead',
			'plural'   => 'leads',
			'ajax'     => false,
		) );
	}

	/**
	 * Define table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'id'             => __( '#', 'virtu-connect' ),
			'product_name'   => __( 'Product', 'virtu-connect' ),
			'customer_name'  => __( 'Customer Name', 'virtu-connect' ),
			'customer_email' => __( 'Email', 'virtu-connect' ),
			'customer_phone' => __( 'Phone', 'virtu-connect' ),
			'preferred_datetime' => __( 'Preferred Date/Time', 'virtu-connect' ),
			'status'         => __( 'Status', 'virtu-connect' ),
			'created_at'     => __( 'Submitted At', 'virtu-connect' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'id'         => array( 'id', false ),
			'customer_name' => array( 'customer_name', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Define bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'virtu-connect' ),
		);
	}

	/**
	 * Handle bulk actions.
	 */
	public function process_bulk_action() {
		if ( 'delete' !== $this->current_action() ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-leads' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'virtu-connect' ) );
		}

		$ids = isset( $_REQUEST['lead'] ) ? array_map( 'absint', (array) $_REQUEST['lead'] ) : array();

		foreach ( $ids as $id ) {
			Virtu_Leads::delete_lead( $id );
		}
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Lead row.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="lead[]" value="%d" />', absint( $item->id ) );
	}

	/**
	 * Default column handler.
	 *
	 * @param object $item        Lead row.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return absint( $item->id );
			case 'customer_email':
				return '<a href="mailto:' . esc_attr( $item->customer_email ) . '">' . esc_html( $item->customer_email ) . '</a>';
			case 'customer_phone':
				return esc_html( $item->customer_phone );
			case 'created_at':
				return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->created_at ) ) );
			default:
				return esc_html( isset( $item->$column_name ) ? $item->$column_name : '' );
		}
	}

	/**
	 * Product column.
	 *
	 * @param object $item Lead row.
	 * @return string
	 */
	public function column_product_name( $item ) {
		if ( ! empty( $item->product_url ) ) {
			return '<a href="' . esc_url( $item->product_url ) . '" target="_blank">' . esc_html( $item->product_name ) . '</a>';
		}
		return esc_html( $item->product_name );
	}

	/**
	 * Customer name column with row actions.
	 *
	 * @param object $item Lead row.
	 * @return string
	 */
	public function column_customer_name( $item ) {
		$output = '<strong>' . esc_html( $item->customer_name ) . '</strong>';
		if ( ! empty( $item->message ) ) {
			$output .= '<br><small style="color:#666;">' . esc_html( wp_trim_words( $item->message, 10 ) ) . '</small>';
		}
		return $output;
	}

	/**
	 * Preferred date/time column.
	 *
	 * @param object $item Lead row.
	 * @return string
	 */
	public function column_preferred_datetime( $item ) {
		$parts = array();
		if ( ! empty( $item->preferred_date ) && '0000-00-00' !== $item->preferred_date ) {
			$parts[] = esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->preferred_date ) ) );
		}
		if ( ! empty( $item->preferred_time ) ) {
			$parts[] = esc_html( $item->preferred_time );
		}
		return ! empty( $parts ) ? implode( ' &middot; ', $parts ) : '—';
	}

	/**
	 * Status column with inline dropdown.
	 *
	 * @param object $item Lead row.
	 * @return string
	 */
	public function column_status( $item ) {
		$statuses = array(
			'new'       => __( 'New', 'virtu-connect' ),
			'contacted' => __( 'Contacted', 'virtu-connect' ),
			'closed'    => __( 'Closed', 'virtu-connect' ),
		);

		$html = '<select class="virtu-status-dropdown" data-lead-id="' . absint( $item->id ) . '">';
		foreach ( $statuses as $value => $label ) {
			$html .= '<option value="' . esc_attr( $value ) . '" ' . selected( $item->status, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = 20;
		$current_page = $this->get_pagenum();

		$args = array(
			'per_page' => $per_page,
			'page'     => $current_page,
			'orderby'  => isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'order'    => isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		$result = Virtu_Leads::get_leads( $args );

		$this->items = $result['items'];

		$this->set_pagination_args( array(
			'total_items' => $result['total'],
			'per_page'    => $per_page,
			'total_pages' => $result['total_pages'],
		) );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}
}
