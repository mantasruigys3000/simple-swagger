<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger;

use Closure;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RouteData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Mantasruigys3000\SimpleSwagger\Attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\Attributes\interfaces\RouteParameterAttribute;
use Mantasruigys3000\SimpleSwagger\Attributes\ResponseJson;
use Mantasruigys3000\SimpleSwagger\Attributes\ResponseResource;
use Mantasruigys3000\SimpleSwagger\Attributes\RouteDescription;
use Mantasruigys3000\SimpleSwagger\Attributes\RouteTag;
use Mantasruigys3000\SimpleSwagger\Attributes\Security;
use Mantasruigys3000\SimpleSwagger\Data\RequestContent;
use Mantasruigys3000\SimpleSwagger\Data\ResponseBody;
use Mantasruigys3000\SimpleSwagger\Data\SecurityScheme;
use Mantasruigys3000\SimpleSwagger\Enums\SecuritySchemeType;
use Mantasruigys3000\SimpleSwagger\Helpers\ClassHelper;
use Mantasruigys3000\SimpleSwagger\Helpers\ReferenceHelper;
use Mantasruigys3000\SimpleSwagger\Helpers\RouteHelper;
use Mantasruigys3000\SimpleSwagger\Interfaces\JsonResponse;
use Mantasruigys3000\SimpleSwagger\Traits\HasRequestBodies;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Yaml\Yaml;

class Writer
{
    public Closure $errorCallback;

    public Closure $infoCallback;

    public function __construct() {}

    public function write(): string
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

        // We need another list of response classes that have a collection variant as we need to construct an example as an array type
        // This is inefficient duplication however there is no way to reference examples within examples, so it needs to be re build
        $responseComponentCollectionClasses = [];

        // same for request body schemas
        $requestBodyComponentClasses = [];

        foreach ($routes as $route) {
            $this->info(sprintf('Building route %s:/%s', $route->methods()[0], $route->uri()));

            $controllerClass = $route->getControllerClass();
            $functionName = $route->getActionMethod();
            $routeTags = [];

            $reflection = new ReflectionClass($controllerClass);
            $routeTagAttributes = $reflection->getAttributes(RouteTag::class);

            if (empty($routeTagAttributes)) {
                $this->error(sprintf('class %s missing RouteTag element', $controllerClass));
            }

            foreach ($routeTagAttributes as $attribute) {
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
            if ($functionName === $controllerClass) {
                $functionName = '__invoke';
            }

            $routeReflection = new ReflectionMethod($controllerClass, $functionName);

            /**
             *  Only need the first instance, no need to support multiple descriptions
             *
             * @var ?RouteDescription $descriptionObject
             */
            $descriptionAttributes = $routeReflection->getAttributes(RouteDescription::class);
            $descriptionObject = count($descriptionAttributes) === 0 ? null : $descriptionAttributes[0]->newInstance();

            $operationId = sprintf('%s@%s', $controllerClass, $functionName);
            $operationId = Str::of($operationId)->afterLast('\\')->toString();

            if (is_null($descriptionObject)) {
                $this->error(sprintf('%s @%s has no RouteDescription', $controllerClass, $functionName));
            }

            // Construct path object for this route
            // $data['paths']['uri']['operation']
            $pathUri = '/'.$route->uri;

            // Construct the responses. we need to make sure to use incline defined responses along with re-usable components
            $responses = $this->getRouteResponses($routeReflection, $responseComponentClasses, $responseComponentCollectionClasses);

            // Construct the request bodies
            $requestBody = $this->getRouteRequests($routeReflection, $requestBodyComponentClasses);

            $pathObj = [
                'tags' => $routeTags,
                'summary' => $descriptionObject?->summary ?? '',
                'description' => $descriptionObject->description ?? '',
                'parameters' => $this->getParameters($routeReflection),
                'security' => $this->getRouteSecurity($routeReflection),
                'operationId' => $operationId,
            ];

            if ($requestBody) {
                $pathObj['requestBody'] = $requestBody;
            }

            if ($responses) {
                $pathObj['responses'] = $responses;
            }

            $data['paths'][$pathUri][strtolower($route->methods()[0])] = $pathObj;

            // Append route tags to all known tags
        }

        // Append Generated Data
        $data['tags'] = $tags;

        // Generate schema and examples from response classes

        if ($components) {
            $data['components'] = $components;
        }

        /**
         * Before using all the resource classes, we also need to check the current ones for references to resource classes that have not been registered
         * For example, a response may use $data->resource, and will try reference the components from that resource, however that resource may not be in any
         * #ResponseResource attributes
         */
        $this->getNestedResourceClasses($responseComponentClasses);

        $responseSchemas = $this->getResponseSchemas(array_keys($responseComponentClasses), array_keys($responseComponentCollectionClasses));
        $requestSchemas = $this->getRequestSchemas(array_keys($requestBodyComponentClasses));

        $data['components']['schemas'] = [
            ...$responseSchemas['schemas'],
            ...$requestSchemas['schemas'],
        ];

        $data['components']['examples'] = [
            ...$responseSchemas['examples'],
            ...$requestSchemas['examples'],
        ];

        // Turn to yaml
        $yaml = Yaml::dump($data, flags: Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_OBJECT_AS_MAP, inline: 4);

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
    private function getRoutes(): array
    {
        return RouteHelper::getRoutes();
    }

    /**
     * @return array<array>
     */
    private function getParameters(ReflectionMethod $method): array
    {
        $parameterAttributes = $method->getAttributes(RouteParameterAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        $params = [];
        foreach ($parameterAttributes as $attribute) {

            /**
             * @var RouteParameterAttribute $routeParamAttribute
             */
            $routeParamAttribute = $attribute->newInstance();

            $params[] = $routeParamAttribute->getRouteParameter()->toArray();
        }

        return $params;
    }

    private function getRouteSecurity(ReflectionMethod $method): array
    {
        $securityAttributes = $method->getAttributes(Security::class);
        $security = [];

        foreach ($securityAttributes as $securityAttribute) {

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
     */
    private function getRouteResponses(ReflectionMethod $method, array &$responseClasses, array &$responseCollections): array
    {
        $responseAttributes = $method->getAttributes(ResponseAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        $responses = [];
        $hasOkay = false;
        foreach ($responseAttributes as $responseAttribute) {
            /**
             * @var ResponseAttribute $responseObject
             */
            $responseObject = $responseAttribute->newInstance();
            $status = $responseObject->getStatus();

            if ($status >= 200 && $status <= 399) {
                $hasOkay = true;
            }

            $responses[$status] = $responseObject->toArray();

            if ($responseObject instanceof ResponseResource) {
                // TODO: validate the resource class implements the trait or at least implements the function

                // Using the class names as the key forces each entry to be unique, the value associated with it is not used
                $responseClasses[$responseObject->resourceClass] = 1;

                if ($responseObject->collection) {
                    $responseCollections[$responseObject->resourceClass] = 1;
                }
            }

            if ($responseObject instanceof ResponseJson) {
                $responseClasses[$responseObject->jsonResponse] = 1;
            }
        }

        // If no routes contain a 2** status then throw an error
        if (! $hasOkay) {
            $this->error(sprintf('%s @%s does not document a successful or redirect response', $method->class, $method->name));
        }

        return $responses;
    }

    /**
     * Will check current responses for resources
     * Expensive function call
     *
     * @return void
     */
    private function getNestedResourceClasses(array &$responseClasses)
    {
        foreach ($responseClasses as $class => $one) {
            $responseBodies = [];

            // TODO do a direct trait check first
            if (method_exists($class, 'responseBodies')) {
                /**
                 * @var ResponseBody[] $responseBodies;
                 */
                $responseBodies = $class::responseBodies();
            }

            if (ClassHelper::implements($class, JsonResponse::class)) {
                $responseBodies = (new $class)();
            }

            foreach ($responseBodies as $body){
                foreach ($body->schemaFactory->getNestedResourceClasses() as $nestedResourceClass){
                    $responseClasses[$nestedResourceClass] = 1;
                }
            }
        }
    }

    private function getResponseSchemas(array $responseClasses, array $collectionClasses)
    {
        $schemas = [];
        $examples = [];

        foreach ($responseClasses as $responseClass) {
            $isCollection = in_array($responseClass, $collectionClasses);
            $responseBodies = [];

            // TODO do a direct trait check first
            if (method_exists($responseClass, 'responseBodies')) {
                /**
                 * @var ResponseBody[] $responseBodies;
                 */
                $responseBodies = $responseClass::responseBodies();
            }

            if (ClassHelper::implements($responseClass, JsonResponse::class)) {
                $responseBodies = (new $responseClass)();
            }

            foreach ($responseBodies as $responseBody) {

                // we need to construct a unique id using both resource class and response title
                $id = ReferenceHelper::getResponseID($responseBody, $responseClass);
                $schemas[$id] = [
                    'title' => $responseBody->title,
                    'properties' => $responseBody->schemaFactory->getPropertiesArray(),
                    'required' => $responseBody->schemaFactory->getRequired(),
                ];

                $exampleId = ReferenceHelper::getResponseExampleID($responseBody, $responseClass);
                $examples[$exampleId] = [
                    'summary' => $responseBody->title,
                    'value' => $responseBody->schemaFactory->getExampleArray($responseClass),
                ];

                // If this resource has a collection variant, create that now
                if ($isCollection) {
                    $collectionExampleId = ReferenceHelper::getResponseExampleID($responseBody, $responseClass, true);
                    $examples[$collectionExampleId] = $examples[$exampleId];
                    $examples[$collectionExampleId]['value'] = [$examples[$collectionExampleId]['value']];
                }

            }

        }

        return [
            'schemas' => $schemas,
            'examples' => $examples,
        ];
    }

    private function getRouteRequests(ReflectionMethod $method, array &$requestClasses)
    {
        $body = [
            // 'description' => 'Request body description', // todo
        ];

        // Is the first param a request type and does it implement the trait
        $methodParams = $method->getParameters();

        if (count($methodParams) > 0) {
            $requestParam = $methodParams[0]->getType()->getName();

            if (is_subclass_of($requestParam, FormRequest::class)) {

                $traits = class_uses($requestParam);
                if (in_array(HasRequestBodies::class, $traits)) {

                    // if the array given is empty, then add nothing to the request body data, and do not pass back the class
                    $bodies = $requestParam::requestBodies();
                    if ($bodies) {
                        $requestClasses[$requestParam] = 1;
                        $body = (new RequestContent($requestParam))->toArray();
                    }
                    // the content should consist of references to request schemas
                } else {
                    $this->error(sprintf('%s does not implement HasRequestBodies', $requestParam));
                }
            }
        }

        return $body;
    }

    private function getRequestSchemas(array $requestClasses): array
    {
        $schemas = [];
        $examples = [];

        foreach ($requestClasses as $requestClass) {
            $bodies = $requestClass::requestBodies();

            foreach ($bodies as $body) {
                $id = ReferenceHelper::getRequestID($body, $requestClass);
                $schemas[$id] = [
                    'title' => $body->title,
                    'properties' => $body->schemaFactory->getPropertiesArray(),
                    'required' => $body->schemaFactory->getRequired(),
                ];

                // create example
                $exampleId = ReferenceHelper::getRequestExampleID($body, $requestClass);
                $examples[$exampleId] = [
                    'summary' => $body->title,
                    'value' => $body->schemaFactory->getExampleArray($requestClass),
                ];
            }

        }

        return [
            'schemas' => $schemas,
            'examples' => $examples,
        ];
    }

    private function getSecuritySchemes(): array
    {
        $schemes = [];

        foreach (config('docs.security_schemes') as $scheme) {
            if (! $scheme instanceof SecurityScheme) {
                throw new Exception('Security schemes must be of type '.SecuritySchemeType::class);
            }

            $schemes[$scheme->name] = $scheme->toArray();
        }

        return $schemes;
    }

    private function error(string $error)
    {
        if (! isset($this->errorCallback)) {
            return;
        }

        ($this->errorCallback)($error);
    }

    private function info(string $info)
    {
        if (! isset($this->infoCallback)) {
            return;
        }

        ($this->infoCallback)($info);
    }
}
