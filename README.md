Assets
======

An ultra-simple-to-use assets management package for Laravel 4.

1. [Features](#features).
- [Installation](#installation).
- [Usage](#usage).
 - [Views](#views).
 - [Controllers](#controllers).
- [Configuration](#configuration).
 - [Collections](#collections).
 - [Pipeline](#pipeline).
 - [More options](#options).
- [Collections samples](#samples).

----
<a id="features"></a>
## Features

- **Very easy to use**.
- Autogenerates tags for including your JavaScript and CSS files.
- Supports programmatically adding assets on the fly.
- Supports local (**including packages**) or remote assets.
- Prevents from loading duplicated assets.
- Included assets pipeline (*concatenate and minify all your assets to a single file*).
- Automatically prefixes local assets with a configurable folder name.
- Supports secure (*https*) and protocol agnostic (*//*) links.
- Supports **collections** (*named groups of assets*) that can be nested, allowing assets dependencies.
- Automatically detects type of asset (CSS, JavaScript or collection).
- Allows autoloading by default preconfigured assets and collections.

----
<a id="installation"></a>
## Installation

Edit `composer.json` and add `"stolz/assets": "dev-master"` to the `require` section

	"require": {
		...
		"stolz/assets": "dev-master"
	}

Then run

	composer install

Finally, add the service provider within the `providers` array of `app/config/app.php`

	'providers' => array(
		...
		'Stolz\Assets\ManagerServiceProvider'

There is no need to add the Facade, the package will add it for you.

----
<a id="usage"></a>
## Usage
<a id="views"></a>
### In your views/layouts

To generate the CSS `<link rel="stylesheet">` tags

	echo Assets::css();

To generate the JavaScript `<script>` tags

	echo Assets::js();

<a id="controllers"></a>
### In your routes/controllers

Basically all you have to do to add and asset, no matter if it's CSS or JS, is:

	Assets::add('filename');

For more advanced use keep reading ...

Add more than one asset at once

	Assets::add(array('another/file.js', 'one/more.css'));

Add an asset from a local package

	Assets::add('twitter/bootstrap:bootstrap.min.css');

Note all local assets filenames are considered to be relative to you assets directory so you don't need to provide it every time with `js/file.js` or `css/file.css`, using just `file.js` or `file.css` will be enought.

You may add remote assets in the same fashion

	Assets::add('//cdn.example.com/jquery.js'));
	Assets::add('http://example.com/style.css'));

If your assets have no extension and autodetection fails, then just use canonical functions

	Assets::addCss('asset.css');
	Assets::addJs('asset.js');

*(Canonical functions also accept an array of assets)*

If at some point you decide you added the wrong assets you can reset them and start over

	Assets::reset(); //Both CSS and JS
	Assets::resetCss();
	Assets::resetJs();

All methods that don't generate output can be chained

	Assets::reset()->add('collection')->addJs('file.js')->css();

----
<a id="configuration"></a>
## Configuration

To bring up the config file run

	php artisan config:publish stolz/assets

This will create  `app/config/packages/stolz/config.php` file that you may use to configure your application assets.

<a id="collections"></a>
### Collections

A collection is a named group of assets, that is, a set of JavaScript and CSS files. Any collection may include more collections, allowing dependencies definition and collection nesting.

Let me use an example to show you how easy and convenient to use they are.

	'collections' => array(
		'uno'	=> 'uno.css',
		'dos'	=> ['dos.css', 'dos.js'],
		'external'	=> ['http://example.com/external.css', 'https://secure.example.com/https.css', '//example.com/protocol/agnostic.js'],
		'mix'	=> ['internal.css', 'http://example.com/external.js'],
		'nested' => ['uno', 'dos'],
		'duplicates' => ['nested', 'uno.css','dos.css', 'tres.js'],
	),

Using `Assets::add('dos');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="/css/dos.css" />
	<!-- JS -->
	<script type="text/javascript" src="/js/dos.js"></script>

Using `Assets::add('external');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="http://example.com/external.css" />
	<link type="text/css" rel="stylesheet" href="https://secure.example.com/https.css" />
	<!-- JS -->
	<script type="text/javascript" src="//example.com/protocol/agnostic.js"></script>

Using `Assets::add('mix');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="/css/internal.css" />
	<!-- JS -->
	<script type="text/javascript" src="http://example.com/external.js"></script>

Using `Assets::add('nested');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="/css/uno.css" />
	<link type="text/css" rel="stylesheet" href="/css/dos.css" />
	<!-- JS -->
	<script type="text/javascript" src="/js/dos.js"></script>

Using `Assets::add('duplicates');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="/css/uno.css" />
	<link type="text/css" rel="stylesheet" href="/css/dos.css" />
	<!-- JS -->
	<script type="text/javascript" src="/js/dos.js"></script>
	<script type="text/javascript" src="/js/tres.js"></script>

Note even this collection had duplicated assets they have been included only once.

<a id="pipeline"></a>
### Pipeline

To enable pipeline use the config file

	'pipeline' => true,

Once it's enabled all your assets will be concatenated and minified to a single file, improving load speed and reducing the number of requests that a browser makes to render a web page.

This process can take a few seconds depending on the amount of assets and your connection but it's triggered only the first time you load a page whose assets have never been pipelined before. The subsequent times the same page (or any page using the same assets) is loaded, the previously pipelined file will be used giving you much faster loading time and less bandwidth usage.

Using the pipeline is recommended only for production environment.

<a id="options"></a>
### Other configurable options

- `'autoload' => array(),`

	Here you may set which assets (CSS files, JavaScript files or collections) should be loaded by default.
- `'css_dir' => 'css',`
- `'js_dir' => 'js',`

	Override default prefix folder for local assets. Don't use trailing slash!.

- `'debug' => true,`

	When debug mode is enabled information about the process of loading assets will be sent to the log.

<a id="samples"></a>
## Collections samples

	//jQuery (CDN)
	'jquery-cdn' => ['//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'],

	//jQuery UI (CDN)
	'jquery-ui-cdn' => [
		'jquery-cdn',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js',
	],

	//Twitter Bootstrap (CDN)
	'bootstrap-cdn' => [
		'jquery-cdn',
		'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js'
	],

	// Twitter Bootstrap
	// Install with: composer require twitter/bootstrap:3.0.*; artisan asset:publish twitter/bootstrap --path=vendor/twitter/bootstrap/dist/
	'bootstrap' => [
		'jquery-cdn',
		'twitter/bootstrap:bootstrap.min.css',
		'twitter/bootstrap:bootstrap-theme.min.css',
		'twitter/bootstrap:bootstrap.min.js'
	],

	//Zurb Foundation (CDN)
	'foundation-cdn' => [
		'jquery-cdn',
		'//cdn.jsdelivr.net/foundation/4.3.2/css/normalize.css',
		'//cdn.jsdelivr.net/foundation/4.3.2/css/foundation.min.css',
		'//cdn.jsdelivr.net/foundation/4.3.2/js/foundation.min.js',
		'app.js'
	],

