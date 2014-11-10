<?php namespace Stolz\Assets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class ManagerServiceProvider extends ServiceProvider
{
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
		// Register the package namespace
		$this->package('stolz/assets');

		// Read settings from config file
		$config = $this->app->config->get('assets::config', array());
		$config['public_dir'] = public_path();

		// Apply config settings
		$this->app['stolz.assets']->config($config);

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Stolz\Assets\Facades\Assets');

		// Add artisan command
		$this->commands('stolz.assets.command.purgepipeline');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Bind 'stolz.assets' shared component to the IoC container
		$this->app->singleton('stolz.assets', function ($app) {
			return new Manager();
		});

		// Bind 'stolz.assets.command.purgepipeline' component to the IoC container
		$this->app->bind('stolz.assets.command.purgepipeline', function ($app) {
			return new PurgePipelineCommand();
		});
	}
}
