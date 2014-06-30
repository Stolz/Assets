Assets
===============






* Class name: Manager
* Namespace: Stolz\Assets



Constants
----------


### DEFAULT_REGEX

```
const DEFAULT_REGEX = '/.\.(css|js)$/i'
```





### CSS_REGEX

```
const CSS_REGEX = '/.\.css$/i'
```





### JS_REGEX

```
const JS_REGEX = '/.\.js$/i'
```





Properties
----------


### $pipeline

```
protected boolean $pipeline = false
```

Enable assets pipeline (concatenation and minification).



* Visibility: **protected**


### $public_dir

```
protected string $public_dir
```

Absolute path to the public directory of your App (WEBROOT).

No trailing slash!.

* Visibility: **protected**


### $css_dir

```
protected string $css_dir = 'css'
```

Directory for local CSS assets.

Relative to your public directory.
No trailing slash!.

* Visibility: **protected**


### $js_dir

```
protected string $js_dir = 'js'
```

Directory for local JavaScript assets.

Relative to your public directory ('public_dir').
No trailing slash!.

* Visibility: **protected**


### $pipeline_dir

```
protected string $pipeline_dir = 'min'
```

Directory for storing pipelined assets.

Relative to your assets directories ('css_dir' and 'js_dir').
No trailing slash!.

* Visibility: **protected**


### $fetch_command

```
protected \Closure $fetch_command
```

Closure used by the pipeline to fetch assets.

Useful when file_get_contents() function is not available in your PHP instalation.
The closure receives as parameter the path/url of the asset it should return the content of the asset file as a string.

* Visibility: **protected**


### $collections

```
protected array $collections = array()
```

Available collections.

Each collection is an array of assets.
Collections may also contain other collections.

* Visibility: **protected**


### $css

```
protected array $css = array()
```

CSS files already added.

Not accepted as an option of config() method.

* Visibility: **protected**


### $js

```
protected array $js = array()
```

JavaScript files already added.

Not accepted as an option of config() method.

* Visibility: **protected**


Methods
-------


### __construct()

```
void __construct()(array $options)
```

Class constructor.



* Visibility: **public**

#### Arguments

* $options **array** - &lt;p&gt;See config() method for details.&lt;/p&gt;



### config()

```
Assets config()(array $config)
```

Set up configuration options.

All the class properties except 'js' and 'css' are accepted here.
Also, an extra option 'autoload' may be passed containing an array of
assets and/or collections that will be automatically added on startup.

* Visibility: **public**

#### Arguments

* $config **array**



### add()

```
Assets add()(mixed $asset)
```

Add an asset or a collection of assets.

It automatically detects the asset type (JavaScript, CSS or collection).
You may add more than one asset passing an array as argument.

* Visibility: **public**

#### Arguments

* $asset **mixed**



### addCss()

```
Assets addCss()(mixed $asset)
```

Add a CSS asset.

It checks for duplicates.
You may add more than one asset passing an array as argument.

* Visibility: **public**

#### Arguments

* $asset **mixed**



### addJs()

```
Assets addJs()(mixed $asset)
```

Add a JavaScript asset.

It checks for duplicates.
You may add more than one asset passing an array as argument.

* Visibility: **public**

#### Arguments

* $asset **mixed**



### css()

```
string css()()
```

Build the CSS link tags.



* Visibility: **public**



### js()

```
string js()()
```

Build the JavaScript script tags.



* Visibility: **public**



### registerCollection()

```
Assets registerCollection()(string $collectionName, array $assets)
```

Add/replace collection.



* Visibility: **public**

#### Arguments

* $collectionName **string**
* $assets **array**



### reset()

```
Assets reset()()
```

Reset all assets.



* Visibility: **public**



### resetCss()

```
Assets resetCss()()
```

Reset CSS assets.



* Visibility: **public**



### resetJs()

```
Assets resetJs()()
```

Reset JavaScript assets.



* Visibility: **public**



### cssPipeline()

```
string cssPipeline()()
```

Minifiy and concatenate CSS files.



* Visibility: **protected**



### jsPipeline()

```
string jsPipeline()()
```

Minifiy and concatenate JavaScript files.



* Visibility: **protected**



### buildBuffer()

```
string buildBuffer()(array $links)
```

Download and concatenate links.



* Visibility: **protected**

#### Arguments

* $links **array**



### buildLocalLink()

```
string buildLocalLink()(string $asset, string $dir)
```

Build link to local asset.

Detects packages links.

* Visibility: **protected**

#### Arguments

* $asset **string**
* $dir **string**



### assetIsFromPackage()

```
boolean|array assetIsFromPackage()(string $asset)
```

Determine whether an asset is normal or from a package.



* Visibility: **protected**

#### Arguments

* $asset **string**



### isRemoteLink()

```
boolean isRemoteLink()(string $link)
```

Determine whether a link is local or remote.

Undestands both "http://" and "https://" as well as protocol agnostic links "//"

* Visibility: **protected**

#### Arguments

* $link **string**



### getCss()

```
array getCss()()
```

Get all CSS assets already added.



* Visibility: **public**



### getJs()

```
array getJs()()
```

Get all JavaScript assets already added.



* Visibility: **public**



### addDir()

```
Assets addDir()(string $directory, string $pattern)
```

Add all assets matching $pattern within $directory.



* Visibility: **public**

#### Arguments

* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;
* $pattern **string** - &lt;p&gt;(regex)&lt;/p&gt;



### addDirCss()

```
Assets addDirCss()(string $directory)
```

Add all CSS assets within $directory (relative to public dir).



* Visibility: **public**

#### Arguments

* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;



### addDirJs()

```
Assets addDirJs()(string $directory)
```

Add all JavaScript assets within $directory.



* Visibility: **public**

#### Arguments

* $directory **string** - &lt;p&gt;Relative to $this-&gt;public_dir&lt;/p&gt;



### rglob()

```
array rglob()(string $directory, string $pattern, string $ltrim)
```

Recursively get files matching $pattern within $directory.



* Visibility: **protected**

#### Arguments

* $directory **string**
* $pattern **string** - &lt;p&gt;(regex)&lt;/p&gt;
* $ltrim **string** - &lt;p&gt;Will be trimed from the left of the file path&lt;/p&gt;


