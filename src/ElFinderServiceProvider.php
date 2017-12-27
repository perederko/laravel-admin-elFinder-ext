<?php

namespace Perederko\Laravel\Ext\Admin\ElFinder;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class ElFinderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/admin-elfinder.php';
        $this->mergeConfigFrom($configPath, 'admin-elfinder');
        $this->publishes([$configPath => config_path('admin-elfinder.php')], 'config');

        $this->app->singleton('command.admin-elfinder.publish', function ($app) {
            $publicPath = $app['path.public'];
            return new Console\PublishCommand($app['files'], $publicPath);
        });
        $this->commands('command.admin-elfinder.publish');
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  Route $router
     * @return void
     */
    public function boot(Route $router)
    {
        $viewPath = __DIR__ . '/../resources/views';
        $this->loadViewsFrom($viewPath, ElFinder::VIEW_NAMESPACE);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $viewPath => base_path('resources/views/vendor/laravel-admin-elfinder'),
            ], 'views');

            $this->publishes(
                [__DIR__ . '/../resources/assets/' => public_path('vendor/laravel-admin-elfinder')],
                'admin-elfinder'
            );
        }

        if (!defined('ELFINDER_IMG_PARENT_URL')) {
            define('ELFINDER_IMG_PARENT_URL', $this->app['url']->asset('packages/' . ElFinder::PACKAGE));
        }

        ElFinder::boot();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('command.admin-elfinder.publish');
    }
}
