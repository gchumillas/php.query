<?php
/**
 * This file contains the CssParserCombinatorGeneral class.
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
use DOMElement;
use com\soloproyectos\core\css\parser\combinator\CssParserCombinator;
use com\soloproyectos\core\xml\dom\XmlDomHelper;

/**
 * Class CssParserCombinatorGeneral.
 * 
 * This class represents a filter in a CSS expression.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserCombinatorGeneral extends CssParserCombinator
{
    
    /**
     * Gets the general sibling nodes.
     * 
     * @param DOMElement $node    DOMElement object
     * @param string     $tagname Tag name
     * 
     * @return array of DOMElement
     */
    public function filter($node, $tagname)
    {
        $ret = array();
        while ($node = XmlDomHelper::getNextSiblingElement($node)) {
            array_push($ret, $node);
        }
        return $ret;
    }
}
