<?php

namespace Referee\Tokenizer;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsIterator()
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
}
