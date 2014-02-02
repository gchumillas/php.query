<?php
/**
 * This file contains the CssParserModelFactor class.
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
use com\soloproyectos\core\css\parser\combinator\CssParserCombinator;
use com\soloproyectos\core\css\parser\model\CssParserModelElement;

/**
 * Class CssParserModelFactor.
 * 
 * This class represents a factor in a CSS expression. A factor is composed a
 * combinator and an element.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserModelFactor
{
    const DESCENDANT_OPERATOR = "";
    const CHILD_OPERATOR = ">";
    const ADJACENT_OPERATOR = "+";
    
    /**
     * Combinator.
     * @var string
     */
    private $_combinator;
    
    /**
     * Element.
     * @var CssParserModelElement
     */
    private $_element;
    
    /**
     * Constructor.
     * 
     * @param CssParserCombinator   $combinator Combinator
     * @param CssParserModelElement $element    Element object
     */
    public function __construct($combinator, $element)
    {
        $this->_combinator = $combinator;
        $this->_element = $element;
    }
    
    /**
     * Gets the element.
     * 
     * @return CssParserModelElement
     */
    public function getElement()
    {
        return $this->_element;
    }
    
    /**
     * Gets the elements that matches the factor.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @return array of DOMElement
     */
    public function filter($node)
    {
        $ret = array();
        $items = $this->_combinator->filter($node, $this->_element->getTagName());
        
        // filters items by element
        foreach ($items as $item) {
            if ($this->_element->match($item)) {
                array_push($ret, $item);
            }
        }
        
        // filter items by element filters
        $filters = $this->_element->getFilters();
        foreach ($filters as $filter) {
            $items = array();
            foreach ($ret as $i => $item) {
                if ($filter->match($item, $i, $ret)) {
                    array_push($items, $item);
                }
            }
            $ret = $items;
        }
        
        return $ret;
    }
}
