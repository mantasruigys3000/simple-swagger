<?php

declare(strict_types=1);

namespace Mantasruigys3000\SimpleSwagger\commands;

use Illuminate\Console\Command;
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
class ParseFile extends Command
{
    protected $signature = 'swag:parse {file}';

    /**
     * @throws ReflectionException
     */
    public function handle()
    {
        $file = $this->argument('file');
        $keys = (new ResourceKeyParser($file))->getKeys();
        dd($keys);


//        $dumper = new NodeDumper;
//        echo $dumper->dump($ast) . "\n";

    }

}
