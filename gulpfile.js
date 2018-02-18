var gulp        = require('gulp'),
    concat      = require('gulp-concat'),
    cssCompress = require('gulp-minify-css'),
    uglify      = require('gulp-uglify')
;

var cssFiles = [
    'node_modules/font-awesome/css/font-awesome.css',
    'node_modules/awesomplete/awesomplete.css',
    'assets/css/*css'
];

var jsFiles = [
    'node_modules/awesomplete/awesomplete.js',
    'assets/js/jquery-imageloader/jquery.imageloader.js',
    'assets/js/main.js'
];

var fontFiles = [
    'node_modules/font-awesome/fonts/**'
];

gulp.task('css', function () {
    gulp.src(cssFiles)
        .pipe(concat('all.css'))
        .pipe(gulp.dest('web/css/'))
        .pipe(cssCompress())
        .pipe(concat('all.min.css'))
        .pipe(gulp.dest('web/css/'))
    ;

    gulp.src(['node_modules/semantic-ui-css/themes/**'])
        .pipe(gulp.dest('web/css/themes'));
});

gulp.task('js', function () {
    gulp.src(jsFiles)
        .pipe(concat('all.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(uglify())
        .pipe(concat('all.min.js'))
        .pipe(gulp.dest('web/js/'))
    ;
});

gulp.task('fonts', function () {
    gulp.src(fontFiles)
        .pipe(gulp.dest('web/fonts/'));
});

gulp.task('watch', ['js', 'css'], function () {
    gulp.watch('assets/css/*.css', ['css']);
    gulp.watch('assets/js/*.js', ['js']);
});

gulp.task('default', ['js', 'css', 'fonts']);
