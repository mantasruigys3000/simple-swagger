<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\commands;

use Illuminate\Console\Command;
use Mantasruigys3000\SimpleSwagger\helpers\RouteHelper;
use Mantasruigys3000\SimpleSwagger\parser\ArrayKeyResolver;
use Mantasruigys3000\SimpleSwagger\parser\ResourceKeyParser;
use PhpParser\ConstExprEvaluator;
use PhpParser\NodeDumper;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PHPUnit\Event\Code\ClassMethod;
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

        // Loop through registered routes
        foreach (RouteHelper::getRoutes() as $route)
        {
            $controllerClass = $route->getControllerClass();
            $functionName = $route->getActionMethod();

            if ($functionName === $controllerClass) {
                $functionName = '__invoke';
            }

            $method = new ReflectionMethod($controllerClass,$functionName);
            dump($method->name);

        }
        // Compare all requests

        // Compare all responses

        // Final output
    }

}
