var gulp = require('gulp');
var sass = require('gulp-sass');
var packageImporter = require('node-sass-package-importer');

gulp.task('sass', function () {
    return gulp.src('../scss/*.scss')
        .pipe(sass({ 
            outputStyle: 'expanded',
            importer: packageImporter({
                extentions: ['.scss', 'css']
            })
        }))
        .pipe(gulp.dest('../css/'));
});

gulp.task('sass:watch', () => {
    gulp.watch('../scss/*.scss', gulp.series('sass'));
});