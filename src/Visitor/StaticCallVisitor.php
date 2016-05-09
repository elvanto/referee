<?php

namespace Referee\Visitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;

/**
 * Replaces functions with static method calls.
 */
class StaticCallVisitor extends NodeVisitorAbstract
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
     * @var string[]
     */
    protected $functionNames = [];

    /**
     * @var string
     */
    protected $currentNamespace;

    /**
     * @var integer
     */
    protected $replacementsCount;

    /**
     * Creates a new visitor.
     *
     * @param  string   $namespace Namespace for the class
     * @param  string   $class_name Class name
     * @param  string   $function_names Function names that should be replaced
     */
    function __construct($namespace, $class_name, $function_names)
    {
        $this->namespace = $namespace;
        $this->className = $class_name;
        $this->functionNames = $function_names;
    }

    /**
     * Returns the number of replacements made in the traversal.
     *
     * @return integer
     */
    public function getReplacementsCount()
    {
        return $this->replacementsCount;
    }

    /**
     * Returns the fully qualified class name.
     *
     * @return string
     */
    public function getQualifiedName()
    {
        return '\\' . $this->namespace . '\\' . $this->className;
    }

    /**
     * Resets the current namespace and replacements count.
     *
     * @param  Node[] $node Nodes passed to the traverser
     * @return null
     */
    public function beforeTraverse(array $node)
    {
        $this->currentNamespace = '';
        $this->replacementsCount = 0;
    }

    /**
     * Replaces any functions in the list with a static method
     * call.
     *
     * @param  Node $node Current node in traversal
     * @return Node
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->currentNamespace = new Node\Name($node->name);
        }

        if ($node instanceof Expr\FuncCall) {
            if (in_array($node->name, $this->functionNames)) {
                if (
                    $this->currentNamespace instanceof Node\Name &&
                    $this->currentNamespace->toString() == $this->namespace
                ) {
                    $class = new Node\Name('self');
                } else {
                    $class = new Node\Name($this->getQualifiedName());
                }

                $this->replacementsCount++;

                return new Expr\StaticCall($class, $node->name, $node->args);
            }
        }
    }
}
