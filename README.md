Assets
======

An ultra simple assets management library for Laravel.

## Features

- Autogenerates links for JavaScript and CSS files.
- Supports local or remote assets.
- Prevents from loading duplicated assets.
- Automatically prefixes local assets with a configurable folder name.
- Sopports secure (https) and protocol agnostic (//) links.
- Supports **collections** (named groups of assets) that can be nested (aka dependencies).
- Automatically detects type of asset (CSS, JavaScript or collection).
- Allows autoloading of preconfigured assets and collections.

## Installation

Edit `composer.json` and add

	"require": {
		"stolz/assets": "dev-master"
	}

Then run

	composer install

Finally, add the service provider to `app/config/app.php`, within the `providers` array.

	'providers' => array(
		'Stolz\Assets\AssetsServiceProvider'

There is no need to add the Facade. The package will add it for you.

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

It works also for external assets

	Assets::add('//cdn.example.com/jquery.js'));
	Assets::add('http://example.com/style.css'));

If autodetection is not for you just use canonical functions

	Assets::addCss('assets.css');
	Assets::addJs('assets.js');

Methods can be chained

	Assets::add('collection')->addJs('assets.js')->css();


### Collections

A collection is named group of assets, that is, a set of JavaScript and CSS files.

Let me use an example to show you how easy and convenient to use they are.

Run

	php artisan config:publish stolz/assets

And set up a few collections inside of `app/config/packages/stolz/config.php`

	'collections' => array(
		'uno'	=> 'uno.css',
		'dos'	=> ['dos.css', 'dos.js'],
		'external'	=> ['http://example.com/external.css', 'https://secure.example.com/https.css', '//example.com/protocol/agnostic.js'],
		'mix'	=> ['internal.css', 'http://example.com/external.js'],
		'nested' => ['uno', 'dos'],
		'noduplicates' => ['nested', 'uno.css','dos.css', 'tres.js'],
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

Using `Assets::add('noduplicates');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="/css/uno.css" />
	<link type="text/css" rel="stylesheet" href="/css/dos.css" />
	<!-- JS -->
	<script type="text/javascript" src="/js/dos.js"></script>
	<script type="text/javascript" src="/js/tres.js"></script>

