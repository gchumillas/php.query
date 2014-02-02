<?php
/**
 * This file contains the CssParserFilterPseudoUserDefined class.
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
use Closure;
use DOMElement;
use com\soloproyectos\core\css\parser\filter\CssParserFilterPseudo;

/**
 * Class CssParserFilterPseudoUserDefined.
 * 
 * This class represents the first-child pseudo filter.
 * 
 * @category Css
 * @package  CssParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw2.github.com/soloproyectos/php.common-libs/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterPseudoUserDefined extends CssParserFilterPseudo
{
    
    /**
     * String input.
     * @var string
     */
    private $_input;
    
    /**
     * User defined function.
     * @var Closure
     */
    private $_userDefFunction;
    
    /**
     * Constructor.
     * 
     * @param string  $input           String input
     * @param Closure $userDefFunction User defined function
     * 
     * @return void
     */
    public function __construct($input, $userDefFunction)
    {
        $this->_input = $input;
        $this->_userDefFunction = $userDefFunction;
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
        $userDefFunction = $this->_userDefFunction;
        return $userDefFunction($node, $this->_input, $position, $items);
    }
}
