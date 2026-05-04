<?php
/**
 * Leads data management.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Leads
 *
 * Provides static methods for querying, updating, deleting, and exporting leads.
 */
class Virtu_Leads {

	/**
	 * Get leads from the database with optional filtering and pagination.
	 *
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 *     @type int    $per_page Number of results per page. Default 20.
	 *     @type int    $page     Current page number. Default 1.
	 *     @type string $search   Search term to match against customer name or email.
	 *     @type string $status   Filter by lead status (new, contacted, closed).
	 *     @type string $orderby  Column to order by. Default 'created_at'.
	 *     @type string $order    Sort direction (ASC or DESC). Default 'DESC'.
	 * }
	 * @return array {
	 *     @type array $items      Array of lead objects.
	 *     @type int   $total      Total number of matching leads.
	 *     @type int   $total_pages Total pages available.
	 * }
	 */
	public static function get_leads( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'search'   => '',
			'status'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);

		$args       = wp_parse_args( $args, $defaults );
		$table_name = $wpdb->prefix . 'virtu_leads';

		// Whitelist orderby columns.
		$allowed_orderby = array( 'id', 'product_name', 'customer_name', 'customer_email', 'status', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		// Build WHERE clauses.
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '( customer_name LIKE %s OR customer_email LIKE %s )';
			$values[] = $like;
			$values[] = $like;
		}

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = $args['status'];
		}

		$where_clause = implode( ' AND ', $where );

		// Count total.
		$count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
		if ( ! empty( $values ) ) {
			$count_query = $wpdb->prepare( $count_query, $values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$total = (int) $wpdb->get_var( $count_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Fetch results.
		$offset       = ( $args['page'] - 1 ) * $args['per_page'];
		$select_query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$query_values = array_merge( $values, array( $args['per_page'], $offset ) );
		$items        = $wpdb->get_results( $wpdb->prepare( $select_query, $query_values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return array(
			'items'       => $items ? $items : array(),
			'total'       => $total,
			'total_pages' => ceil( $total / $args['per_page'] ),
		);
	}

	/**
	 * Update the status of a lead.
	 *
	 * @param int    $id     Lead ID.
	 * @param string $status New status value.
	 * @return bool True on success, false on failure.
	 */
	public static function update_lead_status( $id, $status ) {
		global $wpdb;

		$result = $wpdb->update(
			$wpdb->prefix . 'virtu_leads',
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Delete a lead by ID.
	 *
	 * @param int $id Lead ID.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_lead( $id ) {
		global $wpdb;

		$result = $wpdb->delete(
			$wpdb->prefix . 'virtu_leads',
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Export all leads as a downloadable CSV file.
	 *
	 * Outputs CSV headers and data directly, then exits.
	 */
	public static function export_csv() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'virtu_leads';
		$leads      = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$filename = 'virtu-connect-leads-' . gmdate( 'Y-m-d' ) . '.csv';

		// Set download headers.
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// BOM for UTF-8.
		fwrite( $output, "\xEF\xBB\xBF" );

		// CSV header row.
		fputcsv( $output, array(
			'ID',
			'Product',
			'Customer Name',
			'Email',
			'Phone',
			'Preferred Date',
			'Preferred Time',
			'Message',
			'Status',
			'Submitted At',
		) );

		// Data rows.
		if ( $leads ) {
			foreach ( $leads as $lead ) {
				fputcsv( $output, array(
					$lead['id'],
					$lead['product_name'],
					$lead['customer_name'],
					$lead['customer_email'],
					$lead['customer_phone'],
					$lead['preferred_date'],
					$lead['preferred_time'],
					$lead['message'],
					$lead['status'],
					$lead['created_at'],
				) );
			}
		}

		fclose( $output );
		exit;
	}
}
