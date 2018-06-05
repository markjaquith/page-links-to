(d => {
	// Makes an anchor element open in a new tab.
	const newTab = el => {
		const newTabRegex = /#new_tab$/;
		if (el.tagName === 'A' && newTabRegex.test(el.getAttribute('href'))) {
			el.setAttribute('target', '_blank');
			el.setAttribute('href', el.getAttribute('href').replace(newTabRegex, ''));
		}
	};

	// Immediately attach a click handler.
	d.addEventListener('click', e => newTab(e.target));

	// On page load, convert any existing new tab links.
	d.addEventListener('DOMContentLoaded', () => {
		[...d.getElementsByTagName('A')].forEach(newTab);
	});
})(document);
