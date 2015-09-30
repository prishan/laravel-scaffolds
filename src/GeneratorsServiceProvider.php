<?php

namespace Prishan\LaravelScaffolds;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//

	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{

		$this->registerScaffoldGenerator();
                
                /*
                 * Register the service provider for the dependency.
                 */
                $this->app->register('Proengsoft\JsValidation\JsValidationServiceProvider');
                
                /*
                 * Create aliases for the dependency.
                 */
                $loader = \Illuminate\Foundation\AliasLoader::getInstance();
                $loader->alias('JsValidator', 'Proengsoft\JsValidation\Facades\JsValidatorFacade');

	}


	/**
	 * Register the make:scaffold generator.
	 */
	private function registerScaffoldGenerator()
	{
		$this->app->singleton('command.larascaf.scaffold', function ($app) {
			return $app['Prishan\LaravelScaffolds\Commands\ScaffoldMakeCommand'];
		});

		$this->commands('command.larascaf.scaffold');
	}


}
