<?php

namespace Referee\Transformation;

interface TransformationInterface
{
    /**
     * Applies the transformation to a source file and returns the result.
     *
     * @param  string $source Source file contents
     * @return string
     */
    public function transform($source);
}
