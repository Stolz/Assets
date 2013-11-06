<?php namespace Stolz\Assets;

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
		$this->package('stolz/assets');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Bind 'stolz.assets' component to the IoC container
		$this->app['stolz.assets'] = $this->app->share(function($app)
		{
			return new Manager;
		});

		// Add Manager facade alias
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Assets', 'Stolz\Assets\Facades\Assets');
		});

		// Bind 'stolz.assets.command.purgepipeline' component to the IoC container
		$this->app['stolz.assets.command.purgepipeline'] = $this->app->share(function($app)
		{
			return new PurgePipelineCommand();
		});

		// Add artisan command
		$this->commands('stolz.assets.command.purgepipeline');
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
