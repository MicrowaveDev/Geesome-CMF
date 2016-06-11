//var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

//elixir(function(mix) {
//    mix.sass('app.scss');
//});


var gulp = require('gulp');

var concat = require('gulp-concat');
var concatCss = require('gulp-concat-css');

var js_dir = 'public/assets/js/';
var css_dir = 'public/assets/css/';
var dist_dir = 'public/assets/dist/';

var paths = {
    scripts: [
        js_dir +'angular/awesome-edit/dist/a-edit.js',
        js_dir +'admin-app/app.js',
        js_dir +'admin-app/**/*.js'
    ],
    styles: [
        js_dir + 'angular/awesome-edit/dist/a-edit.css',
        css_dir + 'sb-admin.css'
    ]
};

gulp.task('concatAngularVendorJS', function() {
    return gulp.src([
            js_dir +'angular/angular.min.js',
            js_dir +'angular/ui-bootstrap*',
            js_dir +'angular/*.js'
        ])
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest(dist_dir));
});

gulp.task('concatAdminAppJS', function() {
    return gulp.src(paths.scripts)
        .pipe(concat('app.js'))
        .pipe(gulp.dest(dist_dir));
});

gulp.task('concatAdminAppCSS', function() {
    return gulp.src(paths.styles)
        .pipe(concatCss("app.css"))
        .pipe(gulp.dest(dist_dir));
});

gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['concatAdminAppJS']);
    gulp.watch(paths.styles, ['concatAdminAppCSS']);
});

gulp.task('default', ['concatAngularVendorJS', 'concatAdminAppJS', 'concatAdminAppCSS', 'watch']);