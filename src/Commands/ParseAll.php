<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Mantasruigys3000\SimpleSwagger\attributes\interfaces\ResponseAttribute;
use Mantasruigys3000\SimpleSwagger\attributes\ResponseJson;
use Mantasruigys3000\SimpleSwagger\attributes\ResponseResource;
use Mantasruigys3000\SimpleSwagger\helpers\ReferenceHelper;
use Mantasruigys3000\SimpleSwagger\helpers\ReflectionHelper;
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

            $method = new ReflectionMethod($controllerClass, $functionName);

            $requestClasses = array_merge(ReflectionHelper::getRequestClasses($method),$requestClasses);
            $responseClasses = array_merge(ReflectionHelper::getResourceClasses($method),$responseClasses);
        }

        $allClasses = array_merge(array_keys($requestClasses),array_keys($responseClasses));

        foreach ($allClasses as $index => $class)
        {
            $this->parse($index,$class);
        }

        while ($this->option('interactive'))
        {
            $class = $this->ask('inspect class?');

            if (is_null($class))
            {
                return;
            }

            $class = (int) $class;

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
