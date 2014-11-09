<?php namespace Stolz\Assets;

use Closure;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Manager
{
	/** @const Regex to match CSS and JavaScript files */
	const DEFAULT_REGEX = '/.\.(css|js)$/i';

	/** @const Regex to match CSS files */
	const CSS_REGEX = '/.\.css$/i';

	/** @const Regex to match JavaScript files */
	const JS_REGEX = '/.\.js$/i';

	/**
	 * Enable assets pipeline (concatenation and minification).
	 * @var bool
	 */
	protected $pipeline = false;

	/**
	 * Absolute path to the public directory of your App (WEBROOT).
	 * No trailing slash!.
	 * @var string
	 */
	protected $public_dir;

	/**
	 * Directory for local CSS assets.
	 * Relative to your public directory.
	 * No trailing slash!.
	 * @var string
	 */
	protected $css_dir = 'css';

	/**
	 * Directory for local JavaScript assets.
	 * Relative to your public directory ('public_dir').
	 * No trailing slash!.
	 * @var string
	 */
	protected $js_dir = 'js';

	/**
	 * Directory for storing pipelined assets.
	 * Relative to your assets directories ('css_dir' and 'js_dir').
	 * No trailing slash!.
	 * @var string
	 */
	protected $pipeline_dir = 'min';

	/**
	 * Closure used by the pipeline to fetch assets.
	 *
	 * Useful when file_get_contents() function is not available in your PHP
	 * instalation or when you want to apply any kind of preprocessing to
	 * your assets before they get pipelined.
	 *
	 * The closure will receive as the only parameter a string with the path/URL of the asset and
	 * it should return the content of the asset file as a string.
	 * @var Closure
	 */
	protected $fetch_command;

	/**
	 * Closure used to wrap output when serve assets.
	 * @var Closure
	 */
	protected $wrap_command;

	/**
	 * Available collections.
	 * Each collection is an array of assets.
	 * Collections may also contain other collections.
	 * @var array
	 */
	protected $collections = array();

	/**
	 * CSS files already added.
	 * Not accepted as an option of config() method.
	 * @var array
	 */
	protected $css = array();

	/**
	 * JavaScript files already added.
	 * Not accepted as an option of config() method.
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
	 * @param  array $options Configurable options.
	 * @return Manager
	 * @throws Exception
	 */
	public function config(array $config)
	{
		// Set pipeline mode
		if(isset($config['pipeline']))
			$this->pipeline = $config['pipeline'];

		// Set public dir
		if(isset($config['public_dir']))
			$this->public_dir = $config['public_dir'];

		// Pipeline requires public dir
		if($this->pipeline and ! is_dir($this->public_dir))
			throw new Exception('stolz/assets: Public dir not found');

		// Set custom pipeline directory
		if(isset($config['pipeline_dir']))
			$this->pipeline_dir = $config['pipeline_dir'];

		// Set custom pipeline fetch command
		if(isset($config['fetch_command']) and ($config['fetch_command'] instanceof Closure))
			$this->fetch_command = $config['fetch_command'];

		// Set custom wrap command
		if(isset($config['wrap_command']) and ($config['wrap_command'] instanceof Closure))
			$this->wrap_command = $config['wrap_command'];

		// Set custom CSS directory
		if(isset($config['css_dir']))
			$this->css_dir = $config['css_dir'];

		// Set custom JavaScript directory
		if(isset($config['js_dir']))
			$this->js_dir = $config['js_dir'];

		// Set collections
		if(isset($config['collections']) and is_array($config['collections']))
			$this->collections = $config['collections'];

		// Autoload assets
		if(isset($config['autoload']) and is_array($config['autoload']))
		{
			foreach($config['autoload'] as $asset)
			{
				$this->add($asset);
			}
		}

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
		{
			$this->add($this->collections[$asset]);
		}
		else
		{
			// JavaScript or CSS
			$info = pathinfo($asset);
			if(isset($info['extension']))
			{
				$ext = strtolower($info['extension']);
				if($ext === 'css')
					$this->addCss($asset);
				elseif($ext === 'js')
					$this->addJs($asset);
			}
		}

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
	 * Build the CSS link tags.
	 *
	 * @return string
	 */
	public function css()
	{
		if( ! $this->css)
			return null;

		if($this->pipeline)
			return '<link type="text/css" rel="stylesheet" href="'.$this->wrap($this->cssPipeline()).'" />'."\n";

		$output = '';
		foreach($this->css as $file)
			$output .= '<link type="text/css" rel="stylesheet" href="'.$this->wrap($file).'" />'."\n";

		return $output;
	}

	/**
	 * Build the JavaScript script tags.
	 *
	 * @return string
	 */
	public function js()
	{
		if( ! $this->js)
			return null;

		if($this->pipeline)
			return '<script type="text/javascript" src="'.$this->wrap($this->jsPipeline()).'"></script>'."\n";

		$output = '';
		foreach($this->js as $file)
			$output .= '<script type="text/javascript" src="'.$this->wrap($file).'"></script>'."\n";

		return $output;
	}

	/**
	 * Add/replace collection.
	 *
	 * @param  string  $collectionName
	 * @param  array   $assets
	 * @return Manager
	 */
	public function registerCollection($collectionName, Array $assets)
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
		$timestamp = (intval($this->pipeline) > 1) ? '?' . $this->pipeline : null;
		$file = md5($timestamp . implode($this->css)).'.css';
		$relative_path = "{$this->css_dir}/{$this->pipeline_dir}/$file";
		$absolute_path = $this->public_dir . DIRECTORY_SEPARATOR . $this->css_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir . DIRECTORY_SEPARATOR . $file;

		// If pipeline exist return it
		if(file_exists($absolute_path))
			return $relative_path . $timestamp;

		// Create destination directory
		$directory = $this->public_dir . DIRECTORY_SEPARATOR . $this->css_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir;
		if( ! is_dir($directory))
			mkdir($directory, 0777, true);

		// Concatenate files
		$buffer = $this->gatherLinks($this->css);

		// Minifiy
		$min = new \CSSmin();
		$min = $min->run($buffer);

		// Write file
		file_put_contents($absolute_path, $min);

		return $relative_path . $timestamp;
	}

	/**
	 * Minifiy and concatenate JavaScript files.
	 *
	 * @return string
	 */
	protected function jsPipeline()
	{
		$timestamp = (intval($this->pipeline) > 1) ? '?' . $this->pipeline : null;
		$file = md5($timestamp . implode($this->js)).'.js';
		$relative_path = "{$this->js_dir}/{$this->pipeline_dir}/$file";
		$absolute_path = $this->public_dir . DIRECTORY_SEPARATOR . $this->js_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir . DIRECTORY_SEPARATOR . $file;

		// If pipeline exist return it
		if(file_exists($absolute_path))
			return $relative_path . $timestamp;

		// Create destination directory
		$directory = $this->public_dir . DIRECTORY_SEPARATOR . $this->js_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir;
		if( ! is_dir($directory))
			mkdir($directory, 0777, true);

		// Concatenate files
		$buffer = $this->gatherLinks($this->js);

		// Minifiy
		$min = \JSMin::minify($buffer);

		// Write file
		file_put_contents($absolute_path, $min);

		return $relative_path . $timestamp;
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
				if('//' === substr($link, 0, 2))
					(isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off') ?
                        			$link = 'https:' . $link :
                        			$link = 'http:' . $link;
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

		return '/packages/' . $package[0] . '/' .$package[1] . '/' . ltrim($dir, '/') . '/' .$package[2];
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
	public function addDir($directory, $pattern = self::DEFAULT_REGEX)
	{
		// Check if public_dir exists
		if( ! is_dir($this->public_dir))
			throw new Exception('stolz/assets: Public dir not found');

		// Get files
		$files = $this->rglob($this->public_dir . DIRECTORY_SEPARATOR . $directory, $pattern, $this->public_dir);

		// No luck? Nothing to do
		if( ! $files)
			return $this;

		// Add CSS files
		if($pattern === self::CSS_REGEX)
		{
			$this->css = array_unique(array_merge($this->css, $files));
			return $this;
		}

		// Add JavaScript files
		if($pattern === self::JS_REGEX)
		{
			$this->js = array_unique(array_merge($this->js, $files));
			return $this;
		}

		// Unknown pattern. We must poll to know the extension :(
		foreach($files as $asset)
		{
			$info = pathinfo($asset);
			if(isset($info['extension']))
			{
				$ext = strtolower($info['extension']);
				if($ext === 'css' and ! in_array($asset, $this->css))
					$this->css[] = $asset;
				elseif($ext === 'js' and ! in_array($asset, $this->js))
					$this->js[] = $asset;
			}
		}

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
		return $this->addDir($directory, self::CSS_REGEX);
	}

	/**
	 * Add all JavaScript assets within $directory.
	 *
	 * @param  string $directory Relative to $this->public_dir
	 * @return Manager
	 */
	public function addDirJs($directory)
	{
		return $this->addDir($directory, self::JS_REGEX);
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

	/**
	 * Wrap a link
	 * @param  string $link
	 * @return string
	 */
	protected function wrap($link)
	{
		return ($this->wrap_command instanceof Closure) ? $this->wrap_command->__invoke($link) : $link;
	}
}
