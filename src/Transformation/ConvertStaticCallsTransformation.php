<?php

namespace Referee\Transformation;

use Referee\Tokenizer\Tokenizer;
use Referee\Tokenizer\TokenQuery;

class ConvertStaticCallsTransformation implements TransformationInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $objectName;

    /**
     * @var string[]
     */
    private $methodNames = [];

    /**
     * @var array
     */
    private $lines = [];

    /**
     * Sets the fully qualified class name to search for.
     *
     * @param  string $class_name
     * @return null
     */
    public function setClassName($class_name)
    {
        $segments = explode('\\', $class_name);

        $this->className = array_pop($segments);
        $this->namespace = implode('\\', $segments);

        $this->objectName = preg_replace(
            '/([a-z])([A-Z])/',
            '$1_$2',
            $this->className
        );

        $this->objectName = '$' . strtolower($this->objectName);
    }

    /**
     * Sets the method names for which static calls should be
     * converted to instance calls.
     *
     * @param  string[] $method_names
     * @return null
     */
    public function setMethodNames($method_names)
    {
        $this->methodNames = $method_names;
    }

    /**
     * Applies the transformation to a source file and returns the result.
     *
     * @param  string $source Source file contents
     * @return string
     */
    public function transform($source)
    {
        $class_aliases = [];

        $call_query = (new TokenQuery)
            ->expect(T_STRING)
            ->expect(T_DOUBLE_COLON)
            ->expect(T_STRING);

        $tokens = new Tokenizer($source);
        $static_calls = $tokens->query($call_query);

        /* Convert static calls to instance calls */
        foreach ($static_calls as $call) {
            if ($call[0]->getText() == $this->className) {
                if (in_array($call[2]->getText(), $this->methodNames)) {
                    $call[0]->setText($this->objectName);
                    $call[1]->setText('->');

                    $call[0]->setText($this->objectName . ' = new ' . $this->className . "();\n" . $call[0]->getText());
                }
            }
        }

        return (string) $tokens;
    }
}
