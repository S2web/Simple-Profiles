
module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        // sass
        sass: {
            options: {
                outputStyle: 'compressed',
                sourceMap: false
            },
            dist: {
                files: {
                    'assets/css/profiles.css': 'assets/css/profiles.scss',
                }
            }
        },

        // autoprefixer
        autoprefixer: {
            
            multiple_files: {
                options: {
                    browsers: ['last 2 versions', 'ie 8', 'ie 9', 'ios 6', 'android 4'],
                    map: false
                },
                expand: true,
                flatten: true,
                src: 'assets/css/*.css',
                dest: 'assets/css/',
            }
        },

        // watch for changes and trigger sass
        watch: {
            sass: {
                files: ['assets/css/*.{scss,sass}'],
                tasks: ['sass', 'autoprefixer']
            }
        },

    });

    
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // register task
    grunt.registerTask( 'default', ['sass', 'autoprefixer', 'watch'] );

};