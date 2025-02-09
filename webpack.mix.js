const mix = require('laravel-mix');

// Compilar JS
mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/sneat.js', 'public/js'); // Agrega el JS de Sneat

// Compilar CSS
mix.css('resources/css/app.css', 'public/css')
   .css('resources/css/sneat.css', 'public/css'); // Agrega el CSS de Sneat

// Versionar los archivos
mix.version();
