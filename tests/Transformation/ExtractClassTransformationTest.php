<?php

namespace Referee\Transformation;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

class ExtractClassTransformationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
        $this->printer = new PrettyPrinter\Standard();
    }

	public function testTransformation()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/Functions.php');
        $expect = file_get_contents(__DIR__ . '/Fixtures/FunctionsExpected.php');

        $input = $this->parser->parse($source);
        $transformation = new ExtractClassTransformation('Legacy', 'StaticClass');

        $output = $transformation->transform($input);
        $this->assertEquals(trim($expect), $this->printer->prettyPrintFile($output));
        $this->assertEquals(array('func_a', 'func_b'), $transformation->getFunctionNames());
	}
}
