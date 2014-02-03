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
use com\soloproyectos\core\xml\query\exception\XmlQueryException;
use com\soloproyectos\core\xml\query\exception\XmlQueryExceptionInvalidDocument;

/**
 * Class XmlQuery.
 * 
 * This class is used to manage XML documents. It can traverse an XML document as
 * well as manipulate it. The class uses CSS selectors to get information.
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
     * List of DOMNode elements.
     * @var array of DOMNode
     */
    private $_items;
    
    /**
     * DOMDocument object.
     * @var DOMDocument
     */
    private $_doc;
    
    /**
     * Constructor.
     * 
     * <p>This function creates an instance. For example:</p>
     * <code>// several ways to create an instance
     * $q = new XmlQuery("<root>well formed document</root>");
     * $q = new XmlQuery("http://www.any-site.com/page", "text/html", "UTF-8");
     * $q = new XmlQuery("http://www.another-site.com");
     * $q = new XmlQuery("/home/john/myfile.xml");
     * </code>
     * 
     * @param mixed  $source     Source (default is array())
     * @param string $mimetype   Mime-type (not required, ignored if the source is
     *                           not a filename or URL)
     * @param string $charset    Charset (not required, ignored if the source is not
     *                           a file or URL)
     * @param array  $attrs      Attributes (not required)
     * @param Callable $callback Callback function (not required)
     * 
     * @return void
     */
    public function __construct(
        $source = array(),
        $mimetype = null,
        $charset = null,
        $attrs = array(),
        $callback = null) {
        $this->items = array();
        
        // loads arguments. Some of these arguments are optional
        $args = Arr::fetch(func_get_args(), array(
            "source" => array(
                "type" => "string|array|Traversable|DOMNode|DOMNodeList" .
                    "|com\soloproyectos\core\xml\query\XmlQuery",
                "default" => array()
            ),
            "mimetype" => array(
                "type" => "string",
                "default" => null
            ),
            "charset" => array(
                "type" => "string",
                "default" => null
            ),
            "attrs" => array(
                "type" => "array",
                "default" => array()
            ),
            "callback" => array(
                "type" => "function",
                "required" => false,
            )
        ));
        
        if (is_string($source)) {
            call_user_func(
                array($this, "_loadFromString"),
                $args["source"],
                $args["mimetype"],
                $args["charset"]
            );
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
     * @param string $source   URL, filename, word or well-formed XML document
     * @param string $mimetype Mime-type (not required, automatically detected)
     * @param string $charset  Charset (not required, automatically detected)
     * 
     * @return void
     */
    private function _loadFromString($source, $mimetype = "", $charset = "")
    {
        // uses internal errors and preserves original status
        $errors = libxml_get_errors();
        $offset = count($errors);
        $useInternalErrors = libxml_use_internal_errors(true);
        
        // loads contents
        $contents = "";
        if ($this->_isUrl($source)) {
            list($contents, $mimetype, $charset) = $this->_getContentsFromUrl(
                $source, $mimetype, $charset
            );
        } elseif (is_file($source)) {
            $contents = file_get_contents($source);
        } elseif (preg_match("/^\w+$/i", $source)) {
            $contents = "<$source />";
        } else {
            $contents = $source;
        }
        
        // default mime-type
        if (strlen($mimetype) == 0) {
            $mimetype = "text/xml";
        }
        
        // default charset
        if (strlen($charset) == 0) {
            $charset = "ISO-8859-1";
        }
        
        // creates the document
        $this->_doc = new DOMDocument("1.0", $charset);
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->formatOutput = true;
        if ($mimetype == "text/html") {
            // cleans and repares the string
            $tidy = tidy_parse_string($contents);
            tidy_clean_repair($tidy);
            $contents = "" . $tidy;
            
            $this->_doc->loadHTML($contents);
        } else {
            $this->_doc->loadXML($contents);
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
        $this->_items = array($this->_doc->documentElement);
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
        $this->_doc = $query->_doc;
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
        $this->_doc = $this->_ownerDocument($node);
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
            $this->_doc = $this->_ownerDocument($item);
        }
    }
    
    /**
     * Gets contents from a URL.
     * 
     * @param string $url      URL
     * @param string $mimetype Mime-type
     * @param string $charset  Charset
     * 
     * @return array returns contents, mimetype and charset
     */
    private function _getContentsFromUrl($url, $mimetype = "", $charset = "")
    {
        // loads the url contents and, optionally, the headers
        $loadHeaders = (strlen($charset) == 0) || (strlen($mimetype) == 0);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $loadHeaders);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        $contents = curl_exec($ch);
        curl_close($ch);
        
        // tries to detect charset and mime-type from headers
        if ($loadHeaders) {
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
                    if (strlen($mimetype) == 0) {
                        $mimetype = count($matches) > 1? $matches[1]: "";
                    }
                    if (strlen($charset) == 0) {
                        $charset = count($matches) > 3? $matches[3]: "";
                    }
                }
            }
        }
        
        return array($contents, $mimetype, $charset);
    }
    
    /**
     * Escapes a CSS attribute.
     * 
     * <p>Escapes a string to be used as CSS attribute. For example:</p>
     * 
     * <code>// escape example
     * $item = $query->query("item[name=" . XmlQuery::escape("a.b?c") . "]");
     * </code>
     * 
     * @param string $str A string
     * 
     * @return string
     */
    public static function escape($str)
    {
        $metaChars = '!"#$%&\'()*+,./:;<=>?@[\]^`{|}~';
        $regex = '/[' . preg_quote($metaChars, "/") . ']/';
        return preg_replace($regex, "\\\\$0", $str);
    }
    
    /**
     * Gets the owner document of a given node.
     * 
     * @param DOMNode $node Node object
     * 
     * @return DOMDocument
     */
    private function _ownerDocument($node)
    {
        return $node instanceof DOMDocument? $node : $node->ownerDocument;
    }
    
    /**
     * Loads an HTML document.
     * 
     * @param string $source  An HTML document
     * @param string $charset Document charset, autodetected (default is null)
     * 
     * @return void
     */
    public function loadHTML($source, $charset = null)
    {
        $this->_loadFromString($source, "text/html", $charset);
    }
    
    /**
     * Loads an XML document.
     * 
     * @param string $source  An XML docuemnt
     * @param string $charset Document charset, autodetected (default is null)
     * 
     * @return void
     */
    public function loadXML($source, $charset = null)
    {
        $this->_loadFromString($source, "text/xml", $charset);
    }
    
    /**
     * Gets the line number of the current node.
     * 
     * @return integer
     */
    public function line()
    {
        $ret = 0;
        $current = current($this->_items);
        if ($current !== false) {
            $ret = $current->getLineNo();
        }
        return $ret;
    }
    
    /**
     * Gets the filename of the document.
     * 
     * @return string
     */
    public function filename()
    {
        return $this->_doc != null? realpath($this->_doc->documentURI) : "";
    }
    
    /**
     * Gets node XPath of the current node.
     * 
     * @return string XPath expression
     */
    public function getXPath()
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            $ret = $current->getNodePath();
        }
        return $ret;
    }
    
    /**
     * Gets the parent of the current node.
     * 
     * This function may return null if the current node has no parent.
     * 
     * @return null|XmlQuery
     */
    public function parent()
    {
        $ret = null;
        $current = current($this->_items);
        if ($current !== false && !($current->parentNode instanceof DOMDocument)) {
            $ret = new XmlQuery($current->parentNode);
        }
        return $ret;
    }
    
    /**
     * Stores or gets arbitrary data.
     * 
     * <p>This function stores or gets arbitrary data into the current node. For
     * example:</p>
     * <code>// data example
     * $q->data("my-data", array(1, 2, 3)); // saves an array into the current node
     * print_r($q->data("my-data"));        // prints an array
     * </code>
     * 
     * @param string $name  Identifier
     * @param mixed  $value A value (default is null)
     * 
     * @return mixed
     */
    public function data($name, $value = null)
    {
        $ret = null;
        $current = current($this->_items);
        if ($current !== false) {
            $data = $current->hasAttribute("__data__")
                ? unserialize($current->getAttribute("__data__"))
                : array();
            if (func_num_args() > 1) {
                $data[$name] = $value;
                $current->setAttribute("__data__", serialize($data));
            }
            $ret = isset($data[$name])? $data[$name] : "";
        }
        return $ret;
    }
    
    /**
     * Gets nodes by a given CSS selector.
     * 
     * <p>This function returns the nodes that satisfy a given CSS selector. For
     * example:</p>
     * 
     * <code>// query example
     * $q = new XmlQuery("<root><item>one</item><item>two</ite></root>");
     * $items = $q->query("root > item");
     * foreach ($items as $item) {
     *     echo $item->text() . "\n";
     * }
     * </code>
     * 
     * @param string $query CSS selector expression
     * 
     * @return XmlQuery
     */
    public function query($query)
    {
        $ret = null;
        $current = current($this->_items);
        if ($current === false) {
            $ret = new XmlQuery();
        } else {
            $parser = new CssParser($current);
            $ret = new XmlQuery($parser->query($query));
        }
        return $ret;
    }
    
    /**
     * Gets nodes by a given xpath expression.
     * 
     * <p>This function is similar to 'query', except that it uses XPath expressions
     * instead of CSS selectors.</p>
     * 
     * @param string $query XPath expression
     * 
     * @return XmlQuery
     */
    public function xpath($query)
    {
        $ret = null;
        $current = current($this->_items);
        if ($current === false) {
            $ret = new XmlQuery();
        } else {
            $xpath = new DOMXPath($current->ownerDocument);
            $ret = new XmlQuery($xpath->query($query, $current));
        }
        return $ret;
    }
    
    /**
     * Deletes all child nodes.
     * 
     * This method is similar to the 'empty' jQuery function. But in our case,
     * 'empty' is a reserved PHP word, so I decided to use 'deleteAll' instead.
     * 
     * @return XmlQuery
     */
    public function clear()
    {
        $current = current($this->_items);
        if ($current !== false) {
            while ($current->hasChildNodes()) {
                $current->removeChild($current->firstChild);
            }
        }
        return $this;
    }
    
    /**
     * Removes the elements.
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
     * <p>This function inserts a new child node at the end of the current
     * element.</p>
     * 
     * <p>Example 1:</p>
     * <code>// Adding a new node
     * // you can write '&lt;root /&gt;' or simply 'root'
     * $xml = new XmlQuery('root');
     * $xml->append('item');
     * echo $xml->html();
     * </code>
     * 
     * <p>Example 2:</p>
     * <code>// Settings attributes and inner text
     * $xml = new XmlQuery('root');
     * $xml->append('item', array("id" => "1", "title" => "Item 1"), "Title here");
     * echo $xml->html();
     * </code>
     * 
     * <p>Example 3:</p>
     * <code>// Adding subitems to the current item
     * $xml = new XmlQuery('root');
     * $xml->append(
     *      "item",
     *      array("id" => "1", "title" => "Item 1"),
     *      function($target) {
     *          $target->append("node", "Child 1");
     *          $target->append("node", "Child 2");
     *      }
     * );
     * echo $xml->html();
     * </code>
     * 
     * @param XmlQuery|string $source   Node
     * @param array           $attrs    Attributes (default is null)
     * @param string          $text     Text (default is null)
     * @param Closure         $callback Callback function (default is null)
     * 
     * @return XmlQuery
     */
    public function append($source, $attrs = null, $text = null, $callback = null)
    {
        $ret = null;
        $current = current($this->_items);
        $args = Arr::fetch(func_get_args(), array(
            "source" => "string|com\soloproyectos\core\xml\query\XmlQuery",
            "attrs" => array(
                "type" => "array",
                "default" => array()
            ),
            "callback" => array(
                "type" => "function",
                "required" => false
            )
        ));
        
        if ($current !== false) {
            $str = $args["source"] instanceof XmlQuery
                ? $args["source"]->html()
                : trim($args["source"]);
            
            if (preg_match("/^\w+$/", $str)) {
                $str = "<$str />";
            }
            
            $node = $this->_doc->createDocumentFragment();
            $node->appendXML($str);
            $ret = new XmlQuery($current->appendChild($node));
            
            // sets the attributes
            foreach ($args["attrs"] as $name => $value) {
                $ret->attr($name, $value);
            }
            
            // calls the callback
            if ($args["callback"] !== null) {
                $args["callback"]($ret);
            }
        }
        
        return $ret === null? new XmlQuery() : $ret;
    }
    
    /**
     * Prepends a child node.
     * 
     * <p>This function inserts a new child node at the beginning of the current
     * element.</p>
     * 
     * <p>Example 1:</p>
     * <code>// Prepending a new node
     * $xml = new XmlQuery(
     *      '&lt;root&gt;&lt;item id="1" title="Item 1" /&gt;&lt;/root&gt;'
     * );
     * // you can write '&lt;item /&gt;' or simply 'item'
     * $xml->prepend('item');
     * echo $xml->html();
     * </code>
     * 
     * <p>Example 2:</p>
     * <code>// Settings attributes and inner text
     * $xml = new XmlQuery(
     *      '&lt;root&gt;&lt;item id="1" title="Item 1" /&gt;&lt;/root&gt;'
     * );
     * $xml->prepend('item', array("id" => "2", "title" => "Item 2"), "Title here");
     * echo $xml->html();
     * </code>
     * 
     * <p>Example 3:</p>
     * <code>// Adding subitems to the current item
     * $xml = new XmlQuery(
     *      '&lt;root&gt;&lt;item id="1" title="Item 1" /&gt;&lt;/root&gt;'
     * );
     * $xml->prepend(
     *      "item",
     *      array("id" => "2", "title" => "Item 2"),
     *      function($target) {
     *          $target->append("node", "Child 1");
     *          $target->append("node", "Child 2");
     *      }
     * );
     * echo $xml->html();
     * </code>
     * 
     * @param XmlQuery|string $source   Node
     * @param array           $attrs    Attributes (default is null)
     * @param string          $text     Text (default is null)
     * @param Closure         $callback Callback function (default is null)
     * 
     * @return XmlQuery
     */
    public function prepend($source, $attrs = null, $text = null, $callback = null)
    {
        $ret = null;
        $current = current($this->_items);
        $args = Arr::fetch(func_get_args(), array(
            "source" => "string|com\soloproyectos\core\xml\query\XmlQuery",
            "attrs" => array(
                "type" => "array",
                "default" => array()
            ),
            "callback" => array(
                "type" => "function",
                "required" => false
            )
        ));
        
        if ($current !== false) {
            $str = $args["source"] instanceof XmlQuery
                ? $args["source"]->html()
                : trim($args["source"]);
            
            if (preg_match("/^\w+$/", $str)) {
                $str = "<$str />";
            }
            
            $node = $this->_doc->createDocumentFragment();
            $node->appendXML($str);
            $ret = new XmlQuery(
                $current->insertBefore($node, $current->firstChild)
            );
            
            // sets the attributes
            foreach ($args["attrs"] as $name => $value) {
                $ret->attr($name, $value);
            }
            
            // calls the callback
            if ($args["callback"] !== null) {
                $args["callback"]($ret);
            }
        }
        
        return $ret === null? new XmlQuery() : $ret;
    }

    /**
     * Gets the current node name.
     * 
     * @return string
     */
    public function name()
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            $ret = $current->nodeName;
        }
        return $ret;
    }

    /**
     * Gets or sets an attribute.
     * 
     * @param string $name  Attribute name
     * @param string $value A value (default is null)
     * 
     * @return string
     */
    public function attr($name, $value = null)
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            if (func_num_args() > 1) {
                try {
                    $current->setAttribute($name, $value);
                } catch (DOMException $e) {
                    throw new XmlQueryException("Invalid attribute name: $name");
                }
            }
            $ret = $current->getAttribute($name);
        }
        return $ret;
    }
    
    /**
     * Gets or sets the inner text of the current node.
     * 
     * @param string $value A value (default is null)
     * 
     * @return string
     */
    public function text($value = null)
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            if (func_num_args() > 0) {
                $current->nodeValue = $value;
            }
            $ret = $current->nodeValue;
        }
        return $ret;
    }
    
    /**
     * Gets the inner XML text of the current node.
     * 
     * @return string
     */
    public function html()
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            $doc = $this->_ownerDocument($current);
            $ret = $doc->saveXML($current);
        }
        return $ret;
    }
    
    /**
     * Synonymous of XmlQuery::html()
     * 
     * @return string
     */
    public function xml()
    {
        return $this->html();
    }
    
    /**
     * Is the current node equal to a given node?
     * 
     * @param mixed $object A node or an arbitrary value
     * 
     * @return boolean
     */
    public function equal($object)
    {
        $node0 = $this[0];
        $node1 = $object instanceof XmlQuery
            ? $object[0]
            : null;
        return $node0 !== null && $node1 !== null && $node0->isSameNode($node1);
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
    
    /***************************
     * Iterator implementation *
     ***************************/

    /**
     * Gets the current node.
     * 
     * This function implements the Iterator::current() method.
     * 
     * @return boolean|XmlQuery
     */
    public function current()
    {
        $ret = false;
        $current = current($this->_items);
        if ($current !== false) {
            $ret = new XmlQuery($current);
        }
        return $ret;
    }

    /**
     * Advances to the next node.
     * 
     * This function implements the Iterator::next() method.
     * 
     * @return XmlQuery
     */
    public function next()
    {
        $ret = false;
        $current = next($this->_items);
        if ($current !== false) {
            $ret = new XmlQuery($current);
        }
        return $ret;
    }

    /**
     * Gets the internal pointer.
     * 
     * This function implements the Iterator::key() method.
     * 
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
      * @return integer
      */
    public function count()
    {
        return count($this->_items);
    }
}
