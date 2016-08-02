<?php

use Legacy\UpdatedClass;

class AnotherClass
{
    public static function doSomething()
    {
        $updated_class = new UpdatedClass();
        $updated_class->func_a();
    }
}

$updated_class = new UpdatedClass();
$updated_class->func_b();
