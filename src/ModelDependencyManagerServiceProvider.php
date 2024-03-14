<?php

namespace MSaddamKamal\ModelDependencySync;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ModelDependencyManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model_dependencies.php', 'model_dependencies');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/model_dependencies.php' => config_path('model_dependencies.php'),
        ], 'model-dependency-sync-config');


        $handlerClass = config('model_dependencies.handler', BaseModelDependencyHandler::class);
        $handler = new $handlerClass();

        Event::listen('eloquent.updated: *', function ($event, $model) use ($handler) {
            $handler->handleModelUpdated($model); // Ensure the argument is always an array
        });

        // Your existing event listener logic here
    }
}
