<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\attributes\ResponseJson;
use Mantasruigys3000\SimpleSwagger\attributes\ResponseResource;
use Mantasruigys3000\SimpleSwagger\helpers\ReferenceHelper;
use Mantasruigys3000\SimpleSwagger\helpers\RouteHelper;
use Mantasruigys3000\SimpleSwagger\parser\ArrayKeyResolver;
use Mantasruigys3000\SimpleSwagger\parser\ResourceKeyParser;
use Mantasruigys3000\SimpleSwagger\traits\HasRequestBodies;
use PhpParser\ConstExprEvaluator;
use PhpParser\NodeDumper;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PHPUnit\Event\Code\ClassMethod;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use PhpParser\Node;
use ReflectionMethod;

class ParseAll extends Command
{
    protected $signature = 'swag:parse-all';

    /**
     * @throws ReflectionException
     */
    public function handle()
    {
        $this->info('Parsing all files....');


        $requestClasses = [];
        $responseClasses = [];

        // Loop through registered routes
        foreach (RouteHelper::getRoutes() as $route) {
            $controllerClass = $route->getControllerClass();
            $functionName = $route->getActionMethod();

            if ($functionName === $controllerClass) {
                $functionName = '__invoke';
            }

            $method = new ReflectionMethod($controllerClass, $functionName);

            // Get request method attribute
            $methodParams = $method->getParameters();

            if (count($methodParams) > 0) {
                $requestParam = $methodParams[0]->getType()->getName();

                if (is_subclass_of($requestParam, FormRequest::class)) {

                    $traits = class_uses($requestParam);
                    if (in_array(HasRequestBodies::class, $traits)) {
                        $requestClasses[$requestParam] = 1;
                    }
                }
            }

            // Get all response classes
            $responseAttributes = $method->getAttributes(ResponseAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($responseAttributes as $responseAttribute) {


                // TODO do not want to update this every time a new case is added

                /**
                 * @var ResponseAttribute $responseObject
                 */
                $responseObject = $responseAttribute->newInstance();
                if ($responseObject instanceof ResponseResource) {
                    $responseClasses[$responseObject->resourceClass] = 1;
                }

                if ($responseObject instanceof ResponseJson) {
                    $responseClasses[$responseObject->jsonResponse] = 1;
                }
            }
        }

        dd($requestClasses,$responseClasses);
        // Compare all requests

        // Compare all responses

        // Final output
    }


}
