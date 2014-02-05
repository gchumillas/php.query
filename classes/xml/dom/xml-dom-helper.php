<?php
/**
 * This file contains the XmlDomHelper class.
 * 
 * PHP Version 5.3
 * 
 * @category Xml
 * @package  XmlDom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\core\xml\dom;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Class XmlDomHelper.
 * 
 * This is a helper class.
 * 
 * @category Xml
 * @package  XmlDom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class XmlDomHelper
{
    
    /**
     * This is a library of static functions, not a class. It couldn't be
     * instantiated.
     * 
     * @return void
     */
    private function __construct()
    {
    }
    
    /**
     * Gets the previous sibling DOMElement object.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return null|DOMElement
     */
    public static function getPreviousSiblingElement($node)
    {
        do {
            $node = $node->previousSibling;
        } while ($node && !($node instanceof DOMElement));
        return $node;
    }
    
    /**
     * Gets the next sibling DOMElement object.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return null|DOMElement
     */
    public static function getNextSiblingElement($node)
    {
        do {
            $node = $node->nextSibling;
        } while ($node && !($node instanceof DOMElement));
        return $node;
    }
    
    /**
     * Gets child elements of a given node.
     * 
     * This function returns all subnodes that are instance of DOMElement. It
     * ignores the rest of the subnodes.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return array of DOMNode objects
     */
    public static function getChildElements($node)
    {
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
     * 
     * This function returns all subnodes that have a given tagname.
     * 
     * @param DOMElement $node    DOMElement object
     * @param string     $tagName Tag name
     * 
     * @return array of DOMElement objects
     */
    public static function getElementsByTagName($node, $tagName)
    {
        $ret = array();
        $nodes = $node->getElementsByTagName($tagName);
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                array_push($ret, $node);
            }
        }
        return $ret;
    }
    
    /**
     * Searches a node in a list.
     * 
     * This function may return false, if the node was not found.
     * 
     * @param DOMNode $node   DOMNode object
     * @param array   $items  List of DOMNode objects
     * @param integer $offset Offset (default is 0)
     * 
     * @return false|integer
     */
    public static function searchNode($node, $items, $offset = 0)
    {
        $len = count($items);
        for ($i = $offset; $i < $len; $i++) {
            $item = $items[$i];
            if ($item->isSameNode($node)) {
                return $i;
            }
        }
        return false;
    }
    
    /**
     * Merges two lists of nodes in a single list.
     * 
     * This function merges two list of nodes in a single list without repeating
     * nodes.
     * 
     * @param array $items1 List of DOMNode objects
     * @param array $items2 List of DOMNode objects
     * 
     * @return array of DOMNode objects
     */
    public static function mergeNodes($items1, $items2)
    {
        $ret = array();
        $items = array_merge($items1, $items2);
        $len = count($items);
        
        for ($i = 0; $i < $len; $i++) {
            $item = $items[$i];
            $position = XmlDomHelper::searchNode($item, $items, $i + 1);
            if ($position === false) {
                array_push($ret, $item);
            }
        }
        
        return $ret;
    }
    
    /**
     * Gets the owner document of a node.
     * 
     * <p>If the node is an instance of DOMDocument, this function returns the node
     * itself.</p>
     * 
     * @param DOMNode $node Node
     * 
     * @return DOMDocument
     */
    public static function getOwnerDocument($node)
    {
        return $node instanceof DOMDocument? $node : $node->ownerDocument;
    }
    
    /**
     * Removes all child nodes.
     * 
     * @param DOMNode $node Node
     * 
     * @return void
     */
    public static function removeChildNodes($node)
    {
        while ($node->hasChildNodes()) {
            $node->removeChild($node->firstChild);
        }
    }
    
    /**
     * Gets inner HTML of a node
     * 
     * @param DOMNode $node node
     * 
     * @return string
     */
    public static function getInnerHtml($node)
    {
        $ret = "";
        $items = $node->childNodes;
        
        foreach ($items as $item) {
            $ret .= XmlDomHelper::dom2str($item);
        }
        
        return $ret;
    }
    
    /**
     * Gets the string representation of a node.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return string
     */
    public static function dom2str($node)
    {
        $doc = $node instanceof DOMDocument? $node : $node->ownerDocument;
        return $doc->saveXML($node);
    }
}
