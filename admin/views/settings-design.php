<?php
/**
 * Design settings tab view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'virtu_design' );
	do_settings_sections( 'virtu_design' );
	submit_button();
	?>
</form>
