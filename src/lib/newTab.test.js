/**
 * @jest-environment jsdom
 */

import { handleClicks, handleExistingElements } from './newTab';

const $ = document.getElementById.bind(document);

const HTML = `
<main>
	<div>
		<a id="bare" href="https://example.com/bare-link#new_tab">Bare Link</a>
	</div>
	<div>
		<a id="nested" href="https://example.com/nested-html#new_tab"><div><h2 id="h2">Nested HTML</h2></div></a>
	</div>
	<div id="no-parent-link"></div>
</main>
`;

const expectTransformed = id => {
	return expectOpensInNewTab(id) && expectHasRelNoopener(id);
};

const expectNotTransformed = id => {
	return expectDoesNotOpenInNewTab(id) && expectDoesNotHaveRelNoopener(id);
};

const expectOpensInNewTab = id =>
	expect($(id).getAttribute('target')).toEqual('_blank');
const expectDoesNotOpenInNewTab = id =>
	expect($(id).getAttribute('target')).not.toEqual('_blank');
const expectHasRelNoopener = id =>
	expect(
		$(id)
			.getAttribute('rel')
			.indexOf('noopener') > 0
	);
const expectDoesNotHaveRelNoopener = id =>
	expect(
		$(id)
			.getAttribute('rel')
			.indexOf('noopener') === -1
	);

const LATER_HTML = `
	<div>
		<a id="later" href="htps://example.com/later/#new_tab">Later</a>
	</div>
`;

describe('Click handler', () => {
	let removeClickListener;

	beforeEach(() => {
		document.body.innerHTML = HTML;
		removeClickListener = handleClicks();
	});

	afterEach(() => {
		removeClickListener();
	});

	it('Handles nested content', () => {
		$('h2').click();
		expectTransformed('nested');
	});

	it('Handles regular text links', () => {
		$('bare').click();
		expectTransformed('bare');
	});

	it('Handles content added later', () => {
		document.body.innerHTML = HTML + LATER_HTML;
		$('later').click();
		expectTransformed('later');
	});

	it('Handles clicks on things with no parent link', () => {
		document.body.innerHTML = HTML;
		expect(() => $('no-parent-link').click()).not.toThrow();
	});
});

describe('Load handler', () => {
	beforeEach(() => {
		document.body.innerHTML = HTML;
		handleExistingElements();
		handleClicks();
		document.body.innerHTML = document.body.innerHTML + LATER_HTML;
	});

	it('Has converted links present on load, but not ones added later', () => {
		expectTransformed('nested');
		expectTransformed('bare');
		expectNotTransformed('later');
	});

	it('Lets the click handler handle new ones', () => {
		$('later').click();
		expectTransformed('later');
	});
});
