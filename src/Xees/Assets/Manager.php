<?php namespace Xees\Assets;

class Manager
{
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
	 * Relative to your public directory.
	 * No trailing slash!.
	 * @var string
	 */
	protected $js_dir = 'js';

	/**
	 * Directory for storing pipelined assets.
	 * Relative to your assets directories.
	 * No trailing slash!.
	 * @var string
	 */
	protected $pipeline_dir = 'min';

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
	 *
	 * @param  array $options
	 * @return void
	 */
	function __construct(array $options = array())
	{
		if($options)
			$this->config($options);
	}

	/**
	 * Set config options
	 *
	 * @param  array $options
	 * @return void
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
		if( ! is_dir($this->public_dir))
			throw new \Exception('xees/assets: Public dir not found');

		// Set custom Pipeline directory
		if(isset($config['pipeline_dir']))
			$this->pipeline_dir = $config['pipeline_dir'];

		// Set custom CSS directory
		if(isset($config['css_dir']))
			$this->css_dir = $config['css_dir'];

		// Set custom JavaScript directory
		if(isset($config['js_dir']))
			$this->js_dir = $config['js_dir'];
	}

	/**
	 * Add a CSS asset
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
	 * Add a JavaScript asset
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
	 * Build the CSS link tags
	 *
	 * @param mixed $asset
	 * @return string
	 */
	public function css($asset = null)
	{
		if(!$asset){
			// clear older assets
			$this->resetCss();
			// add new items to empty array
			$this->addCss($asset);
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
	 * @param mixed $asset
	 * @return string
	 */
	public function js($asset = null)
	{
		if(!$asset){
		// clear older assets
		$this->resetJs();
		// add new items to empty array
		$this->addJs($asset);
		}

		if($this->pipeline)
			return '<script type="text/javascript" src="'.$this->jsPipeline().'"></script>'."\n";

		$output = '';
		foreach($this->js as $file)
			$output .= '<script type="text/javascript" src="'.$file.'"></script>'."\n";

		return $output;
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
		$relative_path = "{$this->css_dir}/{$this->pipeline_dir}/$file";
		$absolute_path =  $this->public_dir . DIRECTORY_SEPARATOR . $this->css_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir . DIRECTORY_SEPARATOR . $file;
		$timestamp = (intval($this->pipeline) > 1) ? '?' . $this->pipeline : null;

		// If pipeline exist return it
		if(file_exists($absolute_path))
			return $relative_path . $timestamp;

		// Create destination directory
		$directory = $this->public_dir . DIRECTORY_SEPARATOR . $this->css_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir;
		if( ! is_dir($directory))
			mkdir($directory, 0777, true);

		// Concatenate files
		$buffer = $this->buildBuffer($this->css);

		// Minifiy
		$min = new \CSSmin();
		$min = $min->run($buffer);

		// Write file
		file_put_contents($absolute_path, $min);

		return $relative_path . $timestamp;
	}

	/**
	 * Minifiy and concatenate JavaScript files
	 *
	 * @return string
	 */
	protected function jsPipeline()
	{
		$file = md5(implode($this->js)).'.js';
		$relative_path = "{$this->js_dir}/{$this->pipeline_dir}/$file";
		$absolute_path =  $this->public_dir . DIRECTORY_SEPARATOR . $this->js_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir . DIRECTORY_SEPARATOR . $file;
		$timestamp = (intval($this->pipeline) > 1) ? '?' . $this->pipeline : null;

		// If pipeline exist return it
		if(file_exists($absolute_path))
			return $relative_path . $timestamp;

		// Create destination directory
		$directory = $this->public_dir . DIRECTORY_SEPARATOR . $this->js_dir . DIRECTORY_SEPARATOR . $this->pipeline_dir;
		if( ! is_dir($directory))
			mkdir($directory, 0777, true);

		// Concatenate files
		$buffer = $this->buildBuffer($this->js);

		// Minifiy
		$min = \JSMin::minify($buffer);

		// Write file
		file_put_contents($absolute_path, $min);

		return $relative_path . $timestamp;
	}

	/**
	 * Download and concatenate links
	 *
	 * @param  array  $links
	 * @return string
	 */
	protected function buildBuffer(array $links)
	{
		$buffer = '';
		foreach($links as $link)
		{
			if($this->isRemoteLink($link))
			{
				if('//' == substr($link, 0, 2))
					$link = 'http:' . $link;
			}
			else
			{
				$link = $this->public_dir . DIRECTORY_SEPARATOR . $link;
			}

			$buffer .= file_get_contents($link);
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
	 * Determine whether a link is local or remote
	 *
	 * Undestands both "http://" and "https://" as well as protocol agnostic links "//"
	 *
	 * @param  string $link
	 * @return bool
	 */
	protected function isRemoteLink($link)
	{
		return ('http://' == substr($link, 0, 7) or 'https://' == substr($link, 0, 8) or '//' == substr($link, 0, 2));
	}

}
