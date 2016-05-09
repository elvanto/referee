<?php

namespace Referee\Transformation;

use PhpParser\Node\Stmt;

interface TransformationInterface
{
    /**
     * Applies the transformation to an array of statements
     * and returns the result.
     *
     * @param  Stmt[] $stmts
     * @return Stmt[]
     */
    public function transform($stmts);
}
