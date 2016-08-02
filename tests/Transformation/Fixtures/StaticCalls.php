<?php

use Legacy\UpdatedClass;

class AnotherClass
{
    public static function doSomething()
    {
        UpdatedClass::func_a();
    }
}

\Legacy\UpdatedClass::func_b();
