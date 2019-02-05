module.exports = function(grunt) {
	const ignores = [
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
	];

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		sass: {
			default: {
				options: {
					style: 'expanded',
				},
				files: {
					'css/quick-add.css': 'sass/quick-add.sass',
				},
			},
		},

		postcss: {
			default: {
				src: 'css/*.css',
				options: {
					map: true,
					processors: [require('autoprefixer'), require('cssnano')],
				},
			},
		},

		phpunit: {
			default: {
				options: {
					bin: 'vendor/bin/phpunit',
				},
			},
		},

		browserify: {
			options: {
				paths: ['../node_modules'],
				watch: true,
				transform: [
					[
						'babelify',
						{
							presets: ['env', 'react'],
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
					// [
					// 	'uglifyify',
					// 	{
					// 		global: true,
					// 	},
					// ],
				],
				browserifyOptions: {
					debug: true,
				},
			},
			default: {
				files: {
					'js/new-tab.min.js': 'js/new-tab.jsx',
					'js/meta-box.min.js': 'js/meta-box.jsx',
					'js/quick-add.min.js': 'js/quick-add.jsx',
					'js/gutenberg.min.js': 'js/gutenberg.jsx',
				},
			},
		},

		watch: {
			php: {
				files: ['**/*.php', ...ignores],
				tasks: ['phpunit'],
				options: {
					debounceDelay: 5000,
				},
			},
			sass: {
				files: ['sass/**/*.sass', ...ignores],
				tasks: ['sass', 'postcss'],
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
					svn_user: 'markjaquith',
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
			clipboard: {
				src: ['node_modules/clipboard/dist/clipboard.min.js'],
				dest: 'js/clipboard.min.js',
			},
			main: {
				src: ['**', ...ignores],
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
				],
			},
			plugin: {
				src: ['classes/plugin.php'],
				overwrite: true,
				replacements: [
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
					},
				],
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
						to: '\n\n',
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
				src: ['js/**.jsx', 'Gruntfile.js'],
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

	require('load-grunt-tasks')(grunt);
	grunt.task.run('notify_hooks');

	// Default task
	grunt.registerTask('default', [
		'replace',
		'copy:clipboard',
		'prettier',
		'browserify',
		'sass',
		'postcss',
	]);

	// Build task
	grunt.registerTask('build', ['default', 'clean']);

	// Develop.
	grunt.registerTask('dev', ['default', 'watch']);

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
