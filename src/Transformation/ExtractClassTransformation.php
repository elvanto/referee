<?php

namespace Referee\Transformation;

use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\Node\Stmt;

/**
 * Converts a series of statements defining functions into a class
 * with equivalent static methods.
 */
class ExtractClassTransformation implements TransformationInterface
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $className;

    /**
     * string[]
     */
    protected $functionNames = [];

    /**
     * @var BuilderFactory
     */
    protected $builder;

    function __construct($namespace, $class_name)
    {
        $this->namespace = $namespace;
        $this->className = $class_name;
        $this->builder = new BuilderFactory();
    }

    /**
     * Returns the names of functions that have been converted
     * to static methods.
     *
     * @return integer
     */
    public function getFunctionNames()
    {
        return $this->functionNames;
    }

    /**
     * Applies the transformation to an array of statements
     * and returns the result.
     *
     * @param  Stmt[] $stmts
     * @return Stmt[]
     */
    public function transform($stmts)
    {
        $node = $this->builder->namespace($this->namespace);
        $class = $this->builder->class($this->className);

        /**
         * Collect all functions and add them as static methods
         * on the new class.
         */
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Stmt\Function_) {
                $this->functionNames[] = $stmt->name;
                $class->addStmt($this->staticMethod($stmt));
            }
        }

        $node->addStmt($class);
        return array($node->getNode());
    }

    /**
     * Returns a static method using a standard
     * function.
     *
     * @param  Stmt\Function_ $function
     * @return Stmt\ClassMethod
     */
    protected function staticMethod(Stmt\Function_ $function)
    {
        $method = $this->builder->method($function->name)
            ->makePublic()
            ->makeStatic();

        if ($function->byRef)
            $method->makeReturnByRef();

        foreach ($function->params as $param)
            $method->addParam($param);

        foreach ($function->stmts as $stmt)
            $method->addStmt($stmt);

        
        /**
         * Add the original comments to the method.
         *
         * If a DocComment is present it is given preference over
         * standard comments.
         */
        $doc = $function->getDocComment();
        $comments = $function->getAttribute('comments');

        if (!is_null($doc)) {
            $method->setDocComment($doc);
        } else if (!empty($comments)) {
            $comments = array_map(function ($c) {
                return $c->getText();
            }, $comments);
            $method->setDocComment(implode(PHP_EOL, $comments));
        }

        return $method;
    }
}
