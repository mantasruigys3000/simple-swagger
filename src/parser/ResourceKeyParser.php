<?php

namespace Mantasruigys3000\SimpleSwagger\parser;

use Mantasruigys3000\SimpleSwagger\data\RequestBody;
use Mantasruigys3000\SimpleSwagger\data\SchemaFactory;
use Mantasruigys3000\SimpleSwagger\helpers\ClassHelper;
use Mantasruigys3000\SimpleSwagger\traits\HasRequestBodies;
use Mantasruigys3000\SimpleSwagger\traits\HasResponseBodies;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use ReflectionClass;
use PhpParser\Node;

class ResourceKeyParser
{
    protected NodeFinder $finder;

    protected string $functionSignature = '';

    public function __construct(protected string $resourceClass)
    {
        $this->finder = new NodeFinder();

        // We need to assign a function signature based on class type
        // If the class provided is a resource, then we look in the toArray method
        if (ClassHelper::uses($this->resourceClass,HasResponseBodies::class)){
            $this->functionSignature = 'toArray';
        }

        // We should look in the rules method for request bodies
        if (ClassHelper::uses($this->resourceClass,HasRequestBodies::class)){
            $this->functionSignature = 'rules';
        }
    }

    public function getKeys() : array
    {
        $keys = [];

        $reflectionClass = new ReflectionClass($this->resourceClass);
        $filepath = $reflectionClass->getFileName();
        $content = file_get_contents($filepath);
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($content);

        // Here we need to figure out if we need to parse an array ast directly or spit out a variable

        // Get return expression of the function
        $finder = new NodeFinder();
        $classMethod = $finder->findFirst($ast,function(Node $node) use ($finder){
            if ( $node instanceof Node\Stmt\ClassMethod && $node->name->toString() === $this->functionSignature)
            {
                return true;
            }
        });

        $returnExpression = $finder->find($classMethod,fn(Node $node) => $node instanceof Node\Stmt\Return_);

        foreach ($returnExpression as $node){
            $keys = array_merge($keys,$this->getKeysFromExpression($node->expr,$classMethod));
        }

        return $keys;
    }

    /**
     * Get keys that are already documented in this resource
     *
     * @return array
     */
    public function getDocumentedKeys() : array
    {
        if(! ClassHelper::uses($this->resourceClass,HasResponseBodies::class)){
            return [];
        }

        // Get bodies
        /**
         * @var RequestBody[] $bodies
         */
        $bodies = $this->resourceClass::responseBodies();

        $keys = [];

        foreach ($bodies as $body){
            $keys = array_merge($keys,$body->schemaFactory->getDocumentedKeys());
        }

        return $keys;
    }

    /**
     * Get keys from expression
     *
     * @param Node\Expr $expression
     * @return void
     */
    private function getKeysFromExpression(Node\Expr $expression,$functionAst) : array
    {
        return match ($expression::class)
        {
            Node\Expr\Array_::class => $this->getKeysFromArrayExpression($expression),
            Node\Expr\Variable::class => $this->getKeysFromVariableExpression($expression->name,$functionAst),
        };
    }

    private function getKeysFromArrayExpression($ast) : array
    {
        $keys = [];

        $arrayItems = $this->finder->findInstanceOf($ast,Node\ArrayItem::class);
        foreach ($arrayItems as $arrayItem){
            if (isset($arrayItem->key)){
                $keys[] = $arrayItem->key->value;
            }
        }

        return $keys;
    }

    private function getKeysFromVariableExpression(string $variableName,$ast) : array {
        $keys = [];

        $nodeFinder = new NodeFinder();
        $assignments = $nodeFinder->find($ast,function(Node $node) use ($variableName){
            if ($node instanceof Node\Expr\Assign){
                $var = $node->var;
                return match ($var::class)
                {
                    Node\Expr\Variable::class => $var->name === $variableName,
                    Node\Expr\ArrayDimFetch::class => $var->var->name === $variableName,
                };
            }
        });

        /**
         * @var Node\Expr\Assign[] $assignments
         */
        foreach ($assignments as $assignment)
        {
            /**
             * Individual key assignment eg $array['stuff']
             */
            if ($assignment->var instanceof Node\Expr\ArrayDimFetch){
                $keys[] = $assignment->var->dim->value;
                continue;
            }

            // For now ignore function calls
            if ($assignment->expr instanceof Node\Expr\FuncCall)
            {
                continue;
            }

            /**
             * Mass array item assignment
             * $array = [
             *  'stuff one',
             *  'stuff two',
             * ]
             */
            foreach ($assignment->expr->items as $attribute){
                $keys[] = $attribute->key->value;
            }

        }

        return $keys;

    }
}