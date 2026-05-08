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

<?php
// ─── Test Email Box ───────────────────────────────────────────────────────────
?>
<div style="background:#fff;border:1px solid #ddd;border-radius:6px;padding:20px 24px;max-width:700px;margin-top:20px;">
	<h2 style="margin-top:0;font-size:16px;">🧪 Test Email Delivery</h2>
	<p style="color:#555;margin-bottom:16px;">
		Send a test email right now to confirm delivery is working. The result will appear below <strong>and</strong> be logged to the Email Delivery Log.
	</p>

	<p>
		<button type="button" id="virtu-test-admin-email" class="button button-primary" style="margin-right:10px;">
			📨 Test Admin Notification
		</button>
		<button type="button" id="virtu-test-customer-email" class="button button-secondary">
			📩 Test Customer Auto-Reply
		</button>
	</p>

	<div id="virtu-test-email-result" style="display:none;margin-top:14px;padding:12px 16px;border-radius:4px;font-size:13px;line-height:1.6;"></div>

	<p style="margin-top:12px;margin-bottom:0;font-size:12px;color:#888;">
		Both test emails are sent to <strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong>. Check your inbox <em>and</em> spam folder.
	</p>
</div>

<?php
// ─── Email Delivery Log Viewer ────────────────────────────────────────────────
$log_file = VIRTU_PATH . 'virtu-error.log';
?>
<div style="background:#fff;border:1px solid #ddd;border-radius:6px;padding:20px 24px;max-width:700px;margin-top:20px;">
	<h2 style="margin-top:0;font-size:16px;">📋 Email Delivery Log</h2>
	<p style="color:#555;margin-bottom:12px;">
		Every email attempt (success or failure) is logged here. This is the fastest way to find out exactly why an email is not being delivered.
	</p>

	<p>
		<button type="button" id="virtu-refresh-log" class="button button-secondary" style="margin-right:8px;">🔄 Refresh Log</button>
		<button type="button" id="virtu-clear-log" class="button" style="color:#a00;">🗑️ Clear Log</button>
	</p>

	<div id="virtu-log-output" style="margin-top:14px;background:#1e1e1e;color:#d4d4d4;font-family:monospace;font-size:12px;line-height:1.7;padding:14px 16px;border-radius:4px;max-height:320px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;">
		<?php
		if ( file_exists( $log_file ) ) {
			$log_content = file_get_contents( $log_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$log_lines   = array_filter( explode( PHP_EOL, $log_content ) );
			if ( empty( $log_lines ) ) {
				echo '<span style="color:#888;">No log entries yet. Send a test email to start logging.</span>';
			} else {
				// Show latest 50 lines, newest at top.
				$log_lines = array_reverse( array_slice( $log_lines, -50 ) );
				foreach ( $log_lines as $line ) {
					$color = '#d4d4d4';
					if ( strpos( $line, 'SUCCESS' ) !== false ) {
						$color = '#4ec9b0';
					} elseif ( strpos( $line, 'FAILED' ) !== false || strpos( $line, 'Error' ) !== false || strpos( $line, 'SKIPPED' ) !== false ) {
						$color = '#f48771';
					} elseif ( strpos( $line, 'Attempting' ) !== false || strpos( $line, 'Falling back' ) !== false ) {
						$color = '#dcdcaa';
					}
					echo '<span style="color:' . esc_attr( $color ) . ';">' . esc_html( $line ) . '</span>' . "\n";
				}
			}
		} else {
			echo '<span style="color:#888;">No log file found yet. Send a test email to create it.</span>';
		}
		?>
	</div>

	<p style="margin-top:10px;margin-bottom:0;font-size:11px;color:#aaa;">
		Log file: <code><?php echo esc_html( $log_file ); ?></code>
	</p>
</div>

<script type="text/javascript">
(function($) {
	'use strict';

	var testNonce = '<?php echo esc_js( wp_create_nonce( 'virtu_test_email_nonce' ) ); ?>';
	var logNonce  = '<?php echo esc_js( wp_create_nonce( 'virtu_log_action_nonce' ) ); ?>';

	// ── Test Email ──────────────────────────────────────────────────────────
	function sendTestEmail(emailType, $btn) {
		var origText = $btn.text();
		var $result  = $('#virtu-test-email-result');

		$btn.prop('disabled', true).text('Sending…');
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
				$btn.prop('disabled', false).text(origText);
				$result.show();
				if (response.success) {
					$result.css({background:'#edfbee', border:'1px solid #5cb85c', color:'#2d6a2d'})
					       .html('✅ ' + response.data);
				} else {
					$result.css({background:'#fdf2f2', border:'1px solid #d9534f', color:'#7a1a1a'})
					       .html('❌ ' + response.data);
				}
				// Auto-refresh the log after a test.
				setTimeout(refreshLog, 1200);
			},
			error: function(xhr) {
				$btn.prop('disabled', false).text(origText);
				$result.show()
				       .css({background:'#fdf2f2', border:'1px solid #d9534f', color:'#7a1a1a'})
				       .html('❌ AJAX error (HTTP ' + xhr.status + '). Is the plugin active on this site?');
			}
		});
	}

	// ── Log Actions ─────────────────────────────────────────────────────────
	function refreshLog() {
		$.ajax({
			url:  ajaxurl,
			type: 'POST',
			data: { action: 'virtu_read_log', nonce: logNonce },
			success: function(response) {
				if (response.success) {
					$('#virtu-log-output').html(response.data);
					$('#virtu-log-output').scrollTop(0);
				}
			}
		});
	}

	function clearLog() {
		if (!confirm('Clear the entire email delivery log?')) { return; }
		$.ajax({
			url:  ajaxurl,
			type: 'POST',
			data: { action: 'virtu_clear_log', nonce: logNonce },
			success: function() { refreshLog(); }
		});
	}

	// ── Bind ────────────────────────────────────────────────────────────────
	$(document).ready(function() {
		$('#virtu-test-admin-email').on('click',    function() { sendTestEmail('admin', $(this)); });
		$('#virtu-test-customer-email').on('click', function() { sendTestEmail('customer', $(this)); });
		$('#virtu-refresh-log').on('click', refreshLog);
		$('#virtu-clear-log').on('click',   clearLog);
	});

})(jQuery);
</script>
