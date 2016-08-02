<?php

namespace Referee\Visitor;

use Referee\Tokenizer\Tokenizer;

class ClassDependencyVisitorTest extends \PHPUnit_Framework_TestCase
{
	public function testTransformation()
	{
        $source = file_get_contents(__DIR__ . '/Fixtures/ClassA.php');
        $visitor = new ClassDependencyVisitor;

        $tokenizer = new Tokenizer($source);
        $visitor->visit($tokenizer);
	}
}
