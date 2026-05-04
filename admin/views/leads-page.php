<?php
/**
 * Leads management page view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$table = new Virtu_Leads_Table();
$table->prepare_items();

$export_url = wp_nonce_url(
	admin_url( 'admin.php?page=virtu-connect-leads&virtu_export=csv' ),
	'virtu_export_csv'
);
?>
<div class="wrap virtu-admin-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'VirtuConnect — Leads', 'virtu-connect' ); ?></h1>
	<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'virtu-connect' ); ?></a>
	<hr class="wp-header-end">

	<form method="get">
		<input type="hidden" name="page" value="virtu-connect-leads" />
		<?php $table->search_box( __( 'Search Leads', 'virtu-connect' ), 'virtu-search' ); ?>
	</form>

	<form method="post">
		<?php $table->display(); ?>
	</form>
</div>
