<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Local assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul prefix folder for local assets. They are relative to your
	| public folder. Don't use trailing slash!.
	|
	| Default for CSS: 'css'
	| Default for JavaScript: 'js'
	| Default for packages: 'packages'
	*/

	'css_dir' => 'css',
	'js_dir' => 'js',
	'packages_dir' => 'packages',

	/*
	|--------------------------------------------------------------------------
	| Assets collections
	|--------------------------------------------------------------------------
	|
	| Collections allow you to have named groups of assets (CSS or JavaScript files).
	|
	| If an asset has been loaded already it won't be added again. Collections may be
	| nested but please be careful to avoid recursive loops.
	|
	| To avoid conflicts with the autodetection of asset types make sure your
	| collections names don't end with ".js" or ".css".
	|
	|
	| Example:
	|	'collections' => array(
	|
	|		// jQuery (CDN)
	|		'jquery-cdn' => [
	|			'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js
	|		'],
	|
	|		// Twitter Bootstrap (CDN)
	|		'bootstrap-cdn' => [
	|			'jquery-cdn',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js'
	|		],
	|
	|		//Zurb Foundation (CDN)
	|		'foundation-cdn' => [
	|			'//cdn.jsdelivr.net/foundation/5.3.0/js/vendor/modernizr.js',
	|			'jquery2-cdn',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/js/foundation.min.js',
	|			'app.js',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/css/normalize.css',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/css/foundation.min.css',
	|		],
	|	),
	*/

	'collections' => array(),

	/*
	|--------------------------------------------------------------------------
	| Preload assets
	|--------------------------------------------------------------------------
	|
	| Here you may set which assets (CSS files, JavaScript files or collections)
	| should be loaded by default even if you don't explicitly add them on run time.
	|
	*/

	'autoload' => array(),

	/*
	|--------------------------------------------------------------------------
	| Assets pipeline
	|--------------------------------------------------------------------------
	|
	| When enabled, all your assets will be concatenated and minified to a sigle
	| file, improving load speed and reducing the number of requests that the
	| browser makes to render a web page.
	|
	| It's a good practice to enable it only on production environment.
	|
	| Use an integer value greather than 1 to append that value as an URL timestamp.
	|
	| Default: false
	*/

	'pipeline' => false,

	/*
	|--------------------------------------------------------------------------
	| Public directory / Webroot
	|--------------------------------------------------------------------------
	|
	| Absolute path to the public directory of your App (WEBROOT).
	| Required ONLY if you enable the pipeline.
	| No trailing slash!.
	|
	| Default: none
	*/

	//'public_dir' => '/var/www/myapp/public',

	/*
	|--------------------------------------------------------------------------
	| Pipelined assets directory
	|--------------------------------------------------------------------------
	|
	| Override defaul folder for storing pipelined assets. Relative to your
	| assets folder. Don't use trailing slash!.
	|
	| Default: 'min'
	*/

	'pipeline_dir' => 'min',

	/*
	|--------------------------------------------------------------------------
	| Pipelined Gzip
	|--------------------------------------------------------------------------
	|
	| Enable pipelined assets compression with Gzip.
	| Useful only if your webserver supports Gzip in $_SERVER['HTTP_ACCEPT_ENCODING'].
	| Set to true to use the default compression level.
	| Set an integer between 0 (no compression) and 9 (maximum compression) to choose compression level.
	|
	| Default: false
	*/

	'pipeline_gzip' => false, // Do not enable unless you know what you are doing!

);
