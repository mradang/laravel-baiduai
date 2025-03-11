<?php

namespace mradang\LaravelBaiduAI;

use Illuminate\Support\ServiceProvider;

class LaravelBaiduAIServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/baiduai.php' => config_path('baiduai.php'),
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/baiduai.php',
            'baiduai'
        );

        $this->app->singleton('laravel-baiduai', function ($app) {
            return new BaiduAIManager($app);
        });
    }
}
