<?php

namespace Referee\Tokenizer;

/**
 * Tokenizer
 *
 * Extracts tokens from source code and provides interfaces for
 * sequence querying and iteration.
 */
class Tokenizer extends \ArrayIterator
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
     * Returns the first sequence of tokens satisfying the provided
     * token query. Null is returned if a match could not be found.
     *
     * @param  TokenQuery    $query
     * @param  ArrayIterator $tokens
     * @return array
     */
    protected static function match(TokenQuery $query, \ArrayIterator $tokens)
    {
        $sequence = [];
        $query->start();

        $original_offset = $tokens->key();

        while ($tokens->valid()) {
            $token = $tokens->current();

            if ($query->isComplete()) {
                break;
            }

            /* Rules are not applied to whitespace */
            if ($token->getType() == T_WHITESPACE) {
                if (!empty($sequence)) {
                    $sequence[] = $token;
                }

                $tokens->next();
                continue;
            }

            $rule = $query->next();

            if (isset($rule['type']) && $rule['type'] != $token->getType()) {
                if (isset($rule['required']) && $rule['required']) {
                    $query->start();
                    $sequence = [];
                    $tokens->next();
                }

                continue;
            }

            if (isset($rule['query'])) {
                /**
                 * If a sub-query does not yield matches and is not required
                 * to do so, the next rule will be checked against the
                 * same token.
                 */
                $offset = $tokens->key();

                $match_count = 0;
                while ($matches = self::match($rule['query'], $tokens)) {
                    $match_count++;
                    $sequence = array_merge($sequence, $matches);
                }

                if ($match_count == 0) {
                    if (isset($rule['required']) && $rule['required']) {
                        $query->start();
                        $sequence = [];
                        $tokens->next();
                        continue;
                    } else {
                        $tokens->seek($offset);
                        continue;
                    }
                }

                continue;
            }

            $sequence[] = $token;
            $tokens->next();
        }

        if (empty($sequence)) {
            $tokens->seek($original_offset);
        }

        return $sequence;
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

    /**
     * Returns an array of token iterators containing sequences
     * that satisfy the provided token query.
     *
     * @param  TokenQuery $query
     * @return ArrayIterator[]
     */
    public function query(TokenQuery $query)
    {
        $sequences = [];
        $this->rewind();

        while ($tokens = self::match($query, $this)) {
            $sequences[] = new \ArrayIterator($tokens);
        }

        return $sequences;
    }
}
