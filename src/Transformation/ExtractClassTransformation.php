<?php

namespace Referee\Transformation;

/**
 * Converts a source file containing function definitions into a namespaced
 * class containing equivalent static methods.
 */
class ExtractClassTransformation implements TransformationInterface
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $className;

    /**
     * string[]
     */
    protected $functionNames = [];

    function __construct($namespace, $class_name)
    {
        $this->namespace = $namespace;
        $this->className = $class_name;
    }

    /**
     * Returns true if a line contains a function definition
     *
     * @param  string $line Line of source file
     * @return boolean
     */
    protected function isFunctionDefinition($line)
    {
        return strpos(trim($line), 'function') === 0;
    }

    /**
     * Returns the names of functions that have been converted
     * to static methods.
     *
     * @return integer
     */
    public function getFunctionNames()
    {
        return $this->functionNames;
    }

    /**
     * Applies the transformation to a source file and returns the result.
     *
     * @param  string $source Source file contents
     * @return string
     */
    public function transform($source)
    {
        $lines = explode("\n", trim($source));
        $transformed = array();

        $in_class = false;
        foreach ($lines as $line) {

            /* Convert function definition to static method definition */
            if ($this->isFunctionDefinition($line)) {
                $line = str_replace('function', 'public static function', $line);

                /* Store function name */
                preg_match('/function \&?([\w]*)/', $line, $matches);
                $this->functionNames[] = $matches[1];
            }

            /* Wrap following content in a class definition */
            if (!$in_class && !empty($line) && strpos($line, '<?php') === false) {
                $transformed[] = 'namespace ' . $this->namespace . ';';
                $transformed[] = '';
                $transformed[] = 'class ' . $this->className;
                $transformed[] = '{';

                $in_class = true;
            }

            /* Apply indentation to lines within the class */
            if ($in_class && trim($line) != '') {
                $line = "    $line";
            }

            $transformed[] = $line;
        }

        /* Close the class definition */
        $transformed[] = '}';
        $result = implode("\n", $transformed);

        foreach ($this->functionNames as $function) {
            /* Replace function usage with static calls on self */
            $result = preg_replace(
                "/(?<![:>]|function |function \&)\b$function\(/",
                "self::$function(",
                $result
            );
        }

        return $result;
    }
}
