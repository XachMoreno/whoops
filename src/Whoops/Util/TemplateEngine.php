<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Util;
use RuntimeException;

/**
 * Exposes a basic interface for dealing with a
 * template and its resources. Made specifically
 * for the PrettyPageHandler.
 */
class TemplateEngine
{
    /**
     * @var string[]
     */
    private $searchPaths = array();

    /**
     * Stores absolute locations to known resources,
     * so we're not doing multiple checks on all resource
     * paths every time we need something.
     * 
     * Do note, however, that there's an assumption that this
     * code will not usually be running in production, or will
     * not be running during normal execution flow.
     * 
     * @var array[]
     */
    private $resourceCache;

    /**
     * Return the first argument if it's not empty, return
     * the second otherwise (the default value)
     * 
     * @param  mixed $value
     * @param  mixed $defaultValue
     * @return mixed
     */
    public function pick($value, $defaultValue)
    {
        return $value ?: $defaultValue;
    }

    /**
     * Escape string content for output in an HTML template
     * 
     * @param  string $raw
     * @return string
     */
    public function escape($raw)
    {
        return htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns a slug version of a string
     * 
     * @example $engine->slug("Hello world!") // hello-world
     * 
     * @param  string $original
     * @return string
     */
    public function slug($original)
    {
        $slug = str_replace(" ", "-", $original);
        $slug = preg_replace('/[^\w\d\-\_]/i', '', $slug);
        
        return strtolower($slug);
    }

    /**
     * Semantic alias to TemplateEngine::executeTemplate
     * 
     * @param string $resource
     * @param array  $variables
     */
    public function template($resource, array $variables = null)
    {
        $this->executeTemplate($resource, $variables);
    }

    /**
     * Escape string content, but preserve URIs and convert
     * them to clickable anchor elements.
     * 
     * @todo Add support for custom anchor titles/text
     * 
     * @param  string $raw
     * @return string
     */
    public function escapeButPreserveUris($raw)
    {
        return preg_replace(
            '@([A-z]+?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            "<a href=\"$1\" target=\"_blank\">$1</a>", $this->escape($raw)
        );
    }

    /**
     * Given a resource path, loads it as a PHP template in
     * a private scope, optionally with an array of variables.
     * 
     * @param  string $resource
     * @param  array  $variables
     */
    public function executeTemplate($resource, array $__variables = null)
    {
        $__template = $this->getResource($resource);
        $__context  = $this;

        // Run the template within a private scope:
        call_user_func(function() use($__template, $__context, $__variables) {
            // Expose the engine as an utility $tpl variable:
            $tpl = $__context;

            // Extract variables into the template's scope:
            if($__variables !== null) {
                extract($__variables);
            }

            require $__template;
        });
    }

    /**
     * Adds a path to the list of resource search paths
     * 
     * @see   Whoops\Util\TemplateEngine::setSearchPaths
     * @param string $searchPath
     */
    public function addSearchPath($searchPath)
    {
        $this->searchPaths[] = $searchPath;
    }
    
    /**
     * Sets a list of search paths for this template
     * engine. When a resource is requested, paths will
     * be scanned in reverse order until a resource is
     * successfully matched.
     * 
     * @param string[] $searchPaths
     */
    public function setSearchPaths(array $searchPaths)
    {
        $this->searchPaths = $searchPaths;
    }

    /**
     * @return string[]
     */
    public function getSearchPaths()
    {
        return $this->searchPaths;
    }

    /**
     * Given a resource path, search for and return its absolute
     * location.
     * 
     * @throws RuntimeException If a resource cannot be found
     * @param  string $resource
     * @return array
     */
    public function getResource($resource)
    {
        // Return a resource from the cache if it's available:
        if(isset($this->resourceCache[$resource])) {
            return $this->resourceCache[$resource];
        } elseif($path = $this->findResource($resource)) {
            return $path;
        } else {
            throw new RuntimeException(
                 "Resource could not be located: '$resource' in the following paths:\n"
               . join("\n", $this->searchPaths)
            );
        }
    }

    /**
     * Returns true if the given resource was found. Resource
     * paths should always be relative, and when scanning will
     * be appended to each individual resource path
     * 
     * NOTE: Some caching is done on calls to this method, to
     *       reduce scanning costs.
     * 
     * @example $engine->hasResource("css/base.css"); // bool
     * @param   string $resource
     * @return  bool
     */
    public function hasResource($resource)
    {
        return null !== $this->getResourcePath($resource);
    }

    /**
     * Look for a resource in the available paths, and cache
     * it if found.
     * 
     * This is the only method actually doing any searching
     * on the FS, and completely disregards the existing cache;
     * 
     * @throws RuntimeException If no resource paths are available
     * 
     * @param  string $resource
     * @return string|null Null if resource isn't found
     */
    private function findResource($resource)
    {
        if(empty($this->searchPaths)) {
            throw new RuntimeException(
                "No resource paths available, cannot locate resource '$resource'"
            );
        }

        // Go through all available paths in reverse order:
        $totalPaths   = count($this->searchPaths);
        $resourcePath = null;
        for($i = $totalPaths - 1; $i >= 0; $i--) {
            $searchPath = $this->searchPaths[$i];
            $searchPath = rtrim($searchPath, "/") . "/" . ltrim($resource, "/");

            // If the file is available, cache it and break out of
            // the search loop:
            if(is_readable($searchPath)) {
                $resourcePath = $searchPath;
                $this->resourceCache[$resource] = $resourcePath;

                break;
            }
        }

        return $resourcePath;
    }
}
