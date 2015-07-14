var gulp = require('gulp');

gulp.task('copy-foundation', function() {
    console.log('Copying foundation files');
    /*
    You can do this 'src' stuff nicer, but I'm using it this way for now so moo! :)
     */
    gulp.src(
        [
            'bower_components/foundation/js/foundation.min.js',
            'bower_components/jquery/dist/jquery.min.js',
            'bower_components/jquery/dist/jquery.min.map',
            'bower_components/modernizr/modernizr.js'
        ]
    ).pipe(gulp.dest('web/js/'));

    console.log('Copied JS');

    gulp.src(
        [
            'bower_components/foundation/css/normalize.css',
            'bower_components/foundation/css/foundation.css',
            'bower_components/foundation/css/normalize.css.map',
            'bower_components/foundation/css/foundation.css.map'
        ]
    ).pipe(gulp.dest('web/css/'));

    console.log('Copied CSS');
});
