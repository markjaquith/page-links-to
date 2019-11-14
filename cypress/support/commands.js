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
	Cypress.Cookies.defaults({
		whitelist: () => true,
	});
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

const wpCli = command => {
	cy.exec(`wp ${command}`, {
		log: false,
		failOnNonZeroExit: false,
	});
};

Cypress.Commands.add('wpCli', command => {
	wpCli(command);
	Cypress.log({
		name: 'runWpCliCommand',
		displayName: 'WPCLI',
		message: `Ran command: ${command}`,
	});
});

Cypress.Commands.add('activatePlugin', plugin => {
	wpCli(`plugin install --activate ${plugin}`);
	Cypress.log({
		name: 'activatePlugin',
		displayName: 'Activate Plugin',
		message: `Activated ${plugin}`,
	});
});

Cypress.Commands.add('deactivatePlugin', plugin => {
	wpCli(`plugin deactivate ${plugin}`);
	Cypress.log({
		name: 'deactivatePlugin',
		displayName: 'Deactivate Plugin',
		message: `Deactivated ${plugin}`,
	});
});

Cypress.Commands.add('enablePrettyPermalinks', () => {
	wpCli(`rewrite structure '/%postname%/'`);
	Cypress.log({
		name: 'enablePrettyPermalinks',
		displayName: 'Enable Pretty Permalinks',
		message: 'Enabled pretty permalinks',
	});
});
