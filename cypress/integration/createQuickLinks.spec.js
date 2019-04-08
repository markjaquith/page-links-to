import faker from 'faker'

describe('Quick links', () => {

	before(() => {
		cy.login();
		Cypress.Cookies.defaults({
			whitelist: () => true,
		});
		cy.visit('/wp-admin/');
	});

	it('can see the menu item', () => {
		const subMenuTitle = 'Add Page Link';
		const customSlug = `${faker.random.word().toLowerCase()}-${faker.random.word().toLowerCase()}`;
		const linkedUrl = 'https://wordpress.org/';

		cy.location('pathname').should('eq', '/wp-admin/');

		cy.get('#plt-quick-add')
			.as('modal')
			.should('not.be.visible');
		
		cy.get('#menu-pages')
			.as('menu')
			.find(subMenuTitle)
			.should('not.be.visible')
		
		cy.get('@menu')
			.hoverWpMenuItem()
			.contains(subMenuTitle)
			.click()
			
		cy.get('@modal')
			.should('be.visible');
		
		cy.get('#plt-quick-add-publish')
			.as('publish')
			.should('be.disabled');
		
		cy.get('#plt-quick-add-save')
			.as('save')
			.should('be.disabled');

		cy.get('@modal')
			.find('input[name="title"]')
			.as('title')
			.should('be.empty');
		
		cy.get('@modal')
			.find('input[name="url"]')
			.as('url')
			.should('be.empty');

		cy.get('@modal')
			.find('input[name="slug"]')
			.as('slug')
			.should('be.empty');
		
		cy.get('@title')
			.type('Short Title');

		cy.get('@slug')
			.should('have.attr', 'placeholder', 'short-title');
		
		cy.get('@modal')
			.find('.short-url-message')
			.as('lengthWarning')
			.should('not.be.visible');
		
		cy.get('@url')
			.type(linkedUrl);
		
		cy.get('@save')
			.should('not.be.disabled');

		cy.get('@publish')
			.should('not.be.disabled');

		cy.get('@title')
			.clear();
		
		cy.get('@publish')
			.should('be.disabled');
		
		cy.get('@save')
			.should('be.disabled');

		cy.get('@title')
			.type('Short Two');

		cy.get('@slug')
			.should('have.attr', 'placeholder', 'short-two');

		cy.get('@save')
			.should('not.be.disabled');

		cy.get('@publish')
			.should('not.be.disabled');

		cy.get('@title')
			.clear()
			.type('Super Long Title Way Too Long');
		
		cy.get('@lengthWarning')
			.should('be.visible');
		
		cy.get('@slug')
			.type(customSlug);
		
		cy.get('@publish')
			.click();
		
		cy.get('@modal')
			.contains('New page link published!');
		
		cy.request({
			url: `/${customSlug}/`,
			followRedirect: false,
		})
			.then(resp => {
				expect(resp.status).to.eq(301)
				expect(resp.redirectedToUrl).to.eq(linkedUrl)
			});
	});
});
