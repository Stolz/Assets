Assets
======

An ultra-simple-to-use assets management PHP library.

[![Build Status](https://travis-ci.org/Stolz/Assets.png?branch=master)](https://travis-ci.org/Stolz/Assets) [![Join the chat at https://gitter.im/Stolz/Assets](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Stolz/Assets?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

- [Features](#features).
- [Supported frameworks](#frameworks).
- [Installation](#installation).
- [Usage](#usage).
	- [Views](#views).
	- [Controllers](#controllers).
- [Configuration](#configuration).
	- [Collections](#collections).
	- [Pipeline](#pipeline).
	- [More options](#options).
	- [Multitenancy](#multitenancy).
- [Non static interface usage](#nonstatic).
- [Sample collections](#samples).
- [Troubleshooting / F.A.Q.](#troubleshooting).
- [License](#license).

----

<a id="features"></a>
## Features

- **Very easy to use**.
- Autogenerates HTML tags for including your JavaScript and CSS files.
- Automatically detects type of asset (CSS, JavaScript or collection).
- Supports programmatically adding assets on the fly.
- Supports local (**including packages**) or remote assets.
- Prevents from loading duplicated assets.
- Included assets **pipeline** (*concatenate and minify all your assets to a single file*) with URL **timestamps** and **gzip** support.
- Automatically prefixes local assets with a configurable folder name or url.
- Supports secure (*https*) and protocol agnostic (*//*) links.
- Supports **collections** (*named sets of assets*) that can be nested, allowing assets dependencies definitions.
- Supports **multitenancy** (multiple independent configurations) for different groups of assets (*this feature is only available for Laravel >= 5.0*).
- Allows autoloading by default preconfigured assets and collections.

<a id="frameworks"></a>
## Supported frameworks

The library is framework agnostic and it should work well with any framework or naked PHP application. Nevertheless, since the library is most popular between Laravel users the following instructions have been tailored for **Laravel 5** framework ([still on Laravel 4?](https://github.com/Stolz/Assets/issues/84#issuecomment-171149242)). If you want to use the library in any other scenario please read the [non static interface](#nonstatic) instructions.

<a id="installation"></a>
## Installation

In your project base directory run

	composer require stolz/assets

If you are using Laravel version 5.5 or later there is nothing else you need to do. The service provider will be automatically loaded for you.

If you are using an older version of Laravel or you disabled the package discovery feature, then you have to manually edit `config/app.php` file and add the service provider within the `providers` array.

	'providers' => [
		//...
		'Stolz\Assets\Laravel\ServiceProvider',
		//...
	],

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

Basically all you have to do to add and asset, no matter if it's CSS or JS or a collection of both, is:

	Assets::add('filename');

>For more advanced uses keep reading but please note that there are some more methods not documented here. For a **full list of all the available methods** please read the provided [`API.md`](https://github.com/Stolz/Assets/blob/master/API.md) file.


Add more than one asset at once

	Assets::add(['another/file.js', 'one/more.css']);

Add an asset from a local package

	Assets::add('twitter/bootstrap:bootstrap.min.css');

Note all local assets filenames are considered to be relative to you assets directory (configurable via `css_dir` and `js_dir` options) so you don't need to provide it every time with `js/file.js` or `css/file.css`, using just `file.js` or `file.css` will be enough.

You may add remote assets in the same fashion

	Assets::add('//cdn.example.com/jquery.js');
	Assets::add('http://example.com/style.css');

If your assets have no extension and autodetection fails, then just use canonical functions *(they accept an array of assets too)*

	Assets::addCss('CSSfile.foo');
	Assets::addJs('JavaScriptFile.bar');

If at some point you decide you added the wrong assets you can reset them and start over

	Assets::reset();    // Reset both CSS and JS
	Assets::resetCss(); // Reset only CSS
	Assets::resetJs();  // Reset only JS

All methods that don't generate output will accept chaining:

	Assets::reset()->add('collection')->addJs('file.js')->css();

<a id="configuration"></a>
## Configuration

To bring up the config file run

	php artisan vendor:publish

This will create the file `config/assets.php` that you may use to configure the library. With the provided comments all options should be self explanatory.

If you are using the [non static interface](#nonstatic) just pass an associative array of config settings to the class constructor.

<a id="collections"></a>
### Collections

A collection is a named set of assets, that is, a set of JavaScript and CSS files. Any collection may include more collections, allowing dependencies definition and collection nesting. Collections can be created on run time or via config file.

To register a collection on run time for later use:

	Assets::registerCollection($collectionName, ['some', 'awesome', 'assets']);

To preconfigure collections using the config file:

	// ... File: config/assets.php ...
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

Alternatively, you may set the `pipeline` config option to a string value that evaluates to `true`. That value will be used as the salt of the pipeline hash. If you use `'auto'` as value the salt will be automatically calculated based on your assets last modification time.

Example:

	'pipeline' => 'version 1.0',

Finally, if you happen to use NGINX with the [gzip_static](http://nginx.org/en/docs/http/ngx_http_gzip_static_module.html) feature enabled, add the following config option to automatically create a suitable gziped version of the pipelined assets:

	'pipeline_gzip' => true,

<a id="options"></a>
### Other configurable options

For a **full list of all the available config options** please read the provided [`API.md`](https://github.com/Stolz/Assets/blob/master/API.md) file.

- `'autoload' => [],`

	Here you may set which assets (CSS files, JavaScript files or collections) should be loaded by default.
- `'css_dir' => 'css',`
- `'js_dir' => 'js',`

	Override default base URL/folder for assets. Don't use trailing slash!. They will be prepended to all your local assets. Both relative paths or full URLs are supported.

- `'pipeline_dir' => 'min',`

	Override default folder for pipelined assets. Don't use trailing slash!.

It is possible to **change any config options on the fly** by passing an array of settings to the `config()` method. Useful if some assets use a different base directory or if you want to pipeline some assets and skip others from the pipeline. i.e:

	echo Assets::reset()->add('do-not-pipeline-this.js')->js(),
	     Assets::reset()->add('please-pipeline-this.js')->config(['pipeline' => true])->js();

<a id="multitenancy"></a>
### Multitenancy

**Note:** *This feature is only available for Laravel >= 5.0*.

Multitenancy is achieved using groups. A group is an isolated container of the library. Each group is totally independent of other groups so it uses its own settings and assets flow. This is useful if you need different approaches for different types of assets (for instance, you may need some assets to be pipelined but some others no). Therefore, when using multiple groups is your responsability to make sure the assets of different groups that depend on eachother are loaded in ther right order.

By default if no groups are defined the default group is used. To define a group just nest your normal settings within an array in the config file. The array key will be the group name. For instance:


	// ... File: config/assets.php ...

	// Default group
	'default' => [
		'pipeline' => true,
		'js_dir' => 'js',
		// ... more options for default group
	],

	// Other group
	'group1' => [
		'pipeline' => false,
		'public_dir' => '/foo',
		// ... more options for group1
	],

	// Another group
	'group2' => [
		'pipeline' => false,
		'css_dir' => 'css/admin',
		// ... more options for group2
	],

For choosing which group you want to interact with, use the `group()` method. If no group is specified the 'default' group will be used.

	Assets::add('foo.js')->js(); // Uses default group
	Assets::group('group1')->add('bar.css')->css(); // Uses the 'group1' group.

Please note the `group()` method is part of the Facade, so it does not accept chaining and it always has to be used at the beginning of each interaction with the library.

----

<a id="nonstatic"></a>
## Non static interface

You can use the library without using static methods. The signature of all methods is the same as described above but using an instance of the class instead.

	// Load the library with composer
	require __DIR__ . '/vendor/autoload.php';

	// Set config options
	$config = [
		'collections' => [...],
		'autoload' => [...],
		'pipeline' => true,
		'public_dir' => '/absolute/path/to/your/webroot/public/dir'
		...
	];

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
	'jquery-cdn' => ['//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js'],

	// jQuery UI (CDN)
	'jquery-ui-cdn' => [
		'jquery-cdn',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js',
		// Uncomment to load all languages '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/i18n/jquery-ui-i18n.min.js',
		// Uncomment to load a single language '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/i18n/jquery.ui.datepicker-es.min.js',
		// Uncomment to load a theme' //ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css',
	],

	// Zurb Foundation (CDN)
	'foundation-cdn' => [
		'jquery-cdn',
		'//cdn.jsdelivr.net/foundation/5.5.1/css/normalize.css',
		'//cdn.jsdelivr.net/foundation/5.5.1/css/foundation.min.css',
		'//cdn.jsdelivr.net/foundation/5.5.1/js/foundation.min.js',
		'app.js'
	],

	// Twitter Bootstrap (CDN)
	'bootstrap-cdn' => [
		'jquery-cdn',
		'//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css',
		'//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js'
	],

	// Flags of all countries in one sprite (CDN)
	'flags-16px' => ['//cloud.github.com/downloads/lafeber/world-flags-sprite/flags16.css'],
	'flags-32px' => ['//cloud.github.com/downloads/lafeber/world-flags-sprite/flags32.css'],

<a id="license"></a>
## License

MIT License
© [Stolz](https://github.com/Stolz)

Read the provided `LICENSE` file for details.

<a id="troubleshooting"></a>
## Troubleshooting / F.A.Q.

<a id="faq_support"></a>
### Where can I ask for help/support?

First please make sure you have read the [F.A.Q.](#troubleshooting) and [API docs](https://github.com/Stolz/Assets/blob/master/API.md) and if you still need help explain your problem in our [Gitter chat](https://gitter.im/Stolz/Assets).

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

	Assets::add([
		'foo.css',
		'bar.js',
		'somevendor/somepackage:lorem.css',
		'anothervendor/anotherpackage:ipsum.js'
	]);

<a id="faq_base"></a>
### Why assets work for the main page but not for subpages?

If your assets seem to work fine for <http://example.com> but not for <http://example.com/some/other/place> your are likely to be using relative links. If you use links relative to your root URI in an URI that is not your root URI for them to work you must use the [`<base>` HTML tag](http://www.w3.org/TR/html4/struct/links.html#h-12.4) pointing to your root URI. This behavior is not related to the library or the framework but related to the [HTML standard](http://www.w3.org/TR/html401/struct/links.html#h-12.4.1) itself. Please make sure you understand the [semantics of relative links](http://tools.ietf.org/html/rfc3986#section-4) before reporting a bug.

<a id="faq_pipeline"></a>
### The pipeline is not working

Make sure `public_dir` config option is set and it's pointing to the **absolute** path of your webroot/public folder and the user that is running the library has write permissions for that folder.

If you use a massive amount of assets make sure your connection is fast enough and your computer is powerful enough to download and compress all the assets before the PHP maximum execution time is reached.

<a id="faq_config_on_the_fly"></a>
### Can I use multiple instances of the library?

Yes you can but there is no need. You better use the [multitenancy feature](#multitenancy) (*only available for Laravel >= 5.0*).

<a id="faq_instances"></a>
### Can I change settings on the fly?

Yes you can. There is a `config()` public method to change settings on the fly. This allows you to use same instance of the library with different settings. i.e:

	echo Assets::add('jquery-cdn')->js();
	echo Assets::reset()->add(['custom.js', 'main.js'])->config(['pipeline' => true])->js();

If you want the different settings to be permanent, then use the [multitenancy feature](#multitenancy).

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
