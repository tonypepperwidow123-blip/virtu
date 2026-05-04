<?php
/**
 * WhatsApp settings tab view.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
	settings_fields( 'virtu_whatsapp' );
	do_settings_sections( 'virtu_whatsapp' );
	submit_button();
	?>
</form>
