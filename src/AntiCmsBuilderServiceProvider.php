<?php

namespace AntiCmsBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AntiCmsBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\PageBuilderCommand::class,
            ]);
        }

        // Register services
        $this->app->singleton(FieldService::class);
        $this->app->singleton(ComponentManager::class);
    }

    public function boot(): void
    {
        $this->bootPublishing();
        $this->bootRoutes();
    }

    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish React components and resources
            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/anti-cms-builder'),
            ], 'anti-cms-builder-resources');

            // Publish config file
            $this->publishes([
                __DIR__.'/../config/anti-cms-builder.php' => config_path('anti-cms-builder.php'),
            ], 'anti-cms-builder-config');
        }

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/anti-cms-builder.php', 'anti-cms-builder');
    }

    protected function bootRoutes(): void
    {
        Route::macro('crud', function (string $url, string $controller, array $options = []) {
            $as = $options['as'] ?? str($url)->replace('/', '.')->after('.');
            $middleware = $options['middleware'] ?? [];
            $title = $options['title'] ?? ucfirst(str($as)->afterLast('.')->replace('.', ' ')->toString());

            Route::prefix($url)
                ->middleware($middleware)
                ->group(function () use ($controller, $as) {
                    Route::get('/', [$controller, 'index'])->name("$as.index");
                    Route::get('/create', [$controller, 'create'])->name("$as.create");
                    Route::prefix('details/{id}')->group(function () use ($controller, $as) {
                        Route::get('/edit', [$controller, 'edit'])->name("$as.edit");
                        Route::get('/show', [$controller, 'show'])->name("$as.show");
                        Route::put('/update', [$controller, 'update'])->name("$as.update");
                        Route::get('/restore', [$controller, 'restore'])->name("$as.restore");
                        Route::delete('/delete', [$controller, 'delete'])->name("$as.delete");
                        Route::delete('/force-delete', [$controller, 'forceDelete'])->name("$as.delete.force");
                    });
                    Route::post('/', [$controller, 'store'])->name("$as.store");
                });
        });
    }
}
