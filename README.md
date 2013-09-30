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
- Autoload of preconfigured assets and collections.

## Usage

### In your view

To generate CSS links

	echo Assets::css();

To generate JavaScript links

	echo Assets::js();

### In your controller

Add a single local asset

	Assets::add('assets.js');

Add more than one asset at once

	Assets::add(array('another/assets.js', 'one/more.css'));

It works also for external assets

	Assets::add('//cdn.example.com/jquery.js'));
	Assets::add('http://example.com/style.css'));

If autodetection is not for you just use canonical functions

	Assets::add_css('assets.css');
	Assets::add_js('assets.js');

Methods can be chained

	Assets::add('collection')->add_js('assets.js')->css();



### Collections

Let me use an example to show you how easy and convinient to use they are. Set this inside of `config.php`

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

