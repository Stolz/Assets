Assets
======

An ultra-simple-to-use assets management PHP library.

[![Build Status](https://travis-ci.org/Stolz/Assets.png?branch=master)](https://travis-ci.org/Stolz/Assets)

1. [Features](#features).
- [Supported frameworks](#frameworks).
- [Installation](#installation).
- [Usage](#usage).
 - [Views](#views).
 - [Controllers](#controllers).
 - [API](#api).
- [Configuration](#configuration).
 - [Collections](#collections).
 - [Pipeline](#pipeline).
 - [More options](#options).
- [Non static interface usage](#nonstatic).
- [Sample collections](#samples).
- [Troubleshooting / F.A.Q.](#troubleshooting).
- [License](#license).

----

<a id="features"></a>
## Features

- **Very easy to use**.
- Autogenerates HTML tags for including your JavaScript and CSS files.
- Supports programmatically adding assets on the fly.
- Supports local (**including packages**) or remote assets.
- Prevents from loading duplicated assets.
- Included assets **pipeline** (*concatenate and minify all your assets to a single file*) with URL **timestamps** and **gzip** support.
- Automatically prefixes local assets with a configurable folder name or url.
- Supports secure (*https*) and protocol agnostic (*//*) links.
- Supports **collections** (*named groups of assets*) that can be nested, allowing assets dependencies definitions.
- Automatically detects type of asset (CSS, JavaScript or collection).
- Allows autoloading by default preconfigured assets and collections.


<a id="frameworks"></a>
## Supported frameworks

The library is framework agnostic and it should work well with any framework or naked PHP application. Nevertheless, the following instructions have been tailored for Laravel framework. If you want to use the library in any other scenario please read the [non static interface](#nonstatic) instructions.

<a id="installation"></a>
## Installation

In your project base directory run

	composer require stolz/assets

Then edit `config/app.php` and add the service provider within the `providers` array.

	'providers' => array(
		...
		'Stolz\Assets\Laravel\ServiceProvider',

There is no need to add the Facade, the package will bind it to the IoC for you.

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

*For more advanced uses keep reading ...*

Add more than one asset at once

	Assets::add(array('another/file.js', 'one/more.css'));

Add an asset from a local package

	Assets::add('twitter/bootstrap:bootstrap.min.css');

Note all local assets filenames are considered to be relative to you assets directory (configurable via `css_dir` and `js_dir` options) so you don't need to provide it every time with `js/file.js` or `css/file.css`, using just `file.js` or `file.css` will be enought.

You may add remote assets in the same fashion

	Assets::add('//cdn.example.com/jquery.js');
	Assets::add('http://example.com/style.css');

If your assets have no extension and autodetection fails, then just use canonical functions *(they accept an array of assets too)*

	Assets::addCss('asset.css');
	Assets::addJs('asset.js');

If at some point you decide you added the wrong assets you can reset them and start over

	Assets::reset();    // Reset both CSS and JS
	Assets::resetCss(); // Reset only CSS
	Assets::resetJs();  // Reset only JS

All methods that don't generate output will accept chaining:

	Assets::reset()->add('collection')->addJs('file.js')->css();

<a id="api"></a>
### API

There are some methods not documented here. For a **full list of all the availabe methods** please read the provided [`API.md`](https://github.com/Stolz/Assets/blob/master/API.md) file.

<a id="configuration"></a>
## Configuration

To bring up the config file run

	php artisan vendor:publish

This will create the file `config/assets.php` that you may use to configure the library. With the provided comments all options should be selfexplanatory.

If you are using the [non static interface](#nonstatic) just pass an associative array of config settings to the class constructor.

<a id="collections"></a>
### Collections

A collection is a named group of assets, that is, a set of JavaScript and CSS files. Any collection may include more collections, allowing dependencies definition and collection nesting. Collections can be created on run time or via config file.

To register a collection on run time for later use:

	Assets::registerCollection($collectionName, array('some', 'awesome', 'assets'));

To preconfigure collections using the config file:

	// ... config.php ...
	'collections' => [
		'one'	=> 'one.css',
		'two'	=> ['two.css', 'two.js'],
		'external'	=> ['http://example.com/external.css', 'https://secure.example.com/https.css', '//example.com/protocol/agnostic.js'],
		'mix'	=> ['internal.css', 'http://example.com/external.js'],
		'nested' => ['one', 'two'],
		'duplicated' => ['nested', 'one.css','two.css', 'three.js'],
	],

Let me show you how to use the above collection in different scenarios:

Using `Assets::add('two');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="css/two.css" />
	<!-- JS -->
	<script type="text/javascript" src="js/two.js"></script>

Using `Assets::add('external');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="http://example.com/external.css" />
	<link type="text/css" rel="stylesheet" href="https://secure.example.com/https.css" />
	<!-- JS -->
	<script type="text/javascript" src="//example.com/protocol/agnostic.js"></script>

Using `Assets::add('mix');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="css/internal.css" />
	<!-- JS -->
	<script type="text/javascript" src="http://example.com/external.js"></script>

Using `Assets::add('nested');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="css/one.css" />
	<link type="text/css" rel="stylesheet" href="css/two.css" />
	<!-- JS -->
	<script type="text/javascript" src="js/two.js"></script>

Using `Assets::add('duplicated');` will result in

	<!-- CSS -->
	<link type="text/css" rel="stylesheet" href="css/one.css" />
	<link type="text/css" rel="stylesheet" href="css/two.css" />
	<!-- JS -->
	<script type="text/javascript" src="js/two.js"></script>
	<script type="text/javascript" src="js/three.js"></script>

Note even this last collection had duplicated assets they have been included only once.

<a id="pipeline"></a>
### Pipeline

To enable pipeline use the `pipeline` config option

	'pipeline' => true,

Once it's enabled all your assets will be concatenated and minified to a single file, improving load speed and reducing the number of requests that a browser makes to render a web page.

This process can take a few seconds depending on the amount of assets and your connection but it's triggered only the first time you load a page whose assets have never been pipelined before. The subsequent times the same page (or any page using the same assets) is loaded, the previously pipelined file will be used giving you much faster loading time and less bandwidth usage.

**Note:** For obvious reasons, using the pipeline is recommended only for production environment.

If your assets have changed since they were pipelined use the provided artisan command to purge the pipeline cache

	php artisan asset:flush

To deal with cache issues a custom timestamp may be appended to the pipelined assets filename by setting `pipeline` config option to an integer value greather than 1:

Example:

	'pipeline' => 12345,

will produce:

	<link type="text/css" rel="stylesheet" href="css/min/pipelineHash.12345.css" />
	<script type="text/javascript" src="js/min/pipelineHash.12345.js"></script>

If you happend to use NGINX with the [gzip_static](http://nginx.org/en/docs/http/ngx_http_gzip_static_module.html) feature enabled, add the following config option to automatically create a suitable gziped version of the pipelined assets:

	'pipeline_gzip' => true,

<a id="options"></a>
### Other configurable options

For a **full list of all the availabe config options** please read the provided [`API.md`](https://github.com/Stolz/Assets/blob/master/API.md) file.

- `'autoload' => array(),`

	Here you may set which assets (CSS files, JavaScript files or collections) should be loaded by default.
- `'css_dir' => 'css',`
- `'js_dir' => 'js',`

	Override default base URL/folder for assets. Don't use trailing slash!. They will be prepended to all your local assets. Both relative paths or full URLs are supported.

- `'pipeline_dir' => 'min',`

	Override default folder for pipelined assets. Don't use trailing slash!.

It is possible to **change any config options on the fly** by passing an array of settings to the `config()` method. Usefull if some assets use a different base directory or if you want to pipeline some assets and skip others from the pipeline. i.e:

	echo Assets::reset()->add('do-not-pipeline-this.js')->js(),
	     Assets::reset()->add('please-pipeline-this.js')->config(array('pipeline' => true))->js();

----

<a id="nonstatic"></a>
## Non static interface

You can use the library without using static methods. The signature of all methods is the same as described above but using an instance of the class instead.

	// Load the library with composer
	require __DIR__ . '/vendor/autoload.php';

	// Set config options
	$config = array(
		'collections' => array(...),
		'autoload' => array(...),
		'pipeline' => true,
		'public_dir' => '/absolute/path/to/your/webroot/public/dir'
		...
	);

	// Instantiate the library
	$assets = new \Stolz\Assets\Manager($config);

	// Add some assets
	$assets->add('style.css')->add('script.js');

	// Generate HTML tags
	echo $assets->css(),$assets->js();

----

<a id="samples"></a>
## Sample collections

	// jQuery (CDN)
	'jquery-cdn' => ['//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js'],

	// jQuery UI (CDN)
	'jquery-ui-cdn' => [
		'jquery-cdn',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js',
	),

	// Twitter Bootstrap (CDN)
	'bootstrap-cdn' => [
		'jquery-cdn',
		'//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js'
	],

	// Zurb Foundation (CDN)
	'foundation-cdn' => [
		'jquery-cdn',
		'//cdn.jsdelivr.net/foundation/5.4.7/css/normalize.css',
		'//cdn.jsdelivr.net/foundation/5.4.7/css/foundation.min.css',
		'//cdn.jsdelivr.net/foundation/5.4.7/js/foundation.min.js',
		'app.js'
	],

<a id="license"></a>
## License

MIT License
(c) [Stolz](https://github.com/Stolz)

Check provided `LICENSE` file for details.

<a id="troubleshooting"></a>
## Troubleshooting / F.A.Q.

<a id="faq_support"></a>
### Where can I ask for help/support?

First make sure you read this [F.A.Q.](#troubleshooting) and if you still need help [open an issue on GitHub](https://github.com/Stolz/Assets/issues/new) or use your GitHub account to [ask for support here](http://laravel.io/forum/02-17-2014-package-an-ultra-simple-to-use-assets-managementpipeline-package).

<a id="faq_folders"></a>
### Where should I copy my assets files?

They should be copied to the subfolders you specified with the `css_dir` and `js_dir` config options. Both folders are relative to your webroot/public folder. For package assets it's the same but relative to the `packages` folder within your webroot/public folder.

i.e: Assuming the next scenario:

- You are using the default settings.
- Your webroot/public folder is `/myproject/public`
- Your webroot/public contains:
    - /myproject/public/css/foo.css
    - /myproject/public/js/bar.js
    - /myproject/public/packages/somevendor/somepackage/css/lorem.css
    - /myproject/public/packages/anothervendor/anotherpackage/js/ipsum.js

Then to load the assets you should run:

	Assets::add(['foo.css', 'bar.js', 'somevendor/somepackage:lorem.css', 'anothervendor/anotherpackage:ipsum.js']);


<a id="faq_base"></a>
### Why assets work for the main page but not for subpages?

If your assets seem to work fine for <http://example.com> but not for <http://example.com/some/other/place> your are likely to be using relative links. If you use links relative to your root URI in an URI that is not your root URI for them to work you must use the [`<base>` HTML tag](http://www.w3.org/TR/html4/struct/links.html#h-12.4) pointing to your root URI. This behavior is not related to the library or the framework but related to the [HTML standard](http://www.w3.org/TR/html401/struct/links.html#h-12.4.1) itself. Please make sure you understand the [semantics of relative links](http://tools.ietf.org/html/rfc3986#section-4) before reporting a bug.

<a id="faq_pipeline"></a>
### The pipeline is not working

Make sure `public_dir` config option is set and it's pointing to the **absolute** path of your webroot/public folder and the user that is running the library has write permissions for that folder.

If you use a massive amount of assets make sure your connection is fast enough and your computer is powerful enough to download and compress all the assets before the PHP maximum execution time is reached.

<a id="faq_config_on_the_fly"></a>
### Can I use multiple instances of the library?

Yes you can but there is no need. Read next question. If you still want to use multiple instances, [read how](https://github.com/Stolz/Assets/issues/37#issuecomment-57676554).

<a id="faq_instances"></a>
### Can I change settings on the fly?

Yes you can. There is a `config()` public method to change settings on the fly. This allows you to use same instance of the library with different settings. i.e:

	echo Assets::add('jquery-cdn')->js();
	echo Assets::reset()->add(array('custom.js', 'main.js'))->config(array('pipeline' => true))->js();

<a id="faq_filter"></a>
### Can I filter/preprocess my assets?

The library does not include any built in filter/preprocessor functionality but it offers a way to provide your custom one when pipeline is enabled. Simply use the [fetch_command](https://github.com/Stolz/Assets/blob/master/API.md#fetch_command) config option to apply a custom [filter](https://github.com/Stolz/Assets/issues/23).

<a id="faq_to_help"></a>
### How can I contribute?

Send a pull requests to the **develop** branch. Read next question for your PR to have more chances to be accepted.

<a id="faq_pull_request_not_merged"></a>
### Why my pull requests was not accepted?

Remember, the main reason for the library to exist is to be easy to use. If your PR involves changing this and makes the library cumbersome to use then it will not be accepted.

This is a framework agnostic library, if your PR uses code related to your framework it will not be accepted.

If your contribution adds new features make sure to include a proper PHPUnit test for it.

Please use PHP_CodeSniffer to make sure your code follows the project coding standards (which is a slightly variation of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)).
