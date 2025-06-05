const mix = require('laravel-mix');

// Copying AdminLTE CSS and JS
mix.copy('node_modules/admin-lte/dist/css/adminlte.min.css', 'public/css/adminlte.min.css');
mix.copy('node_modules/admin-lte/dist/js/adminlte.min.js', 'public/js/adminlte.min.js');

// Optionally, if you need to copy Bootstrap and jQuery
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css/bootstrap.min.css');
mix.copy('node_modules/jquery/dist/jquery.min.js', 'public/js/jquery.min.js');
// Don't forget to copy Bootstrap's JS if you need it
mix.copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js/bootstrap.bundle.min.js');

// If you have SASS files or need to compile JS
mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
