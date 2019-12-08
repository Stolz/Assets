<?php namespace Stolz\Assets\Laravel;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class FlushPipelineCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'asset:flush';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Flush assets pipeline files.';

	/**
	 * Class constructor.
	 *
	 * @param \Illuminate\Contracts\Config\Repository $config
	 * @param \Illuminate\Filesystem\Filesystem $filesystem
	 *
	 * @return void
	 */
	public function __construct()//Config $config, Filesystem $filesystem (See NOTE below)
	{
		parent::__construct();

		// NOTE: Dependency injection for Artisan commands constructor was not introduced until Laravel 5.1 (LST).
		// In order to keep compatibility with Laravel 5.0 we manually resolve the dependencies

		//$this->config = $config;
		//$this->filesystem = $filesystem;

		$this->config = app(Config::class);
		$this->filesystem = app(Filesystem::class);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		// Get directories to purge
		if( ! $directories = $this->getPipelineDirectories())
			return $this->error('The provided group does not exist');

		// Ask for confirmation
		if( ! $this->option('force'))
		{
			$this->info('All content of the following directories will be deleted:');
			foreach($directories as $dir)
				$this->comment($dir);

			if( ! $this->confirm('Do you want to continue?'))
				return;
		}

		// Purge directories
		$this->comment('Flushing pipeline directories...');
		foreach($directories as $dir)
		{
			$this->output->write("$dir ");

			if($this->purgeDir($dir))
				$this->info('OK');
			else
				$this->error('ERROR');
		}

		$this->comment('Done!');
	}

	/**
	 * Get the pipeline directories of the groups.
	 *
	 * @return array
	 */
	protected function getPipelineDirectories()
	{
		// Parse configured groups
		$config = $this->config->get('assets', []);
		$groups = (isset($config['default'])) ? $config : ['default' => $config];
		if( ! is_null($group = $this->option('group')))
			$groups = array_only($groups, $group);

		// Parse pipeline directories of each group
		$directories = [];
		foreach($groups as $group => $config)
		{
			$pipelineDir = (isset($config['pipeline_dir'])) ? $config['pipeline_dir'] : 'min';
			$publicDir = (isset($config['public_dir'])) ? $config['public_dir'] : public_path();
			$publicDir = rtrim($publicDir, DIRECTORY_SEPARATOR);

			$cssDir = (isset($config['css_dir'])) ? $config['css_dir'] : 'css';
			$directories[] = implode(DIRECTORY_SEPARATOR, [$publicDir, $cssDir, $pipelineDir]);

			$jsDir = (isset($config['js_dir'])) ? $config['js_dir'] : 'js';
			$directories[] = implode(DIRECTORY_SEPARATOR, [$publicDir, $jsDir, $pipelineDir]);
		}

		// Clean results
		$directories = array_unique($directories);
		sort($directories);

		return $directories;
	}

	/**
	 * Remove the contents of a given directory.
	 *
	 * @param  string $directory
	 *
	 * @return bool
	 */
	protected function purgeDir($directory)
	{
		if( ! $this->filesystem->isDirectory($directory))
			return true;

		if($this->filesystem->isWritable($directory))
			return $this->filesystem->cleanDirectory($directory);

		$this->error($directory . ' is not writable');

		return false;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['group', 'g', InputOption::VALUE_REQUIRED, 'Only flush the provided group'],
			['force', 'f', InputOption::VALUE_NONE, 'Do not prompt for confirmation'],
		];
	}
}
