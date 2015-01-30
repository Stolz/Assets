<?php namespace Stolz\Assets;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register paths to be published by the vendor:publish artisan command.
		$this->publishes([
			$this->configFilePath => config_path('assets.php'),
		]);

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Stolz\Assets\Facades\Assets');

		// Add artisan command
		$this->commands('stolz.assets.command.flush');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Set the path for the default config file
		$this->configFilePath = realpath(__DIR__.'/../config.php');

		// Merge default configuration with user's configuration.
		$this->mergeConfigFrom('assets', $this->configFilePath);

		// Get package config
		$config = $this->app->config->get('assets', []);

		// Bind 'stolz.assets' shared component to the IoC container
		$this->app->singleton('stolz.assets', function ($app) use ($config) {
			return new Manager($config);
		});

		// Bind 'stolz.assets.command.flush' component to the IoC container
		$this->app->bind('stolz.assets.command.flush', function ($app) {
			return new PurgePipelineCommand();
		});
	}
}
