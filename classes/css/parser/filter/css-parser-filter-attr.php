<?php
/**
 * This file contains the CssParserFilterAttr class.
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
use com\soloproyectos\core\css\parser\filter\CssParserFilter;

/**
 * Class CssParserFilterAttr.
 * 
 * This class represents the attribute filter.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterAttr extends CssParserFilter
{
    const EQUAL_SELECTOR = '=';
    const NOT_EQUAL_SELECTOR = '!=';
    const CONTAIN_SELECTOR = '*=';
    const CONTAIN_WORD_SELECTOR = '~=';
    const CONTAIN_PREFIX_SELECTOR = '|=';
    const START_WITH_SELECTOR = '^=';
    const END_WITH_SELECTOR = '$=';
    
    /**
     * List of operators
     * @static
     * @var array of strings
     */
    private static $_operators = array(
        CssParserFilterAttr::EQUAL_SELECTOR,
        CssParserFilterAttr::NOT_EQUAL_SELECTOR,
        CssParserFilterAttr::CONTAIN_SELECTOR,
        CssParserFilterAttr::CONTAIN_WORD_SELECTOR,
        CssParserFilterAttr::CONTAIN_PREFIX_SELECTOR,
        CssParserFilterAttr::START_WITH_SELECTOR,
        CssParserFilterAttr::END_WITH_SELECTOR
    );
    
    /**
     * Attribute name.
     * @var string
     */
    private $_attrName;
    
    /**
     * Operator.
     * @var string
     */
    private $_op;
    
    /**
     * Value.
     * @var string
     */
    private $_value;
    
    /**
     * Constructor.
     * 
     * @param string $attrName Attribute name
     * @param string $op       Operator
     * @param string $value    A value
     */
    public function __construct($attrName, $op, $value)
    {
        $this->_attrName = $attrName;
        $this->_op = $op;
        $this->_value = $value;
    }
    
    /**
     * Gets a list of available operators.
     * 
     * @static
     * @return array of strings
     */
    public static function getOperators()
    {
        return self::$_operators;
    }
    
    /**
     * Equal selector.
     * 
     * Selects elements that have the specified attribute with a value exactly equal
     * to a certain value.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-equals-selector/
     * @return boolean
     */
    private function _isEqualSelector($node)
    {
        return $node->hasAttribute($this->_attrName)
            && $node->getAttribute($this->_attrName) == $this->_value;
    }
    
    /**
     * Not equal selector.
     * 
     * Select elements that either don't have the specified attribute, or do have
     * the specified attribute but not with a certain value.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-not-equal-selector/
     * @return boolean
     */
    private function _isNotEqualSelector($node)
    {
        return !$node->hasAttribute($this->_attrName)
            || $node->getAttribute($this->_attrName) != $this->_value;
    }
    
    /**
     * Contain selector.
     * 
     * Selects elements that have the specified attribute with a value containing
     * the a given substring.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-contains-selector/
     * @return boolean
     */
    private function _isContainSelector($node)
    {
        if ($node->hasAttribute($this->_attrName)) {
            $attr = $node->getAttribute($this->_attrName);
            $len = strlen($this->_value);
            if ($len > 0) {
                $pos = strpos($attr, $this->_value);
                return $pos !== false;
            }
        }
        
        return false;
    }
    
    /**
     * Contain word selector.
     * 
     * Selects elements that have the specified attribute with a value containing a
     * given word, delimited by spaces.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-contains-word-selector/
     * @return boolean
     */
    private function _isContainWordSelector($node)
    {
        if ($node->hasAttribute($this->_attrName)) {
            $items = explode(" ", trim($node->getAttribute($this->_attrName)));
            foreach ($items as $item) {
                if (preg_match("/^\w+$/", $item) && $this->_value == $item) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Contain prefix selector.
     * 
     * Selects elements that have the specified attribute with a value either equal
     * to a given string or starting with that string followed by a hyphen (-).
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-contains-prefix-selector/
     * @return boolean
     */
    private function _isContainPrefixSelector($node)
    {
        if ($node->hasAttribute($this->_attrName)) {
            $attr = $node->getAttribute($this->_attrName);
            $len = strlen($this->_value);
            if ($len > 0) {
                $pos = stripos($attr, $this->_value);
                return $pos === 0 && (strlen($attr) <= $len || $attr[$len] == "-");
            }
        }
        
        return false;
    }
    
    /**
     * Start with selector.
     * 
     * Selects elements that have the specified attribute with a value beginning
     * exactly with a given string.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-starts-with-selector/
     * @return boolean
     */
    private function _isStartWithSelector($node)
    {
        if ($node->hasAttribute($this->_attrName) && strlen($this->_value) > 0) {
            $attrValue = $node->getAttribute($this->_attrName);
            return strpos($attrValue, $this->_value) === 0;
        }
        return false;
    }
    
    /**
     * End with selector.
     * 
     * Selects elements that have the specified attribute with a value ending
     * exactly with a given string. The comparison is case sensitive.
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @link http://api.jquery.com/attribute-ends-with-selector/
     * @return boolean
     */
    private function _isEndWithSelector($node)
    {
        if ($node->hasAttribute($this->_attrName)) {
            $len = strlen($this->_value);
            if ($len > 0) {
                $attr = $node->getAttribute($this->_attrName);
                $attrLen = strlen($attr);
                return $len <= $attrLen
                    && strpos($attr, $this->_value, $attrLen - $len) !== false;
            }
        }
        return false;
    }
    
    /**
     * Does the node has the attribute?
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @return boolean
     */
    private function _hasAttribute($node)
    {
        return $node->hasAttribute($this->_attrName);
    }
    
    /**
     * Is an attribute selector?
     * 
     * @param DOMElement $node DOMElement object
     * 
     * @return boolean
     */
    private function _isAttrSelector($node)
    {
        $ret = false;
        
        if ($this->_op == CssParserFilterAttr::EQUAL_SELECTOR) {
            $ret = $this->_isEqualSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::NOT_EQUAL_SELECTOR) {
            $ret = $this->_isNotEqualSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::CONTAIN_SELECTOR) {
            $ret = $this->_isContainSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::CONTAIN_WORD_SELECTOR) {
            $ret = $this->_isContainWordSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::CONTAIN_PREFIX_SELECTOR) {
            $ret = $this->_isContainPrefixSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::START_WITH_SELECTOR) {
            $ret = $this->_isStartWithSelector($node);
        } elseif ($this->_op == CssParserFilterAttr::END_WITH_SELECTOR) {
            $ret = $this->_isEndWithSelector($node);
        } else {
            $ret = $this->_hasAttribute($node);
        }
        
        return $ret;
    }
    
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
        return $this->_isAttrSelector($node);
    }
}
