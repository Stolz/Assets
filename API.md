Assets
===============






* Class name: Manager
* Namespace: Stolz\Assets





Properties
----------


### $asset_regex

    protected string $asset_regex = '/.\.(css|js)$/i'

Regex to match against a filename/url to determine if it is an asset.



* Visibility: **protected**


### $css_regex

    protected string $css_regex = '/.\.css$/i'

Regex to match against a filename/url to determine if it is a CSS asset.



* Visibility: **protected**


### $js_regex

    protected string $js_regex = '/.\.js$/i'

Regex to match against a filename/url to determine if it is a JavaScript asset.



* Visibility: **protected**


### $no_minification_regex

    protected string $no_minification_regex = '/.[-.]min\.(css|js)$/i'

Regex to match against a filename/url to determine if it should not be minified by pipeline.



* Visibility: **protected**


### $public_dir

    protected string $public_dir

Absolute path to the public directory of your App (WEBROOT).

Required if you enable the pipeline.
No trailing slash!.

* Visibility: **protected**


### $css_dir

    protected string $css_dir = 'css'

Directory for local CSS assets.

Relative to your public directory ('public_dir').
No trailing slash!.

* Visibility: **protected**


### $js_dir

    protected string $js_dir = 'js'

Directory for local JavaScript assets.

Relative to your public directory ('public_dir').
No trailing slash!.

* Visibility: **protected**


### $packages_dir

    protected string $packages_dir = 'packages'

Directory for local package assets.

Relative to your public directory ('public_dir').
No trailing slash!.

* Visibility: **protected**


### $pipeline

    protected boolean $pipeline = false

Enable assets pipeline (concatenation and minification).

Use a string that evaluates to `true` to provide the salt of the pipeline hash.
Use 'auto' to automatically calculated the salt from your assets last modification time.

* Visibility: **protected**


### $pipeline_dir

    protected string $pipeline_dir = 'min'

Directory for storing pipelined assets.

Relative to your assets directories ('css_dir' and 'js_dir').
No trailing slash!.

* Visibility: **protected**


### $pipeline_gzip

    protected boolean $pipeline_gzip = false

Enable pipelined assets compression with Gzip. Do not enable unless you know what you are doing!.

Useful only if your webserver supports Gzip HTTP_ACCEPT_ENCODING.
Set to true to use the default compression level.
Set an integer between 0 (no compression) and 9 (maximum compression) to choose compression level.

* Visibility: **protected**


### $fetch_command

    protected \Closure $fetch_command

Closure used by the pipeline to fetch assets.

Useful when file_get_contents() function is not available in your PHP
instalation or when you want to apply any kind of preprocessing to
your assets before they get pipelined.

The closure will receive as the only parameter a string with the path/URL of the asset and
it should return the content of the asset file as a string.

* Visibility: **protected**


### $notify_command

    protected \Closure $notify_command

Closure invoked by the pipeline whenever new assets are pipelined for the first time.

Useful if you need to hook to the pipeline event for things such syncing your pipelined
assets with an external server or CDN.

The closure will receive five parameters:
- String containing the name of the file that has been created.
- String containing the relative URL of the file.
- String containing the absolute path (filesystem) of the file.
- Array containing the assets included in the file.
- Boolean indicating whether or not a gziped version of the file was also created.

* Visibility: **protected**


### $css_minifier

    protected \Closure $css_minifier

Closure used by the pipeline to minify CSS assets.



* Visibility: **protected**


### $js_minifier

    protected \Closure $js_minifier

Closure used by the pipeline to minify JavaScript assets.



* Visibility: **protected**


### $collections

    protected array $collections = array()

Available collections.

Each collection is an array of assets.
Collections may also contain other collections.

* Visibility: **protected**


### $css

    protected array $css = array()

CSS files already added.

Not accepted as an option of config() method.

* Visibility: **protected**


### $js

    protected array $js = array()

JavaScript files already added.

Not accepted as an option of config() method.

* Visibility: **protected**


Methods
-------


### __construct

    void __construct(array $options)

Class constructor.



* Visibility: **public**


#### Arguments
* $options **array** - &lt;p&gt;See config() method for details.&lt;/p&gt;



### config

    Assets config(array $config)

Set up configuration options.

All the class properties except 'js' and 'css' are accepted here.
Also, an extra option 'autoload' may be passed containing an array of
assets and/or collections that will be automatically added on startup.

* Visibility: **public**


#### Arguments
* $config **array** - &lt;p&gt;Configurable options.&lt;/p&gt;



### add

    Assets add(mixed $asset)

Add an asset or a collection of assets.

It automatically detects the asset type (JavaScript, CSS or collection).
You may add more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### prepend

    Assets prepend(mixed $asset)

Add an asset or a collection of assets to the beginning of the queue.

It automatically detects the asset type (JavaScript, CSS or collection).
You may prepend more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### addCss

    Assets addCss(mixed $asset)

Add a CSS asset.

It checks for duplicates.
You may add more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### prependCss

    Assets prependCss(mixed $asset)

Add a CSS asset to the beginning of the queue.

It checks for duplicates.
You may prepend more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### addJs

    Assets addJs(mixed $asset)

Add a JavaScript asset.

It checks for duplicates.
You may add more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### prependJs

    Assets prependJs(mixed $asset)

Add a JavaScript asset to the beginning of the queue.

It checks for duplicates.
You may prepend more than one asset passing an array as argument.

* Visibility: **public**


#### Arguments
* $asset **mixed**



### css

    string css(array|\Closure $attributes)

Build the CSS `<link>` tags.

Accepts an array of $attributes for the HTML tag.
You can take control of the tag rendering by
providing a closure that will receive an array of assets.

* Visibility: **public**


#### Arguments
* $attributes **array|Closure**



### js

    string js(array|\Closure $attributes)

Build the JavaScript `<script>` tags.

Accepts an array of $attributes for the HTML tag.
You can take control of the tag rendering by
providing a closure that will receive an array of assets.

* Visibility: **public**


#### Arguments
* $attributes **array|Closure**



### registerCollection

    Assets registerCollection(string $collectionName, array $assets)

Add/replace collection.



* Visibility: **public**


#### Arguments
* $collectionName **string**
* $assets **array**



### reset

    Assets reset()

Reset all assets.



* Visibility: **public**




### resetCss

    Assets resetCss()

Reset CSS assets.



* Visibility: **public**




### resetJs

    Assets resetJs()

Reset JavaScript assets.



* Visibility: **public**




### cssPipeline

    string cssPipeline()

Minifiy and concatenate CSS files.



* Visibility: **protected**




### jsPipeline

    string jsPipeline()

Minifiy and concatenate JavaScript files.



* Visibility: **protected**




### pipeline

    string pipeline(array $assets, string $extension, string $subdirectory, \Closure $minifier)

Minifiy and concatenate files.



* Visibility: **protected**


#### Arguments
* $assets **array**
* $extension **string**
* $subdirectory **string**
* $minifier **Closure**



### calculatePipelineHash

    string calculatePipelineHash(array $assets)

Calculate the pipeline hash.



* Visibility: **protected**


#### Arguments
* $assets **array**



### packLinks

    string packLinks(array $links, \Closure $minifier)

Download, concatenate and minifiy the content of several links.



* Visibility: **protected**


#### Arguments
* $links **array**
* $minifier **Closure**



### buildLocalLink

    string buildLocalLink(string $asset, string $dir)

Build link to local asset.

Detects packages links.

* Visibility: **protected**


#### Arguments
* $asset **string**
* $dir **string**



### buildTagAttributes

    string buildTagAttributes(array $attributes)

Build an HTML attribute string from an array.



* Visibility: **public**


#### Arguments
* $attributes **array**



### assetIsFromPackage

    boolean|array assetIsFromPackage(string $asset)

Determine whether an asset is normal or from a package.



* Visibility: **protected**


#### Arguments
* $asset **string**



### isRemoteLink

    boolean isRemoteLink(string $link)

Determine whether a link is local or remote.

Undestands both "http://" and "https://" as well as protocol agnostic links "//"

* Visibility: **protected**


#### Arguments
* $link **string**



### getCss

    array getCss()

Get all CSS assets already added.



* Visibility: **public**




### getJs

    array getJs()

Get all JavaScript assets already added.



* Visibility: **public**




### addDir

    Assets addDir(string $directory, string $pattern)

Add all assets matching $pattern within $directory.



* Visibility: **public**


#### Arguments
* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;
* $pattern **string** - &lt;p&gt;(regex)&lt;/p&gt;



### addDirCss

    Assets addDirCss(string $directory)

Add all CSS assets within $directory (relative to public dir).



* Visibility: **public**


#### Arguments
* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;



### addDirJs

    Assets addDirJs(string $directory)

Add all JavaScript assets within $directory (relative to public dir).



* Visibility: **public**


#### Arguments
* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;



### rglob

    array rglob(string $directory, string $pattern, string $ltrim)

Recursively get files matching $pattern within $directory.



* Visibility: **protected**


#### Arguments
* $directory **string**
* $pattern **string** - &lt;p&gt;(regex)&lt;/p&gt;
* $ltrim **string** - &lt;p&gt;Will be trimed from the left of the file path&lt;/p&gt;


