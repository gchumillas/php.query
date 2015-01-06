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
use com\soloproyectos\common\css\parser\combinator\CssParserCombinator;
use com\soloproyectos\common\dom\DomHelper;

/**
 * Class CssParserCombinatorChild.
 *
 * This class represents a filter in a CSS expression.
 *
 * @package Css\Parser\Combinator
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class CssParserCombinatorChild extends CssParserCombinator
{
    /**
     * Gets the child nodes.
     *
     * @param DOMElement $node    DOMElement object
     * @param string     $tagname Tag name
     *
     * @return array of DOMElement
     */
    public function filter($node, $tagname)
    {
        return DomHelper::getChildElements($node);
    }
}
