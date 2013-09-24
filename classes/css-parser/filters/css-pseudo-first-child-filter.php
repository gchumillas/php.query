<?php
require_once dirname(dirname(__DIR__)) . "/css-parser/css-helper.php";
require_once dirname(dirname(__DIR__)) . "/css-parser/filters/css-pseudo-filter.php";

class CSSPseudoFirstChildFilter extends CSSPseudoFilter {
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        return !CSSHelper::getPreviousSiblingElement($node);
    }
}
