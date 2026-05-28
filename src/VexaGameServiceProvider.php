<?php

namespace Rrq\Vexagame;

use Illuminate\Support\ServiceProvider;

class VexaGameServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/vexagame.php' => config_path('vexagame.php'),
        ], 'vexagame-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vexagame.php', 'vexagame');

        $this->app->singleton(VexaGame::class, function ($app) {
            return new VexaGame(config('vexagame'));
        });

        $this->app->alias(VexaGame::class, 'vexagame');
    }
}
