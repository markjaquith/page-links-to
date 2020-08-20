import faker from 'faker';

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.random.number()];

	return parts.join(' ');
};

function tid(id) {
	return cy.get(`[data-testid=${id}]`);
}

const selectors = {
	saveButton: () => cy.get('.editor-post-save-draft'),
	publishButton: () => cy.get('.editor-post-publish-button'),
	savedNotice: () => cy.get('.editor-post-saved-state.is-saved'),
	chooseWordPress: () =>
		cy.get('.plt-panel input[type="radio"][value="wordpress"]'),
	chooseCustom: () => cy.get('.plt-panel input[type="radio"][value="custom"]'),
	newTab: () => tid('plt-newtab'),
	url: () => tid('plt-url'),
};

const clickCustom = () => {
	selectors.chooseCustom().click();
};

const clickWordPress = () => {
	selectors.chooseWordPress().click();
};

const openPanel = () => {
	cy.get('@panel').then($panel => {
		if (!$panel.hasClass('is-opened')) {
			cy.wrap($panel).click();
		}
	});
};

const assertWordPress = () => {
	it('normal WordPress URL is selected', () => {
		selectors
			.chooseWordPress()
			.should('be.visible')
			.and('be.checked');
	});

	it('custom URL is not selected', () => {
		selectors
			.chooseCustom()
			.should('be.visible')
			.and('not.be.checked');
	});

	it('custom URL UI is not visible', () => {
		selectors.url().should('not.be.visible');
		selectors.newTab().should('not.be.visible');
	});
};

const assertCustom = () => {
	it('custom URL is selected', () => {
		selectors
			.chooseCustom()
			.should('be.visible')
			.and('be.checked');
	});

	it('normal WordPress URL is not selected', () => {
		selectors
			.chooseWordPress()
			.should('be.visible')
			.and('not.be.checked');
	});

	it('custom URL UI is visible', () => {
		selectors.url().should('be.visible');
		selectors.newTab().should('be.visible');
	});
};

const save = () => {
	selectors.saveButton().click();
	selectors.savedNotice().should('be.visible');
	cy.reload();
	cy.get('button[aria-label="Close dialog"]').click({ force: true });
	openPanel();
};

describe('Block Editor', () => {
	const linkedUrl = Cypress.config().baseUrl + '/?3';
	const draftTitle = postTitle();
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	before(() => {
		cy.login();
		Cypress.Cookies.defaults({
			preserve: () => true,
		});
		cy.deactivatePlugin('classic-editor');
		cy.visit('/wp-admin/post-new.php?post_type=page');
		cy.url().should('contain', '/wp-admin/post-new.php?post_type=page');
	});

	// prettier-ignore
	beforeEach(() => {
		// Aliases.
		cy.get('.plt-panel')
			.as('panel');
		cy.get('.editor-post-title__input').first()
			.as('title');
		cy.get('.edit-post-sidebar')
			.as('sidebar');
		cy.get('.editor-post-publish-panel__toggle')
			.as('publishButton');
	});

	context('title', () => {
		it('is filled', () => {
			cy.get('button[aria-label="Close dialog"]').click({ force: true });
			cy.get('@title').type(draftTitle);
		});
	});

	context('panel', () => {
		it('is open', openPanel);
	});

	context('radio button', () => {
		it('starts choosing a normal WordPress URL', () => {
			assertWordPress();
		});

		it('stays the same after saving a draft', () => {
			selectors.saveButton().click();
			selectors.savedNotice().should('be.visible');
			assertWordPress();
		});

		it('chooses custom', () => {
			clickCustom();
			assertCustom();
		});

		it('chooses WordPress', () => {
			clickWordPress();
			assertWordPress();
		});
	});

	context('url', () => {
		it('persists through changing link type', () => {
			clickCustom();
			selectors
				.url()
				.clear()
				.type(linkedUrl);
			clickWordPress();
			clickCustom();
			selectors.url().should('have.value', linkedUrl);
		});

		it('saves its state', () => {
			save();
			selectors.chooseCustom().should('be.checked');
			selectors.url().should('have.value', linkedUrl);
		});
	});

	context('new tab checkbox', () => {
		it('persists through checking/unchecking', () => {
			selectors.newTab().check();
			selectors.newTab().should('be.checked');
			clickWordPress();
			clickCustom();
			selectors.newTab().should('be.checked');
		});

		it('saves its state', () => {
			save();
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
