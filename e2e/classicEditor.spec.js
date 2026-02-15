const { faker } = require('@faker-js/faker');
const { test, expect } = require('./fixtures');

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.number.int()];
	return parts.join(' ');
};

test.describe('Classic Editor', () => {
	const draftTitle = postTitle();
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	test.afterEach(async ({}, testInfo) => {
		const { execSync } = require('child_process');
		execSync('wp plugin deactivate classic-editor', { stdio: 'pipe' });
	});

	test('supports Page Links To meta box with custom URL, new tab checkbox, and redirect', async ({
		page,
		baseURL,
		enablePrettyPermalinks,
		activatePlugin,
	}) => {
		const linkedUrl = baseURL + '/?1';
		const linkedUrl2 = baseURL + '/?2';

		enablePrettyPermalinks();
		activatePlugin('classic-editor');

		await page.goto('/wp-admin/post-new.php?post_type=page');
		await expect(page).toHaveURL(/\/wp-admin\/post-new\.php\?post_type=page/);

		// Scroll to meta box area.
		await page.locator('#cws-links-to-choose-wp').scrollIntoViewIfNeeded();

		// Meta box is on the page.
		await expect(page.getByText('Its normal WordPress URL')).toBeVisible();

		// Starts pointing to the normal WordPress URL.
		await expect(page.locator('#cws-links-to-choose-wp')).toBeChecked();
		await expect(page.locator('#cws-links-to')).toBeHidden();

		// Does not change after saving the page.
		await page.locator('#title').fill(draftTitle);
		await page.locator('#save-post').click();
		await expect(page.getByText('Page draft updated')).toBeVisible();
		await page.locator('#cws-links-to-choose-wp').scrollIntoViewIfNeeded();
		await expect(page.locator('#cws-links-to-choose-wp')).toBeChecked();
		await expect(page.locator('#cws-links-to')).toBeHidden();

		// Shows hidden fields when custom link option enabled.
		await page.locator('#cws-links-to-choose-custom').scrollIntoViewIfNeeded();
		await page.locator('#cws-links-to-choose-custom').click();
		await expect(page.locator('#cws-links-to-choose-custom')).toBeChecked();
		await expect(page.locator('#cws-links-to-choose-wp')).not.toBeChecked();
		await expect(page.locator('#cws-links-to')).toBeVisible();
		await expect(page.locator('#cws-links-to')).toBeFocused();
		await expect(page.locator('#cws-links-to-new-tab')).not.toBeChecked();

		// Hides fields when custom link option is disabled.
		await page.locator('#cws-links-to-choose-wp').click();
		await expect(page.locator('#cws-links-to-choose-wp')).toBeChecked();
		await expect(page.locator('#cws-links-to-choose-custom')).not.toBeChecked();
		await expect(page.locator('#cws-links-to')).toBeHidden();

		// Persists a custom URL.
		await page.locator('#cws-links-to-choose-custom').scrollIntoViewIfNeeded();
		await page.locator('#cws-links-to-choose-custom').click();
		await expect(page.locator('#cws-links-to-choose-custom')).toBeChecked();
		await page.locator('#cws-links-to').fill(linkedUrl);
		await page.locator('#save-post').click();
		await expect(page.getByText('Page draft updated')).toBeVisible();
		await page.locator('#cws-links-to-choose-custom').scrollIntoViewIfNeeded();
		await expect(page.locator('#cws-links-to-choose-custom')).toBeChecked();
		await expect(page.locator('#cws-links-to')).toBeVisible();
		await expect(page.locator('#cws-links-to')).toHaveValue(linkedUrl);
		await expect(page.locator('#cws-links-to-new-tab')).not.toBeChecked();

		// Persists the new tab checkbox.
		await page.locator('#cws-links-to-new-tab').click();
		await expect(page.locator('#cws-links-to-new-tab')).toBeChecked();
		await page.locator('#save-post').click();
		await expect(page.getByText('Page draft updated')).toBeVisible();
		await page.locator('#cws-links-to').scrollIntoViewIfNeeded();
		await page.locator('#cws-links-to').fill(linkedUrl2);
		await page.locator('#cws-links-to-new-tab').click();
		await expect(page.locator('#cws-links-to-new-tab')).not.toBeChecked();
		await page.locator('#save-post').click();
		await expect(page.getByText('Page draft updated')).toBeVisible();
		await page.locator('#cws-links-to').scrollIntoViewIfNeeded();
		await expect(page.locator('#cws-links-to')).toBeVisible();
		await expect(page.locator('#cws-links-to')).toHaveValue(linkedUrl2);
		await expect(page.locator('#cws-links-to-new-tab')).not.toBeChecked();

		// Publish and verify the short URL redirects to the custom URL.
		await page.locator('#publish').click();
		await page.waitForLoadState('networkidle');

		const response = await page.request.get(`/${draftSlug}/`, {
			maxRedirects: 0,
			failOnStatusCode: false,
		});
		expect(response.status()).toBe(301);
		expect(response.headers()['location']).toBe(linkedUrl2);
	});
});
