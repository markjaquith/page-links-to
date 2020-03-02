// Makes an anchor element open in a new tab.
export const handleElement = el => {
	const newTabRegex = /#new_tab/;
	if (
		el?.tagName?.toUpperCase() === 'A' &&
		newTabRegex.test(el?.getAttribute('href'))
	) {
		el.setAttribute('target', '_blank');
		el.setAttribute('href', el.getAttribute('href').replace(newTabRegex, ''));
	}
};

const listener = event => handleElement(event.target.closest('a'));

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
