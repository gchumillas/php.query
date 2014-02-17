<?php
/**
 * This file contains the XmlQueryTransversable class.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\xml\query;
use ArrayAccess;
use DOMNode;
use Countable;
use Iterator;
use com\soloproyectos\common\arr\ArrHelper;

/**
 * Class XmlQueryTransversable.
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
abstract class XmlQueryTransversable implements Iterator, ArrayAccess, Countable
{
    /**
     * List of DOMNode objects.
     * @var array of DOMNode
     */
    protected $items;
    
    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->items = array();
    }
    
    /***************************
     * Iterator implementation *
     ***************************/

    /**
     * Gets the current node.
     * 
     * @return boolean|XmlQuery
     */
    public function current()
    {
        return ($item = current($this->items))? new XmlQuery($item) : false;
    }

    /**
     * Advances to the next node.
     * 
     * @return XmlQuery
     */
    public function next()
    {
        return ($item = next($this->items))? new XmlQuery($item) : false;
    }

    /**
     * Gets the internal pointer.
     * 
     * @return integer
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * Rewinds the internal pointer.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->items);
    }

    /**
     * Is a valid internal position?
     * 
     * @return boolean
     */
    public function valid()
    {
        return (key($this->items) !== null);
    }
    
    /******************************
     * ArrayAccess implementation *
     ******************************/

    /**
     * Does the DOMNode exist at a given position?
     * 
     * @param integer $offset Node position
     * 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return ArrHelper::is($this->items, $offset);
    }

    /**
     * Gets a node by a given position.
     * 
     * @param integer $offset Node position
     * 
     * @return DOMNode
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Sets a node by a given position.
     * 
     * @param integer $offset Node position
     * @param DOMNode $value  A node object
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Unsets a node by a given position.
     * 
     * @param integer $offset Node position
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
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
        return count($this->items);
    }
}
