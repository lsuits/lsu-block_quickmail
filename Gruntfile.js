"use strict";
 
module.exports = function (grunt) {
 
    // We need to include the core Moodle grunt file too, otherwise we can't run tasks like "amd".
    require("grunt-load-gruntfile")(grunt);
    grunt.loadGruntfile("../../Gruntfile.js");
 
    // Load all grunt tasks.
    grunt.loadNpmTasks("grunt-contrib-sass");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-clean");
 
    grunt.initConfig({
        sass: {
            dist: {
                files: {
                    'style.css' : 'sass/style.sass'
                }
            }
        },
        watch: {
            files: '**/*.sass',
            tasks: ['sass']
        }
    });
    // The default task (running "grunt" in console).
    grunt.registerTask("default", ["sass"]);
};