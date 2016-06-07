<?php

namespace Referee\Tokenizer;

/**
 * Token Query
 *
 * Manages a set of rules used by the Tokenizer to extract
 * sequences of tokens from source.
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
     * type. Optionally, the required text of the token may be
     * set.
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
