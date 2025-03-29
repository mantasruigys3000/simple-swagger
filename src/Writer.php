<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RouteData;
use Illuminate\Support\Str;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\RouteParameterAttribute;
use Mantasruigys3000\SimpleSwagger\attributes\PathParameter;
use Mantasruigys3000\SimpleSwagger\attributes\ResponseResource;
use Mantasruigys3000\SimpleSwagger\attributes\RouteDescription;
use Mantasruigys3000\SimpleSwagger\attributes\RouteTag;
use Mantasruigys3000\SimpleSwagger\attributes\Security;
use Mantasruigys3000\SimpleSwagger\data\ResponseBody;
use Mantasruigys3000\SimpleSwagger\data\SecurityScheme;
use Mantasruigys3000\SimpleSwagger\enums\SecuritySchemeType;
use ReflectionAttribute;
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

        // This is a list of resource class names that are added that need to be constructed into response body schemes and examples
        // We keep a list of the class names referenced to avoid duplicates when constructing the schemes
        $responseComponentClasses = [];

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

            // Construct the responses. we need to make sure to use incline defined responses along with re-usable components
            $responses = $this->getRouteResponses($routeReflection,$responseComponentClasses);

            $pathObj = [
                'tags' => $routeTags,
                'summary' => $descriptionObject?->summary ?? '',
                'description' => $descriptionObject->description ?? '',
//                'requestBody' => [],
                'responses' => $responses,
                'parameters' => $this->getParameters($routeReflection),
                'security' => $this->getRouteSecurity($routeReflection),
            ];

            $data['paths'][$pathUri][strtolower($route->methods()[0])] = $pathObj;

            // Append route tags to all known tags
        }

        // Append Generated Data
        $data['tags'] = $tags;

        // Generate schema and examples from response classes

        if ($components)
        {
            $data['components'] = $components;
        }

        $data['components']['schemas'] = $this->getResponseSchemas(array_keys($responseComponentClasses));


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
        $parameterAttributes = $method->getAttributes(RouteParameterAttribute::class,ReflectionAttribute::IS_INSTANCEOF);
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

    /**
     * Get inline responses and references
     * This function does not construct response component schemes
     *
     * @return array
     */
    private function getRouteResponses(ReflectionMethod $method, array &$responseClasses) : array
    {
        $responseAttributes = $method->getAttributes(ResponseAttribute::class,ReflectionAttribute::IS_INSTANCEOF);
        $responses = [];
        foreach ($responseAttributes as $responseAttribute)
        {
            /**
             * @var ResponseAttribute $responseObject
             */

            $responseObject = $responseAttribute->newInstance();
            $responses[$responseObject->getStatus()] = $responseObject->toArray();

            if ($responseObject instanceof ResponseResource){
                // TODO: validate the resource class implements the trait or at least implements the function

                // Using the class names as the key forces each entry to be unique, the value associated with it is not used
                $responseClasses[$responseObject->resourceClass] = 1;
            }
        }

        return $responses;
    }

    private function getResponseSchemas(array $responseClasses)
    {
        $schemas = [];

        foreach ($responseClasses as $responseClass)
        {
            /**
             * @var ResponseBody[] $responseBodies;
             */
            $responseBodies = $responseClass::responseBodies();

            foreach ($responseBodies as $responseBody){

                // we need to construct a unique id using both resource class and response title
                $id = $responseBody->title. $responseClass;
                $id = Str::of($id)->snake()->replace('\\','')->replace('/','')->lower()->toString();

                $schemas[$id] = [
                    'title' => $responseBody->title,
                    'properties' => $responseBody->schemaFactory->getPropertiesArray(),
                    'required' => $responseBody->schemaFactory->getRequired(),
                ];

            }
        }

        return $schemas;
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
