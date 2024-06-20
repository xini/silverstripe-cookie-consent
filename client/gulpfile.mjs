import gulp from 'gulp';
const { series, parallel, src, dest, task, watch } = gulp;
import autoprefixer from 'gulp-autoprefixer';
import cssimport from 'gulp-cssimport';
import cleancss from 'gulp-clean-css';
import { deleteSync } from 'del';
import plumber from 'gulp-plumber';
import dartSass from 'sass';
import gulpSass from 'gulp-sass';
const sass = gulpSass(dartSass);
import sourcemaps from 'gulp-sourcemaps';

// load paths
const paths = {
    "styles": {
        "src": "src/scss/",
        "filter": "/*.+(scss)",
        "dist": "dist/css/"
    }
};

const sassOptions = {
    errLogToConsole: true,
    outputStyle: 'compressed'
};

const autoprefixerOptions = {
    cascade: false,
    supports: false
};

function styles(cb) {
    src(paths.styles.src + paths.styles.filter)
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(sourcemaps.init())
        .pipe(cssimport({matchPattern: "*.css"}))
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(autoprefixer(autoprefixerOptions))
        .pipe(cleancss({
            level: {
                1: {
                    normalizeUrls: false
                },
                2: {
                    restructureRules: true
                }
            }
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(dest(paths.styles.dist));
    cb();
}

function cleanstyles(cb) {
    deleteSync([
        paths.styles.dist + paths.styles.distfilter
    ]);
    cb();
}

function watchAll() {
    // watch for style changes
    watch(paths.styles.src + paths.styles.filter, series(cleanstyles, styles));
}

function onError(err) {
    console.log(err);
}

task('clean', series(
    parallel(
        cleanstyles
    )
));

task('build', series(
    parallel(
        cleanstyles
    ),
    parallel(
        styles
    )
));

task('css', series(
    cleanstyles,
    styles
));

task('default', series(
    parallel(
        cleanstyles
    ),
    parallel(
        styles
    ),
    watchAll
));
