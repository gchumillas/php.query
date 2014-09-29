<?php
/**
 * This file contains the DomNodeIterable class.
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
use \Iterator;
use \Countable;

/**
 * DomNodeIterable class.
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class DomNodeIterable implements Iterator, Countable
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
