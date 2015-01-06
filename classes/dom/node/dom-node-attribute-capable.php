<?php
/**
 * This file contains the DomNodeAttributeCapable trait.
 *
 * PHP Version 5.3
 *
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom\node;

/**
 * DomNodeAttributeCapable trait.
 *
 * @package Dom\Node
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
trait DomNodeAttributeCapable
{
    /**
     * List of elements.
     *
     * @return array of DOMElement
     */
    abstract public function elements();

    /**
     * Gets or sets an attribute.
     *
     * @param string $name  Attribute name
     * @param string $value Attribute value (not required)
     *
     * @return DomNode|string
     */
    public function attr($name, $value = null)
    {
        if (func_num_args() > 1) {
            return $this->_setAttribute($name, $value);
        }

        return $this->_getAttribute($name);
    }

    /**
     * Has the node an attribute?
     *
     * @param string $name Attribute name
     *
     * @return boolean
     */
    public function hasAttr($name)
    {
        foreach ($this->elements() as $element) {
            return $element->hasAttribute($name);
        }

        return false;
    }

    /**
     * Gets an attribute.
     *
     * @param string $name Attribute name
     *
     * @return string
     */
    private function _getAttribute($name)
    {
        foreach ($this->elements() as $element) {
            return $element->getAttribute($name);
        }

        return "";
    }

    /**
     * Sets an attribute.
     *
     * @param string $name  Attribute name
     * @param string $value Attribute value
     *
     * @return DomNode
     */
    private function _setAttribute($name, $value)
    {
        foreach ($this->elements() as $element) {
            $element->setAttribute($name, $value);
        }

        return $this;
    }
}
