<?php

namespace Referee\Tokenizer;

/**
 * Token Iterator
 *
 * Manages a traversable collection of tokens and provides a query
 * interface for token sequence extraction.
 */
class TokenIterator extends \ArrayIterator
{
    /**
     * Creates a new iterator from an array of
     * tokens.
     *
     * @param Token[] $tokens
     */
    function __construct(array $tokens)
    {
        parent::__construct($tokens);
    }

    /**
     * Returns the first sequence of tokens satisfying the provided
     * token query, or null if a match could not be found.
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

            if (isset($rule['text']) && $rule['text'] != $token->getText()) {
                $query->start();
                $sequence = [];
                $tokens->next();
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

            if (isset($rule['open'], $rule['close'])) {
                if ($token->getText() == $rule['open']) {
                    $level = 1;
                    $sequence[] = $token;
                    $tokens->next();

                    while ($tokens->valid()) {
                        $sequence[] = $tokens->current();

                        if ($tokens->current()->getText() == $rule['open']) {
                            $level++;
                        }

                        if ($tokens->current()->getText() == $rule['close']) {
                            $level--;

                            if ($level == 0) {
                                $tokens->next();
                                continue 2;
                            }
                        }

                        $tokens->next();
                    }
                }

                $query->start();
                $sequence = [];
                $tokens->next();
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
            $sequences[] = new TokenIterator($tokens);
        }

        return $sequences;
    }

    /**
     * Advances the iterator until the next token of requested 
     * type is encountered. If a token is not found, the offset
     * of the iterator will not change and false is returned.
     *
     * @param  mixed $type
     * @return boolean
     */
    public function seekType($type)
    {
        $offset = $this->key();
        $this->next();

        while ($this->valid()) {
            if ($this->current()->getType() == $type) {
                return true;
            }

            $this->next();
        }

        $this->seek($offset);
        return false;
    }
}
