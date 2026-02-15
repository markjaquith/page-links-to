const { test: setup, expect } = require('@playwright/test');
const { execSync } = require('child_process');

const STORAGE_STATE = 'artifacts/storage-states/admin.json';

setup('authenticate', async ({ page, baseURL }) => {
	// Activate the Page Links To plugin before running tests.
	execSync('wp plugin activate page-links-to', { stdio: 'pipe' });
	const username = process.env.WP_USERNAME || 'mark';
	const password = process.env.WP_PASSWORD || 'mark';

	await page.goto(`${baseURL}/wp-login.php`);
	await page.locator('#user_login').fill(username);
	await page.locator('#user_pass').fill(password);
	await page.locator('#wp-submit').click();

	// WordPress may show an admin email verification screen after login.
	// If so, dismiss it before proceeding.
	const emailConfirm = page.getByRole('button', {
		name: 'The email is correct',
	});
	if (await emailConfirm.isVisible({ timeout: 5000 }).catch(() => false)) {
		await emailConfirm.click();
	}

	await page.waitForURL('**/wp-admin/**');
	await expect(page.locator('#wpadminbar')).toBeVisible();
	await page.context().storageState({ path: STORAGE_STATE });
});
