<?php
require_once dirname(dirname(__DIR__)) . "/css-parser/filters/css-filter.php";

class CSSClassFilter extends CSSFilter {
    /**
     * Class name.
     * @var string
     */
    private $class_name;
    
    /**
     * @param string $class_name
     */
    public function __construct($class_name) {
        $this->class_name = $class_name;
    }
    
    /**
     * Is the class in the list?
     * @param string $class
     * @param string $classes
     * @return boolean
     */
    private function isClassInList($class, $classes) {
        $items = explode(" ", trim($classes));
        if (count($items) > 0) {
            foreach ($items as $item) {
                if (strcasecmp($class, trim($item)) == 0) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Does the node match?
     * @param DOMElement $node
     * @return boolean
     */
    public function match($node) {
        return $this->isClassInList($this->class_name, $node->getAttribute("class"));
    }
}
