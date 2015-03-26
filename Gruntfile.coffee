module.exports = (grunt) ->
	# Regex, used twice
	readmeReplacements = [
		from: /^# (.*?)( #+)?$/mg
		to: '=== $1 ==='
	,
		from: /^## (.*?)( #+)?$/mg
		to: '== $1 =='
	,
		from: /^### (.*?)( #+)?$/mg
		to: '= $1 ='
	,
		from: /^Stable tag:\s*?[\w.-]+(\s*?)$/mi
		to: 'Stable tag: <%= pkg.version %>$1'
	]

	# Project configuration
	grunt.initConfig
		pkg: grunt.file.readJSON('package.json')

		coffee:
			options:
				join: yes
				sourceMap: yes
			default:
				files:
					'js/page-links-to.js': 'js/page-links-to.coffee'
					'js/new-tab.js': 'js/new-tab.coffee'

		coffeelint:
			default: [ 'js/*.coffee' ]
			options:
				no_tabs:
					level: 'ignore'
				max_line_length:
					level: 'warn'
				indentation:
					level: 'ignore'

		jshint:
			default: []
			options:
				curly: yes
				eqeqeq: yes
				immed: yes
				latedef: yes
				newcap: yes
				noarg: yes
				sub: yes
				undef: yes
				boss: yes
				eqnull: yes
				globals:
					exports: yes
					module: no

		uglify:
			options:
				sourceMap: yes
				mangle:
						except: [ 'jQuery' ]
			default:
				files: [
					src: 'js/page-links-to.js'
					dest: 'js/page-links-to.min.js'
					sourceMapIn: 'js/page-links-to.js.map'
				,
					src: 'js/new-tab.js'
					dest: 'js/new-tab.min.js'
					sourceMapIn: 'js/new-tab.js.map'
				]

		compass:
			options:
				sassDir: 'css'
				cssDir: 'css'
				imagesDir: 'images'
				sourcemap: yes
				environment: 'production'

		phpunit:
			default: {}

		watch:
			php:
				files: [ '**/*.php' ]
				tasks: [ 'phpunit' ]
				options:
					debounceDelay: 5000
			sass:
				files: [ 'css/*.sass' ]
				tasks: [ 'compass' ]
				options:
					debounceDelay: 500
			scripts:
				files: [
					'js/**/*.coffee'
					'js/vendor/**/*.js'
				]
				tasks: [
					'coffeelint'
					'coffee'
					'jshint'
					'uglify'
					'clean:js'
				]
				options:
					debounceDelay: 500

		svn_checkout:
			default:
				repos: [
					path: [ 'release/svn' ]
					repo: 'http://plugins.svn.wordpress.org/<%= pkg.name %>'
				]

		push_svn:
			options:
				remove: yes
			default:
				src: 'release/svn/<%= pkg.name %>'
				dest: 'http://plugins.svn.wordpress.org/<%= pkg.name %>'
				tmp: 'release/tmp/'

		clean:
			release: [
				'release/<%= pkg.version %>'
				'release/svn/<%= pkg.name %>/trunk'
				'release/svn/<%= pkg.name %>/tags/<%= pkg.version %>'
			]
			js: [
				'js/*.js'
				'!js/*.min.js'
				'js/*.src.coffee'
				'js/*.js.map'
				'!js/*.min.js.map'
			]
			svn_readme_md: [
				'release/svn/<%= pkg.name %>/trunk/readme.md'
				'release/svn/<%= pkg.name %>/tags/<%= pkg.version %>/readme.md'
			]

		copy:
			main:
				src: [
					'**'
					'!node_modules/**'
					'!release/**'
					'!assets/**'
					'!.git/**'
					'!.sass-cache/**'
					'!img/src/**'
					'!Gruntfile.*'
					'!package.json'
					'!.gitignore'
					'!.gitmodules'
					'!tests/**'
					'!bin/**'
					'!.travis.yml'
					'!phpunit.xml'
				]
				dest: 'release/<%= pkg.version %>/'
			svn_trunk:
				cwd: 'release/<%= pkg.version %>/'
				expand: yes
				src: '**'
				dest: 'release/svn/<%= pkg.name %>/trunk/'
			svn_tag:
				cwd: 'release/<%= pkg.version %>/'
				expand: yes
				src: '**'
				dest: 'release/svn/<%= pkg.name %>/tags/<%= pkg.version %>/'
			svn_assets:
				cwd: 'assets/'
				expand: yes
				src: '**'
				dest: 'release/svn/<%= pkg.name %>/assets/'

		replace:
			header:
				src: [ '<%= pkg.name %>.php' ]
				overwrite: yes
				replacements: [
					from: /^Version:(\s*?)[\w.-]+$/m
					to: 'Version: <%= pkg.version %>'
				]
			plugin:
				src: [ 'classes/plugin.php' ]
				overwrite: yes
				replacements: [
					from: /^(\s*?)const(\s+?)VERSION(\s*?)=(\s+?)'[^']+';/m
					to: "$1const$2VERSION$3=$4'<%= pkg.version %>';"
				,
					from: /^(\s*?)const(\s+?)CSS_JS_VERSION(\s*?)=(\s+?)'[^']+';/m
					to: "$1const$2CSS_JS_VERSION$3=$4'<%= pkg.version %>-release';"
				]
			svn_trunk_readme:
				src: [ 'release/svn/<%= pkg.name %>/trunk/readme.md' ]
				dest: 'release/svn/<%= pkg.name %>/trunk/readme.txt'
				replacements: readmeReplacements
			svn_tag_readme:
				src: [ 'release/svn/<%= pkg.name %>/tags/<%= pkg.version %>/readme.md' ]
				dest: 'release/svn/<%= pkg.name %>/tags/<%= pkg.version %>/readme.txt'
				replacements: readmeReplacements

		compress:
			default:
				options:
					mode: 'zip'
					archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip'
				expand: yes
				cwd: 'release/<%= pkg.version %>/'
				src: [ '**/*' ]
				dest: '<%= pkg.name %>/'

	# Load other tasks
	grunt.loadNpmTasks 'grunt-contrib-jshint'
	grunt.loadNpmTasks 'grunt-contrib-concat'
	grunt.loadNpmTasks 'grunt-contrib-coffee'
	grunt.loadNpmTasks 'grunt-coffeelint'
	grunt.loadNpmTasks 'grunt-contrib-uglify'
	grunt.loadNpmTasks 'grunt-contrib-compass'
	grunt.loadNpmTasks 'grunt-contrib-watch'
	grunt.loadNpmTasks 'grunt-contrib-clean'
	grunt.loadNpmTasks 'grunt-contrib-copy'
	grunt.loadNpmTasks 'grunt-contrib-compress'
	grunt.loadNpmTasks 'grunt-text-replace'
	grunt.loadNpmTasks 'grunt-phpunit'
	grunt.loadNpmTasks 'grunt-svn-checkout'
	grunt.loadNpmTasks 'grunt-push-svn'

	# Default task
	grunt.registerTask 'default', [
		'replace:header'
		'replace:plugin'
		'coffeelint'
		'coffee'
		'jshint'
		'uglify'
		'compass'
		'clean:js'
	]

	# Build task
	grunt.registerTask 'build', [
		'default'
		'clean'
		'copy:main'
		# 'compress'
	]

	# Prepare a WordPress.org release
	grunt.registerTask 'release:prepare', [
		'svn_checkout'
		'build'
		'copy:svn_trunk'
		'copy:svn_tag'
		'copy:svn_assets'
		'replace:svn_trunk_readme'
		'replace:svn_tag_readme'
		'clean:svn_readme_md'
	]

	# WordPress.org release task
	grunt.registerTask 'release', [
		'release:prepare'
		'push_svn'
	]

	grunt.util.linefeed = '\n'

