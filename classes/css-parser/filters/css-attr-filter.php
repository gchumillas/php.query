<?php
require_once dirname(dirname(__DIR__)) . "/css-parser/filters/css-filter.php";

class CSSAttrFilter extends CSSFilter {
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
    private static $operators = array(
        CSSAttrFilter::EQUAL_SELECTOR,
        CSSAttrFilter::NOT_EQUAL_SELECTOR,
        CSSAttrFilter::CONTAIN_SELECTOR,
        CSSAttrFilter::CONTAIN_WORD_SELECTOR,
        CSSAttrFilter::CONTAIN_PREFIX_SELECTOR,
        CSSAttrFilter::START_WITH_SELECTOR,
        CSSAttrFilter::END_WITH_SELECTOR
    );
    
    /**
     * Attribute name.
     * @var string
     */
    private $attr_name;
    
    /**
     * Operator.
     * @var string
     */
    private $op;
    
    /**
     * Value.
     * @var string
     */
    private $value;
    
    /**
     * @param string $attr_name
     * @param string $op
     * @param string $value
     */
    public function __construct($attr_name, $op, $value) {
        $this->attr_name = $attr_name;
        $this->op = $op;
        $this->value = $value;
    }
    
    /**
     * Gets the list of operators.
     * @static
     * @return array of strings
     */
    public static function getOperators() {
        return self::$operators;
    }
    
    /**
     * Is the attribute exactly equal to a certain value? More info:
     * http://api.jquery.com/attribute-equals-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isEqualSelector($node) {
        return $node->hasAttribute($this->attr_name) && $node->getAttribute($this->attr_name) == $this->value;
    }
    
    /**
     * Is the attribute not equal to a given value? More info:
     * http://api.jquery.com/attribute-not-equal-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isNotEqualSelector($node) {
        return !$node->hasAttribute($this->attr_name) || $node->getAttribute($this->attr_name) != $this->value;
    }
    
    /**
     * Does the attribute contain the a given substring? More info:
     * http://api.jquery.com/attribute-contains-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isContainSelector($node) {
        if ($node->hasAttribute($this->attr_name)) {
            $attr = $node->getAttribute($this->attr_name);
            $len = strlen($this->value);
            if ($len > 0) {
                $pos = strpos($attr, $this->value);
                return $pos !== FALSE;
            }
        }
        return FALSE;
    }
    
    /**
     * Does the attribute contain a given word, delimited by spaces? More info:
     * http://api.jquery.com/attribute-contains-word-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isContainWordSelector($node) {
        if ($node->hasAttribute($this->attr_name)) {
            $items = explode(" ", trim($node->getAttribute($this->attr_name)));
            foreach ($items as $item) {
                if (preg_match("/^\w+$/", $item) && $this->value == $item) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Is the attribute either equal to a given string or start with that string followed by a hyphen (-)? More info:
     * http://api.jquery.com/attribute-contains-prefix-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isContainPrefixSelector($node) {
        if ($node->hasAttribute($this->attr_name)) {
            $attr = $node->getAttribute($this->attr_name);
            $len = strlen($this->value);
            if ($len > 0) {
                $pos = stripos($attr, $this->value);
                return $pos === 0 && (strlen($attr) <= $len || $attr[$len] == "-");
            }
        }
        return FALSE;
    }
    
    /**
     * Does the attribute start exactly with a given string? More info:
     * http://api.jquery.com/attribute-starts-with-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isStartWithSelector($node) {
        if ($node->hasAttribute($this->attr_name) && strlen($this->value) > 0) {
            return strpos($node->getAttribute($this->attr_name), $this->value) === 0;
        }
        return FALSE;
    }
    
    /**
     * Does the attribute end exactly with a given string?
     * The comparison is case sensitive. More info:
     * http://api.jquery.com/attribute-ends-with-selector/
     * 
     * @param DOMElement $node
     * @return boolean
     */
    private function isEndWithSelector($node) {
        if ($node->hasAttribute($this->attr_name)) {
            $len = strlen($this->value);
            if ($len > 0) {
                $attr = $node->getAttribute($this->attr_name);
                $attr_len = strlen($attr);
                return $len <= $attr_len && strpos($attr, $this->value, $attr_len - $len) !== FALSE;
            }
        }
        return FALSE;
    }
    
    private function isAttrSelector($node) {
        $ret = FALSE;
        
        if ($this->op == CSSAttrFilter::EQUAL_SELECTOR) {
            $ret = $this->isEqualSelector($node);
        } else
        if ($this->op == CSSAttrFilter::NOT_EQUAL_SELECTOR) {
            $ret = $this->isNotEqualSelector($node);
        } else
        if ($this->op == CSSAttrFilter::CONTAIN_SELECTOR) {
            $ret = $this->isContainSelector($node);
        } else
        if ($this->op == CSSAttrFilter::CONTAIN_WORD_SELECTOR) {
            $ret = $this->isContainWordSelector($node);
        } else
        if ($this->op == CSSAttrFilter::CONTAIN_PREFIX_SELECTOR) {
            $ret = $this->isContainPrefixSelector($node);
        } else
        if ($this->op == CSSAttrFilter::START_WITH_SELECTOR) {
            $ret = $this->isStartWithSelector($node);
        } else
        if ($this->op == CSSAttrFilter::END_WITH_SELECTOR) {
            $ret = $this->isEndWithSelector($node);
        }
        return $ret;
    }
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        return $ret = $this->isAttrSelector($node);
    }
}
