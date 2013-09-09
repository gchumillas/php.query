<?php
/**
 * This file contains the XMLQuery class.
 * 
 * @author Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @package xml-query
 */

require_once dirname(__DIR__) . "/xml-query/css-query.php";

/**
 * class XMLQuery
 * 
 * Parses an XML document.
 * 
 * @package xml-query
 */
class XMLQuery implements Countable, Iterator, ArrayAccess {
    /**
     * @var array of DOMNode
     */
    private $items;
    
    /**
     * @var array of string
     */
    private $errors;
    
    /**
     * @var DOMDocument
     */
    private $doc;
    
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
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     */
    public function __construct($param1 = NULL, $param2 = NULL, $param3 = NULL) {
        $this->items = array();
        $this->errors = array();
        
        if (is_string($param1)) {
            call_user_func_array(array($this, "constructor1"), func_get_args());
        } else
        if ($param1 instanceof XMLQuery) {
            call_user_func_array(array($this, "constructor2"), func_get_args());
        } else
        if (is_array($param1) || $param1 instanceof DOMNode || $param1 instanceof DOMNodeList) {
            call_user_func_array(array($this, "constructor3"), func_get_args());
        } else {
            $type = is_object($param1)? get_class($param1) : gettype($param1);
            throw new InvalidArgumentException("Expects parameter 1 to be string|array|DOMNode|DOMNodeList|XMLQuery, $type given");
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
     * @param string $source A filename, a url or a string containing an XML document.
     * @param string $mimetype = NULL Tries to guess the mimetype from the source. It assumes "text/xml" otherwise.
     * @param string $charset = NULL Tries to guess the charset from the source. It assumes "UTF-8" otherwise.
     */
    private function constructor1($source, $mimetype = NULL, $charset = NULL) {
        $this->load($source, $mimetype, $charset);
    }
    
    /**
     * Wraps an XMLQuery object, inherits its behaviours and creates new ones. For example:
     * 
     * <code>
     * class MyCustomQuery extends XMLQuery {
     * 
     *     public function getSomeThings() {
     *         return $this->select("div.some-things");
     *     }
     * }
     * 
     * // this extends the XMLQuery behaviours by adding a function called "getSomeThings"
     * $query = new MyCustomQuery($root);
     * $query->getSomeThings();
     * </code>
     * 
     * @param XMLQuery $object
     * @param array|DOMNode|DOMNodeList $items
     */
    private function constructor2($object, $items = array()) {
        if (!(is_array($items) || $items instanceof DOMNode || $items instanceof DOMNodeList)) {
            $type = is_object($items)? get_class($items) : gettype($items);
            throw new InvalidArgumentException("Expects parameter 2 to be array|DOMNode|DOMNodeList, $type given");
        }
        
        if (func_num_args() > 1) {
            $this->constructor3($items);
        } else {
            $this->doc = $object->doc;
            $this->items = $object->items;
        }
    }
    
    /**
     * Creates an instance from one or more DOMNode objects.
     * @param array|DOMNode|DOMNodeList $items
     */
    private function constructor3($items = array()) {
        if ($items instanceof DOMNode) {
            $this->items = array($items);
            $this->doc = $items->ownerDocument;
        } else {
            $this->items = array();
            foreach ($items as $item) {
                array_push($this->items, $item);
                $this->doc = $item->ownerDocument;
            }
        }
    }
    
    /**
     *
     * @param string $xpath
     * @return XMLQuery
     */
    public function __invoke($xpath) {
        return $this->select($xpath);
    }
    
    /**
     * Load an XML or HTML document. Automatically detects the content type.
     * @param string $source
     * @param string $mimetype
     * @param string $charset 
     */
    public function load($source, $mimetype = NULL, $charset = NULL) {
        $this->items = array();
        $this->errors = array();
        $errors = libxml_get_errors();
        $offset = count($errors);
        $use_internal_errors = libxml_use_internal_errors(TRUE);
        
        $content = NULL;
        if ($this->isURL($source)) {
            $load_header = (strlen($mimetype) == 0) || (strlen($charset) == 0);
            
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, $load_header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            $result = curl_exec($ch);
            curl_close($ch);

            if ($load_header) {
                $headers = NULL;
                $separator = "\r\n\r\n";
                $pos = strpos($result, $separator);
                if ($pos !== FALSE) {
                    $headers = substr($result, 0, $pos);
                    $content = substr($result, $pos + strlen($separator));
                }
                
                $lines = explode("\r\n", $headers);
                foreach ($lines as $line) {
                    if (preg_match('@Content-Type:\s*([\w/+]+)(;\s*charset=(\S+))?@i', $line, $matches) > 0) {
                        if (strlen($mimetype) == 0) {
                            $mimetype = array_key_exists(1, $matches)? $matches[1]: NULL;
                        }
                        if (strlen($charset) == 0) {
                            $charset = array_key_exists(3, $matches)? $matches[3]: NULL;
                        }
                    }
                }
            } else {
                $content = $result;
            }
        } else
        if (!is_file($source)) {
            $content = $source;
        }
        
        if (strlen($charset) == 0) {
            $charset = "iso-8859-1";
        }
        
        $this->doc = new DOMDocument("1.0", $charset);
        $this->doc->preserveWhiteSpace = FALSE;

        $success = FALSE;
        if ($mimetype == "text/html") {
            $success = $content !== NULL? $this->doc->loadHTML($content) : $this->doc->loadHTMLFile($source);
        } else {
            $mimetype = "text/xml";
            $success = $content !== NULL? $this->doc->loadXML($content) : $this->doc->load($source);
        }
        
        if (!$success) {
            throw new DomainException("Invalid document. Assumed $mimetype");
        } else {
            $this->items = array($this->doc->documentElement);
        }
        
        $this->errors = array_slice(libxml_get_errors(), $offset);
        libxml_use_internal_errors($use_internal_errors);
    }
    
    /**
     * Load a HTML document
     * @param string $source
     * @param string $charset
     */
    public function loadHTML($source, $charset = NULL) {
        $this->load($source, "text/html", $charset);
    }
    
    /**
     * Load an XML document
     * @param string $source
     * @param string $charset
     */
    public function loadXML($source, $charset = NULL) {
        $this->load($source, "text/xml", $charset);
    }
    
    /**
     * Gets the internal owner document.
     * You will not need to use this function in most cases.
     * @return DOMDocument
     */
    public function getDOMDocument() {
        return $this->doc;
    }
    
    /**
     * Gets the current DOMNode object.
     * @return DOMNode
     */
    public function getDOMNode() {
        return current($this->items);
    }
    
    /**
     * Gets the line number of the current node.
     * @return int
     */
    public function line() {
        $ret = 0;
        $current = current($this->items);
        if ($current !== FALSE) {
            $ret = $current->getLineNo();
        }
        return $ret;
    }
    
    /**
     * Gets the filename of the document.
     * @return string
     */
    public function filename() {
        return $this->doc != NULL? realpath($this->doc->documentURI) : "";
    }
    
    /**
     * Gets node path of the current node.
     * @return string XPath expression
     */
    public function path() {
        $ret = "";
        $current = current($this->items);
        if ($current !== FALSE) {
            $ret = $current->getNodePath();
        }
        return $ret;
    }
    
    /**
     * Gets errors.
     * @return array
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Gets the parent of the current node.
     * This function returns NULL if the current node has no parent.
     * @return XMLQuery
     */
    public function parent() {
        $ret = NULL;
        $current = current($this->items);
        if ($current !== FALSE && !($current->parentNode instanceof DOMDocument)) {
            $ret = new XMLQuery($this, $current->parentNode);
        }
        return $ret;
    }
    
    /**
     * Gets or sets an arbitrary value.
     * @param string $name
     * @param string $value = NULL
     * @return mixed
     */
    public function data($name, $value = NULL) {
        $ret = NULL;
        $current = current($this->items);
        if ($current !== FALSE) {
            $data = $current->hasAttribute("__data__")? unserialize($current->getAttribute("__data__")) : array();
            if (func_num_args() > 1) {
                $data[$name] = $value;
                $current->setAttribute("__data__", serialize($data));
            }
            $ret = isset($data[$name])? $data[$name] : "";
        }
        return $ret;
    }
    
    /**
     * Evaluates the given CSS expression and returns an XMLQuery containing all nodes matching it.
     * @param string $query
     * @return XMLQuery
     */
    public function select($query) {
        $ret = NULL;
        $current = current($this->items);
        if ($current === FALSE) {
            // creates an "empty" node
            $ret = new XMLQuery($this, array());
        } else {
            // creates an node from a list of DOMNode objects
            $cssquery = new CSSQuery($current->ownerDocument);
            $items = $cssquery->query($query, $current);
            $ret = new XMLQuery($this, $items);
        }
        return $ret;
    }
    
    /**
     * Clears the current element.
     * @return XMLQuery
     */
    public function clear() {
        $current = current($this->items);
        if ($current !== FALSE) {
            while ($current->hasChildNodes()) {
                $current->removeChild($current->firstChild);
            }
        }
        return $this;
    }
    
    /**
     * Removes the current element.
     * @return XMLQuery
     */
    public function remove() {
        $current = current($this->items);
        if ($current !== FALSE) {
            $parent = $current->parentNode;
            if ($parent !== NULL) {
                $parent->removeChild($current);
            }
        }
        // TODO: no debería pasar al siguiente elemento?
        return $this;
    }
    
    /**
     * Inserts a new child at the end of the current element.
     * @param XMLQuery|string $object
     */
    public function append($object) {
        $current = current($this->items);
        if ($current !== FALSE) {
            $str = $object instanceof XMLQuery? $object->html() : $object;
            $node = $this->doc->createDocumentFragment();
            $node->appendXML($str);
            $current->appendChild($node);
        }
        return $this;
    }
    
    /**
     * Inserts a new child at the beginning of the current element.
     * @param XMLQuery|string $object
     */
    public function prepend($object) {
        $current = current($this->items);
        if ($current !== FALSE) {
            $str = $object instanceof XMLQuery? $object->html() : $object;
            $node = $this->doc->createDocumentFragment();
            $node->appendXML($str);
            $current->insertBefore($node, $current->firstChild);
        }
        return $this;
    }

    /**
     * Returns the more accurate name for the current node type.
     * @return string
     */
    public function name() {
        $ret = "";
        $current = current($this->items);
        if ($current !== FALSE) {
            $ret = $current->nodeName;
        }
        return $ret;
    }

    /**
     * Gets or sets an attribute.
     * @param string $name
     * @param string $value (optional)
     * @return string
     */
    public function attr($name, $value = NULL) {
        $ret = "";
        $current = current($this->items);
        if ($current !== FALSE) {
            if (func_num_args() > 1) {
                $current->setAttribute($name, $value);
            }
            $ret = $current->getAttribute($name);
        }
        return $ret;
    }
    
    /**
     * Gets the value of the current element.
     * @param $value = NULL
     * @return string
     */
    public function text($value = NULL) {
        $ret = "";
        $current = current($this->items);
        if ($current !== FALSE) {
            if (func_num_args() > 0) {
                $current->nodeValue = $value;
            }
            $ret = $current->nodeValue;
        }
        return $ret;
    }
    
    /**
     * Returns the XML representation of the node
     * @return string
     */
    public function html() {
        $ret = "";
        $current = current($this->items);
        if ($current !== FALSE) {
            $ret = $current->ownerDocument->saveXML($current);
        }
        return $ret;
    }
    
    /**
     * Returns the XML representation of the node
     * @return string
     */
    public function xml() {
        return $this->html();
    }
    
    /**
     * Is the current node equal to a given object?
     * @param mixed $object
     * @return boolean
     */
    public function equal($object) {
        $node0 = $this->getDOMNode();
        $node1 = $object instanceof XMLQuery? $object->getDOMNode() : NULL;
        return $node0 !== NULL && $node1 !== NULL && $node0->isSameNode($node1);
    }
    
    /**
     * Magic 'get' method.
     * @param string $name
     * @return string
     */
    public function __get($name) {
        return $this->attr($name);
    }
    
    /**
     * Magic 'set' method.
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        $this->attr($name, $value);
    }
    
    /**
     * The value of this node, depending on its type.
     * @return string
     */
    public function __toString() {
        return $this->text();
    }
    
    /**
     * Returns TRUE if the string is a URL, FALSE otherwise.
     * @param string $str
     * @return bool
     */
    private function isURL($str) {
        return preg_match('#^https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?#', $str) > 0;
    }
    
    /***************************
     * Iterator implementation *
     ***************************/

    /**
     * Returns the current node.
     * @return boolean|XMLQuery
     */
    public function current() {
        $ret = FALSE;
        $current = current($this->items);
        if ($current !== FALSE) {
            $ret = new XMLQuery($this, $current);
        }
        return $ret;
    }

    /**
     * Moves forward to next node.
     * @return XMLQuery
     */
    public function next() {
        $ret = FALSE;
        $current = next($this->items);
        if ($current !== FALSE) {
            $ret = new XMLQuery($this, $current);
        }
        return $ret;
    }

    /**
     * Returns the internal pointer.
     * @return integer
     */
    public function key() {
        return key($this->items);
    }

    /**
     * Rewinds the internal pointer.
     */
    public function rewind() {
        reset($this->items);
    }

    /**
     * Checks if current position is valid.
     * @return bool
     */
    public function valid() {
        return (key($this->items) !== NULL);
    }
    
    /******************************
     * ArrayAccess implementation *
     ******************************/

    /**
     * Whether or not an offset exists.
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Gets the value at specified offset.
     * @param integer $offset
     * @return DOMNode
     */
    public function offsetGet($offset) {
        return $this->items[$offset];
    }

    /**
     * Assigns a value to the specified offset.
     * @param integer $offset
     * @param DOMNode $value
     */
    public function offsetSet($offset, $value) {
        $this->items[$offset] = $value;
    }

    /**
     * Unsets an offset.
     * @param integer $offset
     */
    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }
    
    /****************************
     * Countable implementation *
     ****************************/
     
     /**
      * Gets the number of records.
      * @return integer
      */
     public function count() {
         return count($this->items);
     }
}
