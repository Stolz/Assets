<?php namespace Xees\Assets;

use Illuminate\Support\ServiceProvider;

class ManagerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('xees/assets');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Bind 'xees.assets' component to the IoC container
		$this->app['xees.assets'] = $this->app->share(function($app)
		{
			$config = \Config::get('assets::config', array());
			$config['public_dir'] = public_path();

			return new Manager($config);
		});

		// Add Manager facade alias
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Assets', 'xees\Assets\Facades\Assets');
		});

		// Bind 'xees.assets.command.purgepipeline' component to the IoC container
		$this->app['xees.assets.command.purgepipeline'] = $this->app->share(function($app)
		{
			return new PurgePipelineCommand();
		});

		// Add artisan command
		$this->commands('xees.assets.command.purgepipeline');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
