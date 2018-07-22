jQuery($ => {
	const section = $('#cws-links-to-custom-section');
	const input = $('input[type=radio]', '#page-links-to');

	if (input.filter('input[value="wp"]').prop('checked')) {
		section.fadeTo(1, 0).hide();
	}

	input.change(function() {
		const $this = $(this);

		if ($this.val() === 'wp') {
			section.fadeTo('fast', 0, () => {
				section.slideUp();
			});
		} else {
			section.slideDown('fast', () => {
				section.fadeTo('fast', 1, () => {
					const $linksTo = $('#cws-links-to');
					$linksTo.focus().val($linksTo.val());
				});
			});
		}
	});
});
