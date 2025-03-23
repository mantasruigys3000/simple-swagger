<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RouteData;
use Mantasruigys3000\SimpleSwagger\attributes\RouteDescription;
use Mantasruigys3000\SimpleSwagger\attributes\RouteTag;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Yaml\Yaml;
use function Orchestra\Testbench\package_path;

class Writer
{
    public function __construct() {}

    public function write() : string
    {

        /* Example Data
        $examplePath = realpath(__DIR__ . "..\\..\\examples\\openapi-example.php");
        $exampleArray = include $examplePath;
        $data = $exampleArray;
        */

        /**
         * This is the object all OpenAPI data is written to, including paths and schemas
         */
        $data = [];

        /**
         * We can start with appending standard openAPI data. this will mostly be determined by the project config
         */

        $data['openapi'] = config('docs.openapi');
        $data['info'] = config('docs.info');
        $data['servers'] = config('docs.servers');

        // Set up the paths and components lists
        $data['paths'] = [];
        $data['components'] = [];

        // Now we need to gather any registered routes we want documented, and construct paths for them
        $routes = $this->getRoutes();
        $tags = [];

        foreach ($routes as $route)
        {
            $controllerClass = $route->getControllerClass();
            $functionName = $route->getActionMethod();
            $routeTags = [];

            $reflection = new ReflectionClass($controllerClass);
            foreach ($reflection->getAttributes(RouteTag::class) as $attribute)
            {
                /**
                 * @var RouteTag $routeTag
                 */
                $routeTag = $attribute->newInstance();
                $tags[] = $routeTag->toArray();
                $routeTags[] = $routeTag->name;

            }

            /**
             * Get description
             * Using attribute is preferable but will need to implement ability to use the docblock comment
             */
            $routeReflection = new ReflectionMethod($controllerClass,$functionName);

            /**
             *  Only need the first instance, no need to support multiple descriptions
             *  @var ?RouteDescription $descriptionObject
             */
            $descriptionAttributes = $routeReflection->getAttributes(RouteDescription::class);
            $descriptionObject = count($descriptionAttributes) === 0 ? null : $descriptionAttributes[0]->newInstance();

            // Construct path object for this route
            //$data['paths']['uri']['operation']
            $pathUri = $route->uri;
            $pathObj = [
                'tags' => $routeTags,
                'summary' => $descriptionObject?->summary ?? '',
                'description' => $descriptionObject->description ?? '',
                'requestBody' => [],
                'responses' => [],
                'parameters' => [['in' => 'path','name' => 'member','type' => 'string']],
            ];
            $data['paths'][$pathUri][strtolower($route->methods()[0])] = $pathObj;

            // Append route tags to all known tags
        }

        // Append Generated Data
        $data['tags'] = $tags;

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

    /**
     * @return RouteData[]
     */
    private function getRoutes() : array
    {
        $routes = Route::getRoutes()->getRoutes();

        // Filter out unwanted routes
        $routes = array_filter($routes,function(RouteData $route){

            // Look through every allowed route, if any accept they allow the route
            foreach (config('docs.allowed_routes') as $allowedRoute)
            {
                if (fnmatch($allowedRoute,$route->uri)){
                    return true;
                }
            }

            return false;
        });

        return $routes;
    }
}
