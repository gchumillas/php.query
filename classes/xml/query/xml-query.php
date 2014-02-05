<?php
/**
 * This file contains the XmlQuery class.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\xml\query;
use ArrayAccess;
use Closure;
use DomainException;
use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Countable;
use InvalidArgumentException;
use Iterator;
use Traversable;
use com\soloproyectos\core\arr\Arr;
use com\soloproyectos\core\css\parser\CssParser;
use com\soloproyectos\core\text\Text;
use com\soloproyectos\core\xml\dom\XmlDomHelper;
use com\soloproyectos\core\xml\query\exception\XmlQueryException;
use com\soloproyectos\core\xml\query\exception\XmlQueryExceptionInvalidDocument;

/**
 * Class XmlQuery.
 * 
 * <p>This class is used to manage XML documents. It can traverse an XML document as
 * well as manipulate it. The class uses CSS selectors to get information.</p>
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class XmlQuery implements Countable, Iterator, ArrayAccess
{
    /**
     * List of DOMNode objects.
     * @var array of DOMNode
     */
    private $_items;
    
    /**
     * Constructor.
     * 
     * <p>This function creates an instance. For example:</p>
     * <pre>// several ways to create an instance
     * $q = new XmlQuery("&lt;root&gt;well formed document&lt;/root&gt;");
     * $q = new XmlQuery("http://www.any-site.com/page");
     * $q = new XmlQuery("/home/john/myfile.xml");
     * $q = new XmlQuery($domNode);
     * $q = new XmlQuery($arrayOfDomNodes);
     * </code>
     * 
     * @param string|array|DOMNode|XmlQuery $source   Source (not required)
     * @param array                         $attrs    Attributes (not required)
     * @param string                        $text     Inner text (not required)
     * @param Callable                      $callback Callback function (not
     *                                                required)
     * 
     * @return void
     */
    public function __construct(
        $source = array(),
        $attrs = array(),
        $text = null,
        $callback = null
    ) {
        $this->items = array();
        
        // loads arguments. Some of these arguments are optional
        $args = Arr::fetch(
            func_get_args(),
            array(
                "source" => array(
                    "type" => "string|array|Traversable|DOMNode|DOMNodeList" .
                        "|com\soloproyectos\core\xml\query\XmlQuery",
                    "default" => array()
                ),
                "attrs" => array(
                    "type" => "array",
                    "default" => array()
                ),
                "text" => array(
                    "type" => "scalar",
                    "required" => false
                ),
                "callback" => array(
                    "type" => "function",
                    "required" => false,
                )
            )
        );
        
        if (is_string($source)) {
            call_user_func(array($this, "_loadFromString"), $args["source"]);
        } elseif ($source instanceof XmlQuery) {
            call_user_func(array($this, "_loadFromQuery"), $args["source"]);
        } elseif ($source instanceof DOMNode) {
            call_user_func(array($this, "_loadFromNode"), $args["source"]);
        } elseif (is_array($source)
            || $source instanceof Traversable
            || $source instanceof DOMNodeList
        ) {
            call_user_func(array($this, "_loadFromArray"), $args["source"]);
        } else {
            $type = is_object($source)? get_class($source) : gettype($source);
            throw new InvalidArgumentException(
                "The first parameter was expected to be " .
                "string|array|Traversable|DOMNode|DOMNodeList|XmlQuery, $type given"
            );
        }
        
        // sets the attributes
        foreach ($args["attrs"] as $name => $value) {
            $this->attr($name, $value);
        }
        
        // sets text
        if ($args["text"] !== null) {
            $this->text($args["text"]);
        }
        
        // calls the callback
        if ($args["callback"] !== null) {
            $args["callback"]($this);
        }
    }
    
    /**
     * Loads an XML document from a string.
     * 
     * <p>The source can be either a URL, filename, word or a well-formed XML
     * string.</p>
     * 
     * @param string $source URL, filename, word or well-formed XML document
     * 
     * @return void
     */
    private function _loadFromString($source)
    {
        // uses internal errors and preserves original status
        $errors = libxml_get_errors();
        $offset = count($errors);
        $useInternalErrors = libxml_use_internal_errors(true);
        
        // loads contents
        $contents = "";
        $mimetype = "text/xml";
        $charset = "ISO-8859-1";
        if ($this->_isUrl($source)) {
            list($contents, $mimetype, $charset) = $this->_getContentsFromUrl(
                $source
            );
        } elseif (is_file($source)) {
            $contents = file_get_contents($source);
        } elseif (preg_match("/^\w+$/i", $source)) {
            $contents = "<$source />";
        } else {
            $contents = $source;
        }
        
        // creates the document
        $doc = new DOMDocument("1.0", $charset);
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        if ($mimetype == "text/html") {
            // cleans and repares the string
            $tidy = tidy_parse_string($contents);
            tidy_clean_repair($tidy);
            $contents = "" . $tidy;
            
            $doc->loadHTML($contents);
        } else {
            $doc->loadXML($contents);
        }
        
        // gets errors and recovers original status
        $errors = array_slice(libxml_get_errors(), $offset);
        libxml_use_internal_errors($useInternalErrors);
        
        // if not success, prints a nice error
        if (count($errors) > 0) {
            $str = "";
            foreach ($errors as $error) {
                $message = trim($error->message);
                $str = Text::concat(
                    "\n",
                    $str,
                    "$message on line {$error->line}, column {$error->column}"
                );
            }
            
            throw new XmlQueryExceptionInvalidDocument($str);
        }
        
        // there is only one item: the root of the document
        $this->_items = array($doc->documentElement);
    }
    
    /**
     * Loads from a query instance
     * 
     * @param XmlQuery $query XmlQuery object
     * 
     * @return void
     */
    private function _loadFromQuery($query)
    {
        $this->_items = $query->_items;
    }
    
    /**
     * Loads from a DOM instance.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return void
     */
    private function _loadFromNode($node)
    {
        $this->_items = array($node);
    }
    
    /**
     * Loads from an array.
     * 
     * @param array|Traversable|DOMNodeList $items List of DOMNode objects
     * 
     * @return void
     */
    private function _loadFromArray($items)
    {
        $this->_items = array();
        foreach ($items as $item) {
            array_push($this->_items, $item);
        }
    }
    
    /**
     * Gets contents from a URL.
     * 
     * @param string $url URL
     * 
     * @return array returns contents, mimetype and charset
     */
    private function _getContentsFromUrl($url)
    {
        $mimetype = "text/xml";
        $charset = "ISO-8859-1";
        
        // loads the url contents and, optionally, the headers
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        $contents = curl_exec($ch);
        curl_close($ch);
        
        // tries to detect charset and mime-type from headers
        $headers = "";
        $separator = "\r\n\r\n";
        $pos = strpos($contents, $separator);
        
        if ($pos !== false) {
            $headers = substr($contents, 0, $pos);
            $contents = substr($contents, $pos + strlen($separator));
        }
        
        $lines = explode("\r\n", $headers);
        foreach ($lines as $line) {
            $regexp = '@Content-Type:\s*([\w/+]+)(;\s*charset=(\S+))?@i';
            if (preg_match($regexp, $line, $matches) > 0) {
                $mimetype = count($matches) > 1? $matches[1]: "";
                $charset = count($matches) > 3? $matches[3]: "";
                break;
            }
        }
        
        return array($contents, $mimetype, $charset);
    }
    
    /**
     * Gets node data.
     * 
     * @param DOMNode $node Node
     * @param string  $name Attribute name
     * 
     * @return mixed
     */
    private function _getData($node, $name)
    {
        $data = $node->hasAttribute("__data__")
            ? unserialize($node->getAttribute("__data__"))
            : array();
        return Arr::get($data, $name);
    }
    
    /**
     * Sets node data.
     * 
     * @param DOMNode $node  Node
     * @param string  $name  Attribute name
     * @param mixed   $value Value
     * 
     * @return void
     */
    private function _setData($node, $name, $value)
    {
        $data = $node->hasAttribute("__data__")
            ? unserialize($node->getAttribute("__data__"))
            : array();
        $data[$name] = $value;
        $node->setAttribute("__data__", serialize($data));
    }
    
    /**
     * Is an url?
     * 
     * @param string $str An arbitrary string
     * 
     * @return bool
     */
    private function _isUrl($str)
    {
        $regexp = '#^https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?#';
        return preg_match($regexp, $str) > 0;
    }
    
    /**
     * Loads an XML document.
     * 
     * @param string $source URL, filename or well formed document
     * 
     * @return void
     */
    public function load($source)
    {
        $this->_items = array();
        $this->_loadFromString($source);
    }
    
    /**
     * Gets the parents of the select nodes.
     * 
     * @return null|XmlQuery
     */
    public function parent()
    {
        $parents = array();
        
        foreach ($this->_items as $item) {
            $parent = $item->parentNode;
            if ($parent !== null
                && !$parent instanceof DOMDocument
                && XmlDomHelper::searchNode($parent, $parents) === false
            ) {
                array_push($parents, $item->parentNode);
            }
        }
        
        return new XmlQuery($parents);
    }
    
    /**
     * Gets or sets arbitrary data.
     * 
     * <p>This function gets or sets arbitrary data into the selected nodes. For
     * example:</p>
     * <pre>
     * $item = $xml->query("item[id = 1]");
     * $item->data("myArray", array(1, 2, 3)); // saves an array into the item(s)
     * print_r($item->data("myArray"));        // prints the array
     * </code>
     * 
     * @param string $name  Identifier
     * @param mixed  $value A value (not required)
     * 
     * @return mixed
     */
    public function data($name, $value = null)
    {
        $ret = $this;
        
        if (func_num_args() > 1) {
            foreach ($this->_items as $item) {
                $this->_setData($item, $name, $value);
            }
        } else {
            return count($this) > 0? $this->_getData($this[0], $name) : "";
        }
        
        return $ret;
    }
    
    /**
     * Gets nodes by CSS selectors.
     * 
     * <p>This function returns the nodes that satisfy a given CSS selector. For
     * example:</p>
     * <pre>// query example
     * $q = new XmlQuery("<root><item>one</item><item>two</ite></root>");
     * $items = $q->query("root &gt; item");
     * foreach ($items as $item) {
     *      echo "$item\n";
     * }
     * </code>
     * 
     * @param string $query CSS selector expression
     * 
     * @return XmlQuery
     */
    public function query($query)
    {
        $nodes = array();
        
        foreach ($this->_items as $item) {
            $parser = new CssParser($item);
            $items = $parser->query($query);
            $nodes = XmlDomHelper::mergeNodes($nodes, $items->getArrayCopy());
        }
        
        return new XmlQuery($nodes);
    }
    
    /**
     * Gets nodes by an XPath expression.
     * 
     * <p>This function is similar to XmlQuery::query, except that it uses
     * XPath expressions instead of CSS selectors.</p>
     * 
     * @param string $query XPath expression
     * 
     * @return XmlQuery
     */
    public function xpath($query)
    {
        $nodes = array();
        
        foreach ($this->_items as $item) {
            $doc = XmlDomHelper::getOwnerDocument($item);
            $xpath = new DOMXPath($doc);
            array_push($nodes, $xpath->query($query, $current));
        }
        
        return new XmlQuery($nodes);
    }
    
    /**
     * Deletes all child nodes.
     * 
     * <p>This method is similar to the jQuery 'empty' function.</p>
     * 
     * @return XmlQuery
     */
    public function clear()
    {
        foreach ($this->_items as $item) {
            XmlDomHelper::removeChildNodes($item);
        }
        
        return $this;
    }
    
    /**
     * Removes the selected nodes.
     * 
     * @return XmlQuery
     */
    public function remove()
    {
        foreach ($this->_items as $item) {
            $parent = $item->parentNode;
            
            if ($parent !== null) {
                $parent->removeChild($item);
            }
        }
        
        return $this;
    }
    
    /**
     * Appends a child node.
     * 
     * <p>This function inserts a child node at the end of the selected nodes.</p>
     * 
     * <p>Example 1:</p>
     * <pre>
     * // appends a new child node
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->append('&lt;item /&gt;');
     * echo $xml->html();
     * </pre>
     * 
     * <p>Example 2:</p>
     * <pre>
     * // sets attributes and inner texts
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->append(
     *      '&lt;item /&gt;',
     *      array("id" => "1", "title" => "Item title ..."),
     *      "Text here ..."
     *);
     * echo $xml->html();
     * </pre>
     * 
     * <p>Example 3:</p>
     * <pre>
     * // appends subitems to the inserted node
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->append(
     *      "&lt;item /&gt;",
     *      array("id" => "1", "title" => "Item 1"),
     *      function($target) {
     *          $target->append("node", "Child 1");
     *          $target->append("node", "Child 2");
     *      }
     * );
     * echo $xml->html();
     * </pre>
     * 
     * @param string|DOMElement|XmlQuery $source   Node
     * @param array                      $attrs    Attributes (default is null)
     * @param string                     $text     Text (default is null)
     * @param Closure                    $callback Callback (default is null)
     * 
     * @return XmlQuery
     */
    public function append($source, $attrs = null, $text = null, $callback = null)
    {
        $args = Arr::fetch(
            func_get_args(),
            array(
                "source" => "string|DOMNode|"
                    . "com\soloproyectos\core\xml\query\XmlQuery",
                "attrs" => array(
                    "type" => "array",
                    "default" => array()
                ),
                "text" => array(
                    "type" => "scalar",
                    "required" => false
                ),
                "callback" => array(
                    "type" => "function",
                    "required" => false
                )
            )
        );
        
        // gets HTML code
        $html = "";
        if ($args["source"] instanceof XmlQuery) {
            $html = "" . $args["source"];
        } elseif ($args["source"] instanceof DOMNode) {
            $html = XmlDomHelper::dom2str($args["source"]);
        } else {
            $html = trim($args["source"]);
        }
        
        if (preg_match("/^\w+$/", $html)) {
            $html = "<$html />";
        }
        
        foreach ($this->_items as $item) {
            $doc = XmlDomHelper::getOwnerDocument($item);
            $node = $doc->createDocumentFragment();
            $node->appendXML($html);
            $target = new XmlQuery($item->appendChild($node));
            
            // sets the attributes
            foreach ($args["attrs"] as $name => $value) {
                $target->attr($name, $value);
            }
            
            // sets text
            if ($args["text"] !== null) {
                $target->text($args["text"]);
            }
            
            // calls the callback
            if ($args["callback"] !== null) {
                $args["callback"]($target);
            }
        }
        
        return $this;
    }
    
    /**
     * Prepends a child node.
     * 
     * <p>This function inserts a child node at the beginning of the selected
     * nodes</p>
     * 
     * <p>Example 1:</p>
     * <pre>
     * // prepends a new child node
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->prepend('&lt;item /&gt;');
     * echo $xml->html();
     * </pre>
     * 
     * <p>Example 2:</p>
     * <pre>
     * // sets attributes and inner texts
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->prepends(
     *      '&lt;item /&gt;',
     *      array("id" => "1", "title" => "Item title ..."),
     *      "Text here ..."
     *);
     * echo $xml->html();
     * </pre>
     * 
     * <p>Example 3:</p>
     * <pre>
     * // appends subitems to the inserted node
     * $xml = new XmlQuery('&lt;root /&gt;');
     * $xml->prepend(
     *      "&lt;item /&gt;",
     *      array("id" => "1", "title" => "Item 1"),
     *      function($target) {
     *          $target->append("node", "Child 1");
     *          $target->append("node", "Child 2");
     *      }
     * );
     * echo $xml->html();
     * </pre>
     * 
     * @param string|DOMElement|XmlQuery $source   Node
     * @param array                      $attrs    Attributes (not required)
     * @param string                     $text     Inner texts (not required)
     * @param Closure                    $callback Callback function (not required)
     * 
     * @return XmlQuery
     */
    public function prepend($source, $attrs = null, $text = null, $callback = null)
    {
        $ret = null;
        $args = Arr::fetch(
            func_get_args(),
            array(
                "source" => "string|DOMNode|"
                    . "com\soloproyectos\core\xml\query\XmlQuery",
                "attrs" => array(
                    "type" => "array",
                    "default" => array()
                ),
                "text" => array(
                    "type" => "scalar",
                    "required" => false
                ),
                "callback" => array(
                    "type" => "function",
                    "required" => false
                )
            )
        );
        
        // gets HTML code
        $html = "";
        if ($args["source"] instanceof XmlQuery) {
            $html = "" . $args["source"];
        } elseif ($args["source"] instanceof DOMNode) {
            $html = XmlDomHelper::dom2str($args["source"]);
        } else {
            $html = trim($args["source"]);
        }
        
        if (preg_match("/^\w+$/", $html)) {
            $html = "<$html />";
        }
        
        foreach ($this->_items as $item) {
            $doc = XmlDomHelper::getOwnerDocument($item);
            $node = $doc->createDocumentFragment();
            $node->appendXML($html);
            $target = new XmlQuery(
                $item->insertBefore($node, $item->firstChild)
            );
            
            // sets the attributes
            foreach ($args["attrs"] as $name => $value) {
                $target->attr($name, $value);
            }
            
            // sets text
            if ($args["text"] !== null) {
                $target->text($args["text"]);
            }
            
            // calls the callback
            if ($args["callback"] !== null) {
                $args["callback"]($target);
            }
        }
        
        return $this;
    }

    /**
     * Gets the tag name.
     * 
     * @return string
     */
    public function name()
    {
        $ret = "";
        foreach ($this->_items as $item) {
            $ret = $item->nodeName;
            break;
        }
        return $ret;
    }

    /**
     * Gets or sets an attribute.
     * 
     * <p>Example 1:</p>
     * <pre>
     * // gets an attribute value
     * echo $node->attr('id');
     * </pre>
     * 
     * </p>Example 2:</p>
     * <pre>
     * // sets an attribute value
     * $node->attr('title', 'Node title...');
     * </pre>
     * 
     * <p>Example 3:</p>
     * <pre>
     * // chaining
     * $node->attr('id', 101)->attr('title', 'Node title...');
     * </pre>
     * 
     * @param string $name  Attribute name
     * @param string $value A value (not required)
     * 
     * @return string|XmlQuery
     */
    public function attr($name, $value = null)
    {
        $ret = $this;
        
        if (func_num_args() > 1) {
            foreach ($this->_items as $item) {
                try {
                    $item->setAttribute($name, $value);
                } catch (DOMException $e) {
                    throw new XmlQueryException("Invalid attribute name: $name");
                }
            }
        } else {
            $ret = count($this) > 0? $this[0]->getAttribute($name): "";
        }
        
        return $ret;
    }
    
    /**
     * Gets or sets inner texts.
     * 
     * <p>Example 1:</p>
     * <pre>
     * // gets inner texts
     * echo $item->text();
     * </pre>
     * 
     * <p>Example 2:</p>
     * <pre>
     * // sets inner texts
     * $item->text("This is a text...");
     * </pre>
     * 
     * <p>Example 3:</p>
     * <pre>
     * // chaining
     * $item->attr("id", 101)->text("This is a text...");
     * </pre>
     * 
     * @param string $value A value (default is null)
     * 
     * @return string|XmlQuery
     */
    public function text($value = null)
    {
        $ret = $this;
        
        if (func_num_args() > 0) {
            foreach ($this->_items as $item) {
                $item->nodeValue = $value;
            }
        } else {
            $ret = count($this) > 0? $this[0]->nodeValue: "";
        }
        
        return $ret;
    }
    
    /**
     * Gets or sets inner contents.
     * 
     * <p>Example 1:</p>
     * <pre>
     * // gets inner contents
     * echo $item->html();
     * </pre>
     * 
     * <p>Example 2:</p>
     * <pre>
     * // sets inner contents
     * $item->html('&lt;item id="101" title="Item title..." /&gt;');
     * </pre>
     * 
     * <p>Example 3:</p>
     * <pre>
     * // chaining
     * echo $item->html('&lt;item id="101" title="Item title..." /&gt;')->html();
     * </pre>
     * 
     * @param string $innerText XML code (not required)
     * 
     * @return string|XmlQuery
     */
    public function html($innerText = null)
    {
        $ret = $this;
        
        if (func_num_args() > 0) {
            foreach ($this->_items as $item) {
                $doc = XmlDomHelper::getOwnerDocument($item);
                $node = $doc->createDocumentFragment();
                $node->appendXML($innerText);
                
                XmlDomHelper::removeChildNodes($item);
                $item->appendChild($node);
            }
        } else {
            $ret = count($this) > 0? XmlDomHelper::getInnerHtml($this[0]): "";
        }
        
        return $ret;
    }
    
    /**
     * Gets or sets inner contents.
     * 
     * <p>This method is identical to XmlQuery::html.</p>
     * 
     * @param string $innerText XML code (not required)
     * 
     * @return string
     */
    public function xml($innerText)
    {
        return call_user_func_array(array($this, "html"), func_get_args());
    }
    
    /**
     * Is the current node the same as a given node?
     * 
     * @param XmlQuery $node Node
     * 
     * @return boolean
     */
    public function same($node)
    {
        return count($this) > 0
            && count($node) > 0
            && $this[0]->isSameNode($node[0]);
    }
    
    /**
     * Gets the string representation of the instance.
     * 
     * @return string
     */
    public function __toString()
    {
        $ret = "";
        foreach ($this->_items as $item) {
            $ret = Text::concat("\n", $ret, XmlDomHelper::dom2str($item));
        }
        return $ret;
    }
    
    /***************************
     * Iterator implementation *
     ***************************/

    /**
     * Gets the current node.
     * 
     * This function implements the Iterator::current() method.
     * 
     * @ignore
     * @return boolean|XmlQuery
     */
    public function current()
    {
        return ($item = current($this->_items))? new XmlQuery($item) : false;
    }

    /**
     * Advances to the next node.
     * 
     * This function implements the Iterator::next() method.
     * 
     * @ignore
     * @return XmlQuery
     */
    public function next()
    {
        return ($item = next($this->_items))? new XmlQuery($item) : false;
    }

    /**
     * Gets the internal pointer.
     * 
     * This function implements the Iterator::key() method.
     * 
     * @ignore
     * @return integer
     */
    public function key()
    {
        return key($this->_items);
    }

    /**
     * Rewinds the internal pointer.
     * 
     * This function implements the Iterator::rewind() method.
     * 
     * @ignore
     * @return void
     */
    public function rewind()
    {
        reset($this->_items);
    }

    /**
     * Is a valid internal position?
     * 
     * This function implements the Iterator::valid() method.
     * 
     * @ignore
     * @return boolean
     */
    public function valid()
    {
        return (key($this->_items) !== null);
    }
    
    /******************************
     * ArrayAccess implementation *
     ******************************/

    /**
     * Does the DOMNode exist at a given position?
     * 
     * This function implements the ArrayAccess::offsetExists() method.
     * 
     * @param integer $offset Node position
     * 
     * @ignore
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return Arr::exist($this->_items, $offset);
    }

    /**
     * Gets a node by a given position.
     * 
     * This function implements the ArrayAccess::offsetGet() method.
     * 
     * @param integer $offset Node position
     * 
     * @ignore
     * @return DOMNode
     */
    public function offsetGet($offset)
    {
        return $this->_items[$offset];
    }

    /**
     * Sets a node by a given position.
     * 
     * This function implements the ArrayAccess::offsetSet() method.
     * 
     * @param integer $offset Node position
     * @param DOMNode $value  A node object
     * 
     * @ignore
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_items[$offset] = $value;
    }

    /**
     * Unsets a node by a given position.
     * 
     * This function implements the ArrayAccess::offsetUnset() method.
     * 
     * @param integer $offset Node position
     * 
     * @ignore
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_items[$offset]);
    }
    
    /****************************
     * Countable implementation *
     ****************************/
     
     /**
      * Gets the number of nodes.
      * 
      * This function implements the Countable::count() method.
      * 
      * @ignore
      * @return integer
      */
    public function count()
    {
        return count($this->_items);
    }
}
