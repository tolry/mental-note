const gulp = require('gulp'),
    gulpSass = require('gulp-sass')(require('node-sass')),
    concat = require('gulp-concat'),
    cssCompress = require('gulp-clean-css'),
    terser = require('gulp-terser')
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
    return gulp.src(scssFiles)
        .pipe(gulpSass({
            includePaths: 'node_modules/bootstrap/scss'
        }))
        .pipe(concat('scss-compiled.css'))
        .pipe(gulp.dest('web/css/'))
    ;
});

gulp.task('css', gulp.series('scss', function () {
    return gulp.src(cssFiles)
        .pipe(concat('all.css'))
        .pipe(gulp.dest('web/css/'))
        .pipe(cssCompress())
        .pipe(concat('all.min.css'))
        .pipe(gulp.dest('web/css/'))
    ;
}));

gulp.task('js', function () {
    return gulp.src(jsFiles)
        .pipe(concat('all.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(terser())
        .pipe(concat('all.min.js'))
        .pipe(gulp.dest('web/js/'))
    ;
});

gulp.task('fonts', function () {
    return gulp.src(fontFiles)
        .pipe(gulp.dest('web/fonts/'));
});

gulp.task('watch', gulp.series('js', 'css', function () {
    gulp.watch('assets/scss/*.scss', ['css']);
    gulp.watch('assets/js/*.js', ['js']);
}));

gulp.task('default', gulp.series( 'js', 'css', 'fonts' ));
