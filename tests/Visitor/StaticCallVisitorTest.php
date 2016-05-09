<?php

namespace Referee\Visitor;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;

class ExtractClassTransformationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
        $this->traverser = new NodeTraverser;
        $this->printer = new PrettyPrinter\Standard();
    }

	public function testVisitor()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/Usage.php');
        $expect = file_get_contents(__DIR__ . '/Fixtures/UsageExpected.php');

        $statements = $this->parser->parse($source);
        $visitor = new StaticCallVisitor('Foo', 'Functions', array('func_a'));
        $this->traverser->addVisitor($visitor);

        $output = $this->traverser->traverse($statements);
        $this->assertEquals(trim($expect), $this->printer->prettyPrintFile($output));
        $this->assertEquals(1, $visitor->getReplacementsCount());
	}
}
