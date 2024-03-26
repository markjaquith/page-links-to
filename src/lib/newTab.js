// Makes an anchor element open in a new tab.
export const handleElement = (el) => {
	const newTabRegex = /#new_tab/;
	if (
		el?.tagName?.toUpperCase() === 'A' &&
		newTabRegex.test(el?.getAttribute('href'))
	) {
		const rel = el.getAttribute('rel');

		if (!rel || rel.indexOf('noopener') < 0) {
			el.setAttribute('rel', `${rel ? rel + ' ' : ''}noopener`);
		}

		el.setAttribute('target', '_blank');
		el.setAttribute('aria-label', `${el.innerText} (opens in a new tab)`);
		el.setAttribute('href', el.getAttribute('href').replace(newTabRegex, ''));
	}
};

const listener = (event) => handleElement(event.target.closest('a'));

export const handleClicks = () => {
	document.addEventListener('click', listener);
	return () => document.removeEventListener('click', listener);
};

export const handleExistingElements = () => {
	const anchors = document.getElementsByTagName('A');
	for (const anchor of anchors) {
		handleElement(anchor);
	}
};

export const handleLoad = () =>
	document.addEventListener('DOMContentLoaded', handleExistingElements);
