<?php

namespace Mantasruigys3000\SimpleSwagger\providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Mantasruigys3000\SimpleSwagger\commands\GenerateDoc;

class SimpleSwaggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()){

            $this->commands([
                GenerateDoc::class
            ]);
        }

        $this->publishes([
            __DIR__. '/../../config/docs.php' => config_path('docs.php'),
        ]);
    }
}