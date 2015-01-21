<?php namespace Stolz\Assets;

use Closure;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Manager
{
	/**
	 * Regex to match against a filename/url to determine if it is an asset.
	 *
	 * @var string
	 */
	protected $asset_regex = '/.\.(css|js)$/i';

	/**
	 * Regex to match against a filename/url to determine if it is a CSS asset.
	 *
	 * @var string
	 */

	protected $css_regex = '/.\.css$/i';

	/**
	 * Regex to match against a filename/url to determine if it is a JavaScript asset.
	 *
	 * @var string
	 */
	protected $js_regex = '/.\.js$/i';

	/**
	 * Absolute path to the public directory of your App (WEBROOT).
	 * Required if you enable the pipeline.
	 * No trailing slash!.
	 *
	 * @var string
	 */
	protected $public_dir;

	/**
	 * Directory for local CSS assets.
	 * Relative to your public directory ('public_dir').
	 * No trailing slash!.
	 *
	 * @var string
	 */
	protected $css_dir = 'css';

	/**
	 * Directory for local JavaScript assets.
	 * Relative to your public directory ('public_dir').
	 * No trailing slash!.
	 *
	 * @var string
	 */
	protected $js_dir = 'js';

	/**
	 * Directory for local package assets.
	 * Relative to your public directory ('public_dir').
	 * No trailing slash!.
	 *
	 * @var string
	 */
	protected $packages_dir = 'packages';

	/**
	 * Enable assets pipeline (concatenation and minification).
	 * If you set an integer value greather than 1 it will be used as pipeline timestamp that will be added to the URL.
	 *
	 * @var bool|integer
	 */
	protected $pipeline = false;

	/**
	 * Directory for storing pipelined assets.
	 * Relative to your assets directories ('css_dir' and 'js_dir').
	 * No trailing slash!.
	 *
	 * @var string
	 */
	protected $pipeline_dir = 'min';

	/**
	 * Enable pipelined assets compression with Gzip. Do not enable unless you know what you are doing!.
	 * Useful only if your webserver supports Gzip HTTP_ACCEPT_ENCODING.
	 * Set to true to use the default compression level.
	 * Set an integer between 0 (no compression) and 9 (maximum compression) to choose compression level.
	 *
	 * @var bool|integer
	 */
	protected $pipeline_gzip = false;

	/**
	 * Closure used by the pipeline to fetch assets.
	 *
	 * Useful when file_get_contents() function is not available in your PHP
	 * instalation or when you want to apply any kind of preprocessing to
	 * your assets before they get pipelined.
	 *
	 * The closure will receive as the only parameter a string with the path/URL of the asset and
	 * it should return the content of the asset file as a string.
	 *
	 * @var Closure
	 */
	protected $fetch_command;

	/**
	 * Closure invoked by the pipeline whenever new assets are pipelined for the first time.
	 *
	 * Useful if you need to hook to the pipeline event for things such syncing your pipelined
	 * assets with an external server or CDN.
	 *
	 * The closure will receive five parameters:
	 * - String containing the name of the file that has been created.
	 * - String containing the relative URL of the file.
	 * - String containing the absolute path (filesystem) of the file.
	 * - Array containing the assets included in the file.
	 * - Boolean indicating whether or not a gziped version of the file was also created.
	 *
	 * @var Closure
	 */
	protected $notify_command;

	/**
	 * Available collections.
	 * Each collection is an array of assets.
	 * Collections may also contain other collections.
	 *
	 * @var array
	 */
	protected $collections = array();

	/**
	 * CSS files already added.
	 * Not accepted as an option of config() method.
	 *
	 * @var array
	 */
	protected $css = array();

	/**
	 * JavaScript files already added.
	 * Not accepted as an option of config() method.
	 *
	 * @var array
	 */
	protected $js = array();

	/**
	 * Class constructor.
	 *
	 * @param  array $options See config() method for details.
	 * @return void
	 */
	public function __construct(array $options = array())
	{
		// Forward config options
		if($options)
			$this->config($options);
	}

	/**
	 * Set up configuration options.
	 *
	 * All the class properties except 'js' and 'css' are accepted here.
	 * Also, an extra option 'autoload' may be passed containing an array of
	 * assets and/or collections that will be automatically added on startup.
	 *
	 * @param  array   $config Configurable options.
	 * @return Manager
	 * @throws Exception
	 */
	public function config(array $config)
	{
		// Set regex options
		foreach(array('asset_regex', 'css_regex', 'js_regex') as $option)
			if(isset($config[$option]) and (@preg_match($config[$option], null) !== false))
				$this->$option = $config[$option];

		// Set common options
		foreach(array('public_dir', 'css_dir', 'js_dir', 'packages_dir', 'pipeline',  'pipeline_dir', 'pipeline_gzip') as $option)
			if(isset($config[$option]))
				$this->$option = $config[$option];

		// Pipeline requires public dir
		if($this->pipeline and ! is_dir($this->public_dir))
			throw new Exception('stolz/assets: Public dir not found');

		// Set pipeline commands
		foreach(array('fetch_command', 'notify_command') as $option)
			if(isset($config[$option]) and ($config[$option] instanceof Closure))
				$this->$option = $config[$option];

		// Set collections
		if(isset($config['collections']) and is_array($config['collections']))
			$this->collections = $config['collections'];

		// Autoload assets
		if(isset($config['autoload']) and is_array($config['autoload']))
			foreach($config['autoload'] as $asset)
				$this->add($asset);

		return $this;
	}

	/**
	 * Add an asset or a collection of assets.
	 *
	 * It automatically detects the asset type (JavaScript, CSS or collection).
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param  mixed   $asset
	 * @return Manager
	 */
	public function add($asset)
	{
		// More than one asset
		if(is_array($asset))
		{
			foreach($asset as $a)
				$this->add($a);
		}

		// Collection
		elseif(isset($this->collections[$asset]))
			$this->add($this->collections[$asset]);

		// JavaScript asset
		elseif(preg_match($this->js_regex, $asset))
			$this->addJs($asset);

		// CSS asset
		elseif(preg_match($this->css_regex, $asset))
			$this->addCss($asset);

		return $this;
	}

	/**
	 * Add a CSS asset.
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param  mixed   $asset
	 * @return Manager
	 */
	public function addCss($asset)
	{
		if(is_array($asset))
		{
			foreach($asset as $a)
				$this->addCss($a);

			return $this;
		}

		if( ! $this->isRemoteLink($asset))
			$asset = $this->buildLocalLink($asset, $this->css_dir);

		if( ! in_array($asset, $this->css))
			$this->css[] = $asset;

		return $this;
	}

	/**
	 * Add a JavaScript asset.
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param  mixed   $asset
	 * @return Manager
	 */
	public function addJs($asset)
	{
		if(is_array($asset))
		{
			foreach($asset as $a)
				$this->addJs($a);

			return $this;
		}

		if( ! $this->isRemoteLink($asset))
			$asset = $this->buildLocalLink($asset, $this->js_dir);

		if( ! in_array($asset, $this->js))
			$this->js[] = $asset;

		return $this;
	}

	/**
	 * Build the CSS `<link>` tags.
	 *
	 * Accepts an array of $attributes for the HTML tag.
	 * You can take control of the tag rendering by
	 * providing a closure that will receive an array of assets.
	 *
	 * @param  array|Closure $attributes
	 * @return string
	 */
	public function css($attributes = null)
	{
		if( ! $this->css)
			return '';

		$assets = ($this->pipeline) ? array($this->cssPipeline()) : $this->css;

		if($attributes instanceof Closure)
			return $attributes($assets);

		// Build attributes
		$attributes = (array) $attributes;
		unset($attributes['href']);

		if( ! array_key_exists('type', $attributes))
			$attributes['type'] = 'text/css';

		if( ! array_key_exists('rel', $attributes))
			$attributes['rel'] = 'stylesheet';

		$attributes = $this->buildTagAttributes($attributes);

		// Build tags
		$output = '';
		foreach($assets as $asset)
			$output .= '<link href="' . $asset . '"' . $attributes . " />\n";

		return $output;
	}

	/**
	 * Build the JavaScript `<script>` tags.
	 *
	 * Accepts an array of $attributes for the HTML tag.
	 * You can take control of the tag rendering by
	 * providing a closure that will receive an array of assets.
	 *
	 * @param  array|Closure $attributes
	 * @return string
	 */
	public function js($attributes = null)
	{
		if( ! $this->js)
			return '';

		$assets = ($this->pipeline) ? array($this->jsPipeline()) : $this->js;

		if($attributes instanceof Closure)
			return $attributes($assets);

		// Build attributes
		$attributes = (array) $attributes;
		unset($attributes['src']);

		if( ! array_key_exists('type', $attributes))
			$attributes['type'] = 'text/javascript';

		$attributes = $this->buildTagAttributes($attributes);

		// Build tags
		$output = '';
		foreach($assets as $asset)
			$output .= '<script src="' . $asset . '"' . $attributes . "></script>\n";

		return $output;
	}

	/**
	 * Add/replace collection.
	 *
	 * @param  string  $collectionName
	 * @param  array   $assets
	 * @return Manager
	 */
	public function registerCollection($collectionName, array $assets)
	{
		$this->collections[$collectionName] = $assets;

		return $this;
	}

	/**
	 * Reset all assets.
	 *
	 * @return Manager
	 */
	public function reset()
	{
		return $this->resetCss()->resetJs();
	}

	/**
	 * Reset CSS assets.
	 *
	 * @return Manager
	 */
	public function resetCss()
	{
		$this->css = array();

		return $this;
	}

	/**
	 * Reset JavaScript assets.
	 *
	 * @return Manager
	 */
	public function resetJs()
	{
		$this->js = array();

		return $this;
	}

	/**
	 * Minifiy and concatenate CSS files.
	 *
	 * @return string
	 */
	protected function cssPipeline()
	{
		return $this->pipeline($this->css, '.css', $this->css_dir, function ($buffer) {
			$min = new \CSSmin();
			return $min->run($buffer);
		});
	}

	/**
	 * Minifiy and concatenate JavaScript files.
	 *
	 * @return string
	 */
	protected function jsPipeline()
	{
		return $this->pipeline($this->js, '.js', $this->js_dir, function ($buffer) {
			return \JSMin::minify($buffer);
		});
	}

	/**
	 * Minifiy and concatenate files.
	 *
	 * @param array   $assets
	 * @param string  $extension
	 * @param string  $subdirectory
	 * @param Closure $minifier
	 *
	 * @return string
	 */
	protected function pipeline(array $assets, $extension, $subdirectory, Closure $minifier)
	{
		// Add timestamp to extension
		if(intval($this->pipeline) > 1)
			$extension = '.' . $this->pipeline . $extension;

		// Generate paths
		$filename = md5(implode($assets)) . $extension;
		$relative_path = "$subdirectory/{$this->pipeline_dir}/$filename";
		$absolute_path = $this->buildAbsolutePath($subdirectory, $filename);

		// If pipeline already exist return it
		if(file_exists($absolute_path))
			return $relative_path;

		// Create destination directory
		if( ! is_dir($directory = dirname($absolute_path)))
			mkdir($directory, 0777, true);

		// Concatenate files
		$buffer = $this->gatherLinks($assets);

		// Minifiy
		$min = $minifier->__invoke($buffer);

		// Write minified file
		file_put_contents($absolute_path, $min);

		// Write gziped file
		if($gzip = (function_exists('gzencode') and $this->pipeline_gzip !== false))
		{
			$level = ($this->pipeline_gzip === true) ? -1 : intval($this->pipeline_gzip);
			file_put_contents("$absolute_path.gz", gzencode($min, $level));
		}

		// Hook for pipeline event
		if($this->notify_command instanceof Closure)
			$this->notify_command->__invoke($filename, $relative_path, $absolute_path, $assets, $gzip);

		return $relative_path;
	}

	/**
	 * Build Absolute Path
	 * 
	 * @param type $subdirectory 
	 * @param type $file 
	 * 
	 * @return string
	 */
	protected function buildAbsolutePath($subdirectory, $file) 
	{

		//initialize
		$absolute_path = $this->public_dir . DIRECTORY_SEPARATOR;
		
		//check for empty subdirectory
		if( $subdirectory != '')
		{
			$absolute_path .= $subdirectory . DIRECTORY_SEPARATOR;	
		}
		
		//append file
		$absolute_path .= $this->pipeline_dir . DIRECTORY_SEPARATOR . $file;

		return $absolute_path;
	}

	/**
	 * Download and concatenate the content of several links.
	 *
	 * @param  array  $links
	 * @return string
	 */
	protected function gatherLinks(array $links)
	{
		$buffer = '';
		foreach($links as $link)
		{
			if($this->isRemoteLink($link))
			{
				// Add current protocol to agnostic links
				if('//' === substr($link, 0, 2))
				{
					$protocol = (isset($_SERVER['HTTPS']) and ! empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') ? 'https:' : 'http:';
					$link = $protocol . $link;
				}
			}
			else
			{
				$link = $this->public_dir . DIRECTORY_SEPARATOR . $link;
			}

			$buffer .= ($this->fetch_command instanceof Closure) ? $this->fetch_command->__invoke($link) : file_get_contents($link);
		}

		return $buffer;
	}

	/**
	 * Build link to local asset.
	 *
	 * Detects packages links.
	 *
	 * @param  string $asset
	 * @param  string $dir
	 * @return string the link
	 */
	protected function buildLocalLink($asset, $dir)
	{
		$package = $this->assetIsFromPackage($asset);

		if($package === false)
			return $dir . '/' . $asset;

		return $this->packages_dir . '/' . $package[0] . '/' .$package[1] . '/' . ltrim($dir, '/') . '/' . $package[2];
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public function buildTagAttributes(array $attributes)
	{
		$html = array();

		foreach ($attributes as $key => $value)
		{
			if (is_null($value))
				continue;

			if (is_numeric($key))
				$key = $value;

			$html[] = $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8', false) . '"';
		}

		return (count($html) > 0) ? ' ' . implode(' ', $html) : '';
	}

	/**
	 * Determine whether an asset is normal or from a package.
	 *
	 * @param  string $asset
	 * @return bool|array
	 */
	protected function assetIsFromPackage($asset)
	{
		if(preg_match('{^([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+):(.*)$}', $asset, $matches))
			return array_slice($matches, 1, 3);

		return false;
	}

	/**
	 * Determine whether a link is local or remote.
	 *
	 * Undestands both "http://" and "https://" as well as protocol agnostic links "//"
	 *
	 * @param  string $link
	 * @return bool
	 */
	protected function isRemoteLink($link)
	{
		return ('http://' === substr($link, 0, 7) or 'https://' === substr($link, 0, 8) or '//' === substr($link, 0, 2));
	}

	/**
	 * Get all CSS assets already added.
	 *
	 * @return array
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Get all JavaScript assets already added.
	 *
	 * @return array
	 */
	public function getJs()
	{
		return $this->js;
	}

	/**
	 * Add all assets matching $pattern within $directory.
	 *
	 * @param  string $directory Relative to $this->public_dir
	 * @param  string $pattern (regex)
	 * @return Manager
	 * @throws Exception
	 */
	public function addDir($directory, $pattern = null)
	{
		// Check if public_dir exists
		if( ! is_dir($this->public_dir))
			throw new Exception('stolz/assets: Public dir not found');

		// By default match all assets
		if(is_null($pattern))
			$pattern = $this->asset_regex;

		// Get assets files
		$files = $this->rglob($this->public_dir . DIRECTORY_SEPARATOR . $directory, $pattern, $this->public_dir);

		// No luck? Nothing to do
		if( ! $files)
			return $this;

		// Avoid polling if the pattern is our old friend JavaScript
		if($pattern === $this->js_regex)
			$this->js = array_unique(array_merge($this->js, $files));

		// Avoid polling if the pattern is our old friend CSS
		elseif($pattern === $this->css_regex)
			$this->css = array_unique(array_merge($this->css, $files));

		// Unknown pattern. We must poll to know the asset type :(
		else foreach($files as $asset)
			$this->add($asset);

		return $this;
	}

	/**
	 * Add all CSS assets within $directory (relative to public dir).
	 *
	 * @param  string $directory Relative to $this->public_dir
	 * @return Manager
	 */
	public function addDirCss($directory)
	{
		return $this->addDir($directory, $this->css_regex);
	}

	/**
	 * Add all JavaScript assets within $directory (relative to public dir).
	 *
	 * @param  string $directory Relative to $this->public_dir
	 * @return Manager
	 */
	public function addDirJs($directory)
	{
		return $this->addDir($directory, $this->js_regex);
	}

	/**
	 * Recursively get files matching $pattern within $directory.
	 *
	 * @param  string $directory
	 * @param  string $pattern (regex)
	 * @param  string $ltrim Will be trimed from the left of the file path
	 * @return array
	 */
	protected function rglob($directory, $pattern, $ltrim = null)
	{
		$iterator = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)), $pattern);
		$offset = strlen($ltrim);
		$files = array();

		foreach($iterator as $file)
			$files[] = substr($file->getPathname(), $offset);

		return $files;
	}
}
