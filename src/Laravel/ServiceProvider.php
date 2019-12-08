<?php namespace Stolz\Assets\Laravel;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Stolz\Assets\Manager as Assets;

class ServiceProvider extends LaravelServiceProvider
{
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register paths to be published by 'vendor:publish' Artisan command
		$this->publishes([
			__DIR__ . '/config.php' => config_path('assets.php'),
		]);

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Stolz\Assets\Laravel\Facade');

		// Register the Artisan command
		$this->commands('stolz.assets.command.flush');
	}

	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register the Artisan command binding
		$this->app->bind('stolz.assets.command.flush', function ($app) {
			return new FlushPipelineCommand();
		});

		// Merge user's configuration with the default package config file
		//$this->mergeConfigFrom(__DIR__ . '/config.php', 'assets');
		$config = $this->app['config']->get('assets', []);

		// Register the library instances bindings ...

		// No groups defined. Assume the config is for the default group.
		if( ! isset($config['default']))
			return $this->registerAssetsManagerInstance('default', $config);

		// Multiple groups
		foreach($config as $groupName => $groupConfig)
			$this->registerAssetsManagerInstance($groupName, (array) $groupConfig);
	}

	/**
	 * Register an instance of the assets manager library in the IoC container.
	 *
	 * @param  string $name   Name of the group
	 * @param  array  $config Config of the group
	 *
	 * @return void
	 */
	protected function registerAssetsManagerInstance($name, array $config)
	{
		$this->app->singleton("stolz.assets.group.$name", function ($app) use ($config) {

			if( ! isset($config['public_dir']))
				$config['public_dir'] = public_path();

			return new Assets($config);
		});
	}
}
