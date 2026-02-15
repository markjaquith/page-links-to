// @ts-check
const { defineConfig, devices } = require('@playwright/test');

const STORAGE_STATE = 'artifacts/storage-states/admin.json';

module.exports = defineConfig({
	testDir: './e2e',
	fullyParallel: false,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: 1,
	reporter: 'html',
	use: {
		baseURL: process.env.BASE_URL || 'https://plugins.test',
		ignoreHTTPSErrors: true,
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			name: 'setup',
			testMatch: /global-setup\.js/,
		},
		{
			name: 'chromium',
			use: {
				...devices['Desktop Chrome'],
				storageState: STORAGE_STATE,
			},
			dependencies: ['setup'],
		},
	],
});
