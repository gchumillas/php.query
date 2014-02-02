<?php
/**
 * This file contains the CssParserFilterPseudoFactory class.
 * 
 * PHP Version 5.3
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\core\css\parser\filter;
use Closure;
use com\soloproyectos\core\css\parser\filter\CssParserFilterPseudo;

/**
 * Class CssParserFilterPseudoFactory.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterPseudoFactory
{
    
    /**
     * Gets a psuedo-filter instance by class name.
     * 
     * @param string  $classname       Class name
     * @param string  $input           String input (default is "")
     * @param Closure $userDefFunction Used defined function (not required)
     * 
     * @return CssParserFilterPseudo
     */
    public static function getInstance(
        $classname, $input = "", $userDefFunction = null
    ) {
        $fullname = "com\\soloproyectos\\core\\css\\parser\\filter\\"
            . $classname;
        return new $fullname($input, $userDefFunction);
    }
}
