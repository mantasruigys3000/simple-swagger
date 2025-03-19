<?php

namespace Mantasruigys3000\SimpleSwagger\commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Mantasruigys3000\SimpleSwagger\Writer;

class GenerateDoc extends Command
{
    protected $signature = 'swag:build';

    public function handle()
    {
        //$routes = Route::getRoutes();

        /*foreach ($routes as $route)
        {
            //var_dump('Generating for '. $route->uri);
        }*/

        $writer = new Writer();
        $output = $writer->write();

        $this->info('docs generated at '. $output);
    }
}