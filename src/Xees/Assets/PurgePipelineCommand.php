<?php namespace Xees\Assets;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use File;
use Config;

class PurgePipelineCommand extends Command {

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
		$pipe_dir = Config::get('assets::pipe_dir', 'min');
		$css_dir = Config::get('assets::css_dir', 'css') . DIRECTORY_SEPARATOR . $pipe_dir;
		$js_dir = Config::get('assets::js_dir', 'js') . DIRECTORY_SEPARATOR . $pipe_dir;

		$purge_css = $this->purgeDir(public_path($css_dir));
		$purge_js = $this->purgeDir(public_path($js_dir));

		if($purge_css and $purge_js)
			$this->info('Done!');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

	/**
	 * Purge directory
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
