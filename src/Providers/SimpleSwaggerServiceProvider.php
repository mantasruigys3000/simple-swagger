<?php

namespace Mantasruigys3000\SimpleSwagger\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Mantasruigys3000\SimpleSwagger\Commands\GenerateDoc;
use Mantasruigys3000\SimpleSwagger\Commands\ParseAll;
use Mantasruigys3000\SimpleSwagger\Commands\ParseFile;

class SimpleSwaggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()){

            $this->commands([
                GenerateDoc::class,
                ParseFile::class,
                ParseAll::class,
            ]);
        }

        $this->publishes([
            __DIR__. '/../../config/docs.php' => config_path('docs.php'),
        ]);
    }
}
