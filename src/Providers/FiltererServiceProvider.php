<?php

namespace CultureGr\Filterer\Providers;

use CultureGr\Filterer\Commands\MakeCustomFilterCommand;
use Illuminate\Support\ServiceProvider;

class FiltererServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/filterer.php', 'filterer'
        );

        $this->commands([MakeCustomFilterCommand::class]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/filterer.php' => config_path('filterer.php'),
            ], 'filterer-config');
        }
    }
}
