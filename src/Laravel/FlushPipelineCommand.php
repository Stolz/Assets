<?php namespace Stolz\Assets\Laravel;

use Config;
use File;
use Illuminate\Console\Command;
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
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
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

			if( ! $this->confirm('Do you wish to continue? [yes|no]'))
				return;
		}

		// Purge directories
		$this->comment('Flushing pipeline directories...');
		foreach($directories as $dir)
		{
			$this->output->write("$dir ");

			if($this->purgeDir($dir))
				$this->info('Ok');
			else
				$this->error('Error');
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
		$config = config('assets', []);
		$groups = (isset($config['default'])) ? $config : ['default' => $config];
		if( ! is_null($group = $this->option('group')))
			$groups = array_only($groups, $group);

		// Parse pipeline directories of each group
		$directories = [];
		foreach($groups as $group => $config)
		{
			$pipelineDir = (isset($config['pipeline_dir'])) ? $config['pipeline_dir'] : 'min';

			$cssDir = (isset($config['css_dir'])) ? $config['css_dir'] : 'css';
			$directories[] = public_path($cssDir . DIRECTORY_SEPARATOR . $pipelineDir);


			$jsDir = (isset($config['js_dir'])) ? $config['js_dir'] : 'js';
			$directories[] = public_path($jsDir . DIRECTORY_SEPARATOR . $pipelineDir);
		}

		return array_unique($directories);
	}

	/**
	 * Purge directory.
	 *
	 * @param  string $directory
	 * @return boolean
	 */
	protected function purgeDir($directory)
	{
		if( ! File::isDirectory($directory))
			return true;

		if(File::isWritable($directory))
			return File::cleanDirectory($directory);

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
