<?php
/**
 * This file contains the DomNodeIterable class.
 *
 * PHP Version 5.3
 *
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom\node;
use \ArrayAccess;
use \Countable;
use \Iterator;
use com\soloproyectos\common\dom\node\exception\DomNodeException;

/**
 * DomNodeIterable class.
 *
 * @package Dom\Node
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
class DomNodeIterable implements Iterator, ArrayAccess, Countable
{
    /**
     * Internal DOM elements.
     * @var array of DOMElement
     */
    protected $elements = array();

    /**
     * Gets the current node.
     *
     * This function implements Iterator::current.
     *
     * @return DomNode|false
     */
    public function current()
    {
        $elem = current($this->elements);
        return $elem !== false? DomNode::createFromElement($elem): false;
    }

    /**
     * Gets the next node.
     *
     * This function implements Iterator::next.
     *
     * @return DomNode|false
     */
    public function next()
    {
        $elem = next($this->elements);
        return $elem !== false? DomNode::createFromElement($elem): false;
    }

    /**
     * Gets the key of the current node.
     *
     * This function implements Iterator::key.
     *
     * @return DOMElement|null
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * Sets the internal pointer to the first element.
     *
     * This function implements Iterator::rewind.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * Is the current node valid?
     *
     * This function implements Iterator::valid.
     *
     * @return boolean
     */
    public function valid()
    {
        return (key($this->elements) !== null);
    }

    /**
     * Whether or not an offset exists.
     *
     * This function implements ArrayAccess::offsetExists.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->elements);
    }

    /**
     * Returns the value at specified offset.
     *
     * This function implements ArrayAccess::offsetGet.
     *
     * @param integer $offset Offset
     *
     * @return DomNode
     */
    public function offsetGet($offset)
    {
        return DomNode::createFromElement($this->elements[$offset]);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * This function implements ArrayAccess::offsetSet.
     *
     * @param integer $offset Offset
     * @param DomNode $value  Value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new DomNodeException("This operation is not allowed");
    }

    /**
     * Unsets an offset.
     *
     * This function implements ArrayAccess::offsetUnset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * Gets the number of nodes.
     *
     * This function implements Countable::count.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->elements);
    }
}
