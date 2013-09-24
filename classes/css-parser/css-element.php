<?php

class CSSElement {
    /**
     * Tag name.
     * @var string
     */
    private $tag_name;
    
    /**
     * List of filters.
     * @var array(CSSFilter, ...)
     */
    private $filters;
    
    /**
     * @param string $tag_name
     */
    public function __construct($tag_name) {
        $this->filters = array();
        $this->tag_name = $tag_name;
    }
    
    /**
     * Gets tag name.
     * @return string
     */
    public function getTagName() {
        return $this->tag_name;
    }
    
    /**
     * Adds a filter.
     * @param CSSFilter $filter
     */
    public function addFilter($filter) {
        array_push($this->filters, $filter);
    }
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        $ret = FALSE;
        
        if ($this->tag_name == "*" || $node->nodeName == $this->tag_name) {
            $ret = TRUE;
            foreach ($this->filters as $filter) {
                if (!$filter->match($node)) {
                    $ret = FALSE;
                    break;
                }
            }
        }
        return $ret;
    }
}
