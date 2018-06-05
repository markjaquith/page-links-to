// Grab the right copy of jQuery. Plugins and themes that
// enqueue a custom version of jQuery can go to hell.

(window => {
	const $ = window.jQueryWP || window.jQuery;

	// Makes an anchor element open in a new tab.
	const newTab = el => {
		const $el = $(el);

		$el
			.attr('href', $el.attr('href').replace(/#new_tab/, ''))
			.attr('target', '_blank');
	};

	// Immediately attach a click handler.
	if (typeof $.fn.on === 'function') {
		$(document).on('click', 'a[href$="#new_tab"]', function() {
			newTab(this);
		});
	} else {
		console &&
			console.log &&
			console.log(
				'Page Links To: Some other code has overridden the WordPress copy of jQuery. This is bad. Because of this, Page Links to cannot open links in a new window.'
			);
	}

	// On document ready, transform all new tab anchors.
	$(() => {
		$('a[href$="new_tab"]').each((i, el) => newTab(el));
	});
})(window);
