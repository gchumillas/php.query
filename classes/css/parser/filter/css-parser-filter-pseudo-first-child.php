<?php
/**
 * This file contains the CssParserFilterPseudoFirstChild class.
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
use DOMElement;
use com\soloproyectos\core\css\parser\filter\CssParserFilterPseudo;
use com\soloproyectos\core\xml\dom\XmlDomHelper;

/**
 * Class CssParserFilterPseudoFirstChild.
 * 
 * This class represents the first-child pseudo filter.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterPseudoFirstChild extends CssParserFilterPseudo
{
    
    /**
     * Does the node match?
     * 
     * @param DOMElement $node     DOMElement object
     * @param integer    $position Node position
     * @param array      $items    List of nodes
     * 
     * @return boolean
     */
    public function match($node, $position, $items)
    {
        return !XmlDomHelper::getPreviousSiblingElement($node);
    }
}
