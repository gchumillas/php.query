<?php
/**
 * This file contains the CssParserFilterClass class.
 * 
 * PHP Version 5.3
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\core\css\parser\filter;
use DOMElement;
use com\soloproyectos\core\css\parser\filter\CssParserFilter;

/**
 * Class CssParserFilterClass.
 * 
 * This class represents the class filter.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterClass extends CssParserFilter
{
    /**
     * Class name.
     * @var string
     */
    private $_className;
    
    /**
     * Constructor.
     * 
     * @param string $className Class name
     */
    public function __construct($className)
    {
        $this->_className = $className;
    }
    
    /**
     * Is a class name in a given list?
     * 
     * @param string $class   Class name
     * @param string $classes List of classes
     * 
     * @return boolean
     */
    private function _isClassInList($class, $classes)
    {
        $items = explode(" ", trim($classes));
        if (count($items) > 0) {
            foreach ($items as $item) {
                if (strcasecmp($class, trim($item)) == 0) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Does the node match?
     * 
     * @param DOMElement $node     DOMElement object
     * @param integer    $position Node position
     * @param array      $items    List of nodes
     * 
     * @return boolean
     */
    public function match($node, $position, $items)
    {
        return $this->_isClassInList(
            $this->_className,
            $node->getAttribute("class")
        );
    }
}
