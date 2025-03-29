<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RouteData;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\RouteParameterAttribute;
use Mantasruigys3000\SimpleSwagger\attributes\PathParameter;
use Mantasruigys3000\SimpleSwagger\attributes\RouteDescription;
use Mantasruigys3000\SimpleSwagger\attributes\RouteTag;
use Mantasruigys3000\SimpleSwagger\attributes\Security;
use Mantasruigys3000\SimpleSwagger\data\SecurityScheme;
use Mantasruigys3000\SimpleSwagger\enums\SecuritySchemeType;
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
        $data['components']['securitySchemes'] = $this->getSecuritySchemes(); // Generate the security schemes

        // Set up the paths and components lists
        $data['paths'] = [];
        $components = [];

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
            $pathUri = '/' . $route->uri;
            $pathObj = [
                'tags' => $routeTags,
                'summary' => $descriptionObject?->summary ?? '',
                'description' => $descriptionObject->description ?? '',
//                'requestBody' => [],
//                'responses' => [],
                'parameters' => $this->getParameters($routeReflection),
                'security' => $this->getRouteSecurity($routeReflection),
            ];
            $data['paths'][$pathUri][strtolower($route->methods()[0])] = $pathObj;

            // Append route tags to all known tags
        }

        // Append Generated Data
        $data['tags'] = $tags;

        if ($components)
        {
            $data['components'] = $components;
        }

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

    /**
     * @param ReflectionMethod $method
     * @return array<array>
     */
    private function getParameters(ReflectionMethod $method) : array
    {
        $parameterAttributes = $method->getAttributes(RouteParameterAttribute::class,\ReflectionAttribute::IS_INSTANCEOF);
        $params = [];
        foreach ($parameterAttributes as $attribute){

            /**
             * @var RouteParameterAttribute $routeParamAttribute
             */
            $routeParamAttribute = $attribute->newInstance();

            $params[] = $routeParamAttribute->getRouteParameter()->toArray();
        }

        return $params;
    }

    private function getRouteSecurity(ReflectionMethod $method) : array
    {
        $securityAttributes = $method->getAttributes(Security::class);
        $security = [];

        foreach ($securityAttributes as $securityAttribute){

            /**
             * @var Security $securityObject
             */
            $securityObject = $securityAttribute->newInstance();
            $security[] = $securityObject->toArray();
        }

        return $security;
    }

    private function getSecuritySchemes() : array
    {
        $schemes = [];

        foreach (config('docs.security_schemes') as $scheme){
            if (! $scheme instanceof SecurityScheme ){
                throw new Exception('Security schemes must be of type ' . SecuritySchemeType::class);
            }

            $schemes[$scheme->name] = $scheme->toArray();
        }

        return $schemes;
    }
}
