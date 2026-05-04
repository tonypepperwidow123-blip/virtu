<?php
/**
 * Modal form template.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'yes' !== get_option( 'virtu_vc_enabled', 'yes' ) ) {
	return;
}
?>
<div id="virtu-modal-overlay" class="virtu-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="virtu-modal-title">
	<div class="virtu-modal">
		<button class="virtu-modal-close" aria-label="<?php esc_attr_e( 'Close', 'virtu-connect' ); ?>">&#x2715;</button>

		<div class="virtu-modal-header">
			<h2 id="virtu-modal-title"><?php echo esc_html( get_option( 'virtu_popup_title', 'Schedule a Video Call' ) ); ?></h2>
			<p><?php echo esc_html( get_option( 'virtu_popup_subtitle', 'Please provide your details to continue' ) ); ?></p>
		</div>

		<div class="virtu-modal-product-preview" id="virtu-product-preview">
			<!-- Populated via JS: product name + price -->
		</div>

		<div class="virtu-modal-body">
			<div class="virtu-field">
				<label for="virtu_name"><?php esc_html_e( 'Name', 'virtu-connect' ); ?> <span>*</span></label>
				<input type="text" id="virtu_name" name="virtu_name" placeholder="<?php esc_attr_e( 'Enter your name', 'virtu-connect' ); ?>">
			</div>
			<div class="virtu-field">
				<label for="virtu_email"><?php esc_html_e( 'Email', 'virtu-connect' ); ?> <span>*</span></label>
				<input type="email" id="virtu_email" name="virtu_email" placeholder="<?php esc_attr_e( 'Enter your email', 'virtu-connect' ); ?>">
			</div>
			<div class="virtu-field">
				<label for="virtu_phone"><?php esc_html_e( 'Mobile Number', 'virtu-connect' ); ?> <span>*</span></label>
				<input type="tel" id="virtu_phone" name="virtu_phone" placeholder="<?php esc_attr_e( 'Enter your mobile number', 'virtu-connect' ); ?>">
			</div>
			<?php if ( get_option( 'virtu_form_show_date', 'yes' ) === 'yes' ) : ?>
			<div class="virtu-field">
				<label for="virtu_date"><?php esc_html_e( 'Preferred Date', 'virtu-connect' ); ?></label>
				<input type="date" id="virtu_date" name="virtu_date">
			</div>
			<?php endif; ?>
			<?php if ( get_option( 'virtu_form_show_time', 'yes' ) === 'yes' ) : ?>
			<div class="virtu-field">
				<label for="virtu_time"><?php esc_html_e( 'Preferred Time', 'virtu-connect' ); ?></label>
				<input type="time" id="virtu_time" name="virtu_time">
			</div>
			<?php endif; ?>
			<?php if ( get_option( 'virtu_form_show_message', 'yes' ) === 'yes' ) : ?>
			<div class="virtu-field">
				<label for="virtu_message"><?php esc_html_e( 'Message (Optional)', 'virtu-connect' ); ?></label>
				<textarea id="virtu_message" name="virtu_message" rows="3" placeholder="<?php esc_attr_e( 'Any specific requirements?', 'virtu-connect' ); ?>"></textarea>
			</div>
			<?php endif; ?>
		</div>

		<div class="virtu-modal-footer">
			<button id="virtu-submit-btn" class="virtu-submit-btn"><?php esc_html_e( 'Submit Request', 'virtu-connect' ); ?></button>
			<p class="virtu-form-msg" aria-live="polite"></p>
		</div>
	</div>
</div>
