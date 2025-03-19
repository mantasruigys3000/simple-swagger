<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger;

use Symfony\Component\Yaml\Yaml;
use function Orchestra\Testbench\package_path;

class Writer
{
    public function __construct() {}

    public function write() : string
    {

        $examplePath = realpath(__DIR__ . "..\\..\\examples\\openapi-example.php");
        $exampleArray = include $examplePath;

        // Get example data
        // $example = include base_path('/examples/openapi-example.php');

        // Gather openapi php array

        $data = $exampleArray;

        // Turn to yaml

        $yaml = Yaml::dump($data,flags: Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_OBJECT_AS_MAP);

        // Put yaml to file

        $outputPath = config('docs.output_path');

        $dir = explode(DIRECTORY_SEPARATOR, $outputPath);
        array_pop($dir);
        $dir = implode(DIRECTORY_SEPARATOR, $dir);
        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents($outputPath, $yaml);
        return $outputPath;
    }
}
