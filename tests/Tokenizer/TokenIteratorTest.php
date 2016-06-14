<?php

namespace Referee\Tokenizer;

class TokenIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testSequenceQuery()
    {
        $variables = (new TokenQuery)
            ->expect(',')
            ->expect(T_VARIABLE);

        $globals = (new TokenQuery)
            ->expect(T_GLOBAL)
            ->expect(T_VARIABLE)
            ->any($variables)
            ->expect(';');

        $tokenizer = new Tokenizer('
            <?php
            $msg = "Hello, world!";
            global $db, $user, $account;
            global $is_admin;
        ');

        $declarations = $tokenizer->query($globals);
        $this->assertCount(2, $declarations);
    }

    public function testMatchingQuery()
    {
        $functions = (new TokenQuery)
            ->expect(T_FUNCTION)
            ->expect(T_STRING)
            ->expect('(')
            ->expect(')')
            ->matching('{', '}');

        $tokenizer = new Tokenizer('
            <?php
            function func_a() {
                $msg = "Hello, world!";
                return $msg;
            }
        ');

        $declarations = $tokenizer->query($functions);
        $this->assertCount(1, $declarations);
    }
}
