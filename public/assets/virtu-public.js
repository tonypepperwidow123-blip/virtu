/**
 * VirtuConnect Public Scripts
 *
 * Vanilla JS — no jQuery dependency.
 *
 * @package VirtuConnect
 */
(function() {
	'use strict';

	var overlay        = document.getElementById('virtu-modal-overlay');
	var openBtn        = document.getElementById('virtu-open-modal');
	var closeBtn       = overlay ? overlay.querySelector('.virtu-modal-close') : null;
	var submitBtn      = document.getElementById('virtu-submit-btn');
	var formMsg        = overlay ? overlay.querySelector('.virtu-form-msg') : null;
	var waBtn          = document.getElementById('virtu-wa-btn');
	var productPreview = document.getElementById('virtu-product-preview');

	// Open modal.
	if (openBtn && overlay) {
		openBtn.addEventListener('click', function() {
			overlay.classList.add('virtu-modal-active');
			document.body.style.overflow = 'hidden';
			populateProductPreview();
			setTimeout(function() {
				var first = overlay.querySelector('input');
				if (first) first.focus();
			}, 100);
		});
	}

	// Close modal — overlay click.
	if (overlay) {
		overlay.addEventListener('click', function(e) {
			if (e.target === overlay) closeModal();
		});
	}

	// Close modal — close button.
	if (closeBtn) {
		closeBtn.addEventListener('click', closeModal);
	}

	// Close modal — ESC key.
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && overlay) closeModal();
	});

	function closeModal() {
		if (!overlay) return;
		overlay.classList.remove('virtu-modal-active');
		document.body.style.overflow = '';
		resetForm();
	}

	function resetForm() {
		if (!overlay) return;
		var fields = overlay.querySelectorAll('input, textarea');
		for (var i = 0; i < fields.length; i++) {
			fields[i].value = '';
		}
		if (formMsg) {
			formMsg.textContent = '';
			formMsg.className = 'virtu-form-msg';
		}
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.textContent = 'Submit Request';
		}
	}

	function populateProductPreview() {
		if (!productPreview || !window.virtucConnect) return;
		var d = window.virtucConnect;
		productPreview.innerHTML =
			'<div class="virtu-preview-name">' + escapeHtml(d.product_name) + '</div>' +
			'<div class="virtu-preview-price">\u20B9' + escapeHtml(d.product_price) + '</div>';
	}

	function escapeHtml(str) {
		if (!str) return '';
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}

	// AJAX Form Submit.
	if (submitBtn) {
		submitBtn.addEventListener('click', function() {
			var d = window.virtucConnect;
			if (!d) return;

			var nameEl    = document.getElementById('virtu_name');
			var emailEl   = document.getElementById('virtu_email');
			var phoneEl   = document.getElementById('virtu_phone');
			var dateEl    = document.getElementById('virtu_date');
			var timeEl    = document.getElementById('virtu_time');
			var messageEl = document.getElementById('virtu_message');

			var name    = nameEl ? nameEl.value.trim() : '';
			var email   = emailEl ? emailEl.value.trim() : '';
			var phone   = phoneEl ? phoneEl.value.trim() : '';
			var date    = dateEl ? dateEl.value : '';
			var time    = timeEl ? timeEl.value : '';
			var message = messageEl ? messageEl.value.trim() : '';

			// Validate.
			if (!name || !email || !phone) {
				showMsg(d.strings.required, 'error');
				return;
			}

			var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				showMsg(d.strings.invalid_email, 'error');
				return;
			}

			submitBtn.disabled = true;
			submitBtn.textContent = d.strings.submitting;

			var formData = new FormData();
			formData.append('action', 'virtu_submit_form');
			formData.append('nonce', d.nonce);
			formData.append('virtu_name', name);
			formData.append('virtu_email', email);
			formData.append('virtu_phone', phone);
			formData.append('virtu_date', date);
			formData.append('virtu_time', time);
			formData.append('virtu_message', message);
			formData.append('product_id', d.product_id);
			formData.append('product_name', d.product_name);
			formData.append('product_url', d.product_url);
			formData.append('product_price', d.product_price);

			fetch(d.ajax_url, { method: 'POST', body: formData })
				.then(function(res) { return res.json(); })
				.then(function(data) {
					if (data.success) {
						showMsg(data.data.message, 'success');
						submitBtn.textContent = '\u2713 Submitted';
						setTimeout(closeModal, 3000);
					} else {
						showMsg(data.data || d.strings.error, 'error');
						submitBtn.disabled = false;
						submitBtn.textContent = 'Submit Request';
					}
				})
				.catch(function() {
					showMsg(d.strings.error, 'error');
					submitBtn.disabled = false;
					submitBtn.textContent = 'Submit Request';
				});
		});
	}

	function showMsg(msg, type) {
		if (!formMsg) return;
		formMsg.textContent = msg;
		formMsg.className = 'virtu-form-msg virtu-' + type;
	}

	// WhatsApp redirect.
	if (waBtn) {
		waBtn.addEventListener('click', function() {
			var d = window.virtucConnect;
			if (!d || !d.wa_number) {
				alert('WhatsApp number not configured.');
				return;
			}
			var msg = d.wa_template
				.replace('{product_name}', d.product_name)
				.replace('{product_price}', d.product_price)
				.replace('{product_url}', d.product_url);
			var url = 'https://wa.me/' + d.wa_number + '?text=' + encodeURIComponent(msg);
			window.open(url, '_blank');
		});
	}

})();
