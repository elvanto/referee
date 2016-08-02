<?php

namespace Referee\Visitor;

use Referee\Tokenizer\Tokenizer;
use Referee\Tokenizer\TokenQuery;
use Referee\Tokenizer\TokenIterator;

class ClassDependencyVisitor
{
    /**
     * Class dependency map.
     * @var array
     */
    private $dependencies = [];

    /**
     * Class instance methods.
     * @var array
     */
    private $classMethods = [];

    /**
     * Collects dependencies from the provided token iterator.
     *
     * @param  TokenIterator $tokens
     * @return null
     */
    public function visit(TokenIterator $tokens)
    {
        $global_names = [];

        $class_query = (new TokenQuery)
            ->expect(T_CLASS)
            ->expect(T_STRING)
            ->matching('{', '}');

        $variables_query = (new TokenQuery)
            ->expect(',')
            ->expect(T_VARIABLE);

        $globals_query = (new TokenQuery)
            ->expect(T_GLOBAL)
            ->expect(T_VARIABLE)
            ->any($variables_query)
            ->expect(';')
            ->accept(T_WHITESPACE);

        $classes = $tokens->query($class_query);

        foreach ($classes as $class) {
            $global_defs = $class->query($globals_query);

            print_r($global_defs);
        }
    }

    /**
     * Returns the instance methods for the specified class.
     *
     * @param  string $class_name
     * @return string[]
     */
    public function getClassMethods($class_name)
    {
        if (isset($this->classMethods[$class_name])) {
            return $this->classMethods[$class_name];
        } else {
            return [];
        }
    }
}
