<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\css\parser\filter;
use \DOMElement;
use com\soloproyectos\common\css\parser\filter\CssParserFilterPseudo;
use com\soloproyectos\common\dom\DomHelper;

/**
 * Class CssParserFilterPseudoNthChild.
 *
 * This class represents the nth-child pseudo filter.
 *
 * @package Css\Parser\Filter
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterPseudoNthChild extends CssParserFilterPseudo
{
    /**
     * Sibling position.
     * @var integer
     */
    private $_position;

    /**
     * Constructor.
     *
     * @param string $input String input
     */
    public function __construct($input)
    {
        $this->_position = intval($input);
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
        $i = 1;

        while ($node = DomHelper::getPreviousSiblingElement($node)) {
            $i++;
            if ($i > $this->_position) {
                return false;
            }
        }

        return ($i == $this->_position);
    }
}
