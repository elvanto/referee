<?php

namespace Referee\Tokenizer;

/**
 * Tokenizer
 *
 * Extracts tokens from source code and provides interfaces for
 * sequence querying and iteration.
 */
class Tokenizer extends TokenIterator
{
    /**
     * Creates a new tokenizer from a source string.
     *
     * @param string $source
     */
    function __construct($source)
    {
        $tokens = [];

        foreach (token_get_all($source) as $token) {
            $tokens[] = new Token($token);
        }

        parent::__construct($tokens);
    }

    /**
     * Returns the source representation of the token
     * list.
     *
     * @return string
     */
    public function __toString()
    {
        $source = '';

        foreach ($this as $token) {
            $source .= $token;
        }

        return $source;
    }
}
