<?php
require_once dirname(__DIR__) . "/parser/parser.php";
require_once dirname(__DIR__) . "/css-parser/css-term.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-attr-filter.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-class-filter.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-id-filter.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-pseudo-first-child-filter.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-pseudo-nth-child-filter.php";

/**
 * BNF Grammar:
 * 
 * <expression>                ::= <term> {"," <term>}
 * <term>                      ::= <factor> {<factor>}
 * <factor>                    ::= <operator> <element> | <element>
 * <element>                   ::= ("*" | <identifier>) {<filter>}
 * <filter>                    ::= <class-filter> | <id-filter> | <attr->filter> | <pseudo-filter>
 * <class-filter>              ::= "." <identifier>
 * <id-filter>                 ::= "#" <identifier>
 * <attr-filter>               ::= "[" <identifier> [<attr-operator> <value>] "]"
 * <pseudo-filter>             ::= ":" (<pseudo-first-child-filter> | <pseudo-nth-child-filter>)
 * <pseudo-nth-child-filter>   ::= "nth-child" "(" <number> ")"
 * <pseudo-first-child-filter> ::= "first-child"
 * <identifier>                ::= ( "_" | <alphanum> ) { "_" | "-" | <alphanum> }
 * <attr-operator>             ::= "=" | "~="
 * <operator>                  ::= ">" | "+"
 * <value>                     ::= <quoted string> | <alphanum> {<alphanum>}
 */
 
class CSSParser extends Parser {
    /**
     * Identifier pattern.
     */
    const IDENTIFIER = "[_a-z0-9][_\-a-z0-9]*";
    
    /**
     * List of operators.
     * @var array of strings
     */
    private $operators = array(">", "+");
    
    /**
     * List of 'attribute operators'.
     * @var array of strings
     */
    private $attr_operators = array("=", "~=");
    
    /**
     * A node context.
     * @var DOMNode
     */
    private $node;
    
    /**
     * @param DOMNode $node
     * @param string $query CSS selector expression
     */
    public function __construct($node, $query) {
        $this->node = $node;
        parent::__construct($query);
    }
    
    /**
     * Is the node in array?
     * @param DOMNode $node
     * @param array(DOMNode, ...) $items
     * @return boolean
     */
    private function isNodeInArray($node, $items) {
        foreach ($items as $item) {
            if ($node->isSameNode($item)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Is the next an operator?
     * <operator> ::= ">" | "+"
     * 
     * @return FALSE|array(string)
     */
    protected function operator() {
        return $this->in($this->operators);
    }
    
    /**
     * Is the next an attribute operator?
     * <attr-operator> ::= "=" | "~="
     * 
     * @return FALSE|array(string)
     */
    protected function attrOperator() {
        return $this->in(CSSAttrFilter::getOperators());
    }
    
    /**
     * Is the next an identifier?
     * <identifier> ::= ( "_" | <alphanum> ) { "_" | "-" | <alphanum> }
     * 
     * @return FALSE|array(string)
     */
    protected function identifier() {
        if (list($id) = $this->match(CSSparser::IDENTIFIER)) {
            return array($id);
        }
        return FALSE;
    }
    
    /**
     * Is the next a value?
     * <value> ::= <a quoted string> | <a list of alphanumeric characters>
     * 
     * The following examples are values:
     * 'hello'
     * "hello\"man"
     * 'hello\'man'
     * 0015blah
     * _blah_
     * 
     * @return FALSE|array(string)
     */
    protected function value() {
        if ( !(list($value) = $this->str()) && !(list($value) = $this->match(CSSParser::IDENTIFIER)) ) {
            return array($value);
        }
        return array($value);
    }
    
    /**
     * Is the next a nth-child pseudo filter?
     * <pseudo-nth-child-filter> ::= "nth-child" "(" <number> ")"
     * 
     * @return FALSE|CSSPseudoNthChildFilter
     */
    protected function pseudoNthChildFilter() {
        $position = 0;
        
        if (!$this->eq("nth-child")) {
            return FALSE;
        }
        if (!$this->eq("(")) {
            throw new ParserException($this, "Invalid expression");
        }
        if (!list($position) = $this->number()) {
            throw new ParserException($this, "Invalid position");
        }
        if (!$this->eq(")")) {
            throw new ParserException($this, "Invalid expression");
        }
        return new CSSPseudoNthChildFilter(intval($position));
    }
    
    /**
     * Is the next a first-child pseudo filter?
     * <pseudo-first-child-filter> ::= "first-child"
     * 
     * @return FALSE|CSSPseudoFirstChildFilter
     */
    protected function pseudoFirstChildFilter() {
        if (!$this->eq("first-child")) {
            return FALSE;
        }
        
        return new CSSPseudoFirstChildFilter();
    }
    
    /**
     * Is the next a speudo filter?
     * <pseudo-filter> ::= ":" (<pseudo-first-child-filter> | <pseudo-nth-child-filter>)
     * 
     * @return FALSE|CSSPseudoFilter
     */
    protected function pseudoFilter() {
        $filter = NULL;
        
        if (!$this->match("/^\:/")) {
            return FALSE;
        }
        
        if (
            (!$filter = $this->is("pseudoFirstChildFilter")) &&
            (!$filter = $this->is("pseudoNthChildFilter"))
        ) {
            throw new ParserException($this, "Invalid pseudo filter");
        }
        return $filter;
    }
    
    /**
     * Is the next an attribute filter?
     * <attr-filter> ::= "[" <identifier> (<attr-operator> <value>) "]"
     * 
     * @return FALSE|CSSAttrFilter
     */
    protected function attrFilter() {
        $attr_name = "";
        $op = "";
        $value = "";
        
        if (!$this->match("/^\[/")) {
            return FALSE;
        }
        if (!list($attr_name) = $this->is("identifier")) {
            throw new ParserException($this, "Invalid identifier");
        }
        if (list($op) = $this->is("attrOperator")) {
            if (!list($value) = $this->is("value")) {
                throw new ParserException($this, "Invalid attribute operator");
            }
        }
        if (!$this->eq("]")) {
            throw new ParserException($this, "Invalid expression");
        }
        return new CSSAttrFilter($attr_name, $op, $value);
    }
    
    /**
     * Is the next an id filter?
     * <id-filter> ::= "#" <identifier>
     * 
     * @return FALSE|CSSIdFilter
     */
    protected function idFilter() {
        $id = "";
        
        if (!$this->match("/^\#/")) {
            return FALSE;
        }
        if (!list($id) = $this->is("identifier")) {
            throw new ParserException($this, "Invalid identifier");
        }
        return new CSSIdFilter($id);
    }
    
    /**
     * Is the next a class filter?
     * <class-filter> ::= "." <identifier>
     * 
     * @return FALSE|CSSClassFilter
     */
    protected function classFilter() {
        $class_name = "";
        
        if (!$this->match("/^\./")) {
            return FALSE;
        }
        if (!list($class_name) = $this->is("identifier")) {
            throw new ParserException($this, "Invalid identifier");
        }
        return new CSSClassFilter($class_name);
    }
    
    /**
     * Is the next a filter?
     * <filter> ::= <class-filter> | <id-filter> | <attr->filter> | <pseudo-filter>
     * 
     * @return FALSE|CSSFilter
     */
    protected function filter() {
        $filter = NULL;
        
        if (
            (!$filter = $this->is("classFilter")) &&
            (!$filter = $this->is("idFilter")) &&
            (!$filter = $this->is("attrFilter")) &&
            (!$filter = $this->is("pseudoFilter"))
        ) {
            return FALSE;
        }
        return $filter;
    }
    
    /**
     * Is the next an element?
     * <element> ::= ("*" | <identifier> | <filter>) {<filter>}
     * 
     * The following example is an element:
     * div.my-class[title = hello]:nth-child(1)
     * 
     * In the above example, 'div' is the tag name and the following strings are filters:
     * .my-class       // a class filter
     * [title = hello] // an attribute filter
     * :nth-child(1)   // a pseudo filter
     * 
     * @return FALSE|CSSElement
     */
    protected function element() {
        $element = NULL;
        $filter = NULL;
        $tag_name = "*";
        
        // ignores left spaces
        $this->match("\s+");
        
        if ( (list($name) = $this->eq("*")) || (list($name) = $this->is("identifier")) ) {;
            $tag_name = $name? $name : "*";
        } else
        if (!$filter = $this->is("filter")) {
            return FALSE;
        }
        
        $element = new CSSElement($tag_name);
        
        // first filter
        if ($filter) {
            $element->addFilter($filter);
        }
        
        // additional filters
        while ($filter = $this->is("filter")) {
            $element->addFilter($filter);
        }
        
        return $element;
    }
    
    /**
     * Is the next a factor?
     * <factor> ::= <operator> <element> | <element>
     * 
     * The following example is a factor:
     * > div.my-class[title = hello]
     * 
     * In the above example, ">" is an operator and "div.my-class[title = hello]" is an element.
     * 
     * @return FALSE|CSSFactor
     */
    protected function factor() {
        $op = "";
        
        if (list($op) = $this->is("operator")) {
            if (!$element = $this->is("element")) {
                throw new ParserException($this, "Invalid expression");
            }
        } else
        if (!$element = $this->is("element")) {
            return FALSE;
        }
        
        return new CSSFactor($op, $element);
    }
    
    /**
     * Is the next a term?
     * <term> ::= <factor> {<factor>}
     * 
     * The following example is a term:
     * div > div.class h2[title = 'main-title'] + h3
     * 
     * In the above example, the following strings are factors:
     * div
     * > div.class
     * h2[title = 'main-title']
     * + h3
     * 
     * @return FALSE|CSSTerm
     */
    protected function term() {
        $factor = NULL;
        
        // first factor
        if (!$factor = $this->is("factor")) {
            return FALSE;
        }
        $term = new CSSTerm();
        $term->addFactor($factor);
        
        // additional factors
        while ($factor = $this->is("factor")) {
            $term->addFactor($factor);
        }
        
        return $term;
    }
    
    /**
     * Is the next a css selector expression?
     * <expression> ::= <term> {"," <term>}
     * 
     * The following example is an expression:
     * div > div.class div#id, div > p, pre
     * 
     * In the above example, the following strings are terms:
     * div > div.class div
     * div > p
     * pre
     * 
     * @return array(DOMElement, ...)
     */
    protected function expression() {
        $nodes = array();
        do {
            if (!$term = $this->is("term")) {
                throw new ParserException($this, "Invalid expression");
            }
            $nodes = CSSHelper::mergeNodes($nodes, $term->filter($this->node));
        } while ($this->eq(","));
        return $nodes;
    }
    
    protected function _parse() {
        return $this->is("expression");
    }
}
