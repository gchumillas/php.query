<?php
/**
 * This file contains the ParserException class.
 * 
 * @author Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @package parser
 */

/**
 * class ParserException
 * The only purpose of this class is to provide a method to get printable messages.
 */
class ParserException extends Exception {
    /**
     * The parser instance that threw the exception.
     * @var Parser
     */
    private $parser;
    
    /**
     * @param Parser $parser
     * @param string $message
     * @param int $code = 0
     * @param Exception $previous = NULL
     */
    public function __construct($parser, $message, $code = 0, $previous = NULL) {
        $this->parser = $parser;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Gets a printable message.
     * This function provides a method to get printable messages.
     * @return string
     */
    public function getPrintableMessage() {
        $ret = "";
        $message = $this->parser->end() ? "Unexpected end of line" : $this->message;
        $string = rtrim($this->parser->getString());
        $offset = $this->parser->getOffset();
        
        $right_str = substr($string, $offset);
        $offset0 = $offset + strlen($right_str) - strlen(ltrim($right_str));
        
        $str1 = substr($string, 0, $offset0);
        $offset1 = strrpos($str1, "\n");
        if ($offset1 !== FALSE) {
            $offset1++;
        }
        
        $str2 = substr($string, $offset1);
        $offset2 = strpos($str2, "\n");
        if ($offset2 === FALSE) {
            $offset2 = strlen($str2);
        }
        
        $str3 = substr($str2, 0, $offset2);
        $line = $offset0 > 0? substr_count($string, "\n", 0, $offset0) : 0;
        $column = $offset0 - $offset1;
        
        $ret = "$str3\n" . str_repeat(" ", $column) . "^$message";
        if ($line > 0) {
            $ret .= " (line " . ($line + 1) . ")";
        }
        
        return $ret;
    }
    
    public function __toString() {
        return __CLASS__ . ":\n\n" . $this->getPrintableMessage() . "\n";
    }
}
