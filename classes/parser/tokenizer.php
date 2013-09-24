<?php
/**
 * This file contains the Tokenizer class.
 * 
 * @author Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @package parser
 */
 
/**
 * class Tokenizer
 * This class not only can split a string into smaller pieces called tokens,
 * but it can be used to parse a string on the fly.
 */
class Tokenizer {
    /**
     * This flag indicates that we want to retrieve the position of the matches.
     * This flag affects only to the 'match' function.
     */
    const OFFSET_CAPTURE = 0x1;
    
    /**
     * This flag indicates that we want to distinguish between uppercase and lowercase characters.
     */
    const CASE_SENSITIVE = 0x4;
    
    /**
     * Searches matches anywhere, starting from the offset position.
     */
    const SEARCH_ANYWHERE = 0x8;
    
    /*
     * This regular pattern describes a "token".
     * A token is one or more "word" characters or a single "non-word" character. For example:
     * 
     * hello_there125 -- this is a token because it is a sequence of "word" characters
     * % -- this is a token because it is a single "non-word" chatacter.
     * %! -- this is NOT a token
     */
    const TOKEN = "\w+|.";
    
    /**
     * This regular pattern describes an "identifier".
     * An identifier is an alphabetic character followed by alphanumeric characters. For example:
     * 
     * odyssey2001 -- is an identifier
     * james_bond  -- is an identifier
     * 007bond     -- is NOT an identifier because the first character is not alphabetic
     */
    const IDENTIFIER = "[a-z]\w*";
    
    /**
     * This regular pattern describes a floating point number.
     */
    const NUMBER = '[+-]?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?';
    
    /**
     * This regular pattern describes a string.
     * You can use either single or double quotes delimiters. The following examples are strings:
     * 
     * 'hello there'
     * 'hello \'there'
     * "hello there"
     * "hello \"there"
     */
    const STRING = '(["\'])((?:\\\\\2|.)*?)\2';
    
    /**
     * Flags.
     * @var int
     */
    private $flags;
    
    /**
     * The string to be parsed.
     * @var string
     */
    protected $string;
    
    /**
     * The current offset.
     * @var int
     */
    protected $offset;
    
    /**
     * @param string $string The string to be parsed
     * @param int $flags = 0 This parameter can be Tokenizer::OFFSET_CAPTURE or Tokenizer::CASE_SENSITIVE
     */
    public function __construct($string, $flags = 0) {
        $this->string = $string;
        $this->offset = 0;
        $this->flags = $flags;
    }
    
    /**
     * Is the next equal to a given string?
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * @param string $str
     * @param int $flags = 0
     * @return string
     */
    public function eq($str, $flags = 0) {
        $ret = FALSE;
        
        if (list($str) = $this->match(preg_quote($str, "/"), $matches, $flags)) {
            $ret = array($str);
        }
        
        return $ret;
    }
    
    /**
     * Is the next in a given list?
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * @param array $items An array of strings
     * @param int $flags = 0
     * @return string|FALSE
     */
    public function in($items, $flags = 0) {
        $ret = FALSE;
        
        // sorts the items in descending order according to their length
        usort($items, function($item1, $item2) {
            return strlen($item1) < strlen($item2);
        });
        
        foreach ($items as $item) {
            if ($this->eq($item, $flags)) {
                $ret = array($item);
                break;
            }
        }
        
        return $ret;
    }
    
    /**
     * Is the next a number?
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * @param int $flags = 0
     * @return string|FALSE
     */
    public function number($flags = 0) {
        $ret = FALSE;
        
        if ($number = $this->match(Tokenizer::NUMBER, $matches, $flags)) {
            $ret = $number;
        }
        
        return $ret;
    }
    
    /**
     * Is the next a string?
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * @param int $flags = 0
     * @return string|FALSE
     */
    public function str($flags = 0) {
        $ret = FALSE;
        
        if ($this->match(Tokenizer::STRING, $matches, $flags)) {
            $last_item = end($matches);
            $delimiter = $matches[2];
            $str = $matches[3];
            $str = str_replace("\\$delimiter", "$delimiter", $str);
            $ret = array($str);
        }
        
        return $ret;
    }
    
    /**
     * Is the next a token?
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * Example:
     * 
     * <code>
     * // splits a string into tokens
     * $t = new Tokenizer("lorem ipsum; dolor sit amet.");
     * while (list($token) = $t->token()) {
     *     echo "$token-";
     * }
     * </code>
     * 
     * @return string|FALSE
     */
    public function token() {
        $ret = FALSE;
        
        if (list($token) = $this->match(Tokenizer::TOKEN)) {
            $ret = array($token);
        }
        
        return $ret;
    }
    
    /**
     * Is the next an identifier?
     * @return string|FALSE
     */
    public function id() {
        $ret = FALSE;
        
        if (list($id) = $this->match(Tokenizer::IDENTIFIER)) {
            $ret = array($id);
        }
        
        return $ret;
    }
    
    /**
     * Compares the string with a regular expression and advances the offset if they match.
     * When successful, this function returns an array with a single string. Otherwise, it returns FALSE.
     * 
     * You can use regular expression without delimiters. The advantages of using regular expression without
     * delimiters, is that you do not need to worry about ignoring the left spaces and start parsing from the
     * beginning. The backslash character is reserved for delimiting regular expressions. For example:
     * 
     * <code>
     * // these two lines are identical
     * $t->match("\w+");
     * $t->match("/^\s*(\w+)/");
     * </code>
     * 
     * More examples:
     * 
     * <code>
     * // splits a string into "words"
     * $t = new Tokenizer("Lorem ipsum dolor sit amet");
     * while (list($token) = $t->match("\w+", $matches)) {
     *     echo "$token-";
     * }
     * </code>
     * 
     * // captures the offset
     * <code>
     * $t = new Tokenizer("I am 105 years old");
     * if ($t->match("/\d+/", $matches, Tokenizer::OFFSET_CAPTURE)) {
     *     print_r($matches);
     * }
     * </code>
     * 
     * <code>
     * // parses a basic SQL sentence
     * $t = new Tokenizer("Select Id, Name, Age From users Where Id = 101");
     * if ($t->match("select")) {
     *     // columns
     *     $columns = array();
     *     while (list($column) = $t->match("\w+")) {
     *         array_push($columns, $column);
     *         if (!$t->match(",")) {
     *             break;
     *         }
     *     }
     *     // `from` clause
     *     if ($t->match("from\s+(\w+)", $matches)) {
     *         $table_name = $matches[1];
     *         echo "You want to get the columns " . implode(", ", $columns) . " from the table $table_name.";
     *     }
     * }
     * </code>
     * 
     * @param string $regexp
     * @param array &$matches
     * @param int $flags = 0
     * @return array|FALSE
     * </code>
     */
    public function match($regexp, &$matches = array(), $flags = 0) {
        $ret = FALSE;
        $explicit_regexp = strlen($regexp) > 0 && $regexp[0] == "/";
        $substr = substr($this->string, $this->offset);
        
        if (!$explicit_regexp) {
            $case_sensitive = Tokenizer::CASE_SENSITIVE & ($this->flags | $flags);
            $search_anywhere = Tokenizer::SEARCH_ANYWHERE & ($this->flags | $flags);
            $modifiers = "us" . ($case_sensitive? "" : "i");
            $regexp = $search_anywhere? "/($regexp)/$modifiers" : "/^\s*($regexp)/$modifiers";
        }
        
        if (preg_match($regexp, $substr, $matches, PREG_OFFSET_CAPTURE)) {
            $offset_capture = Tokenizer::OFFSET_CAPTURE & ($this->flags | $flags);
            $str = $matches[0][0];
            $offset = $matches[0][1] + strlen($str);
            
            if ($offset_capture) {
                // fixes offsets
                foreach ($matches as $i => $match) {
                    $matches[$i][1] += $this->offset;
                }
            } else {
                // ignores offsets
                foreach ($matches as $i => $match) {
                    $matches[$i] = $matches[$i][0];
                }
            }
            
            if (!ctype_alnum($substr[$offset - 1]) || $offset == strlen($substr) || !ctype_alnum($substr[$offset])){
                $this->offset += $offset;
                $ret = array(ltrim($str));
            }
        }
        
        return $ret;
    }
    
    /**
     * Gets the offset position.
     * @return int
     */
    public function getOffset() {
        return $this->offset;
    }
    
    /**
     * Sets the offset position.
     * @param string $value
     */
    public function setOffset($value) {
        $this->offset = $value;
    }
    
    /**
     * Gets the string.
     * @return string
     */
    public function getString() {
        return $this->string;
    }
    
    /**
     * Has the offset reached the end of the line?
     * @return boolean
     */
    public function end() {
        return $this->offset >= strlen(rtrim($this->string));
    }
}
