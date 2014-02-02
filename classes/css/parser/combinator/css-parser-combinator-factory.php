<?php
/**
 * This file contains the CssParserCombinatorFactory class.
 * 
 * PHP Version 5.3
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\core\css\parser\combinator;
use Closure;
use com\soloproyectos\core\css\parser\combinator\CssParserCombinator;

/**
 * Class CssParserCombinatorFactory.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserCombinatorFactory
{
    /**
     * Gets a combinator instance by class name.
     * 
     * @param string  $classname       Class name
     * @param Closure $userDefFunction User defined function (not required)
     * 
     * @return CssParserCombinator
     */
    public static function getInstance($classname, $userDefFunction = null)
    {
        $class = "com\\soloproyectos\\core\\css\\parser\\combinator\\"
            . $classname;
        return new $class($userDefFunction);
    }
}
