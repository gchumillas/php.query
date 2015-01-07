<?php
/**
 * This file contains the DomNode class.
 *
 * PHP Version 5.3
 *
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom\node;
use \DOMDocument;
use \DOMElement;
use \DOMXPath;
use com\soloproyectos\common\arr\ArrHelper;
use com\soloproyectos\common\css\parser\CssParser;
use com\soloproyectos\common\dom\DomHelper;
use com\soloproyectos\common\dom\node\exception\DomNodeException;
use com\soloproyectos\common\dom\node\DomNodeIterable;
use com\soloproyectos\common\dom\node\DomNodeAttributeCapable;
use com\soloproyectos\common\dom\node\DomNodeClassCapable;
use com\soloproyectos\common\dom\node\DomNodeCssCapable;
use com\soloproyectos\common\dom\node\DomNodeContentCapable;
use com\soloproyectos\common\dom\node\DomNodeDataCapable;
use com\soloproyectos\common\text\TextHelper;

/**
 * DomNode class.
 *
 * @package Dom\Node
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
class DomNode extends DomNodeIterable
{
    use DomNodeAttributeCapable;
    use DomNodeClassCapable;
    use DomNodeCssCapable;
    use DomNodeContentCapable;
    use DomNodeDataCapable;

    /**
     * Default chararacter set.
     * @var string
     */
    private $_defaultCharset = "ISO-8859-1";

    /**
     * Internal DOM document.
     * @var DOMDocument
     */
    protected $document;

    /**
     * Creates a node.
     *
     * Example 1:
     * ```php
     * // creates a simple node with two attributes and inner text
     * $item = new DomNode("item", array("id" => 101, "title" => "Title 101"), "Inner text here...");
     * echo $item;
     * ```
     *
     * Example 2:
     * ```php
     * // creates a complex node
     * // in this case we use a callback function to add complex structures into the node
     * $root = new DomNode("root", function ($target) {
     *     // adds three subnodes
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
     * ```
     * Example 3:
     * ```php
     * // if not specified, DomNode creates a default DOMDocument instance. But you can pass to the
     * // constructor a given document.
     * $doc = new DOMDocument("1.0", "utf-8");
     * $root = new DomNode($doc, "root", "Inner text...");
     * echo $doc->saveXML();
     * ```
     *
     * @param DOMDocument $document   DOM Document (not required)
     * @param string      $nodeName   Node name (not required)
     * @param array       $attributes List of attributes (not required)
     * @param string      $text       Inner text (not required)
     * @param string      $callback   Callback function (not required)
     */
    public function __construct(
        $document = null, $nodeName = null, $attributes = null, $text = null, $callback = null
    ) {
        $args = ArrHelper::fetch(
            func_get_args(),
            array(
                "document" => "\DOMDocument",
                "nodeName" => "string",
                "attributes" => "array",
                "text" => "scalar",
                "callback" => "function"
            )
        );

        // creates a document
        $this->document = $args["document"] === null
            ? new DOMDocument("1.0", $this->_defaultCharset)
            : $args["document"];
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;

        // creates a DOM element
        if ($args["nodeName"] !== null) {
            $elem = $this->document->createElement($args["nodeName"]);
            $this->document->appendChild($elem);
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
     * @param string      $str         Well formed document
     * @param string      $contentType Content Type (default is "text/xml")
     * @param DOMDocument $document    DOM Document (not required)
     *
     * @return DomNode
     */
    public static function createFromString($str, $contentType = "text/xml", $document = null)
    {
        if ($document == null) {
            $document = new DOMDocument("1.0");
            $document->preserveWhiteSpace = false;
            $document->formatOutput = true;
        }

        // use internal errors
        $useInternalErrors = libxml_use_internal_errors(true);

        if ($contentType == "text/html") {
            $document->loadHTML($str);
        } else {
            $document->loadXML($str);
        }

        // retrieves the errors
        $text = "";
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $message = trim($error->message);
            $text = TextHelper::concat(
                "\n", $text, "$message on line {$error->line}, column {$error->column}"
            );
        }
        libxml_clear_errors();

        // restores internal errors status
        libxml_use_internal_errors($useInternalErrors);

        if (!TextHelper::isEmpty($text)) {
            throw new DomNodeException($text);
        }

        $node = new static();
        $node->document = $document;
        $node->elements = array($document->documentElement);

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
        $node = new static();
        $node->document = $element->ownerDocument;
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
        $node = new static();
        $node->document = $document;
        $node->elements = array($document->documentElement);

        return $node;
    }

    /**
     * Creates an instance from a given DomNode object.
     *
     * @param DomNode $node Node
     *
     * @return DomNode
     */
    public static function createFromNode($node)
    {
        $instance = new static();
        $instance->document = $node->document();
        $instance->elements = $node->elements();
        return $instance;
    }

    /**
     * Gets the DOM document.
     *
     * @return DOMDocument
     */
    public function document()
    {
        return $this->document;
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
     * Gets the root node.
     *
     * @return DomNode
     */
    public function root()
    {
        return DomNode::createFromElement($this->document->documentElement);
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
            $elements = DomHelper::mergeNodes($elements, $items->getArrayCopy());
        }

        $node = new DomNode();
        $node->document = $this->document;
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

            $elements = DomHelper::mergeNodes($elements, $nodes);
        }

        $node = new DomNode();
        $node->document = $this->document;
        $node->elements = $elements;
        return $node;
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
