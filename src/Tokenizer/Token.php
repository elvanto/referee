<?php

namespace Referee\Tokenizer;

/**
 * Token
 *
 * Represents a single token extracted from source code.
 */
class Token
{
    /**
     * @var mixed
     */
    protected $type;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var integer
     */
    protected $line;

    /**
     * Creates a new token using a value returned
     * by token_get_all.
     *
     * @param mixed $token
     */
    function __construct($token)
    {
        if (is_array($token)) {
            $this->type = array_shift($token);
            $this->text = array_shift($token);
            $this->line = array_shift($token);
        } else {
            $this->type = $token;
            $this->text = $token;
        }
    }

    /**
     * Return the token's text when used as a
     * string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->text;
    }

    /**
     * Set the token's type.
     *
     * @param  mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the token's type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the token's text.
     *
     * @param  mixed $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get the token's text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the token's original line
     *
     * @return integer
     */
    public function getLine()
    {
        return $this->line;
    }
}
