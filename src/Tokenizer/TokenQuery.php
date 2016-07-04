<?php

namespace Referee\Tokenizer;

/**
 * Token Query
 *
 * Manages a set of rules used by the TokenIterator to extract
 * token sequences.
 */
class TokenQuery
{
    /**
     * @var string[]
     */
    protected $rules = [];

    /**
     * @var integer
     */
    protected $offset = 0;

    /**
     * Resets the state of the query.
     *
     * @return null
     */
    public function start()
    {
        $this->offset = 0;
    }

    /**
     * Adds a rule requiring the next token to be of a specific
     * type. Optionally, a requirement for the token's text may
     * also be set.
     *
     * @param  mixed  $type
     * @param  string $text
     * @return $this
     */
    public function expect($type, $text = null)
    {
        $this->rules[] = [
            'type' => $type,
            'text' => $text,
            'required' => true
        ];

        return $this;
    }

    /**
     * Adds a rule allowing the next token to be of a specific
     * type.
     *
     * @param  mixed  $type
     * @return $this
     */
    public function accept($type)
    {
        $this->rules[] = [
            'type' => $type,
            'required' => false
        ];

        return $this;
    }

    /**
     * Adds a rule requiring the provided token query to
     * be satisfied zero or more times.
     *
     * @param  TokenQuery $query
     * @return $this
     */
    public function any(TokenQuery $query)
    {
        $this->rules[] = [
            'query' => $query,
            'required' => false
        ];

        return $this;
    }

    /**
     * Adds a rule matching all tokens within a matching set of
     * brackets.
     *
     * @param  string $opening
     * @param  string $closing
     * @return $this
     */
    public function matching($opening, $closing)
    {
        $this->rules[] = [
            'open' => $opening,
            'close' => $closing,
            'required' => true
        ];

        return $this;
    }

    /**
     * Returns the next rule.
     *
     * @return array
     */
    public function next()
    {
        if ($this->isComplete()) {
            return null;
        } else {
            return $this->rules[$this->offset++];
        }
    }

    /**
     * Returns true if all rules have been iterated.
     *
     * @return boolean
     */
    public function isComplete()
    {
        return $this->offset >= count($this->rules);
    }
}
