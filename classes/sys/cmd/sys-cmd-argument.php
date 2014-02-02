<?php
/**
 * This file contains the SysCmdArgument class.
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
use com\soloproyectos\core\text\Text;

/**
 * Class SysCmdArgument.
 * 
 * This class represents a command line argument.
 * 
 * @category System
 * @package  SysCmd
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class SysCmdArgument
{
    /**
     * Argument name.
     * @var string
     */
    private $_name;
    
    /**
     * Argument value.
     * @var string
     */
    private $_value;
    
    /**
     * Gets the argument name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Sets an argument name.
     * 
     * @param string $name Argument name
     * 
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Gets the argument value.
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Sets an argument value.
     * 
     * @param string $value A value
     * 
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }
    
    /**
     * Gets the string representation of the instance.
     * 
     * @return string
     */
    public function toString()
    {
        $ret = $this->_name? "-" . SysCmdHelper::escape($this->_name) : null;
        
        if (!Text::isEmpty($this->_value)) {
            $ret = Text::concat(" ", $ret, SysCmdHelper::escape($this->_value));
        }
        
        return $ret;
    }
}
