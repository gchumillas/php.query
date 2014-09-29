<?php
/**
 * This file contains the DomNodeClassCapable trait.
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

/**
 * DomNodeClassCapable trait.
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
trait DomNodeClassCapable
{
    /**
     * List of elements.
     * 
     * @return array of DOMElement
     */
    abstract public function elements();
    
    /**
     * Adds a class name.
     * 
     * @param string $className Class name
     * 
     * @return DomNode
     */
    public function addClass($className)
    {
        $className = trim($className);
        
        if (strlen($className) > 0) {
            foreach ($this->elements() as $element) {
                $classes = $this->_getClassMap($element);
                
                if (array_search($className, $classes) === false) {
                    array_push($classes, $className);
                    $this->_setClassMap($element, $classes);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Removes a class name.
     * 
     * @param string $className Class name
     * 
     * @return DomNode
     */
    public function removeClass($className)
    {
        $className = trim($className);
        
        if (strlen($className) > 0) {
            foreach ($this->elements() as $element) {
                $classes = $this->_getClassMap($element);
                $offset = array_search($className, $classes);
                
                if ($offset !== false) {
                    array_splice($classes, $offset, 1);
                }
                
                $this->_setClassMap($element, $classes);
            }
        }
        
        return $this;
    }
    
    /**
     * Has the node a class name?
     * 
     * @param string $className Class name
     * 
     * @return boolean
     */
    public function hasClass($className)
    {
        $className = trim($className);
        
        if (strlen($className) > 0) {
            foreach ($this->elements() as $element) {
                $classes = $this->_getClassMap($element);
                
                return array_search($className, $classes) !== false;
            }
        }
        
        return false;
    }
    
    /**
     * Gets the list of classes.
     * 
     * @param DOMElement $element DOM element
     * 
     * @return array of string
     */
    private function _getClassMap($element)
    {
        $ret = array();
        $classAttr = $element->getAttribute("class");
        
        if (strlen($classAttr) > 0) {
            $classes = preg_split("/\s+/", $classAttr);
            
            foreach ($classes as $className) {
                if (strlen($className) > 0 && array_search($className, $ret) === false) {
                    array_push($ret, $className);
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * Sets the list of classes.
     * 
     * @param DOMElement $element DOM element
     * @param array      $classes List of classes
     * 
     * @return void
     */
    private function _setClassMap($element, $classes)
    {
        $element->removeAttribute("class");
        
        if (count($classes) > 0) {
            $element->setAttribute("class", implode(" ", $classes));
        }
    }
}
