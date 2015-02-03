<?php namespace Stolz\Assets\Laravel;

use Stolz\Assets\Manager as Assets;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class LegacyServiceProvider extends LaravelServiceProvider
{
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

		// Apply config settings
		$this->app['stolz.assets']->config($config);

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Stolz\Assets\Laravel\Facade');

		// Add artisan command
		$this->commands('stolz.assets.command.flush');
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
			return new Assets();
		});

		// Bind 'stolz.assets.command.flush' component to the IoC container
		$this->app->bind('stolz.assets.command.flush', function ($app) {
			return new FlushPipelineCommand();
		});
	}
}
