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

    public function handle()
    {
        $file = $this->argument('file');
        $parser = new ResourceKeyParser($file);
        $keys = $parser->getKeys();
        $documentedKeys = $parser->getDocumentedKeys();

        $missing = array_diff($keys,$documentedKeys);
        $overDocumented = array_diff($documentedKeys,$keys);

        // First show missing fields from documentation
        $missingHeader = sprintf("%s fields missing: ",count($missing));
        $overDocumentedHeader = sprintf("%s fields documented but not found in list: ",count($overDocumented));

        $this->comment($missingHeader);
        foreach ($missing as $missingKey){
            $this->comment(sprintf('     - %s',$missingKey));
        }
        $this->newLine();
        $this->comment($overDocumentedHeader);
        foreach ($overDocumented as $overDocumentedKey)
        {
            $this->comment(sprintf('     + %s',$overDocumentedKey));
        }

    }

}
