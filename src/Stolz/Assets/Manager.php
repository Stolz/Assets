<?php namespace Stolz\Assets;

use Config;
use Log;
use File;

class Manager
{
	/**
	 * Write debug info to log
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * Set to true to enable assets pipeline (concatenation and minification).
	 * @var bool
	 */
	protected $pipeline = false;

	/**
	 * Directory for local CSS assets. (No trailing slash!).
	 * Relative to your public directory.
	 * @var string
	 */
	protected $css_dir = 'css';

	/**
	 * Directory for local JavaScript assets. (No trailing slash!)
	 * Relative to your public directory.
	 * @var string
	 */
	protected $js_dir = 'js';

	/**
	 * Directory for storing pipelined assets. (No trailing slash!)
	 * Relative to your assets directory.
	 * @var string
	 */
	protected $pipe_dir = 'min';

	/**
	 * Available collections parsed from config file
	 * @var array
	 */
	protected $collections = array();

	/**
	 * CSS files already added
	 * @var array
	 */
	protected $css = array();

	/**
	 * JavaScript files already added
	 * @var array
	 */
	protected $js = array();

	/**
	 * Class constructor.
	 * Parse config file.
	 *
	 * @return void
	 */
	function __construct()
	{
		// Set debug mode
		if(Config::has('assets::debug'))
			$this->debug = (bool) Config::get('assets::debug');

		// Set pipeline mode
		if(Config::has('assets::pipeline'))
			$this->pipeline = (bool) Config::get('assets::pipeline');
		$this->debug and Log::debug('ASSETS: Pipeline '.($this->pipeline ? 'enabled' : 'disabled'));

		// Set custom CSS directory
		if(Config::has('assets::css_dir'))
			$this->css_dir = Config::get('assets::css_dir');
		$this->debug and Log::debug("ASSETS: CSS dir set to '{$this->css_dir}'");

		// Set custom JavaScript directory
		if(Config::has('assets::js_dir'))
			$this->js_dir = Config::get('assets::js_dir');
		$this->debug and Log::debug("ASSETS: JavaScript dir set to '{$this->js_dir}'");

		// Set custom Pipeline directory
		if(Config::has('assets::pipe_dir'))
			$this->pipe_dir = Config::get('assets::pipe_dir');
		$this->debug and Log::debug("ASSETS: Pipeline dir set to '{$this->pipe_dir}'");

		// Read collections from config file
		if(Config::has('assets::collections'))
		{
			if(is_array($conf = Config::get('assets::collections')))
			{
				$this->collections = $conf;
				$this->debug and Log::debug("ASSETS: Defined collections ". implode(', ', array_keys($this->collections)));
			}
			elseif($this->debug)
				Log::warning('ASSETS: Collections must be an array');
		}

		// Autoload assets
		if(is_array($conf = Config::get('assets::autoload')))
		{
			foreach($conf as $a)
			{
				$this->debug and Log::debug("ASSETS: Autoloading '$a'");
				$this->add($a);
			}
		}
	}

	/**
	 * Add an asset or a collection of assets
	 *
	 * It automatically detects the asset type (JavaScript, CSS or collection).
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
	 * @return Manager
	 */
	public function add($asset)
	{
		//More than one asset
		if(is_array($asset))
		{
			foreach($asset as $a)
				$this->add($a);
		}
		//Collection
		elseif(isset($this->collections[$asset]))
		{
			$this->debug and Log::debug("ASSETS: Adding collection '$asset'");
			$this->add($this->collections[$asset]);
		}
		else
		{
			//JavaScript or CSS
			$info = pathinfo($asset);
			if(isset($info['extension']))
			{
				$ext = strtolower($info['extension']);
				if($ext == 'css')
					$this->addCss($asset);
				elseif($ext == 'js')
					$this->addJs($asset);
				elseif($this->debug)
					Log::warning("ASSETS: Unable to add asset '$asset'. Unknown type");
			}
			//Unknown asset type
			elseif($this->debug)
				Log::warning("ASSETS: Unable to add asset '$asset'. Unknown type");
		}

		return $this;
	}

	/**
	 * Add a CSS asset
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
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
		{
			$this->css[] = $asset;
			$this->debug and Log::debug("ASSETS: Added CSS '$asset'");
		}
		elseif($this->debug)
			Log::debug("ASSETS: Skiping already loaded CSS '$asset'");

		return $this;
	}

	/**
	 * Add a JavaScript asset
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
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
		{
			$this->js[] = $asset;
			$this->debug and Log::debug("ASSETS: Added JavaScript '$asset'");
		}
		elseif($this->debug)
			Log::debug("ASSETS: Skiping already loaded JavaScript '$asset'");

		return $this;
	}

	/**
	 * Build the CSS link tags
	 *
	 * @return string
	 */
	public function css()
	{
		if( ! $this->css)
		{
			$this->debug and Log::debug('ASSETS: No CSS assets have been added');
			return null;
		}

		if($this->pipeline)
			return '<link type="text/css" rel="stylesheet" href="'.$this->cssPipeline().'" />'."\n";

		$output = '';
		foreach($this->css as $file)
			$output .= '<link type="text/css" rel="stylesheet" href="'.$file.'" />'."\n";

		return $output;
	}

	/**
	 * Build the JavaScript script tags
	 *
	 * @return string
	 */
	public function js()
	{
		if( ! $this->js)
		{
			$this->debug and Log::debug('ASSETS: No JavaScript assets have been added');
			return null;
		}

		if($this->pipeline)
			return '<script type="text/javascript" src="'.$this->jsPipeline().'"></script>'."\n";

		$output = '';
		foreach($this->js as $file)
			$output .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";

		return $output;
	}

	/**
	 * Reset all assets
	 *
	 * @return Manager
	 */
	public function reset()
	{
		return $this->resetCss()->resetJs();
	}

	/**
	 * Reset CSS assets
	 *
	 * @return Manager
	 */
	public function resetCss()
	{
		$this->css = array();
		return $this;
	}

	/**
	 * Reset JavaScript assets
	 *
	 * @return Manager
	 */
	public function resetJs()
	{
		$this->js = array();
		return $this;
	}

	/**
	 * Minifiy and concatenate CSS files
	 *
	 * @return string
	 */
	protected function cssPipeline()
	{

		$file = md5(implode($this->css)).'.css';
		$relative_path = "{$this->css_dir}/{$this->pipe_dir}/$file";
		$absolute_path =  public_path($this->css_dir . DIRECTORY_SEPARATOR . $this->pipe_dir . DIRECTORY_SEPARATOR . $file);

		// If pipeline exist return it
		if(File::exists($absolute_path))
			return $relative_path;

		$this->debug and Log::debug('ASSETS: Minifying CSS');

		// Create destination directory
		$directory = public_path($this->css_dir . DIRECTORY_SEPARATOR . $this->pipe_dir);
		if( ! File::isDirectory($directory))
			File::makeDirectory($directory);

		// Concatenate files
		$buffer = $this->buildBuffer($this->css);

		// Minifiy
		$min = new \CSSmin();
		$min = $min->run($buffer);

		// Write file
		File::put($absolute_path, $min);

		return $relative_path;
	}

	/**
	 * Minifiy and concatenate JavaScript files
	 *
	 * @return string
	 */
	protected function jsPipeline()
	{
		$file = md5(implode($this->js)).'.js';
		$relative_path = "{$this->js_dir}/{$this->pipe_dir}/$file";
		$absolute_path =  public_path($this->js_dir . DIRECTORY_SEPARATOR . $this->pipe_dir . DIRECTORY_SEPARATOR . $file);

		// If pipeline exist return it
		if(File::exists($absolute_path))
			return $relative_path;

		$this->debug and Log::debug('ASSETS: Minifying JavaScript');

		// Create destination directory
		$directory = public_path($this->js_dir . DIRECTORY_SEPARATOR . $this->pipe_dir);
		if( ! File::isDirectory($directory))
			File::makeDirectory($directory);

		// Concatenate files
		$buffer = $this->buildBuffer($this->js);

		// Minifiy
		$min = \JSMin::minify($buffer);

		// Write file
		File::put($absolute_path, $min);

		return $relative_path;
	}

	/**
	 * Download and concatenate links
	 *
	 * @param  array $links
	 * @return string
	 */
	protected function buildBuffer(array $links)
	{
		$buffer = '';
		foreach($links as $link)
		{
			if($this->isRemoteLink($link))
			{
				if(starts_with($link, '//'))
					$link = 'http:'.$link;

				$this->debug and Log::debug('ASSETS: Downloading '.$link);
				$buffer .= File::getRemote($link);
			}
			else
			{
				$buffer .= File::get(public_path($link));
			}
		}
		return $buffer;
	}

	/**
	 * Build link to local asset
	 *
	 * Detects packages links
	 *
	 * @param  string $asset
	 * @param  string $dir
	 * @return string the link
	 */
	protected function buildLocalLink($asset, $dir)
	{
		if(preg_match('{^([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+):(.*)$}', $asset, $package))
			return '/packages/' . $package[1] . '/' .$package[2] . '/' . ltrim($dir, '/') . '/' .$package[3];

			return $dir . '/' . $asset;
	}

	/**
	 * Determine if a link to an asset is local or remote
	 *
	 * Undestands both "http://" and "https://" as well as protocol agnostic links "//"
	 *
	 * @param string $link
	 * @return bool
	 */
	protected function isRemoteLink($link)
	{
		return ('http://' == substr($link, 0, 7) OR 'https://' == substr($link, 0, 8) OR '//' == substr($link, 0, 2));
	}

}
