const gulp = require('gulp'),
    gulpSass = require('gulp-sass'),
    concat = require('gulp-concat'),
    cssCompress = require('gulp-clean-css'),
    jsUglify = require('gulp-uglify')
;

const scssFiles = [
    'assets/scss/*scss'
];

const cssFiles = [
    'node_modules/font-awesome/css/font-awesome.css',
    'node_modules/awesomplete/awesomplete.css',
    'web/css/scss-compiled.css'
];

const jsFiles = [
    'node_modules/jquery/dist/jquery.js',
    'node_modules/popper.js/dist/umd/popper.js',
    'node_modules/popper.js/dist/umd/popper-utils.js',
    'node_modules/bootstrap/dist/js/bootstrap.js',
    'assets/js/jquery-imageloader/jquery.imageloader.js',
    'node_modules/awesomplete/awesomplete.js',
    'assets/js/main.js'
];

const fontFiles = [
    'node_modules/font-awesome/fonts/**'
];

gulp.task('scss', function () {
    gulp.src(scssFiles)
        .pipe(gulpSass({
            includePaths: 'node_modules/bootstrap/scss'
        }))
        .pipe(concat('scss-compiled.css'))
        .pipe(gulp.dest('web/css/'))
    ;
});

gulp.task('css', ['scss'], function () {
    gulp.src(cssFiles)
        .pipe(concat('all.css'))
        .pipe(gulp.dest('web/css/'))
        .pipe(cssCompress())
        .pipe(concat('all.min.css'))
        .pipe(gulp.dest('web/css/'))
    ;
});

gulp.task('js', function () {
    gulp.src(jsFiles)
        .pipe(concat('all.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(jsUglify())
        .pipe(concat('all.min.js'))
        .pipe(gulp.dest('web/js/'))
    ;
});

gulp.task('fonts', function () {
    gulp.src(fontFiles)
        .pipe(gulp.dest('web/fonts/'));
});

gulp.task('watch', ['js', 'css'], function () {
    gulp.watch('assets/scss/*.scss', ['css']);
    gulp.watch('assets/js/*.js', ['js']);
});

gulp.task('default', ['js', 'css', 'fonts']);
