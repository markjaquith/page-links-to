Cypress.Commands.add('login', () => {
	cy.request({
		url: '/wp-login.php',
		method: 'POST',
		form: true,
		body: {
			log: Cypress.env('wp_username'),
			pwd: Cypress.env('wp_password'),
			rememberme: 'forever',
			testcookie: 1,
		},
	});
});

Cypress.Commands.add('keepAllCookies', () => {
	cy.getCookies().then(cookies =>
		cookies.forEach(cookie => Cypress.Cookies.preserveOnce(cookie.name))
	);
});

Cypress.Commands.add(
	'hoverWpMenuItem',
	{ prevSubject: 'optional' },
	$menuItem => {
		Cypress.$($menuItem).addClass('opensub');
		Cypress.log({
			$el: $menuItem,
			name: 'hoverWpMenuItem',
			displayName: 'Hover',
			message: 'Simulated menu hover',
		});
	}
);
