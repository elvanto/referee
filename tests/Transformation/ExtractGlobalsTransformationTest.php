<?php

namespace Referee\Transformation;

class ExtractGlobalsTransformationTest extends \PHPUnit_Framework_TestCase
{
	public function testTransformation()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/StaticClass.php');
        $expect = file_get_contents(__DIR__ . '/Fixtures/StaticClassExpected.php');

        $transformation = new ExtractGlobalsTransformation();

        $output = $transformation->transform($source);
        $this->assertEquals(trim($expect), trim($output));
        $this->assertEquals('Functions', $transformation->getClassName());
        $this->assertEquals(
            array('func_a', 'func_b', 'func_c'),
            $transformation->getMethodNames()
        );
        $this->assertEquals(
            array('$db', '$user'),
            $transformation->getGlobalNames()
        );
	}
}
