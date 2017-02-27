module.exports = (grunt) ->

	# Define CoffeeScript files in one place (no path or extension)
	coffee_files = [
		'page-links-to'
		'new-tab'
	]

	# Build some arrays and objects
	coffee_parse = (files) ->
		out = {}
		for file in files
			out["js/#{file}.js"] = "js/#{file}.coffee"
		out

	uglify_parse = (file) ->
		src: "js/#{file}.js"
		dest: "js/#{file}.min.js"
		sourceMapIn: "js/#{file}.js.map"

	coffee_uglify_files = (uglify_parse file for file in coffee_files)

	# Project configuration
	grunt.initConfig
		pkg: grunt.file.readJSON('package.json')

		coffee:
			options:
				join: yes
				sourceMap: yes
			default:
				files: coffee_parse coffee_files

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
				files: coffee_uglify_files

		compass:
			default:
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
					'!js/**/*.src.coffee'
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

		wp_deploy:
			default:
				options:
					plugin_slug: '<%= pkg.name %>'
					build_dir: 'release/svn/'
					assets_dir: 'assets/'

		clean:
			release: [
				'release/<%= pkg.version %>/'
				'release/svn/'
			]
			js: [
				'js/*.js'
				'!js/*.min.js'
				'js/*.src.coffee'
				'js/*.js.map'
				'!js/*.min.js.map'
			]
			svn_readme_md: [
				'release/svn/readme.md'
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
					'!js/**/*.src.coffee'
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
			svn:
				cwd: 'release/<%= pkg.version %>/'
				expand: yes
				src: '**'
				dest: 'release/svn/'

		replace:
			header:
				src: [ '<%= pkg.name %>.php' ]
				overwrite: yes
				replacements: [
					from: /Version:(\s*?)[a-zA-Z0-9.-]+$/m
					to: 'Version:$1<%= pkg.version %>'
				]
			plugin:
				src: [ 'classes/plugin.php' ]
				overwrite: yes
				replacements: [
					from: /^(\s*?)const(\s+?)VERSION(\s*?)=(\s+?)'[^']+';/m
					to: "$1const$2VERSION$3=$4'<%= pkg.version %>';"
				,
					from: /^(\s*?)const(\s+?)CSS_JS_VERSION(\s*?)=(\s+?)'[^']+';/m
					to: "$1const$2CSS_JS_VERSION$3=$4'<%= pkg.version %>';"
				]
			svn_readme:
				src: [ 'release/svn/readme.md' ]
				dest: 'release/svn/readme.txt'
				replacements: [
					from: /^# (.*?)( #+)?$/mg
					to: '=== $1 ==='
				,
					from: /^## (.*?)( #+)?$/mg
					to: '== $1 =='
				,
					from: /^### (.*?)( #+)?$/mg
					to: '= $1 ='
				,
					from: /^Stable tag:\s*?[a-zA-Z0-9.-]+(\s*?)$/mi
					to: 'Stable tag: <%= pkg.version %>$1'
				]

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
	grunt.loadNpmTasks 'grunt-wp-deploy'

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
	]

	# Prepare a WordPress.org release
	grunt.registerTask 'release:prepare', [
		'copy:svn'
		'replace:svn_readme'
		'clean:svn_readme_md'
	]

	# Deploy out a WordPress.org release
	grunt.registerTask 'release:deploy', [
		'wp_deploy'
	]

	# WordPress.org release task
	grunt.registerTask 'release', [
		# Everyone builds
		'build'
		# Only for WordPress.org
		'release:prepare'
		'release:deploy'
	]

	grunt.util.linefeed = '\n'
