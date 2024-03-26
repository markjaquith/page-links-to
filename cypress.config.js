const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'https://plugins.test',
		testIsolation: false, // TODO: Remove this line when tests can run isolated.
  },
})
