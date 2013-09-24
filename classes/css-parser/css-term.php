<?php
require_once dirname(__DIR__) . "/css-parser/css-element.php";
require_once dirname(__DIR__) . "/css-parser/css-factor.php";
require_once dirname(__DIR__) . "/css-parser/css-helper.php";
require_once dirname(__DIR__) . "/css-parser/filters/css-filter.php";

class CSSTerm {
    /**
     * List of factors.
     * @var array(CSSFactor, ...)
     */
    private $factors = array();
    
    /**
     * Adds a factor.
     * @param CSSFactor $factor
     */
    public function addFactor($factor) {
        array_push($this->factors, $factor);
    }
    
    /**
     * Filters the children of a given node.
     * @param DOMNode $node
     * @return array(DOMElement, ...)
     */
    public function filter($node) {
        $ret = array();
        $items = array($node);
        foreach ($this->factors as $factor) {
            $ret = $this->getNodesFromListByFactor($items, $factor);
            $items = $ret;
        }
        return $ret;
    }
    
    /**
     * Searches nodes from a list by a given factor.
     * @param array(DOMNode, ...) $nodes
     * @param CSSFactor, $factor
     * @return array(DOMElement, ...)
     */
    private function getNodesFromListByFactor($nodes, $factor) {
        $ret = array();
        foreach ($nodes as $node) {
            $items = $this->getNodesByFactor($node, $factor);
            $ret = CSSHelper::mergeNodes($ret, $items);
        }
        return $ret;
    }
    
    /**
     * Searches nodes by a given factor.
     * @param DOMNode $node
     * @param CSSFactor $factor
     * @return array(DOMElement, ...)
     */
    private function getNodesByFactor($node, $factor) {
        $ret = array();
        $element = $factor->getElement();
        $op = $factor->getOperator();
        
        if ($op == CSSFactor::DESCENDANT_OPERATOR) {
            $ret = $this->getDescendantNodesByElement($node, $element);
        } else
        if ($op == CSSFactor::ADJACENT_OPERATOR) {
            $ret = $this->getAdjacentNodesByElement($node, $element);
        } else
        if ($op == CSSFactor::CHILD_OPERATOR) {
            $ret = $this->getChildNodesByElement($node, $element);
        }
        return $ret;
    }
    
    /**
     * Searches descendant nodes by a given element.
     * @param DOMNode $node
     * @param CSSElement $element
     * @return array(DOMElement, ...)
     */
    private function getDescendantNodesByElement($node, $element) {
        $ret = array();
        $items = CSSHelper::getElementsByTagName($node, $element->getTagName());
        foreach ($items as $item) {
            if ($element->match($item)) {
                array_push($ret, $item);
            }
        }
        return $ret;
    }
    
    /**
     * Searches adjacent nodes by a given element.
     * @param DOMNode $node
     * @param CSSElement $element
     * @return array(DOMElement, ...)
     */
    private function getAdjacentNodesByElement($node, $element) {
        $ret = array();
        $item = CSSHelper::getNextSiblingElement($node);
        if ($item && $element->match($item)) {
            $ret = array($item);
        }
        return $ret;
    }
    
    /**
     * Searches child nodes by a given element.
     * @param DOMNode $node
     * @param CSSElement $elem
     * @return array(DOMElement, ...)
     */
    private function getChildNodesByElement($node, $element) {
        $ret = array();
        $items = CSSHelper::getChildElements($node);
        foreach ($items as $item) {
            if ($element->match($item)) {
                array_push($ret, $item);
            }
        }
        return $ret;
    }
}
