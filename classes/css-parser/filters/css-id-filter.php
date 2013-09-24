<?php
require_once dirname(dirname(__DIR__)) . "/css-parser/filters/css-filter.php";

class CSSIdFilter extends CSSFilter {
    /**
     * Identifier.
     * @var string
     */
    private $id;
    
    /**
     * @param string $id
     */
    public function __construct($id) {
        $this->id = $id;
    }
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        return trim($node->getAttribute("id")) == $this->id;
    }
}
