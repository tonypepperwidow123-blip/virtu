<?php
/**
 * Email settings tab view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'virtu_email' );
	do_settings_sections( 'virtu_email' );
	submit_button();
	?>
</form>
