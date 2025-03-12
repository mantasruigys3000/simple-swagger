<?php

namespace Mantasruigys3000\SimpleSwagger;

use Symfony\Component\Yaml\Yaml;

class Writer
{
    public function __construct()
    {

    }

    public function write()
    {
        // Gather openapi php array

        $data = [
            'version' => '1.0.0',
        ];

        // Turn to yaml

        $yaml = Yaml::dump($data);

        // Put yaml to file

        $dir = explode(DIRECTORY_SEPARATOR,config('docs.output_path'));
        array_pop($dir);
        $dir = implode(DIRECTORY_SEPARATOR,$dir);
        if (! is_dir($dir))
        {
            mkdir($dir,recursive:true);
        }

        file_put_contents(config('docs.output_path'),$yaml);

    }
}