import faker from 'faker';

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.random.number()];

	return parts.join(' ');
};

const getSidebarInputByLabel = label => {
	return cy.get('@sidebar').contains(label).siblings('input').first();
}

const selectors = {
	saveButton: () => cy.get('.editor-post-save-draft'),
	publishButton: () => cy.get('.editor-post-publish-button'),
	savedNotice: () => cy.get('.editor-post-saved-state.is-saved'),
	chooseCustom: () => getSidebarInputByLabel('Custom Permalink'),
	newTab: () => getSidebarInputByLabel('Open in new tab'),
	url: () => getSidebarInputByLabel('Links to'),
};

const clickCheckbox = () => {
	selectors.chooseCustom().click();
}

describe('Block Editor', () => {
	const linkedUrl = 'https://wordpress.org/';
	const draftTitle = postTitle();
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	before(() => {
		cy.login();
		Cypress.Cookies.defaults({
			whitelist: () => true,
		});
		cy.deactivatePlugin('classic-editor');
		cy.visit('/wp-admin/post-new.php?post_type=page');
		cy.url().should('contain', '/wp-admin/post-new.php?post_type=page');
	});

	// prettier-ignore
	beforeEach(() => {
		// Aliases.
		cy.get('.editor-post-title__input').first()
			.as('title');
		cy.get('.edit-post-sidebar')
			.as('sidebar');
		cy.get('.editor-post-publish-panel__toggle')
			.as('publishButton');
	});

	context('sidebar checkbox', () => {
		it('is not checked', () => {
			cy.get('button.nux-dot-tip__disable').click();
			cy.get('@title').type(draftTitle);
			selectors.chooseCustom().should('be.visible').and('not.be.checked');
		});

		it('has not revealed its contents', () => {
			cy.get('@sidebar').should('not.contain', 'Links to');
		});

		it('is not checked after saving a draft', () => {
			selectors.saveButton().click();
			selectors.savedNotice().should('be.visible');
			selectors.chooseCustom().should('be.visible').and('not.be.checked');
		});

		it('shows contents when checked', () => {
			clickCheckbox();
			selectors.chooseCustom().should('be.checked');
			cy.get('@sidebar').should('contain', 'Links to');
		});

		it('hides contents when unchecked', () => {
			clickCheckbox();
			selectors.chooseCustom().should('not.be.checked');
			cy.get('@sidebar').should('not.contain', 'Links to');
		});
	});

	context('url', () => {
		it('persists through checking/unchecking', () => {
			clickCheckbox();
			selectors.url().clear().type(linkedUrl);
			clickCheckbox();
			clickCheckbox();
			selectors.url().should('have.value', linkedUrl);
		});

		it('saves its state', () => {
			selectors.saveButton().click();
			selectors.savedNotice().should('be.visible');
			cy.reload();
			selectors.chooseCustom().should('be.checked');
			selectors.url().should('have.value', linkedUrl);
		});
	});

	context('new tab checkbox', () => {
		it('persists through checking/unchecking', () => {
			selectors.newTab().check();
			selectors.newTab().should('be.checked');
			clickCheckbox();
			clickCheckbox();
			selectors.newTab().should('be.checked');
		});

		it('saves its state', () => {
			selectors.saveButton().click();
			selectors.savedNotice().should('be.visible');
			cy.reload();
			selectors.newTab().should('be.checked');
		});
	});

	context('short url', () => {
		it('should redirect to its custom URL', () => {
			cy.get('@publishButton').click();
			selectors.publishButton().click();
			cy.request({
				url: `/${draftSlug}/`,
				followRedirect: false,
				failOnStatusCode: false,
			}).then(resp => {
				expect(resp.status).to.eq(301);
				expect(resp.redirectedToUrl).to.eq(linkedUrl);
			});
		});
	});
});
