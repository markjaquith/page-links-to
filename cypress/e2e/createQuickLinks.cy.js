import { faker } from '@faker-js/faker';

const postSlug = () => {
	const parts = [faker.lorem.slug(), faker.lorem.slug(), faker.number.int()];

	return parts.join('-');
};

const postTitle = () => {
	const parts = [faker.lorem.word(), faker.lorem.word(), faker.number.int()];

	return parts.join(' ');
};

describe('Quick Links', () => {
	const subMenuTitle = 'Add Page Link';
	const publishSlug = postSlug();
	const linkedUrl = Cypress.config().baseUrl + '/?4';
	const draftTitle = postTitle();
	const longTitle = 'Super Long Title Way Too Long';
	const draftSlug = 'draft-' + postSlug();

	before(() => {
		cy.login();
		cy.enablePrettyPermalinks();
		cy.visit('/wp-admin/');
		cy.location('pathname').should('eq', '/wp-admin/');
	});

	// prettier-ignore
	beforeEach(() => {
		// Aliases.
		cy.get('#plt-quick-add')
			.as('modal');
		cy.get('.ui-dialog-titlebar-close').first()
			.as('modalCloseButton');
		cy.get('#menu-pages')
			.as('menu');
		cy.get('@menu').contains(subMenuTitle)
			.as('subMenuItem');
		cy.get('#plt-quick-add-publish')
			.as('publish');
		cy.get('#plt-quick-add-save')
			.as('save');
		cy.get('@modal').find('input[name="title"]')
			.as('title');
		cy.get('@modal').find('input[name="url"]')
			.as('url');
		cy.get('@modal').find('input[name="slug"]')
			.as('slug');
		cy.get('@modal').find('.short-url-message')
			.as('lengthWarning');
		cy.get('#wp-admin-bar-logout a')
			.as('logout');
	});

	context('submenu item', () => {
		it('starts hidden', () => {
			cy.get('@subMenuItem').should('not.be.visible');
		});

		it('is visible on hover', () => {
			cy.get('@menu').hoverWpMenuItem();
			cy.get('@subMenuItem').should('be.visible');
		});
	});

	context('modal', () => {
		it('starts hidden', () => {
			cy.get('@modal').should('not.be.visible');
		});

		it('is visible after clicking submenu item', () => {
			cy.get('@subMenuItem').click();
			cy.get('@modal').should('be.visible');
		});

		it('is closed after clicking the close button', () => {
			cy.get('@modalCloseButton').click();
			cy.get('@modal').should('not.be.visible');
		});

		it('is closed after clicking outside the modal', () => {
			cy.get('@subMenuItem').click();
			cy.get('@modal').should('be.visible');
			cy.get('body').click(1, 1);
			cy.get('@modal').should('not.be.visible');
			cy.get('@subMenuItem').click({ force: true });
			cy.get('@modal').should('be.visible');
		});
	});

	context('title', () => {
		it('starts empty', () => {
			cy.get('@title').should('be.empty');
		});
	});

	context('URL', () => {
		it('starts empty', () => {
			cy.get('@url').should('be.empty');
		});
	});

	context('slug', () => {
		it('starts empty', () => {
			cy.get('@slug').should('be.empty');
		});
	});

	context('save and publish', () => {
		it('start disabled', () => {
			cy.get('@publish').should('be.disabled');
			cy.get('@save').should('be.disabled');
		});
	});

	context('slug', () => {
		it('is populated as title is typed', () => {
			cy.get('@title').type('Short Title');
			cy.get('@slug').should('have.attr', 'placeholder', 'short-title');
			cy.get('@title').type(' link');
			cy.get('@slug').should('have.attr', 'placeholder', 'short-title-link');
		});
	});

	context('modal', () => {
		it('shows length warning for short slugs', () => {
			cy.get('@lengthWarning').should('not.be.visible');
		});
	});

	context('save and publish', () => {
		it('are enabled after URL and title are provided', () => {
			cy.get('@url').type(linkedUrl);
			cy.get('@save').should('not.be.disabled');
			cy.get('@publish').should('not.be.disabled');
		});

		it('but are disabled again if the title is emptied', () => {
			cy.get('@title').clear();
			cy.get('@publish').should('be.disabled');
			cy.get('@save').should('be.disabled');
		});

		it('and are re-enabled after the title is re-populated', () => {
			cy.get('@title').type('Short Two');
			cy.get('@slug').should('have.attr', 'placeholder', 'short-two');
			cy.get('@save').should('not.be.disabled');
			cy.get('@publish').should('not.be.disabled');
		});
	});

	context('modal', () => {
		it('shows a warning for long slugs', () => {
			cy.get('@title').clear().type(longTitle);
			cy.get('@lengthWarning').should('be.visible');
		});

		it('gives feedback when a new link is published', () => {
			cy.get('@slug').type(publishSlug);
			cy.get('@publish').click();
			cy.get('@modal').contains('New page link published!');
		});
	});

	context('short URL', () => {
		it('should redirect to its custom URL', () => {
			cy.request({
				url: `/${publishSlug}/`,
				followRedirect: false,
			}).then((resp) => {
				expect(resp.status).to.eq(301);
				expect(resp.redirectedToUrl).to.eq(linkedUrl);
			});
		});
	});

	context('modal', () => {
		it('gives feedback when a new link is saved as a draft', () => {
			cy.get('@title').type(draftTitle);
			cy.get('@slug').type(draftSlug);
			cy.get('@url').type(linkedUrl);
			cy.get('@save').should('not.be.disabled').click();
			cy.get('@modal').contains('Page link draft saved!');
		});
	});
});
