<?php

// The first function
function func_a($a, $b)
{
    // Print all arguments
    echo $a, $b;
}

/**
 * Second function
 * @version 1.0.0
 */
function func_b($foo, $bar)
{
    func_a($foo, $bar);
}
