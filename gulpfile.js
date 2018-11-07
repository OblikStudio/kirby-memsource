// RELATED: https://github.com/sass/node-sass/issues/2362
// should be reverted and remove the warning:

// Including .css files with @import is non-standard behaviour which will be removed in future versions of LibSass.
// Use a custom importer to maintain this behaviour. Check your implementations documentation on how to create a custom importer.

var argv = require('yargs').argv;

var path = require('path');
var gulp = require('gulp');
var gulpif = require('gulp-if');
var gulpDebug = require('gulp-debug');

var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var sourcemaps = require('gulp-sourcemaps');
var browserify = require('gulp-browserify');

const OPTIONS = {
    production: (argv.production === true)
};

const PATHS = {
    styles: {
        watch: './widgets/memsource/assets/scss/**/*',
        src: './widgets/memsource/assets/scss/default.scss',
        dest: './widgets/memsource/assets/css/'
    },
    scripts: {
        watch: './widgets/memsource/assets/js/**/*',
        src: './widgets/memsource/assets/js/main.js',
        dest: './widgets/memsource/assets/build/'
    }
};

function compileScss () {
    return gulp.src(PATHS.styles.src)
        .pipe(gulpif(!OPTIONS.production, sourcemaps.init()))
        .pipe(sass({
            includePaths: [
                path.join(__dirname, './node_modules')
            ],
            outputStyle: OPTIONS.production ? 'compressed' : 'nested'
        }).on('error', sass.logError))
        .pipe(gulpif(!OPTIONS.production, sourcemaps.write()))
        .pipe(gulp.dest(PATHS.styles.dest));
}

function compileScripts () {
    return gulp.src(PATHS.scripts.src)
        .pipe(gulpif(!OPTIONS.production, sourcemaps.init()))
        .pipe(browserify({
            paths: ['./git_modules'],
            debug : !OPTIONS.production
        }).on('error', (e) => console.warn(e.message)))
        .pipe(gulpif(!OPTIONS.production, sourcemaps.write()))
        .pipe(gulpif(OPTIONS.production, uglify()))
        .pipe(gulp.dest(PATHS.scripts.dest));
}

gulp.task('scss', compileScss);
gulp.task('scripts', compileScripts);

gulp.task('watch', function () {
    gulp.watch(PATHS.styles.watch, ['scss']);
    gulp.watch(PATHS.scripts.watch, ['scripts']);
});

gulp.task('build', ['scss', 'scripts']);
gulp.task('default', ['watch']);
