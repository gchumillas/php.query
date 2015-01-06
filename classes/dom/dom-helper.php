<?php
/**
 * This file contains the DomHelper class.
 *
 * PHP Version 5.3
 *
 * @category DOM
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom;
use \DOMDocument;
use \DOMElement;
use \DOMNode;

/**
 * DomHelper class.
 *
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class DomHelper
{
    /**
     * Escapes a text.
     *
     * For example:
     * ```php
     * echo DomHelper::escape("M & Em's"); // prints "M &amp;amp; Em's"
     * ```
     *
     * @param string $text A string
     *
     * @return string
     */
    public static function escape($text)
    {
        return htmlspecialchars($text);
    }

    /**
     * Gets a CDATA block.
     *
     * @param string $text Text
     *
     * @return string
     */
    public static function cdata($text)
    {
        // removes all non-printable characters, except the newline
        $text = preg_replace('/[^[:print:]\n]/', '', $text);

        $text = str_replace(array("<![", "]>"), array("&lt;![", "]&gt;"), $text);
        return "<![CDATA[$text]]>";
    }

    /**
     * Is a valid XML fragment?
     *
     * @param string      $xml XML document
     * @param DOMDocument $doc Document (not required)
     *
     * @return boolean
     */
    public static function isValidXmlFragment($xml, $doc = null)
    {
        if ($doc == null) {
            $doc = new DOMDocument("1.0", "ISO-8859-1");
        }

        $fragment = $doc->createDocumentFragment();
        @$fragment->appendXML($xml);
        $node = @$doc->appendChild($fragment);

        return $node !== false;
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

        // retrieves non-repeated elements
        for ($i = 0; $i < $len; $i++) {
            $item = $items[$i];
            $position = DomHelper::searchNode($item, $items, $i + 1);
            if ($position === false) {
                array_push($ret, $item);
            }
        }

        // sorts elements in order of appareance in the document
        usort(
            $ret,
            function ($node0, $node1) {
                $path0 = DomHelper::_getNodePath($node0);
                $path1 = DomHelper::_getNodePath($node1);
                $count0 = count($path0);
                $count1 = count($path1);
                $len = min($count0, $count1);

                for ($i = 0; $i < $len; $i++) {
                    if ($path0[$i] != $path1[$i]) {
                        return $path0[$i] > $path1[$i];
                    }
                }

                return $count0 > $count1;
            }
        );

        return $ret;
    }

    /**
     * Gets the node path.
     *
     * @param DOMNode $node Node
     *
     * @return array
     */
    private static function _getNodePath($node)
    {
        $ret = array();
        $doc = $node->ownerDocument;
        $parentNode = $node->parentNode;

        while ($parentNode !== null && !$doc->isSameNode($parentNode)) {
            // gets the sibling position
            $pos = 0;
            foreach ($parentNode->childNodes as $i => $childNode) {
                if ($childNode->isSameNode($node)) {
                    $pos = $i;
                    break;
                }
            }

            array_unshift($ret, $pos);
            $node = $parentNode;
            $parentNode = $node->parentNode;
        }

        return $ret;
    }
}
