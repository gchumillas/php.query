<?php

class CSSHelper {
    
    /**
     * Gets the previous sibling DOMElement object.
     * @param DOMNode $node
     * @return NULL|DOMElement
     */
    public static function getPreviousSiblingElement($node) {
        do {
            $node = $node->previousSibling;
        } while ($node && !($node instanceof DOMElement));
        return $node;
    }
    
    /**
     * Gets the next sibling DOMElement object.
     * @param DOMNode $node
     * @return NULL|DOMElement
     */
    public static function getNextSiblingElement($node) {
        do {
            $node = $node->nextSibling;
        } while ($node && !($node instanceof DOMElement));
        return $node;
    }
    
    public static function getChildElements($node) {
        $ret = array();
        $nodes = $node->childNodes;
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                array_push($ret, $node);
            }
        }
        return $ret;
    }
    
    /**
     * Gets elements by tagname.
     * @param DOMElement $node
     * @param string $tag_name
     * @return array(DOMElement, ...)
     */
    public static function getElementsByTagName($node, $tag_name) {
        $ret = array();
        $nodes = $node->getElementsByTagName($tag_name);
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                array_push($ret, $node);
            }
        }
        return $ret;
    }
    
    /**
     * Is the node in the given list?
     * @param DOMNode $node
     * @param array(DOMNode, ...) $items
     * @return boolean
     */
    public static function isNodeInList($node, $items, $offset = 0) {
        $len = count($items);
        for ($i = $offset; $i < $len; $i++) {
            $item = $items[$i];
            if ($item->isSameNode($node)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Merge two lists of nodes without repeating nodes.
     * @param array(DOMNode, ...) $nodes1
     * @param array(DOMNode, ...) $nodes2
     * @return array(DOMNode, ...)
     */
    public static function mergeNodes($items1, $items2) {
        $ret = array();
        $items = array_merge($items1, $items2);
        $len = count($items);
        
        for ($i = 0; $i < $len; $i++) {
            $item = $items[$i];
            if (!CSSHelper::isNodeInList($item, $items, $i + 1)) {
                array_push($ret, $item);
            }
        }
        return $ret;
    }
    
    /**
     * Gets the string representation of a DOMNode object.
     * @param DOMNode $node
     * @return string
     */
    public static function dom2str($node) {
        $doc = $node instanceof DOMDocument? $node : $node->ownerDocument;
        return $doc->saveXML($node);
    }
    
    /**
     * Gets the nodes from a css selector expression.
     * This function simplifies the use of CSSParser.
     * @param DOMNode $node
     * @param string $query A CSS selector expression.
     * @return array(DOMElement, ...)
     */
    public static function select($node, $query) {
        $nodes = array();
        $p = new CSSParser($node, $query);
        $nodes = $p->parse();
        return $nodes;
    }
}
