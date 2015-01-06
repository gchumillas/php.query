<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\css\parser\combinator;
use \DOMElement;

/**
 * Class CssParserCombinator.
 *
 * This class represents a filter in a CSS expression.
 *
 * @package Css\Parser\Combinator
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
abstract class CssParserCombinator
{
    /**
     * Gets the nodes that combines with a given node and a tag name.
     *
     * @param DOMElement $node    DOMElement object
     * @param string     $tagname Tag name
     *
     * @return boolean
     */
    abstract public function filter($node, $tagname);
}
