const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

// Original code. 
// elixir((mix) => {
//     mix.sass('app.scss')
//        .webpack('app.js');
// });

// Updated to try to compile sass files.
elixir(function(mix) {
	mix.sass('./public/css/SCSS/main.scss', './public/css/index.css');
	mix.sass('./public/css/SCSS/components/_homepage.scss', './public/css/_homepage.css');
});
