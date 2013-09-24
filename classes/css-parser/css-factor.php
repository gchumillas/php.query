<?php

class CSSFactor {
    const DESCENDANT_OPERATOR = "";
    const CHILD_OPERATOR = ">";
    const ADJACENT_OPERATOR = "+";
    
    /**
     * Operator.
     * @var string
     */
    private $op;
    
    /**
     * Element.
     * @var CSSElement
     */
    private $element;
    
    /**
     * @param string $op
     * @param CSSElement $element
     */
    public function __construct($op, $element) {
        $this->op = $op;
        $this->element = $element;
    }
    
    /**
     * Gets the operator.
     * @return string
     */
    public function getOperator() {
        return $this->op;
    }
    
    /**
     * Gets the element.
     * @return CSSElement
     */
    public function getElement() {
        return $this->element;
    }
}
