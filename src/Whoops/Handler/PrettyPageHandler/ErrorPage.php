<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Handler\HandlerInterface;
use Whoops\Util\TemplateEngine;
use Whoops\Util\VariableDumper;
use Whoops\Util\ShallowAssetCompiler;
use RuntimeException;

/**
 * Display interface/container for the PrettyPageHandler's
 * template logic.
 */
class ErrorPage
{
    /**
     * @var Whoops\Run
     */
    private $whoops;

    /**
     * @var Whoops\Handler\HandlerInterface
     */
    private $handler;

    /**
     * @var Whoops\Util\TemplateEngine
     */
    private $templateEngine;

    /*
     * @var Whoops\Util\ShallowAssetCompiler
     */
    private $assetCompiler;

    /**
     * @var Whoops\Util\VariableDumper
     */
    private $variableDumper;

    /**
     * @var array
     */
    protected $attributes = array(
        // The base template for the error display:
        "template.resource"    => "views/error.html.php",

        // A list of stylesheets - they can either be resources, or, if
        // prefixed with a @ character, full urls which will be included
        // as a link:
        "template.stylesheets" => array(
            "css/whoops.base.css"
        ),
        // Same as the stylesheets:
        "template.javascripts" => array(),

        "page.title"   => "Whoops! There was an error!"
    );

    /**
     * @param Whoops\Handler\PrettyPageHandler $handler
     * @param Whoops\Util\TemplateEngine       $templateEngine
     * @param Whoops\Util\VariableDumper       $variableDumper
     * @param Whoops\Util\ShallowAssetCompiler $assetCompiler
     */
    public function __construct
        ( 
          HandlerInterface $handler,
          TemplateEngine $templateEngine = null,
          VariableDumper $variableDumper = null,
          ShallowAssetCompiler $assetCompiler = null )
    {
        $this->handler = $handler;

        // Optional:
        $this->templateEngine = $templateEngine;
        $this->variableDumper = $variableDumper;
        $this->assetCompiler  = $assetCompiler;
    }

    /**
     * Renders this error page
     */
    public function render()
    {
        throw new RuntimeException("Not implemented");
    }

    /**
     * Lazily instantiates a TemplateEngine for this error page
     * 
     * @return Whoops\Util\TemplateEngine
     */
    public function getTemplateEngine()
    {
        if($this->templateEngine === null) {
            $this->templateEngine = new TemplateEngine(
                $this->assetCompiler, $this->variableDumper
            );
        }

        return $this->templateEngine;
    }

    /**
     * Sets up the default variable dumpers used by Whoops
     */
    private function setupDefaultVariableDumpers()
    {
        $dumpers = array(
            // Match all variables:
            array(
                "whoops.generic", "views/dumper/generic.html.php",
                VariableDumper::MATCH_ALL, null
            ),

            // Match arrays:
            array(
                "whoops.array", "views/dumper/array.html.php",
                VariableDumper::MATCH_EQUAL, "array"
            ),

            // Match objects:
            array(
                "whoops.object", "views/dumper/object.html.php",
                VariableDumper::MATCH_EQUAL, "object"
            ),

            // Match whoops handlers:
            array(
                "whoops.handler", "views/dumper/whoops_handler.html.php",
                VariableDumper::MATCH_CLOSURE, function($variable) {
                    return is_subclass_of($variable, "Whoops\\Handler\\HandlerInterface");
                }
            )
        );

        $this->getTemplateEngine()
            ->getVariableDumper()->addDumpers($dumpers)
        ;
    }

    /**
     * @param string $attributeName
     * @param mixed  $attributeValue
     */
    public function setAttribute($attributeName, $attributeValue)
    {
        if(!is_string($attributeName) || empty($attributeName)) {
            throw new InvalidArgumentException(
                "Attribute name must be a string, and not empty"
            );
        }

        $this->attributes[$attributeName] = $attributeValue;
    }

    /**
     * @param  string $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName)
    {
        if(!is_string($attributeName) || empty($attributeName)) {
            throw new InvalidArgumentException(
                "Attribute name must be a string, and not empty"
            );
        }

        return isset($this->attributes[$attributeName]);
    }

    /**
     * @param  string $attributeName
     * @param  string $defaultValue
     * @return mixed
     */
    public function getAttribute($attributeName, $defaultValue = null)
    {
        if(!is_string($attributeName) || empty($attributeName)) {
            throw new InvalidArgumentException(
                "Attribute name must be a string, and not empty"
            );
        }

        return isset($this->attributes[$attributeName]) ? 
            $this->attributes[$attributeName] : $defaultValue
        ;
    }

    /**
     * @param  string $attributeName
     * @param  array  $value
     * @return mixed
     */
    public function mergeWithAttribute($attributeName, array $value)
    {
        if(!is_string($attributeName) || empty($attributeName)) {
            throw new InvalidArgumentException(
                "Attribute name must be a string, and not empty"
            );
        }

        // Attribute exists, but is not an array?
        if(isset($this->attributes[$attributeName]) && !is_array($this->attributes[$attributeName])) {
            throw new InvalidArgumentException(
                "Attribute '$attributeName' is not an array - cannot perform merge"
            );
        }

        // Attribute does not exist, create it:
        if(!isset($this->attributes[$attributeName])) {
            $this->attributes[$attributeName] = $value;    
        } else {
        // If it does, merge it:
            $this->attributes[$attributeName] = array_merge(
                $this->attributes[$attributeName],
                $value
            );
        }

        // Return the final, merged result:
        return $this->attributes[$attributeName];
    }
}
