<?php namespace Stolz\Assets;

use Illuminate\Console\Command;
use Config;
use File;

class PurgePipelineCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'asset:purge-pipeline';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Flush assets pipeline files.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$pipeDir = Config::get('assets::pipeline_dir', 'min');
		$cssDir = Config::get('assets::css_dir', 'css') . DIRECTORY_SEPARATOR . $pipeDir;
		$jsDir = Config::get('assets::js_dir', 'js') . DIRECTORY_SEPARATOR . $pipeDir;

		$purgeCss = $this->purgeDir(public_path($cssDir));
		$purgeJs = $this->purgeDir(public_path($jsDir));

		if($purgeCss and $purgeJs)
			$this->info('Done!');
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
}
