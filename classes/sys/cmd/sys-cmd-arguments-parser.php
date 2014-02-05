<?php
/**
 * This file contains the SysCmdArgumentsParser class.
 * 
 * PHP Version 5.3
 * 
 * @category System
 * @package  SysCmd
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\sys\cmd;
use com\soloproyectos\core\sys\cmd\SysCmdArgument;
use com\soloproyectos\core\text\parser\exception\TextParserException;
use com\soloproyectos\core\text\parser\TextParser;

/**
 * Class SysCmdArgumentsParser.
 * 
 * This class parses a list of arguments.
 * 
 * @category System
 * @package  SysCmd
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class SysCmdArgumentsParser extends TextParser
{
    /**
     * Describes an unquoted text.
     * This regular pattern describes an unquoted text.
     */
    const UNQUOTED_TEXT = "[^\s\$\|#&\?\*\>\<\\\\]+";
    
    /**
     * Is the next thing a text?
     * 
     * @return false|array of a single string
     */
    protected function text()
    {
        $value = "";
        
        if ((!list($value) = $this->match(SysCmdArgumentsParser::UNQUOTED_TEXT))
            && (!list($value) = $this->str())
        ) {
            return false;
        }
        
        return array($value);
    }
    
    /**
     * Is the next thing an argument?
     * 
     * @return false|SysCmdArgument
     */
    protected function argument()
    {
        $name = "";
        $value = "";
        
        if ($this->eq("-")) {
            if (!list($name) = $this->is("text")) {
                throw new TextParserException("Invalid flag", $this);
            }
            
            list($value) = $this->is("text");
        } elseif (!list($value) = $this->is("text")) {
            return false;
        }
        
        $arg = new SysCmdArgument();
        $arg->setName($name);
        $arg->setValue($value);
        return $arg;
    }
    
    /**
     * Parses the arguments.
     * 
     * @return false|array of SysCmdArgument objects
     */
    protected function evaluate()
    {
        $args = array();
        
        if (!$arg = $this->is("argument")) {
            return false;
        }
        
        array_push($args, $arg);
        while ($arg = $this->is("argument")) {
            array_push($args, $arg);
        }
        
        return $args;
    }
}
