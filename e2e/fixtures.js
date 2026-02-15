const { test: base, expect } = require('@playwright/test');
const { execSync } = require('child_process');

/**
 * Custom test fixture that provides WordPress helper utilities.
 */
const test = base.extend({
	/**
	 * Run a WP-CLI command.
	 */
	wpCli: async ({}, use) => {
		const wpCli = (command) => {
			execSync(`wp ${command}`, { stdio: 'pipe' });
		};
		await use(wpCli);
	},

	/**
	 * Activate a WordPress plugin.
	 */
	activatePlugin: async ({}, use) => {
		const activatePlugin = (plugin) => {
			execSync(`wp plugin install --activate ${plugin}`, { stdio: 'pipe' });
		};
		await use(activatePlugin);
	},

	/**
	 * Deactivate a WordPress plugin.
	 */
	deactivatePlugin: async ({}, use) => {
		const deactivatePlugin = (plugin) => {
			execSync(`wp plugin deactivate ${plugin}`, { stdio: 'pipe' });
		};
		await use(deactivatePlugin);
	},

	/**
	 * Enable pretty permalinks.
	 */
	enablePrettyPermalinks: async ({}, use) => {
		const enablePrettyPermalinks = () => {
			execSync(`wp rewrite structure '/%postname%/'`, { stdio: 'pipe' });
		};
		await use(enablePrettyPermalinks);
	},

	/**
	 * Simulate WP admin menu hover by adding the CSS class.
	 */
	hoverWpMenuItem: async ({ page }, use) => {
		const hoverWpMenuItem = async (selector) => {
			await page.locator(selector).evaluate((el) => {
				el.classList.add('opensub');
			});
		};
		await use(hoverWpMenuItem);
	},
});

module.exports = { test, expect };
