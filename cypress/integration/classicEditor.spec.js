import faker from 'faker';

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.random.number()];

	return parts.join(' ');
};

describe('Classic Editor', () => {
	const linkedUrl = Cypress.config().baseUrl + '/?1';
	const linkedUrl2 = Cypress.config().baseUrl + '/?2';
	const draftTitle = postTitle();
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	before(() => {
		cy.login();
		cy.keepAllCookies();
		cy.enablePrettyPermalinks();
		cy.activatePlugin('classic-editor');
		cy.visit('/wp-admin/post-new.php?post_type=page');
		cy.url().should('contain', '/wp-admin/post-new.php?post_type=page');
	});

	// prettier-ignore
	beforeEach(() => {
		// Aliases.
		cy.get('#cws-links-to-choose-wp')
			.as('chooseWp');
		cy.get('#cws-links-to-choose-custom')
			.as('chooseCustom');
		cy.get('#cws-links-to-new-tab')
			.as('newTab');
		cy.get('#cws-links-to')
			.as('url');
		cy.get('#title')
			.as('pageTitle');
		cy.get('#save-post')
			.as('saveButton');
		cy.get('#publish')
			.as('publishButton');
	});

	context('meta box', () => {
		it('is on the page', () => {
			cy.scrollTo('bottom');
			cy.get('body').contains('Page Links To');
		});

		it('is pointing to the normal WordPress URL', () => {
			cy.get('@chooseWp').should('be.checked');
			cy.get('@url').should('not.be.visible');
		});

		it('does not change after we save the page', () => {
			cy.get('@pageTitle').type(draftTitle);
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.get('@chooseWp').should('be.checked');
			cy.get('@url').should('not.be.visible');
		});

		it('shows hidden fields when custom link option enabled', () => {
			cy.scrollTo('bottom');
			cy.get('@chooseCustom')
				.click()
				.should('be.checked');
			cy.get('@chooseWp').should('not.be.checked');
			cy.get('@url').should('be.visible');
			cy.focused().should('have.attr', 'id', 'cws-links-to');
			cy.get('@newTab').should('not.be.checked');
		});

		it('hides fields when custom link option is disabled', () => {
			cy.get('@chooseWp')
				.click()
				.should('be.checked');
			cy.get('@chooseCustom').should('not.be.checked');
			cy.get('@url').should('not.be.visible');
		});

		it('persists a custom URL', () => {
			cy.scrollTo('bottom');
			cy.get('@chooseCustom')
				.click()
				.should('be.checked');
			cy.get('@url').type(linkedUrl);
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.scrollTo('bottom');
			cy.get('@chooseCustom').should('be.checked');
			cy.get('@url')
				.should('be.visible')
				.and('have.value', linkedUrl);
			cy.get('@newTab').should('not.be.checked');
		});

		it('persists the new tab checkbox', () => {
			cy.get('@newTab')
				.click()
				.should('be.checked');
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.get('@url')
				.should('be.visible')
				.and('have.value', linkedUrl)
				.clear()
				.type(linkedUrl2);
			cy.get('@newTab')
				.should('be.checked')
				.click()
				.should('not.be.checked');
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.get('@url')
				.should('be.visible')
				.and('have.value', linkedUrl2);
			cy.get('@newTab').should('not.be.checked');
		});
	});

	context('short url', () => {
		it('should redirect to its custom URL', () => {
			cy.get('@publishButton').click();
			cy.request({
				url: `/${draftSlug}/`,
				followRedirect: false,
				failOnStatusCode: false,
			}).then(resp => {
				expect(resp.status).to.eq(301);
				expect(resp.redirectedToUrl).to.eq(linkedUrl2);
			});
		});
	});
});
