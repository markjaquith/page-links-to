const { faker } = require('@faker-js/faker');
const { test, expect } = require('./fixtures');

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.number.int()];
	return parts.join(' ');
};

test.describe('Block Editor', () => {
	const draftTitle = postTitle();
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	test('supports Page Links To panel with radio buttons, URL persistence, and new tab checkbox', async ({
		page,
		baseURL,
		deactivatePlugin,
		wpCli,
	}) => {
		const linkedUrl = baseURL + '/?3';

		deactivatePlugin('classic-editor');

		// Disable editor modals (welcome guide, pattern chooser) via user preferences.
		wpCli(
			`eval '$prefs = get_user_meta(1, "wp_persisted_preferences", true); if (!is_array($prefs)) $prefs = []; $prefs["core/edit-post"] = ["welcomeGuide" => false]; $prefs["core/edit-page"] = ["welcomeGuide" => false]; $prefs["core"] = array_merge($prefs["core"] ?? [], ["enableChoosePatternModal" => false, "distractionFree" => false, "isResumed" => true]); $prefs["_modified"] = gmdate("c"); update_user_meta(1, "wp_persisted_preferences", $prefs);'`
		);

		await page.goto('/wp-admin/post-new.php?post_type=page');
		await expect(page).toHaveURL(/\/wp-admin\/post-new\.php\?post_type=page/);

		// In WordPress 6.x+, the editor canvas is inside an iframe.
		const editorCanvas = page.frameLocator('iframe[name="editor-canvas"]');
		const titleField = editorCanvas
			.locator('[aria-label="Add title"], .editor-post-title__input')
			.first();
		await titleField.click({ force: true });
		await titleField.fill(draftTitle);

		// Helper to open the PLT panel if it's closed.
		const openPanel = async () => {
			const panel = page.locator('.plt-panel');
			const isOpened = await panel.evaluate((el) =>
				el.classList.contains('is-opened')
			);
			if (!isOpened) {
				await panel.click();
			}
		};

		const scrollPanelIntoView = async () => {
			await page.locator('.plt-panel').scrollIntoViewIfNeeded();
		};

		const chooseWordPress = page.locator(
			'.plt-panel input[type="radio"][value="wordpress"]'
		);
		const chooseCustom = page.locator(
			'.plt-panel input[type="radio"][value="custom"]'
		);
		const urlInput = page.getByTestId('plt-url');
		const newTabCheckbox = page.getByTestId('plt-newtab');

		const assertWordPress = async () => {
			await scrollPanelIntoView();
			await expect(chooseWordPress).toBeVisible();
			await expect(chooseWordPress).toBeChecked();
			await expect(chooseCustom).toBeVisible();
			await expect(chooseCustom).not.toBeChecked();
			await expect(page.getByTestId('plt-url')).not.toBeVisible();
			await expect(page.getByTestId('plt-newtab')).not.toBeVisible();
		};

		const assertCustom = async () => {
			await scrollPanelIntoView();
			await expect(chooseCustom).toBeVisible();
			await expect(chooseCustom).toBeChecked();
			await expect(chooseWordPress).toBeVisible();
			await expect(chooseWordPress).not.toBeChecked();
			await expect(urlInput).toBeVisible();
			await expect(newTabCheckbox).toBeVisible();
		};

		const clickCustom = async () => {
			await chooseCustom.click({ force: true });
		};

		const clickWordPress = async () => {
			await chooseWordPress.click({ force: true });
		};

		const save = async () => {
			await page.locator('.editor-post-save-draft').click();
			await expect(
				page.locator('.editor-post-saved-state.is-saved')
			).toBeVisible();
			await page.reload();
			await openPanel();
		};

		// Open the PLT panel.
		await openPanel();

		// Starts with WordPress URL selected.
		await assertWordPress();

		// Stays the same after saving a draft.
		await page.locator('.editor-post-save-draft').click();
		await expect(
			page.locator('.editor-post-saved-state.is-saved')
		).toBeVisible();
		await assertWordPress();

		// Choosing custom shows custom UI.
		await clickCustom();
		await assertCustom();

		// Choosing WordPress hides custom UI.
		await clickWordPress();
		await assertWordPress();

		// URL persists through changing link type.
		await clickCustom();
		await urlInput.fill(linkedUrl);
		await clickWordPress();
		await clickCustom();
		await expect(urlInput).toHaveValue(linkedUrl);

		// URL saves its state.
		await save();
		await expect(chooseCustom).toBeChecked();
		await expect(urlInput).toHaveValue(linkedUrl);

		// New tab checkbox persists through checking/unchecking.
		await newTabCheckbox.check();
		await expect(newTabCheckbox).toBeChecked();
		await clickWordPress();
		await clickCustom();
		await expect(newTabCheckbox).toBeChecked();

		// New tab checkbox saves its state.
		await save();
		await expect(newTabCheckbox).toBeChecked();

		// Publish and verify the short URL redirects to the custom URL.
		await page.locator('.editor-post-publish-panel__toggle').click();
		await page.locator('.editor-post-publish-button').click();

		const response = await page.request.get(`/${draftSlug}/`, {
			maxRedirects: 0,
			failOnStatusCode: false,
		});
		expect(response.status()).toBe(301);
		expect(response.headers()['location']).toBe(linkedUrl);
	});
});
