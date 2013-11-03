<?php namespace Stolz\Assets;

use Config;
use Log;

class Manager
{
	/**
	 * Write debug info to log
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * Directory for local CSS assets. (No trailing slash!)
	 * @var string
	 */
	protected $css_dir = '/css';

	/**
	 * Directory for local JavaScript assets. (No trailing slash!)
	 * @var string
	 */
	protected $js_dir = '/js';

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
	 * JS files already added
	 * @var array
	 */
	protected $js = array();

	/**
	 * Class constructor
	 * It parses the config file
	 *
	 * @return void
	 */
	function __construct()
	{
		//Set debug mode
		if(Config::has('assets::debug'))
			$this->debug = (bool) intval(Config::get('assets::debug'));

		//Set custom CSS directory
		if(Config::has('assets::css_dir'))
		{
			$this->css_dir = Config::get('assets::css_dir');
			$this->debug and Log::debug("ASSETS: CSS dir set to '{$this->css_dir}'");
		}

		//Set custom JS directory
		if(Config::has('assets::js_dir'))
		{
			$this->js_dir = Config::get('assets::js_dir');
			$this->debug and Log::debug("ASSETS: JavaScript dir set to '{$this->js_dir}'");
		}

		//Read collections from config file
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

		//Autoload assets
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
			//JS or CSS
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

		if( ! $this->_isRemoteLink($asset))
			$asset = $this->_buildLocalLink($asset, $this->css_dir);

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

		if( ! $this->_isRemoteLink($asset))
			$asset = $this->_buildLocalLink($asset, $this->js_dir);

		if( ! in_array($asset, $this->js))
		{
			$this->js[] = $asset;
			$this->debug and Log::debug("ASSETS: Added JS '$asset'");
		}
		elseif($this->debug)
			Log::debug("ASSETS: Skiping already loaded JS '$asset'");

		return $this;
	}

	/**
	 * Build the CSS link tags
	 *
	 * @return string
	 */
	public function css()
	{
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
		$output = '';
		foreach($this->js as $file)
			$output .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";

		return $output;
	}

	/**
	 * Determine if a link to an asset is local or remote
	 *
	 * Undestands both "http://" and "https://" as well as protocol agnostic links "//"
	 *
	 * @param string $link
	 * @return bool
	 */
	protected function _isRemoteLink($link)
	{
		return ('http://' == substr($link, 0, 7) OR 'https://' == substr($link, 0, 8) OR '//' == substr($link, 0, 2));
	}

	/**
	 * Build a link to a local asset
	 *
	 * Detects packages links
	 *
	 * @param  string $asset
	 * @param  string $dir
	 * @return string the link
	 */
	protected function _buildLocalLink($asset, $dir)
	{
		if(preg_match('{^([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+):(.*)$}', $asset, $package))
			return '/packages/' . $package[1] . '/' .$package[2] . '/' . ltrim($dir, '/') . '/' .$package[3];

			return $dir . '/' . $asset;
	}

	/**
	 * Reset all assets
	 *
	 * @return Manager
	 */
	public function reset()
	{
		$this->resetCss();
		$this->resetJs();
		return $this;
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
	 * Reset JS assets
	 *
	 * @return Manager
	 */
	public function resetJs()
	{
		$this->js = array();
		return $this;
	}
}
