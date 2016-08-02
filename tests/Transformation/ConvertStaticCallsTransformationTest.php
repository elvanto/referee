<?php

namespace Referee\Transformation;

class ConvertStaticCallsTransformationTest extends \PHPUnit_Framework_TestCase
{
	public function testTransformation()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/StaticCalls.php');
        $expect = file_get_contents(__DIR__ . '/Fixtures/StaticCallsExpected.php');

        $transformation = new ConvertStaticCallsTransformation();
        $transformation->setClassName('Legacy\UpdatedClass');
        $transformation->setMethodNames(['func_a', 'func_b']);

        $output = $transformation->transform($source);
        $this->assertEquals(trim($expect), trim($output));
	}
}
