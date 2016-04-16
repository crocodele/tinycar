
    var gulp    = require('gulp');
    var ts      = require('gulp-typescript');
    var sass    = require('gulp-sass');
    var rename  = require('gulp-rename');
    var replace = require('gulp-replace');
    var uglify  = require('gulp-uglify');
    
    // Compile all SCSS-files into one single file, minified
    gulp.task('styles', function()
    {
        return gulp.src('styles/Init.scss').
            pipe(sass({
                style       : 'expanded', 
                outputStyle : 'compressed'
            })).
            pipe(rename('common.min.css')).
            pipe(gulp.dest('public/assets/base/styles'));
    });
    
    // Compile TypeScript into JavaScript and minify into single file
    gulp.task('typescript', function()
    {
    	var tsProject = ts.createProject('tsconfig.json');
    	
    	return tsProject.src().
    		pipe(ts(tsProject)).js.
    		pipe(uglify()).
    		pipe(gulp.dest('public/assets/scripts/'));
    });
    
    // Generate white iconmap from black one
    gulp.task('iconmaps', function()
    {
    	return gulp.src('public/assets/base/images/iconmap.svg').
    	
    	    // Mark nodes existing attributes
    	    pipe(replace(/<circle([^>]{1,})fill=([^>]{1,})>/g, '<xcircle$1fill=$2>')).
    	    pipe(replace(/<path([^>]{1,})fill=([^>]{1,})>/g, '<xpath$1fill=$2>')).
    	    
    	    // Enforce color attributes
    		pipe(replace(/<circle([^>]{1,})>/g, '<circle fill="#000"$1>')).
    		pipe(replace(/<path([^>]{1,})>/g, '<path fill="#000"$1>')).
    		
    		// Unmark nodes
    		pipe(replace(/<xcircle([^>]{1,})>/g, '<circle$1>')).
    		pipe(replace(/<xpath([^>]{1,})>/g, '<path$1>')).
    		
    		// Change color
    		pipe(replace(/#000/g, '#FFF')).
    		
    		// Output
    		pipe(rename('iconmap-lite.svg')).
    		pipe(gulp.dest('public/assets/base/images'));
    });
    
    // Build
    gulp.task('build', ['styles', 'typescript'], function()
    {
    });
