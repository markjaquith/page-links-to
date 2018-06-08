module.exports = function(grunt) {
	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		phpunit: {
			default: {},
		},

		browserify: {
			options: {
				paths: ['../node_modules'],
				transform: [
					[
						'babelify',
						{
							presets: [
								[
									'env',
									{
										targets: {
											browsers: ['>0.25%'],
										},
									},
								],
							],
							plugins: [
								'add-module-exports',
								'transform-class-properties',
								'transform-object-rest-spread',
							],
						},
					],
					[
						'extensify',
						{
							extensions: ['jsx'],
						},
					],
					[
						'uglifyify',
						{
							global: true,
						},
					],
				],
				browserifyOptions: {
					debug: false,
				},
			},
			default: {
				files: {
					'js/new-tab.min.js': 'js/new-tab.jsx',
					'js/page-links-to.min.js': 'js/page-links-to.jsx',
				},
			},
		},

		watch: {
			php: {
				files: [
					'**/*.php',
					'!release/**',
					'!node_modules/**',
					'!.git/**',
					'!.sass-cache/**',
				],
				tasks: ['phpunit'],
				options: {
					debounceDelay: 5000,
				},
			},
			sass: {
				files: ['css/*.sass'],
				tasks: ['compass'],
				options: {
					debounceDelay: 500,
				},
			},
			jsx: {
				files: [
					'js/*.jsx',
					'js/**/*.jsx',
					'!release/**',
					'!node_modules/**',
					'!.git/**',
					'!.sass-cache/**',
				],
				tasks: ['browserify'],
				options: {
					debounceDelay: 500,
				},
			},
			package: {
				files: ['package.json'],
				tasks: ['replace'],
			},
		},

		wp_deploy: {
			default: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'release/svn/',
					assets_dir: 'assets/',
				},
			},
		},

		clean: {
			release: ['release/<%= pkg.version %>/', 'release/svn/'],
			svn_readme_md: ['release/svn/readme.md'],
		},

		notify_hooks: {
			options: {
				success: true,
			},
		},

		copy: {
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!release/**',
					'!assets/**',
					'!.git/**',
					'!.sass-cache/**',
					'!img/src/**',
					'!Gruntfile.*',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!tests/**',
					'!bin/**',
					'!.travis.yml',
					'!phpunit.xml',
				],
				dest: 'release/<%= pkg.version %>/',
			},
			svn: {
				cwd: 'release/<%= pkg.version %>/',
				expand: true,
				src: '**',
				dest: 'release/svn/',
			},
		},

		replace: {
			header: {
				src: ['<%= pkg.name %>.php'],
				overwrite: true,
				replacements: [
					{
						from: /Version:(\s*?)[a-zA-Z0-9.-]+$/m,
						to: 'Version:$1<%= pkg.version %>',
					},
					{
						from: /^(\s*?)const(\s+?)VERSION(\s*?)=(\s+?)'[^']+';/m,
						to: "$1const$2VERSION$3=$4'<%= pkg.version %>';",
					},
					{
						from: /^(\s*?)const(\s+?)CSS_JS_VERSION(\s*?)=(\s+?)'[^']+';/m,
						to: "$1const$2CSS_JS_VERSION$3=$4'<%= pkg.version %>';",
					},
				],
			},
			readme: {
				src: ['readme.md'],
				overwrite: true,
				replacements: [
					{
						from: /^Stable tag:\s*?[a-zA-Z0-9.-]+(\s*?)$/im,
						to: 'Stable tag: <%= pkg.version %>$1',
					}
				]

			},
			svn_readme: {
				src: ['release/svn/readme.md'],
				dest: 'release/svn/readme.txt',
				replacements: [
					{
						from: /^# (.*?)( #+)?$/gm,
						to: '=== $1 ===',
					},
					{
						from: /^## (.*?)( #+)?$/gm,
						to: '== $1 ==',
					},
					{
						from: /^### (.*?)( #+)?$/gm,
						to: '= $1 =',
					},
					{
						from: /^.*travis-ci.org.*$/im,
						to: '',
					},
					{
						from: /\n{3,}/gm,
						to: "\n\n",
					},
				],
			},
		},

		prettier: {
			options: {
				singleQuote: true,
				useTabs: true,
				trailingComma: 'es5',
			},
			default: {
				src: ['js/**.jsx'],
			},
		},

		compress: {
			default: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip',
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/',
			},
		},
	});

	// Load other tasks
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-browserify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-text-replace');
	grunt.loadNpmTasks('grunt-phpunit');
	grunt.loadNpmTasks('grunt-notify');
	grunt.loadNpmTasks('grunt-prettier');
	grunt.loadNpmTasks('grunt-wp-deploy');
	grunt.task.run('notify_hooks');

	// Default task
	grunt.registerTask('default', ['replace', 'browserify']);

	// Build task
	grunt.registerTask('build', ['default', 'clean']);

	// Prepare a WordPress.org release
	grunt.registerTask('release:prepare', [
		'copy:main',
		'copy:svn',
		'replace:svn_readme',
		'clean:svn_readme_md',
	]);

	// Deploy out a WordPress.org release
	grunt.registerTask('release:deploy', ['wp_deploy']);

	// WordPress.org release task
	grunt.registerTask('release', ['build', 'release:prepare', 'release:deploy']);

	grunt.util.linefeed = '\n';
};
