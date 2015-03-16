module.exports = function( grunt ) {

	// Project configuration
	grunt.initConfig( {

		pkg:    grunt.file.readJSON( 'package.json' ),

		coffee: {
			compileWithMaps: {
				options: {
					sourceMap: true
				},
				files: {
					'js/page-links-to.js': 'js/page-links-to.coffee',
					'js/new-tab.js': 'js/new-tab.coffee',
				}
			}
		},

		coffeelint: {
			all: ['js/*.coffee'],
			options: {
				no_tabs: {
					level: 'ignore'
				},
				indentation: {
					level: 'ignore'
				}
			}
		},

		jshint: {
			all: [
				'Gruntfile.js'
			],
			options: {
				curly:   true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
				globals: {
					exports: true,
					module:  false
				}
			}
		},

		uglify: {
			options: {
				sourceMap: true,
				mangle: {
					except: ['jQuery']
				}
			},
			main: {
				files: {
					'js/page-links-to.min.js': ['js/page-links-to.js']
				},
				options: {
					sourceMapIn: 'js/page-links-to.js.map',
				}
			},
			newTab: {
				files: {
					'js/new-tab.min.js': ['js/new-tab.js']
				},
				options: {
					sourceMapIn: 'js/new-tab.js.map',
				}
			}
		},

		compass: {
			dist: {
				options: {
					sassDir: 'css',
					cssDir: 'css',
					imagesDir: 'images',
					sourcemap: true,
					environment: 'production'
				}
			}
		},

		watch:  {
			sass: {
				files: ['css/*.sass'],
				tasks: ['compass'],
				options: {
					debounceDelay: 500
				}
			},
			scripts: {
				files: ['js/**/*.coffee', 'js/vendor/**/*.js'],
				tasks: ['coffeelint', 'coffee', 'jshint', 'uglify'],
				options: {
					debounceDelay: 500
				}
			}
		},

		clean: {
			main: ['release/<%= pkg.version %>']
		},

		copy: {
			// Copy the plugin to a versioned release directory
			main: {
				src:  [
					'**',
					'!node_modules/**',
					'!release/**',
					'!.git/**',
					'!.sass-cache/**',
					'!css/**/*.sass',
					'!js/**/*.coffee',
					'!img/src/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules'
				],
				dest: 'release/<%= pkg.version %>/'
			}
		},

		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/page-links-to.<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: 'page-links-to/'
			}
		}
	} );

	// Load other tasks
	grunt.loadNpmTasks( 'grunt-contrib-jshint'   );
	grunt.loadNpmTasks( 'grunt-contrib-concat'   );
	grunt.loadNpmTasks( 'grunt-contrib-coffee'   );
	grunt.loadNpmTasks( 'grunt-coffeelint'       );
	grunt.loadNpmTasks( 'grunt-contrib-uglify'   );
	grunt.loadNpmTasks( 'grunt-contrib-compass'  );
	grunt.loadNpmTasks( 'grunt-contrib-watch'    );
	grunt.loadNpmTasks( 'grunt-contrib-clean'    );
	grunt.loadNpmTasks( 'grunt-contrib-copy'     );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );

	// Default task.
	grunt.registerTask( 'default', ['coffeelint', 'coffee', 'jshint', 'uglify', 'compass'] );

	grunt.registerTask( 'build', ['default', 'clean', 'copy', 'compress'] );

	grunt.util.linefeed = '\n';
};
