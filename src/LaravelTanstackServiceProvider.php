<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class LaravelTanstackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-tanstack.php',
            'laravel-tanstack'
        );

        $this->app->bind('datatable', function ($app, $params) {
            return new DataTable($params['query'] ?? null);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-tanstack.php' => config_path('laravel-tanstack.php'),
            ], 'laravel-tanstack-config');
        }

        $this->registerMacros();
    }

    /**
     * Add ->toDataTable() macro to query builders for ergonomic usage.
     */
    protected function registerMacros(): void
    {
        EloquentBuilder::macro('toDataTable', function () {
            /** @var EloquentBuilder $this */
            return DataTable::for($this);
        });

        QueryBuilder::macro('toDataTable', function () {
            /** @var QueryBuilder $this */
            return DataTable::for($this);
        });
    }
}
