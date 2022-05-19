let gulp = require('gulp'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  cleanCss = require('gulp-clean-css'),
  rename = require('gulp-rename'),
  postcss = require('gulp-postcss');

const paths = {
  scss: {
    src: './scss/styles.scss',
    dest: './css',
    watch: './scss/**/*.scss'
  }
};

// Compile sass into CSS & auto-inject into browsers
function styles () {
  return gulp.src([paths.scss.src])
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(cleanCss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.scss.dest));
}

gulp.task('build',gulp.series(styles));

gulp.task('watch', gulp.series(function() {
  gulp.watch('scss/**/*.scss', gulp.series('build'));
}));

gulp.task('default', gulp.series('watch', 'build'));

