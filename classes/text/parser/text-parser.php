<?php
/**
 * This file contains the TextParser class.
 * 
 * PHP Version 5.3
 * 
 * @category Text
 * @package  TextParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\text\parser;
use com\soloproyectos\core\text\parser\exception\TextParserException;
use com\soloproyectos\core\text\tokenizer\TextTokenizer;

/**
 * class TextParser
 * 
 * This class is for parsing purpose.
 * 
 * @category Text
 * @package  TextParser
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
abstract class TextParser extends TextTokenizer
{
    /**
     * Non-geedy parsing.
     * This flag indicates that we want to perform a "non-greedy" parsing. This flag
     * is similar to the PCRE_UNGREEDY modifier in regular expressions.
     */
    const UNGREEDY = 0x4;
    
    /**
     * String to be parsed or a TextParser object.
     * @var string|TextParser
     */
    private $_target;
    
    /**
     * Flags.
     * @var integer
     */
    private $_flags;
    
    /**
     * Constructor.
     * 
     * @param string|TextParser $target Target object
     * @param integer           $flags  Flags (default is 0, accepts:
     *                                  TextParser::UNGREEDY)
     */
    public function __construct($target, $flags = 0)
    {
        $this->_target = $target;
        $this->_flags = $flags;
        
        if ($this->_target instanceof Parser) {
            parent::__construct($target->getString(), $flags);
            $this->offset = $target->getOffset();
        } else {
            parent::__construct($target, $flags);
        }
    }
    
    /**
     * Evaluates an expression.
     * 
     * This function is called by the 'TextParser::parse()' function.
     * 
     * @see TextParser::parse()
     * @return mixed
     */
    abstract protected function evaluate();
    
    /**
     * Parses a string.
     * 
     * <p>This function parses a string and throws an exception if unsuccessful. For
     * example:</p>
     * 
     * <code>// parses a string
     * $p = new MyCustomParser($string);
     * try {
     *     $info = $p->parse();
     * } catch(TextParserException $e) {
     *     echo $e->getPrintableMessage();
     * }
     * 
     * if (!$info) {
     *     echo "This is not a valid expressión";
     * } else {
     *     print_r($info);
     * }
     * </code>
     * 
     * @param string $string String target (default is "")
     * 
     * @throws Exception
     * @return mixed
     */
    public function parse($string = "")
    {
        $this->offset = 0;
        $this->string = func_num_args() > 0 ? $string : $this->string;
        
        $ungreedy = TextParser::UNGREEDY & $this->_flags;
        $ret = $this->evaluate();
        
        if ($ret) {
            if ($this->_target instanceof TextParser) {
                $this->_target->setOffset($this->offset);
            } elseif (!$ungreedy && !$this->end()) {
                throw new TextParserException("Unrecognized expression", $this);
            }
        }
        
        return $ret;
    }
    
    /**
     * Does the next thing satisfies a given method?
     * 
     * Matches the string against a function and moves the offset forward if the
     * function returns true.
     * 
     * @param string $methodName Method name
     * 
     * @throws TextParserException
     * @return mixed
     */
    protected function is($methodName /*, $arg1, $arg2, $arg3 ... */)
    {
        if (!method_exists($this, $methodName)) {
            throw new TextParserException(
                "The method `$methodName` does not exist"
            );
        }
        
        if (!is_callable(array($this, $methodName))) {
            throw new TextParserException(
                "The method `$methodName` is inaccessible"
            );
        }
        
        // saves offset
        $offset = $this->offset;
        
        // calls user function
        $ret = call_user_func_array(
            array($this, $methodName),
            array_slice(func_get_args(), 1)
        );
        
        // restores offset
        if (!$ret) {
            $this->offset = $offset;
        }
        
        return $ret;
    }
}
