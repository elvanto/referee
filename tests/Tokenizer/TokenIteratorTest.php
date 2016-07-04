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

    public function testMatchWithText()
    {
        $call_query = (new TokenQuery)
            ->expect(T_STRING)
            ->expect(T_DOUBLE_COLON)
            ->expect(T_STRING, 'func_b');

        $tokenizer = new Tokenizer('
            <?php
            Functions::func_a();
            Functions::func_b();
        ');

        $tokens = $tokenizer->query($call_query);

        $this->assertCount(1, $tokens);
        $this->assertEquals('func_b', $tokens[0][2]->getText());
    }
}
