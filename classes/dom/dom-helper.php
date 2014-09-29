<?php
/**
 * This file contains the DomHelper class.
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
 * DomHelper class.
 * 
 * @category DOM
 * @package  Dom
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class DomHelper
{
    
    /**
     * Gets the string representation of a node.
     * 
     * @param DOMNode $node DOMNode object
     * 
     * @return string
     */
    public static function dom2str($node)
    {
        $doc = $node instanceof DOMDocument? $node : $node->ownerDocument;
        return $doc->saveXML($node);
    }
}
