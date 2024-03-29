<?php

namespace MSaddamKamal\ModelDependencySync;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ModelDependencyManagerServiceProvider extends ServiceProvider
{
    /**
     * Register the model dependencies configuration.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model_dependencies.php', 'model_dependencies');
    }

    /**
     * Boot the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/model_dependencies.php' => config_path('model_dependencies.php'),
        ], 'model-dependency-sync-config');

        $this->publishes([
            __DIR__.'/../resources/model_dependencies_template.php' => app_path('model_dependencies.php'),
        ], 'model-dependency-sync');


        $handlerClass = config('model_dependencies.handler', BaseModelDependencyHandler::class);
        $handler = new $handlerClass();

        Event::listen('eloquent.updated: *', function ($event, $model) use ($handler) {
            $handler->handleModelUpdated($model);
        });
    }
}
