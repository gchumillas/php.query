<?php
/**
 * This file contains the DomNode class.
 * 
 * PHP Version 5.3
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom;
use \DOMDocument;
use \DOMElement;
use \DOMXPath;
use com\soloproyectos\common\arr\ArrHelper;
use com\soloproyectos\common\css\parser\CssParser;
use com\soloproyectos\common\dom\DomHelper;
use com\soloproyectos\common\dom\DomNodeIterable;
use com\soloproyectos\common\dom\DomNodeAttributeCapable;
use com\soloproyectos\common\dom\DomNodeClassCapable;
use com\soloproyectos\common\dom\DomNodeCssCapable;
use com\soloproyectos\common\dom\DomNodeContentCapable;
use com\soloproyectos\common\dom\DomNodeDataCapable;
use com\soloproyectos\common\text\TextHelper;

/**
 * DomNode class.
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class DomNode extends DomNodeIterable
{
    use DomNodeAttributeCapable;
    use DomNodeClassCapable;
    use DomNodeCssCapable;
    use DomNodeContentCapable;
    use DomNodeDataCapable;
    
    /**
     * Creates a node.
     * 
     * Examples:
     * 
     * // creates a simple node with two attributes and inner text
     * $item = new DomNode("item", array("id" => 101, "title" => "Title 101"), "Inner text here...");
     * echo $item;
     * 
     * // creates a complex node
     * // in this case we use a callback function to add complex structures into the node
     * $root = new DomNode("root", function ($target) {
     * // adds three subnodes
     *     for ($i = 0; $i < 3; $i++) {
     *         $target->append(
     *              new DomNode("item", array("id" => $i, "title" => "Title $i"), "This is the item $i")
     *         );
     *     }
     * 
     *     // appends some XML code
     *     $target->append("<text>This text is added to the end.</text>");
     *     
     *     // prepends some XML code
     *     $target->prepend("<text>This text is added to the beginning</text>");
     * });
     * echo $root;
     * 
     * @param string $nodeName   Node name (not required)
     * @param string $document   Document context (not required)
     * @param array  $attributes List of attributes (not required)
     * @param string $text       Inner text (not required)
     * @param string $callback   Callback function (not required)
     */
    public function __construct(
        $nodeName = null, $document = null, $attributes = null, $text = null, $callback = null
    ) {
        $args = ArrHelper::fetch(
            func_get_args(),
            array(
                "nodeName" => "string",
                "document" => "boolean",
                "attributes" => "array",
                "text" => "string",
                "callback" => "function"
            )
        );
        
        // creates a DOM element
        if ($args["nodeName"] !== null) {
            // creates and adds an element
            $doc = $args["document"] === null? new DOMDocument("1.0", "ISO-8859-1"): $args["document"];
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            $elem = $doc->createElement($args["nodeName"]);
            array_push($this->elements, $elem);
        }
        
        // sets attributes
        if ($args["attributes"] !== null) {
            foreach ($args["attributes"] as $name => $value) {
                $this->attr($name, $value);
            }
        }
        
        // sets inner text
        if ($args["text"] !== null) {
            $this->text($args["text"]);
        }
        
        // calls callback function
        if ($args["callback"] !== null) {
            $args["callback"]($this);
        }
    }
    
    /**
     * Creates an instance from a given string.
     * 
     * @param string $str         Well formed document
     * @param string $contentType Content Type (not required, default is "text/xml")
     * @param string $charset     Charset (not required, default is "ISO-8859-1")
     * 
     * @return DomNode
     */
    public static function createFromString($str, $contentType = "text/xml", $charset = "ISO-8859-1")
    {
        $doc = new DOMDocument("1.0", $charset);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        
        if ($contentType == "text/html") {
            $doc->loadHTML($str);
        } else {
            $doc->loadXML($str);
        }
        
        $node = new DomNode();
        $node->elements = array($doc->documentElement);
        
        return $node;
    }
    
    /**
     * Creates an instance from a given DOM element.
     * 
     * @param DOMElement $element DOM element
     * 
     * @return DomNode
     */
    public static function createFromElement($element)
    {
        $node = new DomNode();
        $node->elements = array($element);
        
        return $node;
    }
    
    /**
     * Creates an instance from a given DOM document.
     * 
     * @param DOMDocument $document DOM document
     * 
     * @return DomNode
     */
    public static function createFromDocument($document)
    {
        $node = new DomNode();
        $node->elements = array($document->documentElement);
        
        return $node;
    }
    
    /**
     * Gets the list of DOM elements.
     * 
     * A DomNode node can represent zero, one or more elements. This function returns
     * the internal DOM elements.
     * 
     * @return array of DOMElement
     */
    public function elements()
    {
        return $this->elements;
    }
    
    /**
     * Gets the node name.
     * 
     * @return string
     */
    public function name()
    {
        foreach ($this->elements as $element) {
            return $element->nodeName;
        }
        
        return "";
    }
    
    /**
     * Gets the parent of the node.
     * 
     * This function returns a `null` value if the node has no parent.
     * 
     * @return DomNode|null
     */
    public function parent()
    {
        foreach ($this->elements as $element) {
            // searches the first parent which is instance of DOMElement
            do {
                $element = $element->parentNode;
            } while ($element != null && !($element instanceof DOMElement));
            
            if ($element != null) {
                return DomNode::createFromElement($element);
            }
            
            break;
        }
        
        return null;
    }
    
    /**
     * Removes the node from the document.
     * 
     * @return DomNode
     */
    public function remove()
    {
        foreach ($this->elements as $element) {
            $parent = $element->parentNode;
            
            if ($parent !== null) {
                $parent->removeChild($element);
            }
        }
        
        return $this;
    }
    
    /**
     * Finds nodes.
     * 
     * @param string $cssSelectors List of css selector separated by commas
     * 
     * @return DomNode
     */
    public function query($cssSelectors)
    {
        $elements = array();
        
        foreach ($this->elements as $element) {
            $parser = new CssParser($element);
            $items = $parser->query($cssSelectors);
            $elements = $this->_mergeElements($elements, $items->getArrayCopy());
        }
        
        $node = new DomNode();
        $node->elements = $elements;
        return $node;
    }
    
    /**
     * Finds nodes.
     * 
     * This function is identical to 'query' except it uses XPath expressions, instead of
     * CSS selectors.
     * 
     * @param string $expression XPath expression
     * 
     * @return DomNode
     */
    public function xpath($expression)
    {
        $elements = array();
        
        foreach ($this->elements as $element) {
            $xpath = new DOMXPath($element->ownerDocument);
            $items = $xpath->query($expression, $element);
            
            // converts DOMNodeList to array
            $nodes = array();
            foreach ($items as $item) {
                array_push($nodes, $item);
            }
            
            $elements = $this->_mergeElements($elements, $nodes);
        }
        
        $node = new DomNode();
        $node->elements = $elements;
        return $node;
    }
    
    /**
     * Merges two lists with no duplicate elements.
     * 
     * @param array $elements1 Array of DOMElement
     * @param array $elements2 Array of DOMElement
     * 
     * @return array of DOMElement
     */
    private function _mergeElements($elements1, $elements2)
    {
        $elements = array_filter(
            $elements2,
            function ($element2) use ($elements1) {
                foreach ($elements1 as $element1) {
                    if ($element1->isSameNode($element2)) {
                        return false;
                    }
                }
                return true;
            }
        );
        
        return array_merge($elements1, $elements);
    }
    
    /**
     * Gets a string representation of the node.
     * 
     * @return string
     */
    public function __toString()
    {
        $ret = "";
        
        foreach ($this->elements as $element) {
            $ret = TextHelper::concat("\n", $ret, DomHelper::dom2str($element));
        }
        
        return $ret;
    }
}
