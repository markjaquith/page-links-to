migrate from Cypress to Playwright for e2e tests

## Tasks

- [x] Install Playwright (`@playwright/test`) and remove Cypress dependency
- [x] Create `playwright.config.js` with project-based setup (auth + chromium)
- [x] Create `e2e/global-setup.js` for authentication via storageState
- [x] Create `e2e/fixtures.js` with custom test fixtures (wpCli, activatePlugin, deactivatePlugin, enablePrettyPermalinks, hoverWpMenuItem)
- [x] Migrate `cypress/e2e/blockEditor.cy.js` → `e2e/blockEditor.spec.js`
- [x] Migrate `cypress/e2e/classicEditor.cy.js` → `e2e/classicEditor.spec.js`
- [x] Migrate `cypress/e2e/createQuickLinks.cy.js` → `e2e/createQuickLinks.spec.js`
- [x] Update `package.json` scripts (`cypress` → `e2e`, jest ignore paths)
- [x] Update `.github/workflows/tests.yml` CI job (Cypress → Playwright)
- [x] Update `.gitignore` and `.distignore` for Playwright artifacts
- [x] Update `Gruntfile.js` references from cypress → e2e
- [x] Remove old Cypress config and test files
- [x] Verify all tests pass locally
