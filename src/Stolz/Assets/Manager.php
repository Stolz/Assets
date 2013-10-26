<?php

namespace Stolz\Assets;

use Config;
use Log;

class Manager
{
	protected $debug = FALSE;
	protected $css_dir = '/css'; // Directory for local CSS assets. (No trailing slash!)
	protected $js_dir = '/js'; // Directory for local JavaScript assets. (No trailing slash!)
	protected $collections = array(); //Available collections parsed from config file
	protected $css = array(); //CSS files already added
	protected $js = array(); //JS files already added

	/**
	 * Parses config file
	 */
	function __construct()
	{
		//Set debug mode
		if(Config::has('assets::debug'))
			$this->debug = intval(Config::get('assets::debug'));

		//Set custom CSS directory
		if(Config::has('assets::css_dir'))
		{
			$this->css_dir = Config::get('assets::css_dir');
			$this->debug AND Log::info("ASSETS: CSS dir set to '{$this->css_dir}'");
		}

		//Set custom JS directory
		if(Config::has('assets::js_dir'))
		{
			$this->js_dir = Config::get('assets::js_dir');
			$this->debug AND Log::info("ASSETS: JavaScript dir set to '{$this->js_dir}'");
		}

		//Read collections from config file
		if(Config::has('assets::collections'))
		{
			if(is_array($conf = Config::get('assets::collections')))
			{
				$this->collections = $conf;
				$this->debug AND Log::info("ASSETS: Defined collections ". implode(', ', array_keys($this->collections)));
			}
			elseif($this->debug)
				$this->debug AND Log::warning('ASSETS: Collections must be an array');
		}

		//Autoload assets
		if(is_array($conf = Config::get('assets::autoload')))
		{
			foreach($conf as $a)
			{
				$this->debug AND Log::info("ASSETS: Autoloading '$a'");
				$this->add($a);
			}
		}
	}

	/**
	 * Adds an asset or a collection of assets
	 *
	 * It automatically detects the asset type (JavaScript, CSS or collection).
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
	 * @return $this (for method chaining)
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
			$this->debug AND Log::info("ASSETS: Adding collection '$asset'");
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
			}
			//Unknown asset type
			elseif($this->debug)
				Log::warning("ASSETS: Unable to add asset '$asset'. Unknown type");
		}

		return $this;
	}

	/**
	 * Adds a CSS asset
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
	 * @return $this (for method chaining)
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
			$this->debug AND Log::info("ASSETS: Added CSS '$asset'");
		}
		elseif($this->debug)
			Log::info("ASSETS: Skiping already loaded CSS '$asset'");

		return $this;
	}

	/**
	 * Adds a JavaScript asset
	 *
	 * It checks for duplicates.
	 * You may add more than one asset passing an array as argument.
	 *
	 * @param mixed $asset
	 * @return $this (for method chaining)
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
			$this->debug AND Log::info("ASSETS: Added JS '$asset'");
		}
		elseif($this->debug)
			Log::info("ASSETS: Skiping already loaded JS '$asset'");

		return $this;
	}

	/**
	 * Builds the CSS links
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
	 * Builds the JavaScript links
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
	 * Determines if a link to an asset is local or remote
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
	 * Builds a link to a local asset
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
}
