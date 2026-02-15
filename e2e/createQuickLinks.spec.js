const { faker } = require('@faker-js/faker');
const { test, expect } = require('./fixtures');

const postSlug = () => {
	const parts = [faker.lorem.slug(), faker.lorem.slug(), faker.number.int()];
	return parts.join('-');
};

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.number.int()];
	return parts.join(' ');
};

test.describe('Quick Links', () => {
	const subMenuTitle = 'Add Page Link';
	const publishSlug = postSlug();
	const draftTitle = postTitle();
	const longTitle = 'Super Long Title Way Too Long';
	const draftSlug = 'draft-' + postSlug();

	test('supports Add Page Link modal with slug generation, publish, and draft', async ({
		page,
		baseURL,
		enablePrettyPermalinks,
		hoverWpMenuItem,
	}) => {
		const linkedUrl = baseURL + '/?4';

		enablePrettyPermalinks();

		await page.goto('/wp-admin/');
		await expect(page).toHaveURL(/\/wp-admin\/$/);

		const subMenuItem = page.locator('#menu-pages').getByText(subMenuTitle);

		// Submenu item starts hidden (positioned offscreen via CSS).
		await expect(subMenuItem).not.toBeInViewport();

		// Submenu item is visible on hover.
		await hoverWpMenuItem('#menu-pages');
		await expect(subMenuItem).toBeVisible();

		// Modal starts hidden.
		await expect(page.locator('#plt-quick-add')).toBeHidden();

		// Modal is visible after clicking submenu item.
		await subMenuItem.click();
		await expect(page.locator('#plt-quick-add')).toBeVisible();

		// Modal is closed after clicking the close button.
		await page.locator('.ui-dialog-titlebar-close').first().click();
		await expect(page.locator('#plt-quick-add')).toBeHidden();

		// Modal is closed after clicking outside, then reopened.
		await subMenuItem.click();
		await expect(page.locator('#plt-quick-add')).toBeVisible();
		await page.mouse.click(1, 1);
		await expect(page.locator('#plt-quick-add')).toBeHidden();
		await subMenuItem.click({ force: true });
		await expect(page.locator('#plt-quick-add')).toBeVisible();

		// Fields start empty.
		await expect(
			page.locator('#plt-quick-add input[name="title"]')
		).toHaveValue('');
		await expect(
			page.locator('#plt-quick-add input[name="url"]')
		).toHaveValue('');
		await expect(
			page.locator('#plt-quick-add input[name="slug"]')
		).toHaveValue('');

		// Save and publish start disabled.
		await expect(page.locator('#plt-quick-add-publish')).toBeDisabled();
		await expect(page.locator('#plt-quick-add-save')).toBeDisabled();

		// Slug is populated as title is typed.
		const titleInput = page.locator('#plt-quick-add input[name="title"]');
		await titleInput.pressSequentially('Short Title');
		await expect(
			page.locator('#plt-quick-add input[name="slug"]')
		).toHaveAttribute('placeholder', 'short-title');
		await titleInput.pressSequentially(' link');
		await expect(
			page.locator('#plt-quick-add input[name="slug"]')
		).toHaveAttribute('placeholder', 'short-title-link');

		// Length warning not visible for short slugs.
		await expect(
			page.locator('#plt-quick-add .short-url-message')
		).toBeHidden();

		// Buttons enabled after URL and title are provided.
		const urlInput = page.locator('#plt-quick-add input[name="url"]');
		await urlInput.fill(linkedUrl);
		await urlInput.dispatchEvent('keyup');
		await expect(page.locator('#plt-quick-add-save')).toBeEnabled();
		await expect(page.locator('#plt-quick-add-publish')).toBeEnabled();

		// Buttons disabled again if the title is emptied.
		await titleInput.fill('');
		await titleInput.dispatchEvent('keyup');
		await expect(page.locator('#plt-quick-add-publish')).toBeDisabled();
		await expect(page.locator('#plt-quick-add-save')).toBeDisabled();

		// Buttons re-enabled after the title is re-populated.
		await titleInput.pressSequentially('Short Two');
		await expect(
			page.locator('#plt-quick-add input[name="slug"]')
		).toHaveAttribute('placeholder', 'short-two');
		await expect(page.locator('#plt-quick-add-save')).toBeEnabled();
		await expect(page.locator('#plt-quick-add-publish')).toBeEnabled();

		// Shows a warning for long slugs.
		await titleInput.fill('');
		await titleInput.dispatchEvent('keyup');
		await titleInput.pressSequentially(longTitle);
		await expect(
			page.locator('#plt-quick-add .short-url-message')
		).toBeVisible();

		// Gives feedback when a new link is published.
		await page.locator('#plt-quick-add input[name="slug"]').fill(publishSlug);
		await page.locator('#plt-quick-add-publish').click();
		await expect(
			page.locator('#plt-quick-add').getByText('New page link published!')
		).toBeVisible();

		// Published short URL redirects to the custom URL.
		const response = await page.request.get(`/${publishSlug}/`, {
			maxRedirects: 0,
			failOnStatusCode: false,
		});
		expect(response.status()).toBe(301);
		expect(response.headers()['location']).toBe(linkedUrl);

		// Gives feedback when a new link is saved as a draft.
		await titleInput.fill('');
		await titleInput.dispatchEvent('keyup');
		await titleInput.pressSequentially(draftTitle);
		await page.locator('#plt-quick-add input[name="slug"]').fill(draftSlug);
		await urlInput.fill(linkedUrl);
		await urlInput.dispatchEvent('keyup');
		await expect(page.locator('#plt-quick-add-save')).toBeEnabled();
		await page.locator('#plt-quick-add-save').click();
		await expect(
			page.locator('#plt-quick-add').getByText('Page link draft saved!')
		).toBeVisible();
	});
});
