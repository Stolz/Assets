Assets
======

A simple laravel assets management PHP library from the view. Forked from [Stolz/Assets](https://github.com/Stolz/Assets).
The main reason for the fork is to add the ability to assign and minify assets from the laravel view on the fly without having to use controllers or routes.

1. [Features](#features).
- [Supported frameworks](#frameworks).
- [Installation](#installation).
- [Usage](#usage).
 - [Views](#views).
- [Configuration](#configuration).
 - [Pipeline](#pipeline).
 - [More options](#options).
- [Non static interface usage](#nonstatic).

----

<a id="features"></a>
## Features

- **Very easy to use**.
- Can minify and generate multiple times per page.
- Can be used inside laravel views directly.
- Autogenerates tags for including your JavaScript and CSS files.
- Supports programmatically adding assets on the fly.
- Supports local (**including packages**) or remote assets.
- Prevents from loading duplicated assets.
- Included assets **pipeline** (*concatenate and minify all your assets to a single file*) with URL **timestamps** support.
- Automatically prefixes local assets with a configurable folder name.
- Supports secure (*https*) and protocol agnostic (*//*) links.


<a id="frameworks"></a>
## Supported frameworks

The library is framework agnostic and it should work well with any framework or naked PHP application. Nevertheless, the following instructions have been tailored for Laravel 4 framework. If you want to use the library in any other scenario please read the [non static interface](#nonstatic) instructions.



<a id="installation"></a>
## Installation

In your Laravel base directory run

	composer require "xees/assets:dev-master"

Then edit `app/config/app.php` and add the service provider within the `providers` array

	'providers' => array(
		...
		'Xees\Assets\ManagerServiceProvider'

There is no need to add the Facade, the package will add it for you.



<a id="usage"></a>
## Usage
<a id="views"></a>
### In your views/layouts

To minify and output the CSS using `<link rel="stylesheet">` tags

	echo Assets::css($assets);

To minify and output the JavaScript `<script>` tags

	echo Assets::js($assets);

It accepts an array or a single asset.

<a id="configuration"></a>
## Configuration

To bring up the config file run

	php artisan config:publish xees/assets

This will create  `app/config/packages/xees/config.php` file that you may use to configure your application assets.

If you are using the [non static interface](#nonstatic) just pass an associative array of config settings to the class constructor.

<a id="pipeline"></a>
### Pipeline

To enable pipeline use the `pipeline` config option

	'pipeline' => true,

Once it's enabled all your assets will be concatenated and minified to a single file, improving load speed and reducing the number of requests that a browser makes to render a web page.

This process can take a few seconds depending on the amount of assets and your connection but it's triggered only the first time you load a page whose assets have never been pipelined before. The subsequent times the same page (or any page using the same assets) is loaded, the previously pipelined file will be used giving you much faster loading time and less bandwidth usage.


**Note:** Using the pipeline is recommended only for production environment.

If your assets have changed since they were pipelined use the provided artisan command to purge the pipeline cache

	php artisan asset:purge-pipeline

A custom timestamp may be appended to the pipelined assets URL by setting `pipeline` config option to an integer value greather than 1:

Example:

	'pipeline' => 12345,

will produce:

	<link type="text/css" rel="stylesheet" href="css/min/135b1a960b9fed4dd65d1597ff593321.css?12345" />
	<script type="text/javascript" src="js/min/5bfed4dd65d1597ff1a960b913593321.js?12345"></script>


<a id="options"></a>
### Other configurable options

- `'css_dir' => 'css',`
- `'js_dir' => 'js',`

	Override default folder for local assets. Don't use trailing slash!.

- `'pipeline_dir' => 'min',`

	Override default folder for pipelined assets. Don't use trailing slash!.

----

<a id="nonstatic"></a>
## Non static interface

You can use the library without using static methods. The signature of all methods is the same described above.

	require '/path/to/Xees/Assets/Manager.php';

	// Configure options
	$config = array(
		'pipeline' => true,
		...
	);

	// Load the library
	$assets = new \Xees\Assets\Manager($config);

	// Add some assets
	$assets->addCss('style.css')->addJs('script.js');

	// Generate HTML tags
	echo $assets->css(),$assets->js();

----
