/**
 * VirtuConnect Admin Scripts
 *
 * @package VirtuConnect
 */
(function($) {
	'use strict';

	$(document).ready(function() {

		// Initialize color pickers.
		if ($.fn.wpColorPicker) {
			$('.virtu-color-picker').wpColorPicker();
		}

		// Handle lead status change via AJAX.
		$(document).on('change', '.virtu-status-dropdown', function() {
			var $select = $(this);
			var leadId  = $select.data('lead-id');
			var status  = $select.val();

			$select.prop('disabled', true);

			$.ajax({
				url:  virtuAdmin.ajax_url,
				type: 'POST',
				data: {
					action:  'virtu_update_lead_status',
					nonce:   virtuAdmin.nonce,
					lead_id: leadId,
					status:  status
				},
				success: function(response) {
					$select.prop('disabled', false);
					if (!response.success) {
						alert(virtuAdmin.strings.status_error);
					}
				},
				error: function() {
					$select.prop('disabled', false);
					alert(virtuAdmin.strings.status_error);
				}
			});
		});

	});

})(jQuery);
