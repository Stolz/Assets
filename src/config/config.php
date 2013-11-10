<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Local assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul prefix folder for local assets. Don't use trailing slash!.
	| They are relative to your public folder.
	|
	| Default for CSS: 'css'
	| Default for JS: 'js'
	*/

	'css_dir' => 'css',
	'js_dir' => 'js',

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
	| Use an integer value greather than 1 to append a timestamp to the URL.
	|
	| Default: false
	*/

	'pipeline' => false,

	/*
	|--------------------------------------------------------------------------
	| Pipelined assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul folder for storing pipelined assets. Don't use trailing slash!.
	| Relative to your assets folder.
	|
	| Default: 'min'
	*/

	'pipeline_dir' => 'min'
);
