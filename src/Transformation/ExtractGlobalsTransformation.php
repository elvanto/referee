<?php

namespace Referee\Transformation;

use Referee\Tokenizer\Tokenizer;
use Referee\Tokenizer\TokenQuery;

class ExtractGlobalsTransformation implements TransformationInterface
{
    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var string[]
     */
    protected $globalNames = [];

    /**
     * @var string[]
     */
    protected $superGlobalNames = [];

    /**
     * @var string[]
     */
    protected $methodNames = [];

    /**
     * Returns the name of the class declared in the source file.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
    
    /**
     * Returns the names of all global variables encountered by the
     * transformation.
     *
     * @return string[]
     */
    public function getGlobalNames()
    {
        return $this->globalNames;
    }

    /**
     * Returns the names of static methods that have been converted to
     * instance methods.
     *
     * @return string[]
     */
    public function getMethodNames()
    {
        return $this->methodNames;
    }

    /**
     * Applies the transformation to a source file and returns the result.
     *
     * @param  string $source Source file contents
     * @return string
     */
    public function transform($source)
    {
        $tokens = new Tokenizer($source);

        $functions_query = (new TokenQuery)
            ->accept(T_STATIC)
            ->expect(T_FUNCTION)
            ->expect(T_STRING)
            ->matching('(', ')')
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

        $supers_query = (new TokenQuery)
            ->expect(T_VARIABLE)
            ->expect('[')
            ->expect(T_CONSTANT_ENCAPSED_STRING)
            ->expect(']');

        $functions = $tokens->query($functions_query);

        foreach ($functions as $function) {
            $name = '';
            $local = [];
            $local_supers = [];
            $globals = $function->query($globals_query);
            $supers = $function->query($supers_query);

            $function->rewind();
            if ($function->seekType(T_STRING)) {
                $name = $function->current()->getText();
            }

            foreach ($globals as $declaration) {
                foreach ($declaration as $token) {
                    if ($token->getType() == T_VARIABLE) {
                        if (!in_array($token->getText(), $this->globalNames)) {
                            $this->globalNames[] = $token->getText();
                        }

                        $local[] = $token->getText();
                    }

                    $token->setText('');
                }
            }

            foreach ($supers as $super) {
                $super_name = '';
                foreach ($super as $offset => $token) {
                    if ($offset == 0 && $token->getText() != '$GLOBALS') {
                        break;
                    }

                    if ($token->getType() == T_CONSTANT_ENCAPSED_STRING) {
                        $super_name = $token->getText();
                        $super_name = trim($super_name, "'");
                        $super_name = trim($super_name, '"');

                        $this->superGlobalNames[] = $local_supers[] = $super_name;
                    }

                    $token->setText('');

                    /* Last token is replaced with an instance member variable */
                    if ($offset == 3) {
                        $token->setText('$this->' . $super_name);
                    }
                }
            }

            foreach ($function as $token) {
                if ((count($local) > 0 || count($local_supers) > 0) && $token->getType() == T_STATIC) {
                    $token->setText('');
                    $function->next();

                    if ($function->current()->getType() == T_WHITESPACE) {
                        $function->current()->setText('');
                    }
                }

                if (
                    $token->getType() == T_VARIABLE &&
                    in_array($token->getText(), $local)
                ) {
                    $token->setText(
                        '$this->' . str_replace('$', '', $token->getText())
                    );
                }
            }

            if (count($local) > 0 || count($local_supers) > 0) {
                $this->methodNames[] = $name;
            }
        }

        $tokens->rewind();
        if ($tokens->seekType(T_CLASS) && $tokens->seekType(T_STRING)) {
            $this->className = $tokens->current()->getText();

            if ($tokens->seekType('{')) {
                $token = $tokens->current();
                $append = "\n";
                $vars = implode(', ', $this->globalNames);

                foreach ($this->globalNames as $var) {
                    $append .= "    private $var;\n";
                }

                foreach ($this->superGlobalNames as $var) {
                    $append .= "    private $$var;\n";
                }

                $append .= "\n";
                $append .= "    function __construct()\n";
                $append .= "    {\n";
                $append .= "        global $vars;\n\n";

                foreach ($this->globalNames as $var) {
                    $append .= '        $this->' . str_replace('$', '', $var) . " = $var;\n";
                }

                foreach ($this->superGlobalNames as $var) {
                    $append .= '        $this->' . $var . " = \$GLOBALS['$var'];\n";
                }

                $append .= "    }\n";

                $token->setText($token->getText() . $append);
            }
        }

        return (string) $tokens;
    }
}
