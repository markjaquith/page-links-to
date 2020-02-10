// Makes an anchor element open in a new tab.
export const handleNewTab = el => {
	const newTabRegex = /#new_tab$/;
	if (el.tagName === 'A' && newTabRegex.test(el.getAttribute('href'))) {
		el.setAttribute('target', '_blank');
		el.setAttribute('href', el.getAttribute('href').replace(newTabRegex, ''));
	}
};

const listener = event => handleNewTab(event.target.closest('a'));

export const handleClicks = () => {
	document.addEventListener('click', listener);
	return () => document.removeEventListener('click', listener);
};

export const handleNewTabs = () => {
	const anchors = document.getElementsByTagName('A');
	for (let i = 0; i < anchors.length; i++) {
		handleNewTab(anchors[i]);
	}
};

export const handleLoad = () =>
	document.addEventListener('DOMContentLoaded', handleNewTabs);
