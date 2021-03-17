const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

/** Versioning / Cache Busting
 *
 *  Many developers suffix their compiled assets with a timestamp or unique token
 *  to force browsers to load the fresh assets instead of serving stale copies of
 *  the code. Mix can automatically handle this for you using the version method.
 *
 *  Because versioned files are usually unnecessary in development,
 *  you may instruct the versioning process to only run during
 *  "npm run prod"
 *
 *  Ref: https://laravel.com/docs/master/mix#versioning-and-cache-busting
 */
if (mix.inProduction()) {
    mix.version();
}
