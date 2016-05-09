<?php

namespace Referee\Printer;

use PhpParser\PrettyPrinter;
use PhpParser\Node;

/**
 * Improved pretty printer that attempts to retain
 * some formatting from the original source.
 */
class ImprovedPrinter extends PrettyPrinter\Standard
{
    /**
     * The number of functions or class methods encountered
     * @var integer
     */
    protected $functionsCount = 0;

    /**
     * Last encountered statement
     * @var Node
     */
    protected $lastStatement = null;

    /**
     * Returns true if node is a function or class method.
     *
     * @param  Node $node
     * @return boolean
     */
    protected static function isFunction(Node $node)
    {
        return (
            $node instanceof Node\Stmt\Function_ ||
            $node instanceof Node\Stmt\ClassMethod
        );
    }

    /**
     * Pretty prints an array of nodes (statements) and indents them optionally.
     *
     * @param Node[] $nodes  Array of nodes
     * @param bool   $indent Whether to indent the printed nodes
     *
     * @return string Pretty printed statements
     */
    protected function pStmts(array $nodes, $indent = true) {
        $result = '';
        foreach ($nodes as $node) {
            if (self::isFunction($node)) {
                /* Add lines between function and method definitions */
                if ($this->functionsCount > 0) {
                    $result .= "\n";
                }

                $this->lastStatement = null;
            } else {
                if ($this->lastStatement) {
                    $lastLine = $this->lastStatement->getAttribute('endLine');
                    $nextLine = $node->getAttribute('startLine');

                    $diff = $nextLine - $lastLine;

                    /* Add at most one blank line to match original spacing */
                    for ($i = 1; $i < $diff && $i < 2; $i++) {
                        $result .= "\n";
                    }
                }
                $this->lastStatement = $node;
            }

            $comments = $node->getAttribute('comments', array());
            if ($comments) {
                $result .= "\n" . $this->pComments($comments);
                if ($node instanceof Stmt\Nop) {
                    continue;
                }
            }

            $result .= "\n" . $this->p($node) . ($node instanceof Node\Expr ? ';' : '');

        }
        if ($indent) {
            return preg_replace('~\n(?!$|' . $this->noIndentToken . ')~', "\n    ", $result);
        } else {
            return $result;
        }
    }

    /**
     * Pretty prints a node.
     *
     * @param Node $node Node to be pretty printed
     *
     * @return string Pretty printed node
     */
    protected function p(Node $node)
    {
        if (self::isFunction($node))
            $this->functionsCount++;

        return parent::p($node);
    }

    /**
     * Pretty prints an array of nodes on separate lines and implodes
     * the printed values with commas.
     *
     * @param Node[] $nodes Array of Nodes to be printed
     * @param bool   $indent Whether to indent the printed nodes
     *
     * @return string Comma separated pretty printed nodes
     */
    protected function pCommaSeparatedNewline(array $nodes, $indent = true) {
        $result = (empty($nodes) ? '' : "\n") . $this->pImplode($nodes, ",\n");

        if ($indent = true) {
            return preg_replace('~\n(?!$|' . $this->noIndentToken . ')~', "\n    ", $result);
        } else {
            return $result;
        }
    }

    public function pExpr_Array(Node\Expr\Array_ $node) {
        $syntax = $node->getAttribute('kind',
            $this->options['shortArraySyntax'] ? Node\Expr\Array_::KIND_SHORT : Node\Expr\Array_::KIND_LONG);
        if ($syntax === Node\Expr\Array_::KIND_SHORT) {
            return '[' . $this->pCommaSeparatedNewline($node->items) . ']';
        } else {
            return "array(" .  $this->pCommaSeparatedNewline($node->items) . (empty($node->items) ? ')' : "\n)");
        }
    }

    public function pStmt_InlineHTML(Node\Stmt\InlineHTML $node) {
        return '?>' . $this->pNoIndent("" . $node->value) . '<?php ';
    }
}
