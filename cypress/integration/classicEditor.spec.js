import faker from 'faker';

const postSlug = () => {
	const parts = [faker.lorem.slug(), faker.lorem.slug(), faker.random.number()];

	return parts.join('-');
};

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.random.number()];

	return parts.join(' ');
};

describe('Classic Editor', () => {
	const subMenuTitle = 'Add Page Link';
	const publishSlug = postSlug();
	const linkedUrl = 'https://wordpress.org/';
	const linkedUrl2 = 'https://wordpress.com/';
	const draftTitle = postTitle();
	const longTitle = 'Super Long Title Way Too Long';
	const draftSlug = draftTitle.toLowerCase().replace(/ /g, '-');

	before(() => {
		cy.login();
		Cypress.Cookies.defaults({
			whitelist: () => true,
		});
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
		// cy.get('#plt-quick-add')
		// 	.as('modal');
		// cy.get('.ui-dialog-titlebar-close').first()
		// 	.as('modalCloseButton');
		// cy.get('#menu-pages')
		// 	.as('menu');
		// cy.get('@menu').contains(subMenuTitle)
		// 	.as('subMenuItem');
		// cy.get('#plt-quick-add-publish')
		// 	.as('publish');
		// cy.get('#plt-quick-add-save')
		// 	.as('save');
		// cy.get('@modal').find('input[name="title"]')
		// 	.as('title');
		// cy.get('@modal').find('input[name="url"]')
		// 	.as('url');
		// cy.get('@modal').find('input[name="slug"]')
		// 	.as('slug');
		// cy.get('@modal').find('.short-url-message')
		// 	.as('lengthWarning');
		// cy.get('#wp-admin-bar-logout a')
		// 	.as('logout');
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
			cy.get('@chooseCustom').click().should('be.checked');
			cy.get('@chooseWp').should('not.be.checked');
			cy.get('@url').should('be.visible');
			cy.focused().should('have.attr', 'id', 'cws-links-to');
			cy.get('@newTab').should('not.be.checked');
		});

		it('hides fields when custom link option is disabled', () => {
			cy.get('@chooseWp').click().should('be.checked');
			cy.get('@chooseCustom').should('not.be.checked');
			cy.get('@url').should('not.be.visible');
		});

		it('persists a custom URL', () => {
			cy.scrollTo('bottom');
			cy.get('@chooseCustom').click().should('be.checked');
			cy.get('@url').type(linkedUrl);
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.scrollTo('bottom');
			cy.get('@chooseCustom').should('be.checked');
			cy.get('@url').should('be.visible').and('have.value', linkedUrl);
			cy.get('@newTab').should('not.be.checked');
		});

		it('persists the new tab checkbox', () => {
			cy.get('@newTab').click().should('be.checked');
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.get('@url').should('be.visible').and('have.value', linkedUrl).clear().type(linkedUrl2);
			cy.get('@newTab').should('be.checked').click().should('not.be.checked');
			cy.get('@saveButton').click();
			cy.get('body').contains('Page draft updated');
			cy.get('@url').should('be.visible').and('have.value', linkedUrl2);
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
