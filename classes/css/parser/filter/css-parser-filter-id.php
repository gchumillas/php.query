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
use com\soloproyectos\common\css\parser\filter\CssParserFilter;

/**
 * Class CssParserFilterId.
 *
 * This class represents the id filter.
 *
 * @package Css\Parser\Filter
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class CssParserFilterId extends CssParserFilter
{
    /**
     * Identifier.
     * @var string
     */
    private $_id;

    /**
     * Constructor.
     *
     * @param string $id CSS Identifier
     */
    public function __construct($id)
    {
        $this->_id = $id;
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
        return trim($node->getAttribute("id")) == $this->_id;
    }
}
