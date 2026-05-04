<?php
/**
 * Video Call settings tab view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'virtu_videocall' );
	do_settings_sections( 'virtu_videocall' );
	submit_button();
	?>
</form>
