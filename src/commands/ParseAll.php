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
use function Laravel\Prompts\select;

class ParseAll extends Command
{
    protected $signature = 'swag:parse-all {--I|interactive}';

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

            /*
             * TODO: extract all of this into methods tha can be used here and in the writer
             */

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

        $allClasses = array_merge(array_keys($requestClasses),array_keys($responseClasses));

        foreach ($allClasses as $index => $class)
        {
            $this->parse($index,$class);
        }

        while ($this->option('interactive'))
        {
            $class = (int) $this->ask('inspect class?');

            if ($class === '')
            {
                return;
            }

            dd($class);

            $this->call('swag:parse',[
                'file' => $allClasses[$class]
            ]);

        }

    }

    /**
     * Handle parsing and output for a single class
     *
     * @param string $class
     * @return void
     */
    private function parse(int $index, string $class)
    {

        $info = (new ResourceKeyParser($class))->parse();

        $text = sprintf('%s: %s has %s missing keys and %s over documented keys',$index,addslashes($class),count($info->missingKeys),count($info->overDocumentedKeys));
        $errors = count($info->missingKeys) + count($info->overDocumentedKeys);

        if ($errors > 0)
        {
            $this->warn($text);
        }
        else{
            $this->info($text);
        }

    }


}
