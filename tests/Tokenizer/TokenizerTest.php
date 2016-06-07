<?php

namespace Referee\Tokenizer;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIterator()
    {
        $tokenizer = new Tokenizer('<?php echo "Hello, world!";');

        foreach ($tokenizer as $token) {
            $this->assertInstanceOf(Token::class, $token);
        }
    }

    public function testToString()
    {
        $tokenizer = new Tokenizer('<?php echo time();');

        foreach ($tokenizer as $token) {
            if ($token->getType() == T_STRING && $token->getText() == 'time') {
                $token->setText('rand');
            }
        }

        $this->assertEquals('<?php echo rand();', (string) $tokenizer);
    }

    public function testQuery()
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
            global $db, $user;
            global $is_admin;
        ');

        $declarations = $tokenizer->query($globals);
        $this->assertCount(2, $declarations);
    }
}
