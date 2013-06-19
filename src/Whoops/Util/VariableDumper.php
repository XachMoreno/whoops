<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Util;
use Whoops\Util\TemplateEngine;
use RuntimeException;
use InvalidArgumentException;

/**
 * Exposes methods to dump variables and objects to a
 * human/browser/whatever-friendly format. Includes not
 * only support for specific types, but object types and
 * conditions, so it can be used for some kick-ass implementation-
 * -specific output,
 *  i.e: dump(<Laravel Route>)--> friendly display of where a route
 *       is pointing, etc.
 */
class VariableDumper
{
    /** @var int */
    const MATCH_REGEX   = 0x10;
    const MATCH_CLOSURE = 0x20;
    const MATCH_EQUAL   = 0x30;
    const MATCH_ALL     = 0x40;

    /**
     * The template engine used to resolve resources used
     * by the dumper.
     * 
     * @var Whoops\Util\TemplateEngine
     */
    private $templateEngine;

    /**
     * Available dumpers, in order of addition.
     * 
     * @var array[]
     */
    private $dumpers = array();

    /**
     * @param Whoops\Util\TemplateEngine $templateEngine
     */
    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @return Whoops\Util\TemplateEngine
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * Accepts an arbitrary number of variables, and dumps them.
     * 
     * @param  mixed $var,...
     * @return string
     */
    public function dump()
    {
        $vars = func_get_args();

        if(!func_num_args()) {
            throw new InvalidArgumentException(
                __METHOD__ . " expects at least one argument."
            );
        }

        // Go through each variable provided to the method, and for each
        // of the variables go through all available dumpers, to figure
        // out a method to dump said variable:
        foreach($vars as $variable) {
            foreach($this->dumpers as $dumper) {
                if($this->testIfDumperMatches($dumper, $variable)) {
                    $this->getTemplateEngine()
                        ->executeTemplate($dumper["template"], array(
                            "variable" => $variable,
                            "type"     => gettype($variable)
                        ))
                    ;

                    break;
                }
            }
        }
    }

    /**
     * Given a dumper array, runs its test against a variable's type,
     * (MATCH_REGEX, MATCH_EQUAL) or the variable itself (MATCH_CLOSURE).
     * 
     * @throws RuntimeException If an invalid test type is provided
     * 
     * @param  array $dumper
     * @param  mixed $variable
     * @return bool
     */
    protected function testIfDumperMatches(array $dumper, $variable)
    {
        $type = gettype($variable);

        switch($dumper["testType"]) {
            case self::MATCH_ALL:
                return true;
            break;
            case self::MATCH_EQUAL:
                return $dumper["test"] == $type;
            break;
            case self::MATCH_REGEX:
                return (bool) preg_match($dumper["test"], gettype($variable));
            break;
            case self::MATCH_CLOSURE:
                return (bool) call_user_func($dumper["test"], $variable);
            break;
            default:
                throw new RuntimeException(
                    "Invalid dumper test type provided for dumper: {$dumper["name"]}"
                );
        }
    }

    /**
     * Adds a dumper for a given type. Dumpers are resolved
     * using a test - look at the MATCH_* constants. Matches
     * are dispatched to the given template resource to be
     * rendered.
     * 
     * @param string $name
     * @param string $templateResource
     * @param mixed  $testType
     * @param mixed  $test
     */
    public function addDumper($name, $templateResource, $testType, $test)
    {
        $this->dumpers[] = array(
            "name"     => $name,
            "template" => $templateResource,
            "testType" => $testType,
            "test"     => $test
        );
    }
}
