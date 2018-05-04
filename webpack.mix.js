let mix = require('laravel-mix');

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

// Provided code.
// mix.js('resources/assets/js/app.js', 'public/js')
//    .sass('resources/assets/sass/app.scss', 'public/css');

// Updated to try to compile sass files.
mix.sass('resources/assets/sass/main.scss', 'public/css/index.css')
	.sass('resources/assets/sass/components/_homepage.scss', 'public/css/_homepage.css')
	.sass('resources/assets/sass/components/_resource.scss', 'public/css/_resource.css')
	.sass('resources/assets/sass/components/_tree.scss', 'public/css/_tree.css');
