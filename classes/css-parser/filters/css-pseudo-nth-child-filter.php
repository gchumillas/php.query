<?php
require_once dirname(dirname(__DIR__)) . "/css-parser/css-helper.php";
require_once dirname(dirname(__DIR__)) . "/css-parser/filters/css-filter.php";

class CSSPseudoNthChildFilter extends CSSPseudoFilter {
    /**
     * Sibling position.
     * @var int
     */
    private $position;
    
    /**
     * @param int $position
     */
    public function __construct($position) {
        $this->position = $position;
    }
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        $i = 1;
        
        while ($node = CSSHelper::getPreviousSiblingElement($node)) {
            $i++;
            if ($i > $this->position) {
                return FALSE;
            }
        }
        return ($i == $this->position);
    }
}
