<?php

namespace Referee\Transformation;

class ExtractClassTransformationTest extends \PHPUnit_Framework_TestCase
{
	public function testTransformation()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/Functions.php');
        $expect = file_get_contents(__DIR__ . '/Fixtures/FunctionsExpected.php');

        $transformation = new ExtractClassTransformation(
            'Legacy',
            'StaticClass'
        );

        $output = $transformation->transform($source);
        $this->assertEquals(trim($expect), $output);
        $this->assertEquals(
            array('func_a', 'func_b'),
            $transformation->getFunctionNames()
        );
	}
}
