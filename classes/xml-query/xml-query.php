<?php
/**
 * This file contains the XMLQuery class.
 * 
 * PHP Version 5.3
 * 
 * @category PQuery
 * @package  XML.PQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://github.com/cequiel/xmlquery/blob/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/cequiel/cssparser
 */

require_once dirname(__DIR__) . "/css-parser/css-parser.php";

/**
 * class XMLQuery
 * Parses an XML document.
 * 
 * @category PQuery
 * @package  XML.PQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://github.com/cequiel/xmlquery/blob/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/cequiel/cssparser
 */
class XMLQuery implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array of DOMNode
     */
    private $_items;
    
    /**
     * @var array of string
     */
    private $_errors;
    
    /**
     * @var DOMDocument
     */
    private $_doc;
    
    /**
     * Creates an instance of XMLQuery. Examples:
     * 
     * <code>
     * new XMLQuery("<root>well formed document</root>");
     * new XMLQuery("http://www.any-site.com/blah", "text/html", "UTF-8");
     * new XMLQuery("http://www.another-site.com");
     * new XMLQuery("/home/john/myfile.xml");
     * </code>
     * 
     * @param mixed $param1 = null The object to be examined.
     * @param mixed $param2 = null This parameter depends on the first parameter.
     * @param mixed $param3 = null This parameter depends on the first parameter.
     */
    public function __construct($param1 = null, $param2 = null, $param3 = null)
    {
        $this->_items = array();
        $this->_errors = array();
        
        if (is_string($param1)) {
            call_user_func_array(array($this, "_constructor1"), func_get_args());
        } elseif ($param1 instanceof XMLQuery) {
            call_user_func_array(array($this, "_constructor2"), func_get_args());
        } elseif (is_array($param1)
            || $param1 instanceof DOMNode
            || $param1 instanceof DOMNodeList
        ) {
            call_user_func_array(array($this, "_constructor3"), func_get_args());
        } else {
            $type = is_object($param1)? get_class($param1) : gettype($param1);
            throw new InvalidArgumentException(
                "Expects parameter 1 to be " .
                "string|array|DOMNode|DOMNodeList|XMLQuery, $type given"
            );
        }
    }
    
    /**
     * Loads contents from a file, a url or a string. For example:
     * 
     * <code>
     * new XMLQuery("http://www.php.net");
     * new XMLQuery("/home/john/myfile.xml");
     * new XMLQuery("<root><text>This is a well formed XML document</text></root>");
     * </code>
     * 
     * @param string $source   Filename, url or string.
     * @param string $mimetype = null Document mimetype. Autodetected.
     * @param string $charset  = null Document charset. Autodetected.
     * 
     * @return void
     */
    private function _constructor1($source, $mimetype = null, $charset = null)
    {
        $this->load($source, $mimetype, $charset);
    }
    
    /**
     * Wraps an XMLQuery object, inherits its behaviours and creates new ones.
     * For example:
     * 
     * <code>
     * class MyCustomQuery extends XMLQuery {
     * 
     *     public function getSomeThings() {
     *         return $this->select("div.some-things");
     *     }
     * }
     * 
     * // this extends the XMLQuery behaviours by
     * // adding a function called "getSomeThings"
     * $query = new MyCustomQuery($root);
     * $query->getSomeThings();
     * </code>
     * 
     * @param XMLQuery                  $object The object to be wrapped.
     * @param array|DOMNode|DOMNodeList $items  = array() List of DOMNode objects.
     * 
     * @return void
     */
    private function _constructor2($object, $items = array())
    {
        if (!is_array($items)
            && !$items instanceof DOMNode
            && !$items instanceof DOMNodeList
        ) {
            $type = is_object($items)? get_class($items) : gettype($items);
            throw new InvalidArgumentException(
                "Expects parameter 2 to be array|DOMNode|DOMNodeList, $type given"
            );
        }
        
        if (func_num_args() > 1) {
            $this->constructor3($items);
        } else {
            $this->_doc = $object->doc;
            $this->_items = $object->items;
        }
    }
    
    /**
     * Creates an instance from one or more DOMNode objects.
     * 
     * @param array|DOMNode|DOMNodeList $items = array() List of DOMNode objects.
     * 
     * @return void
     */
    private function _constructor3($items = array())
    {
        if ($items instanceof DOMNode) {
            $this->_items = array($items);
            $this->_doc = $items->ownerDocument;
        } else {
            $this->_items = array();
            foreach ($items as $item) {
                array_push($this->_items, $item);
                $this->_doc = $item->ownerDocument;
            }
        }
    }
    
    /**
     * Loads an XML or HTML document. Automatically detects the content type.
     * 
     * @param string $source   An string, url or filename.
     * @param string $mimetype = null Document mimetype. Autodetected.
     * @param string $charset  = null Document charset. Autodetected.
     * 
     * @return void
     */
    public function load($source, $mimetype = null, $charset = null)
    {
        $this->_items = array();
        $this->_errors = array();
        $errors = libxml_get_errors();
        $offset = count($errors);
        $use_internal_errors = libxml_use_internal_errors(true);
        
        $content = null;
        if ($this->_isURL($source)) {
            $load_header = (strlen($mimetype) == 0) || (strlen($charset) == 0);
            
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, $load_header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            $result = curl_exec($ch);
            curl_close($ch);

            if ($load_header) {
                $headers = null;
                $separator = "\r\n\r\n";
                $pos = strpos($result, $separator);
                if ($pos !== false) {
                    $headers = substr($result, 0, $pos);
                    $content = substr($result, $pos + strlen($separator));
                }
                
                $lines = explode("\r\n", $headers);
                foreach ($lines as $line) {
                    $regexp = '@Content-Type:\s*([\w/+]+)(;\s*charset=(\S+))?@i';
                    if (preg_match($regexp, $line, $matches) > 0) {
                        if (strlen($mimetype) == 0) {
                            $mimetype = array_key_exists(1, $matches)
                                ? $matches[1]
                                : null;
                        }
                        if (strlen($charset) == 0) {
                            $charset = array_key_exists(3, $matches)
                                ? $matches[3]
                                : null;
                        }
                    }
                }
            } else {
                $content = $result;
            }
        } elseif (!is_file($source)) {
            $content = $source;
        }
        
        if (strlen($charset) == 0) {
            $charset = "iso-8859-1";
        }
        
        $this->_doc = new DOMDocument("1.0", $charset);
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->formatOutput = true;

        $success = false;
        if ($mimetype == "text/html") {
            $success = $content !== null
                ? $this->_doc->loadHTML($content)
                : $this->_doc->loadHTMLFile($source);
        } else {
            $mimetype = "text/xml";
            $success = $content !== null
                ? $this->_doc->loadXML($content)
                : $this->_doc->load($source);
        }
        
        if (!$success) {
            throw new DomainException("Invalid document. Assumed $mimetype");
        } else {
            $this->_items = array($this->_doc->documentElement);
        }
        
        $this->_errors = array_slice(libxml_get_errors(), $offset);
        libxml_use_internal_errors($use_internal_errors);
    }
    
    /**
     * Load a HTML document
     * 
     * @param string $source  A filename containing an XML or HTML document.
     * @param string $charset = null Document charset. Autodetected.
     * 
     * @return void
     */
    public function loadHTML($source, $charset = null)
    {
        $this->load($source, "text/html", $charset);
    }
    
    /**
     * Load an XML document
     * 
     * @param string $source  An string representing an XML or HTML document.
     * @param string $charset = null Document charset. Autodetected.
     * 
     * @return void
     */
    public function loadXML($source, $charset = null)
    {
        $this->load($source, "text/xml", $charset);
    }
    
    /**
     * Gets the internal owner document.
     * You will not need to use this function in most cases.
     * 
     * @return DOMDocument
     */
    public function getDOMDocument()
    {
        return $this->_doc;
    }
    
    /**
     * Gets the current DOMNode object.
     * 
     * @return DOMNode
     */
    public function getDOMNode()
    {
        return current($this->_items);
    }
    
    /**
     * Gets the line number of the current node.
     * 
     * @return int
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
     * Gets node path of the current node.
     * 
     * @return string XPath expression
     */
    public function path()
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            $ret = $current->getNodePath();
        }
        return $ret;
    }
    
    /**
     * Gets errors.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->_errors;
    }
    
    /**
     * Gets the parent of the current node.
     * This function returns null if the current node has no parent.
     * 
     * @return XMLQuery
     */
    public function parent()
    {
        $ret = null;
        $current = current($this->_items);
        if ($current !== false && !($current->parentNode instanceof DOMDocument)) {
            $ret = new XMLQuery($this, $current->parentNode);
        }
        return $ret;
    }
    
    /**
     * Gets or sets an arbitrary value.
     * 
     * @param string $name  Identifier.
     * @param mixed  $value = null The value to be saved.
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
     * Evaluates the given CSS expression and returns
     * an XMLQuery containing all nodes matching it.
     * 
     * @param string $query CSS selector expression.
     * 
     * @return XMLQuery
     */
    public function select($query)
    {
        $ret = null;
        $current = current($this->_items);
        if ($current === false) {
            // creates an "empty" node
            $ret = new XMLQuery($this, array());
        } else {
            // creates an node from a list of DOMNode objects
            $items = CSSHelper::select($current, $query);
            $ret = new XMLQuery($this, $items);
        }
        return $ret;
    }
    
    /**
     * Clears the current element.
     * 
     * @return XMLQuery
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
     * Removes the current element.
     * 
     * @return XMLQuery
     */
    public function remove()
    {
        $current = current($this->_items);
        if ($current !== false) {
            $parent = $current->parentNode;
            if ($parent !== null) {
                $parent->removeChild($current);
            }
        }
        // TODO: no debería pasar al siguiente elemento?
        return $this;
    }
    
    /**
     * Inserts a new child at the end of the current element.
     * 
     * @param XMLQuery|string $object The node to be inserted at the end.
     * 
     * @return XMLQuery
     */
    public function append($object)
    {
        $current = current($this->_items);
        if ($current !== false) {
            $str = $object instanceof XMLQuery? $object->html() : $object;
            $node = $this->_doc->createDocumentFragment();
            $node->appendXML($str);
            $current->appendChild($node);
        }
        return $this;
    }
    
    /**
     * Inserts a new child at the beginning of the current element.
     * 
     * @param XMLQuery|string $object The node to be inserted at the beggining.
     * 
     * @return XMLQuery
     */
    public function prepend($object)
    {
        $current = current($this->_items);
        if ($current !== false) {
            $str = $object instanceof XMLQuery? $object->html() : $object;
            $node = $this->_doc->createDocumentFragment();
            $node->appendXML($str);
            $current->insertBefore($node, $current->firstChild);
        }
        return $this;
    }

    /**
     * Returns the more accurate name for the current node type.
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
     * @param string $name  Attribute name.
     * @param string $value = null The value to be saved.
     * 
     * @return string
     */
    public function attr($name, $value = null)
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            if (func_num_args() > 1) {
                $current->setAttribute($name, $value);
            }
            $ret = $current->getAttribute($name);
        }
        return $ret;
    }
    
    /**
     * Gets the value of the current element.
     * 
     * @param string $value = null The value to be saved.
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
     * Returns the XML representation of the node.
     * 
     * @return string
     */
    public function html()
    {
        $ret = "";
        $current = current($this->_items);
        if ($current !== false) {
            $ret = $current->ownerDocument->saveXML($current);
        }
        return $ret;
    }
    
    /**
     * Returns the XML representation of the node.
     * 
     * @return string
     */
    public function xml()
    {
        return $this->html();
    }
    
    /**
     * Is the current node equal to a given object?
     * 
     * @param mixed $object The object to be compared.
     * 
     * @return boolean
     */
    public function equal($object)
    {
        $node0 = $this->getDOMNode();
        $node1 = $object instanceof XMLQuery
            ? $object->getDOMNode()
            : null;
        return $node0 !== null && $node1 !== null && $node0->isSameNode($node1);
    }
    
    /**
     * Returns true if the string is a URL, false otherwise.
     * 
     * @param string $str An arbitrary string.
     * 
     * @return bool
     */
    private function _isURL($str)
    {
        $regexp = '#^https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?#';
        return preg_match($regexp, $str) > 0;
    }
    
    /***************************
     * Iterator implementation *
     ***************************/

    /**
     * Returns the current node.
     * 
     * @return boolean|XMLQuery
     */
    public function current()
    {
        $ret = false;
        $current = current($this->_items);
        if ($current !== false) {
            $ret = new XMLQuery($this, $current);
        }
        return $ret;
    }

    /**
     * Moves forward to next node.
     * 
     * @return XMLQuery
     */
    public function next()
    {
        $ret = false;
        $current = next($this->_items);
        if ($current !== false) {
            $ret = new XMLQuery($this, $current);
        }
        return $ret;
    }

    /**
     * Returns the internal pointer.
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
     * @return void
     */
    public function rewind()
    {
        reset($this->_items);
    }

    /**
     * Checks if current position is valid.
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
     * @param integer $offset Node position.
     * 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_items);
    }

    /**
     * Gets the DOMNode object at a given position.
     * 
     * @param integer $offset Node position.
     * 
     * @return DOMNode
     */
    public function offsetGet($offset)
    {
        return $this->_items[$offset];
    }

    /**
     * Sets a DOMNode object at a given position.
     * 
     * @param integer $offset Node position.
     * @param DOMNode $value  The value to be saved
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_items[$offset] = $value;
    }

    /**
     * Unsets a DOMNode object at a given position.
     * 
     * @param integer $offset Node position.
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
      * @return integer
      */
    public function count()
    {
        return count($this->_items);
    }
}
