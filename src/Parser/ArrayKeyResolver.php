<?php

namespace Mantasruigys3000\SimpleSwagger\Parser;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

class ArrayKeyResolver
{
    protected array $keys = [];

    public function __construct(protected array $stmts,protected string $functionName,protected string $variableName)
    {

    }

    protected function buildKeys()
    {
        // Get the function tree
        $finder = new NodeFinder();
        $function = $finder->find($this->stmts,function(Node $node){
            return $node instanceof Node\Stmt\ClassMethod && $node->name->toString() === $this->functionName;
        });

        //dd($function);

        // Get all assignments where and settings to the variable name
        $assignments = $finder->find($function,function(Node $node){

            if ($node instanceof Node\Expr\Assign){
                $var = $node->var;
                return match ($var::class)
                {
                    Node\Expr\Variable::class => $var->name === $this->variableName,
                    Node\Expr\ArrayDimFetch::class => $var->var->name === $this->variableName,
                };

                return $node->var->name === $this->variableName;
            }
        });

        // Go through these assignments and get all possible keys

        /**
         * @var Node\Expr\Assign[] $assignments
         */
        foreach ($assignments as $assignment)
        {
            /**
             * Individual key assignment eg $array['stuff']
             */
            if ($assignment->var instanceof Node\Expr\ArrayDimFetch){
                $this->keys[] = $assignment->var->dim->value;
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
                $this->keys[] = $attribute->key->value;
            }

        }

    }

    public function getKeys() : array
    {
        $this->buildKeys();

        return $this->keys;
    }
}
