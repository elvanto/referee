<?php

namespace Legacy;

class StaticClass
{
    // The first function
    public static function func_a($a, $b)
    {
        // Print all arguments
        echo $a, $b;
    }
    /**
     * Second function
     * @version 1.0.0
     */
    public static function func_b($foo, $bar)
    {
        func_a($foo, $bar);
    }
}
