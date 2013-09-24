<?php

abstract class CSSFilter {
    
    /**
     * Does the node match?
     * @param DOMNode $node
     * @return boolean
     */
    abstract public function match($node);
}
