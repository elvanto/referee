<?php

namespace Referee\Tokenizer;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWithArray()
    {
        $token = new Token([358, 'class', 1]);

        $this->assertEquals(T_CLASS, $token->getType());
        $this->assertEquals('class', $token->getText());
        $this->assertEquals(1, $token->getLine());
    }

    public function testCreateWithString()
    {
        $token = new Token('?');

        $this->assertEquals('?', $token->getType());
        $this->assertEquals('?', $token->getText());
        $this->assertNull($token->getLine());
    }

    public function testToString()
    {
        $token = new Token([358, 'class', 1]);

        $this->assertEquals('class', (string) $token);
    }
}
