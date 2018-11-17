var argv = require('yargs').argv;
var gulp = require('gulp');
var gulpif = require('gulp-if');

var bro = require('gulp-bro');
var sourcemaps = require('gulp-sourcemaps');

const OPTIONS = {
    production: (argv.production === true)
};

const PATHS = {
    scripts: {
        watch: [
            'widgets/memsource/assets/**/*',
            '!widgets/memsource/assets/build/*'
        ],
        src: 'widgets/memsource/assets/js/main.js',
        dest: 'widgets/memsource/assets/build/'
    }
};

var transforms;
if (OPTIONS.production) {
    transforms = [
        'vueify',
        ['envify', { global: true }],
        ['uglifyify', { global: true }]
    ];
} else {
    transforms = [
        'vueify'
    ];
}

function compileScripts () {
    return gulp.src(PATHS.scripts.src)
        .pipe(gulpif(!OPTIONS.production, sourcemaps.init()))
        .pipe(bro({
            debug : !OPTIONS.production,
            transform: transforms
        }))
        .pipe(gulpif(!OPTIONS.production, sourcemaps.write()))
        .pipe(gulp.dest(PATHS.scripts.dest));
}

gulp.task('scripts', compileScripts);
gulp.task('watch', function () {
    gulp.watch(PATHS.scripts.watch, ['scripts']);
});

gulp.task('build', ['scripts']);
gulp.task('default', ['watch']);