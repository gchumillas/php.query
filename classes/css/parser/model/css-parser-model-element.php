<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\css\parser\model;
use \DOMElement;
use com\soloproyectos\common\css\parser\filter\CssParserFilter;

/**
 * Class CssParserModelElement.
 *
 * This class represents an element in a CSS expression.
 *
 * @package Css\Parser\Model
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class CssParserModelElement
{
    /**
     * Tagname.
     * @var string
     */
    private $_tagName;

    /**
     * List of filters.
     * @var array of CssParserFilter objects
     */
    private $_filters;

    /**
     * Constructor.
     *
     * @param string $tagName Tagname
     */
    public function __construct($tagName)
    {
        $this->_filters = array();
        $this->_tagName = $tagName;
    }

    /**
     * Gets the tagname.
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->_tagName;
    }

    /**
     * Gets the filters.
     *
     * @return array of CssParserFilter
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Adds a filter.
     *
     * @param CssParserFilter $filter Filter object
     *
     * @return void
     */
    public function addFilter($filter)
    {
        array_push($this->_filters, $filter);
    }

    /**
     * Does the node match?
     *
     * @param DOMElement $node DOMElement object
     *
     * @return boolean
     */
    public function match($node)
    {
        return $this->_tagName == "*" || $node->nodeName == $this->_tagName;
    }
}
