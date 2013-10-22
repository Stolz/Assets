Assets
======

An ultra-simple-to-use assets management package for Laravel 4.

## Features

- Autogenerates links for JavaScript and CSS files.
- Supports local (including packages) or remote assets.
- Prevents from loading duplicated assets.
- Automatically prefixes local assets with a configurable folder name.
- Sopports secure (https) and protocol agnostic (//) links.
- Supports **collections** (named groups of assets) that can be nested (aka dependencies).
- Automatically detects type of asset (CSS, JavaScript or collection).
- Allows autoloading of preconfigured assets and collections.

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
		'Stolz\Assets\AssetsServiceProvider'

There is no need to add the Facade, the package will add it for you.

## Usage

### In your view

To generate CSS links

	echo Assets::css();

To generate JavaScript links

	echo Assets::js();

### In your routes/controllers

Add a single local asset

	Assets::add('assets.js');

Add more than one asset at once

	Assets::add(array('another/assets.js', 'one/more.css'));

Add an asset from a local package

	Assets::add('twitter/bootstrap:bootstrap.min.css');

It works also for external assets

	Assets::add('//cdn.example.com/jquery.js'));
	Assets::add('http://example.com/style.css'));

If your assets have no extension and autodetection fails, then just use canonical functions

	Assets::addCss('assets.css');
	Assets::addJs('assets.js');

*(Canonical functions also accept an array of assets)*

Methods can be chained

	Assets::add('collection')->addJs('assets.js')->css();

## Configuration


To bring up the config file run

	php artisan config:publish stolz/assets

This will create  `app/config/packages/stolz/config.php` file.


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

### Other configurable options

- `'autoload' => array(),`

	Here you may set which assets (CSS files, JavaScript files or collections) should be loaded by default.
- `'css_dir' => '/css',`
- `'js_dir' => '/js',`

	Override defaul prefix folder for local assets. Don't use trailing slash!.

- `'debug' => true,`

	When debug mode is enabled information about the process of loading assets will be sent to the log.
