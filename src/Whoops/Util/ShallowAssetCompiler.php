<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Util;
use RuntimeException;

/**
 * Exposes methods to perform basic "compilation" operations
 * on static assets, i.e: css & js.
 */
class ShallowAssetCompiler
{
    /**
     * Given a list of paths, loads them all and concatenates their
     * contents into a single string.
     * 
     * @param  array $resources
     * @return string
     */
    public function compileResourceContents(array $resources)
    {
        return join("\n", array_map(function($path) {
            return file_get_contents($path);
        }, $resources));
    }
    
    /**
     * Given an array of CSS file paths, perform a shallow minification
     * and concatenation, in order, into a single string. There's an
     * assumption that the resources were already checked for validty,
     * and that the paths are absolute.
     * 
     * @param  array $resources
     * @return string 
     */
    public function compileCssResources(array $resources)
    {
        $compiled = $this->compileResourceContents($resources);

        // Perform some basic minification on the string, through
        // assumptions of basic CSS structure:
        
        $compiled = preg_replace(
            // Remove white-space after a ;,: , {, } or ,
            "/\s*([\;\{\}:,])\s*/im",
            "$1",
            $compiled
        );

        return $compiled;
    }

    /**
     * Given an array of JS file paths, concatenate them all into a single
     * string.
     * 
     * @todo Some shallow minification may be performed, but it's too risky
     *       to do without proper investigation, which I have yet to do.
     * 
     * @param  array $resources
     * @return string
     */
    public function compileJsResources(array $resources)
    {
        return $this->compileResourceContents($resources);
    }
}
