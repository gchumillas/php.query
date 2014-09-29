<?php
/**
 * This file contains the DomNodeCssCapable trait.
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
 * DomNodeCssCapable trait.
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
trait DomNodeCssCapable
{
    /**
     * List of elements.
     * 
     * @return array of DOMElement
     */
    abstract public function elements();
    
    /**
     * Gets or sets a CSS attribute.
     * 
     * @param string $name  CSS attribute name
     * @param string $value CSS attribute value (not required)
     * 
     * @return DomNode|string
     */
    public function css($name, $value = null)
    {
        $name = trim($name);
        
        if (func_num_args() > 1) {
            return $this->_setCssAttribute($name, $value);
        }
        
        return $this->_getCssAttribute($name);
    }
    
    /**
     * Has the node a CSS attribute?
     * 
     * @param string $name CSS attribute name
     * 
     * @return boolean
     */
    public function hasCss($name)
    {
        $name = trim($name);
        
        foreach ($this->elements() as $element) {
            $cssMap = $this->_getCssAttributeMap($element);
            $name = strtolower($name);
            return array_key_exists($name, $cssMap);
        }
        
        return false;
    }
    
    /**
     * Gets a CSS atribute.
     * 
     * @param string $name Attribute name
     * 
     * @return string
     */
    private function _getCssAttribute($name)
    {
        foreach ($this->elements() as $element) {
            $cssMap = $this->_getCssAttributeMap($element);
            $name = strtolower($name);
            return array_key_exists($name, $cssMap)? $cssMap[$name] : "";
        }
        
        return "";
    }
    
    /**
     * Sets a CSS attribute.
     * 
     * @param string $name  CSS attribute name
     * @param string $value CSS attribute value
     * 
     * @return DomNode
     */
    private function _setCssAttribute($name, $value)
    {
        foreach ($this->elements() as $element) {
            $cssMap = $this->_getCssAttributeMap($element);
            $cssMap[$name] = $value;
            $this->_setCssAttributeMap($element, $cssMap);
        }
        
        return $this;
    }
    
    /**
     * Gets the list of CSS attributes.
     * 
     * @param DOMElement $element DOM element
     * 
     * @return array of CSS attributes
     */
    private function _getCssAttributeMap($element)
    {
        $ret = array();
        $style = $element->getAttribute("style");
        
        if (strlen($style) > 0) {
            $cssMap = explode(";", $style);
            
            foreach ($cssMap as $cssAttr) {
                $item = explode(":", $cssAttr);
                $key = count($item) > 0? trim($item[0]): "";
                $value = count($item) > 1? trim($item[1]): "";
                
                if (strlen($key) > 0 && strlen($value) > 0) {
                    $ret[$key] = $value;
                }
            }
        }
        
        return $ret;
    }
    
    /**
     * Sets a list of CSS attributes.
     * 
     * @param DOMElement $element DOM element
     * @param array      $cssMap  List of attributes
     * 
     * @return void
     */
    private function _setCssAttributeMap($element, $cssMap)
    {
        $attrs = array();
        
        foreach ($cssMap as $key => $value) {
            if (strlen($key) > 0) {
                array_push($attrs, strtolower($key) . ": $value");
            }
        }
        
        if (count($attrs) > 0) {
            $element->setAttribute("style", implode("; ", $attrs));
        }
    }
}
