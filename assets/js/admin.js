jQuery(function($) {
	$('.imf-copy-btn').on('click', function() {
		var $btn = $(this);
		var $target = $( $btn.data('clipboard-target') );

		if ( ! $target.length ) { return; }

		var text = $target.text();
		if ( navigator.clipboard ) {
			navigator.clipboard.writeText(text).then(function() {
				$btn.text('Copied!');
				setTimeout(function() { $btn.text('Copy'); }, 2000);
			});
		} else {
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();
			document.execCommand('copy');
			$temp.remove();
			$btn.text('Copied!');
			setTimeout(function() { $btn.text('Copy'); }, 2000);
		}
	});

	$('.imf-toggle-input').on('change', function() {
		var $checkbox = $(this);
		var apId = $checkbox.data('ap-id');
		var enabled = $checkbox.is(':checked') ? 1 : 0;

		$checkbox.prop('disabled', true);

		$.post(IMF_Admin.ajax_url, {
			action: 'immens_mcp_toggle_access_point',
			id: apId,
			enabled: enabled,
			_ajax_nonce: IMF_Admin.nonce
		}, function(response) {
			$checkbox.prop('disabled', false);
			if ( ! response.success ) {
				$checkbox.prop('checked', ! $checkbox.prop('checked'));
			}
		}).fail(function() {
			$checkbox.prop('disabled', false);
			$checkbox.prop('checked', ! $checkbox.prop('checked'));
			alert('Toggle failed. Please try again.');
		});
	});

	var $permsTable = $('.imf-permissions-table');

	if ( $permsTable.length ) {
		$('.imf-enable-active-btn').on('click', function() {
			$permsTable.find('tr.imf-plugin-active').each(function() {
				$(this).find('input[type="checkbox"]').prop('checked', true);
			});
		});

		var $hideToggle = $('#imf-hide-inactive');
		$hideToggle.on('change', function() {
			if ( $(this).is(':checked') ) {
				$permsTable.find('tr.imf-plugin-inactive').hide();
			} else {
				$permsTable.find('tr.imf-plugin-inactive').show();
			}
		}).trigger('change');

		var $editForm = $permsTable.closest('form');
		if ( $editForm.length ) {
			$editForm.on('submit', function() {
				$permsTable.find('tr.imf-plugin-inactive').show();
			});
		}
	}
});
