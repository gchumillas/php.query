<?php
/**
 * This file contains the DomNodeContentCapable trait.
 *
 * PHP Version 5.3
 *
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\dom\node;
use \DomainException;
use com\soloproyectos\common\dom\DomHelper;
use com\soloproyectos\common\dom\node\exception\DomNodeException;
use com\soloproyectos\common\text\TextHelper;

/**
 * DomNodeContentCapable trait.
 *
 * @package Dom\Node
 * @author  Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/core
 */
trait DomNodeContentCapable
{
    /**
     * List of elements.
     *
     * @return array of DOMElement
     */
    abstract public function elements();

    /**
     * Gets or sets inner HTML code.
     *
     * @param string $value Inner HTML code (not required)
     *
     * @return DomNode|string
     */
    public function html($value = null)
    {
        if (func_num_args() > 0) {
            return $this->_setInnerHtml($value);
        }

        return $this->_getInnerHtml();
    }

    /**
     * Gets or sets inner text.
     *
     * @param string $value Inner text
     *
     * @return DomNode|string
     */
    public function text($value = null)
    {
        if (func_num_args() > 0) {
            return $this->_setInnerText($value);
        }

        return $this->_getInnerText();
    }

    /**
     * Appends contents to the node.
     *
     * @param string $html Inner contents.
     *
     * @return DomNode
     */
    public function append($html)
    {
        $html = "$html";
        if (!TextHelper::isEmpty($html)) {
            foreach ($this->elements() as $element) {
                $doc = $element->ownerDocument;
                $fragment = $doc->createDocumentFragment();
                @$fragment->appendXML($html);
                $node = @$element->appendChild($fragment);

                if ($node === false) {
                    throw new DomNodeException("Invalid XML fragment");
                }
            }
        }

        return $this;
    }

    /**
     * Prepends contents to the node.
     *
     * @param string $html Inner contents.
     *
     * @return DomNode
     */
    public function prepend($html)
    {
        $html = "$html";
        if (!TextHelper::isEmpty($html)) {
            foreach ($this->elements() as $element) {
                $doc = $element->ownerDocument;
                $fragment = $doc->createDocumentFragment();
                @$fragment->appendXML($html);
                $node = @$element->insertBefore($fragment, $element->firstChild);

                if ($node === false) {
                    throw new DomNodeException("Invalid XML fragment");
                }
            }
        }

        return $this;
    }

    /**
     * Removes all child nodes.
     *
     * @return DomNode
     */
    public function clear()
    {
        foreach ($this->elements() as $element) {
            while ($element->hasChildNodes()) {
                $element->removeChild($element->firstChild);
            }
        }

        return $this;
    }

    /**
     * Gets inner text.
     *
     * @return string
     */
    private function _getInnerText()
    {
        foreach ($this->elements() as $element) {
            return $element->nodeValue;
        }

        return "";
    }

    /**
     * Sets inner text.
     *
     * @param string $value Inner text
     *
     * @return DomNode
     */
    private function _setInnerText($value)
    {
        foreach ($this->elements() as $element) {
            $doc = $element->ownerDocument;
            $element->appendChild($doc->createTextNode($value));
        }

        return $this;
    }

    /**
     * Gets inner HTML code.
     *
     * @return string
     */
    private function _getInnerHtml()
    {
        $ret = "";

        foreach ($this->elements() as $element) {
            $childNodes = $element->childNodes;

            $str = "";
            foreach ($childNodes as $node) {
                $str .= DomHelper::dom2str($node);
            }

            $ret = TextHelper::concat("\n", $ret, $str);
        }

        return $ret;
    }

    /**
     * Sets inner HTML code.
     *
     * @param string $value Inner HTML code
     *
     * @return DomNode
     */
    private function _setInnerHtml($value)
    {
        $this->clear();

        foreach ($this->elements() as $element) {
            $doc = $element->ownerDocument;
            $fragment = $doc->createDocumentFragment();
            @$fragment->appendXML($value);
            $node = @$element->appendChild($fragment);

            if ($node === false) {
                throw new DomNodeException("Invalid XML fragment");
            }
        }

        return $this;
    }
}
