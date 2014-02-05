<?php
/**
 * This file contains the TextTokenizer class.
 * 
 * PHP Version 5.3
 * 
 * @category Text
 * @package  TextTokenizer
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\text\tokenizer;

/**
 * Class TextTokenizer

 * This class can parse and split a string into tokens. It can take a string and
 * split it to retrieve smaller tokens one by one. The format of the tokens is
 * defined by regular expressions passed to the class as parameters.
 * 
 * @category Text
 * @package  TextTokenizer
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class TextTokenizer
{
    /**
     * Offset capture flag.
     * 
     * This flag is similar to the PREG_OFFSET_CAPTURE flag, used in the preg_match
     * function. When it is passed to the function, it returns also the offset
     * position of the matched elements.
     */
    const OFFSET_CAPTURE = 0x1;
    
    /**
     * Case sensitive flag.
     * 
     * When this flag is passed to the function, it distinguishes between lowercase
     * and uppercase characters.
     */
    const CASE_SENSITIVE = 0x4;
    
    /**
     * Search anywhere flag.
     * 
     * When this flag is passed to the function, it searches matches anywhere,
     * starting from the current offset position.
     */
    const SEARCH_ANYWHERE = 0x8;
    
    /**
     * This regular pattern describes a "token".
     * 
     * <p>A token is one or more "word" characters or a single "non-word"
     * character. For example:</p>
     * 
     * <pre>
     * hello_there125 -- this is a token, as it is a sequence of "word" chars.
     * % -- this is a token, as it is a single "non-word" chatacter.
     * %! -- this is NOT a token
     * </pre>
     */
    const TOKEN = "\w+|.";
    
    /**
     * This regular pattern describes an "identifier".
     * 
     * <p>An identifier is an alphabetic character followed by alphanumeric
     * characters. For example:</p>
     * 
     * <pre>
     * odyssey2001 -- is an identifier
     * james_bond  -- is an identifier
     * 007bond -- is NOT an identifier
     * </pre>
     */
    const IDENTIFIER = "[a-z]\w*";
    
    /**
     * This regular pattern describes a number.
     */
    const NUMBER = '[+-]?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?';
    
    /**
     * This regular pattern describes a string.
     * 
     * <p>You can use either single or double quotes delimiters. The following
     * examples are strings:</p>
     * 
     * <pre>
     * 'hello there'
     * 'hello \'there'
     * "hello there"
     * "hello \"there"
     * </pre>
     */
    const STRING = '(["\'])((?:\\\\\2|.)*?)\2';
    
    /**
     * Global flags.
     * @var integer
     */
    private $_flags;
    
    /**
     * The string to be parsed.
     * @var string
     */
    protected $string;
    
    /**
     * The current offset.
     * @var integer
     */
    protected $offset;
    
    /**
     * Constructor.
     * 
     * The $flag argument admits the following values:
     * TextTokenizer::OFFSET_CAPTURE, TextTokenizer::CASE_SENSITIVE and
     * TextTokenizer::SEARCH_ANYWHERE.
     * 
     * @param string  $string The string to be parsed
     * @param integer $flags  Flags (default is 0)
     */
    public function __construct($string, $flags = 0)
    {
        $this->string = $string;
        $this->offset = 0;
        $this->_flags = $flags;
    }
    
    /**
     * Is the next token equal to a given string?
     * 
     * This function returns false if the next token is not equal to a given string
     * or an array with a single string.
     * 
     * @param string  $str   A string
     * @param integer $flags Flags (default is 0)
     * 
     * @return false|array of a single string
     */
    public function eq($str, $flags = 0)
    {
        $ret = false;
        
        if (list($str) = $this->match(preg_quote($str, "/"), $matches, $flags)
        ) {
            $ret = array($str);
        }
        
        return $ret;
    }
    
    /**
     * Is the next token the in a given list?
     * 
     * This function returns false if the next token is not in a given list
     * or an array with a single string.
     * 
     * @param array   $items An array of strings
     * @param integer $flags Flags (default is 0)
     * 
     * @return false|array of a single string
     */
    public function in($items, $flags = 0)
    {
        $ret = false;
        
        // sorts the items in descending order according to their length
        usort(
            $items,
            function ($item1, $item2) {
                return strlen($item1) < strlen($item2);
            }
        );
        
        foreach ($items as $item) {
            if ($this->eq($item, $flags)) {
                $ret = array($item);
                break;
            }
        }
        
        return $ret;
    }
    
    /**
     * Is the next token a number?
     * 
     * This function returns false if the next token is not a number or an array
     * with a single string.
     * 
     * @param integer $flags Flags (default is 0)
     * 
     * @return false|array of a single string
     */
    public function number($flags = 0)
    {
        $ret = false;
        
        if ($number = $this->match(TextTokenizer::NUMBER, $matches, $flags)) {
            $ret = $number;
        }
        
        return $ret;
    }
    
    /**
     * Is the next token a string?
     * 
     * This function returns false if the next token is not a string or an array
     * with a single string.
     * 
     * @param integer $flags Flags (default is 0)
     * 
     * @return false|array of a single string
     */
    public function str($flags = 0)
    {
        $ret = false;
        
        if ($this->match(TextTokenizer::STRING, $matches, $flags)) {
            $delimiter = $matches[2];
            $str = $matches[3];
            $str = str_replace("\\$delimiter", "$delimiter", $str);
            $ret = array($str);
        }
        
        return $ret;
    }
    
    /**
     * Gets the next token.
     * 
     * <p>This function returns false if there are no more tokens or an array with a
     * single string. For example:</p>
     * 
     * <code>// splits a string into tokens
     * $t = new TextTokenizer("lorem ipsum; dolor sit amet.");
     * while (list($token) = $t->token()) {
     *     echo "$token-";
     * }
     * </code>
     * 
     * @return false|array of a single string
     */
    public function token()
    {
        $ret = false;
        
        if (list($token) = $this->match(TextTokenizer::TOKEN)) {
            $ret = array($token);
        }
        
        return $ret;
    }
    
    /**
     * Is the next token an identifier?
     * 
     * This function returns false if the next token is not an identifier or an
     * array with a single string.
     * 
     * @return false|array of a single string
     */
    public function id()
    {
        $ret = false;
        
        if (list($id) = $this->match(TextTokenizer::IDENTIFIER)) {
            $ret = array($id);
        }
        
        return $ret;
    }
    
    /**
     * Matches the string against a regex.
     * 
     * <p>This function matches the string against a regular expressión. If they
     * match, it advances the offset position and returns an array with a single
     * string. Otherwise, it returns false. You can use regex without delimiters.
     * Instead of using /^\s*(\w+)/, you can use simply '\w+'. For example:</p>
     * 
     * <code>// these two lines are identical
     * if ($t->match("\w+")) doSomething();
     * if ($t->match("/^\s*(\w+)/")) doSomething();
     * </code>
     * 
     * <p>Example 1:</p>
     * 
     * <code>// splits a string into "words"
     * $t = new TextTokenizer("Lorem ipsum dolor sit amet");
     * while (list($token) = $t->match("\w+", $matches)) {
     *     echo "$token-";
     * }
     * </code>
     * 
     * <p>Example 2:</p>
     * 
     * <code>// captures the offset
     * $t = new TextTokenizer("I am 105 years old");
     * if ($t->match("/\d+/", $matches, TextTokenizer::OFFSET_CAPTURE)) {
     *     print_r($matches);
     * }
     * </code>
     * 
     * <p>Example 3:</p>
     * 
     * <code>// parses a basic SQL sentence
     * $t = new TextTokenizer("Select Id, Name, Age From users Where Id = 101");
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
     *         $tableName = $matches[1];
     *         echo "You want to get the columns " . implode(", ", $columns) .
     *              " from the table $tableName.";
     *     }
     * }
     * </code>
     * 
     * @param string  $regexp  Regular expression
     * @param array   $matches Matches (default is array(), passed by reference)
     * @param integer $flags   Flags (default is 0)
     * 
     * @return false|array of a single string
     */
    public function match($regexp, &$matches = array(), $flags = 0)
    {
        // we do not like empty strings
        if (strlen($regexp) == 0) {
            return false;
        }
        
        $ret = false;
        $explicitRegexp = strlen($regexp) > 0 && $regexp[0] == "/";
        $substr = substr($this->string, $this->offset);
        
        if (!$explicitRegexp) {
            $caseSensitive  = TextTokenizer::CASE_SENSITIVE
                & ($this->_flags | $flags);
            $searchAnywhere = TextTokenizer::SEARCH_ANYWHERE
                & ($this->_flags | $flags);
            $modifiers = "us" . ($caseSensitive? "" : "i");
            $regexp = $searchAnywhere
                ? "/($regexp)/$modifiers"
                : "/^\s*($regexp)/$modifiers";
        }
        
        if (preg_match($regexp, $substr, $matches, PREG_OFFSET_CAPTURE)) {
            $offsetCapture = TextTokenizer::OFFSET_CAPTURE
                              & ($this->_flags | $flags);
            $str = $matches[0][0];
            $offset = $matches[0][1] + strlen($str);
            
            if ($offsetCapture) {
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
            
            if (!ctype_alnum($substr[$offset - 1])
                || $offset == strlen($substr)
                || !ctype_alnum($substr[$offset])
            ) {
                $this->offset += $offset;
                $ret = array(ltrim($str));
            }
        }
        
        return $ret;
    }
    
    /**
     * Gets the offset position.
     * 
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Sets the offset position.
     * 
     * @param string $value A string value
     * 
     * @return void
     */
    public function setOffset($value)
    {
        $this->offset = $value;
    }
    
    /**
     * Gets the target string.
     * 
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }
    
    /**
     * Resets the parser and start again.
     * 
     * @return void
     */
    public function reset()
    {
        $this->offset = 0;
    }
    
    /**
     * Has the offset reached the end of the line?
     * 
     * @return boolean
     */
    public function end()
    {
        return $this->offset >= strlen(rtrim($this->string));
    }
}
