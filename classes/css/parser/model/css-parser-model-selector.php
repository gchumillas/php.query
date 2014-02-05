<?php
/**
 * This file contains the CssParserModelSelector class.
 * 
 * PHP Version 5.3
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\core\css\parser\model;
use DOMElement;
use DOMNode;
use com\soloproyectos\core\css\parser\model\CssParserModelFactor;
use com\soloproyectos\core\xml\dom\XmlDomHelper;

/**
 * Class CssParserModelSelector.
 * 
 * This class represents a term in a CSS expression.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserModelSelector
{
    
    /**
     * List of factors.
     * @var array of CssParserModelFactor objects
     */
    private $_factors = array();
    
    /**
     * Adds a factor.
     * 
     * @param CssParserModelFactor $factor Factor object
     * 
     * @return void
     */
    public function addFactor($factor)
    {
        array_push($this->_factors, $factor);
    }
    
    /**
     * Gets all filtered subnodes of a given node.
     * 
     * This function filters all subnodes of a given node that satisfy all factors.
     * A term is actually a list of factors, and a factor can be added by the
     * 'addFactor' function.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return array of DOMElement objects
     */
    public function filter($node)
    {
        $ret = array();
        $items = array($node);
        foreach ($this->_factors as $factor) {
            $ret = $this->_getNodesByFactor($items, $factor);
            $items = $ret;
        }
        return $ret;
    }
    
    /**
     * Gets nodes from a list by a given factor.
     * 
     * @param array                $nodes  List of DOMNode objects
     * @param CssParserModelFactor $factor Factor object
     * 
     * @return array of DOMElement objects
     */
    private function _getNodesByFactor($nodes, $factor)
    {
        $ret = array();
        foreach ($nodes as $node) {
            $items = $factor->filter($node);
            $ret = XmlDomHelper::mergeNodes($ret, $items);
        }
        return $ret;
    }
}
