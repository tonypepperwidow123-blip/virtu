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

<hr />
<div class="virtu-test-email-box" style="background:#fff;border:1px solid #ddd;border-radius:6px;padding:20px 24px;max-width:700px;margin-top:20px;">
	<h2 style="margin-top:0;font-size:16px;"><?php esc_html_e( '🧪 Test Email Delivery', 'virtu-connect' ); ?></h2>
	<p style="color:#555;margin-bottom:16px;"><?php esc_html_e( 'Use these buttons to send a test email right now. Results will appear below and will also be written to virtu-error.log inside the plugin folder.', 'virtu-connect' ); ?></p>

	<p>
		<button type="button" id="virtu-test-admin-email" class="button button-primary" style="margin-right:10px;">
			<?php esc_html_e( '📨 Test Admin Notification Email', 'virtu-connect' ); ?>
		</button>
		<button type="button" id="virtu-test-customer-email" class="button button-secondary">
			<?php esc_html_e( '📩 Test Customer Auto-Reply Email', 'virtu-connect' ); ?>
		</button>
	</p>

	<div id="virtu-test-email-result" style="display:none;margin-top:14px;padding:12px 16px;border-radius:4px;font-size:13px;line-height:1.6;"></div>

	<p style="margin-top:16px;margin-bottom:0;font-size:12px;color:#888;">
		<?php esc_html_e( 'Both test emails are sent to the WordPress admin email address. Check your inbox and spam folder. If delivery fails, read virtu-error.log for the exact error.', 'virtu-connect' ); ?>
	</p>
</div>

<script type="text/javascript">
(function($) {
	'use strict';

	var testNonce = '<?php echo esc_js( wp_create_nonce( 'virtu_test_email_nonce' ) ); ?>';

	function sendTestEmail(emailType, $btn) {
		var $result = $('#virtu-test-email-result');
		$btn.prop('disabled', true).text('Sending...');
		$result.hide();

		$.ajax({
			url:  ajaxurl,
			type: 'POST',
			data: {
				action:     'virtu_send_test_email',
				nonce:      testNonce,
				email_type: emailType
			},
			success: function(response) {
				$btn.prop('disabled', false).text(emailType === 'admin' ? '📨 Test Admin Notification Email' : '📩 Test Customer Auto-Reply Email');
				$result.show();
				if (response.success) {
					$result.css({'background': '#edfbee', 'border': '1px solid #5cb85c', 'color': '#2d6a2d'})
						   .html('✅ ' + response.data);
				} else {
					$result.css({'background': '#fdf2f2', 'border': '1px solid #d9534f', 'color': '#7a1a1a'})
						   .html('❌ ' + response.data);
				}
			},
			error: function(xhr) {
				$btn.prop('disabled', false).text(emailType === 'admin' ? '📨 Test Admin Notification Email' : '📩 Test Customer Auto-Reply Email');
				$result.show().css({'background': '#fdf2f2', 'border': '1px solid #d9534f', 'color': '#7a1a1a'})
					   .html('❌ AJAX request failed (HTTP ' + xhr.status + '). Check if the plugin is active.');
			}
		});
	}

	$(document).ready(function() {
		$('#virtu-test-admin-email').on('click', function() {
			sendTestEmail('admin', $(this));
		});
		$('#virtu-test-customer-email').on('click', function() {
			sendTestEmail('customer', $(this));
		});
	});
})(jQuery);
</script>
