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

		// Add the blade directives
		$this->addBladeDirectives();
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

	/**
	 * Adds the blade directives
	 *
	 * @return void
     */
	protected function addBladeDirectives()
	{
		// Adds @asset tag to blade templates
		Blade::extend(function($view, $compiler)
		{
			$pattern = $compiler->createMatcher('asset');

			return preg_replace($pattern, '<?php Assets::add$2; ?>', $view);
		});

		// Adds @assetjs tag to blade templates
		Blade::extend(function($view, $compiler)
		{
			$pattern = $compiler->createMatcher('assetjs');

			return preg_replace($pattern, '<?php Assets::addJs$2; ?>', $view);
		});

		// Adds @assetcss tag to blade templates
		Blade::extend(function($view, $compiler)
		{
			$pattern = $compiler->createMatcher('assetcss');

			return preg_replace($pattern, '<?php Assets::addCss$2; ?>', $view);
		});
	}
}
