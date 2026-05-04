<?php
/**
 * General settings tab view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'virtu_general' );
	do_settings_sections( 'virtu_general' );
	submit_button();
	?>
</form>
