<?php
/**
 * This file contains the ArrArgumentsDescriptor class.
 * 
 * PHP Version 5.3
 * 
 * @category Tools_And_Utilities
 * @package  Arr
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\arr\arguments;

/**
 * Class ArrArgumentsDescriptor.
 * 
 * @category Tools_And_Utilities
 * @package  Arr
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class ArrArgumentsDescriptor
{
    /**
     * List of variable types.
     * @var array
     */
    private $_types;
    
    /**
     * Default value.
     * @var mixed
     */
    private $_default;
    
    /**
     * Is a required argument?
     * @var boolean
     */
    private $_isRequired;
    
    /**
     * Constructor.
     * 
     * @param array   $types    Variable types
     * @param mixed   $default  Default value
     * @param boolean $required Is a required argument?
     * 
     * @return void
     */
    public function __construct($types, $default = null, $required = false)
    {
        $this->_types = $types;
        $this->_default = $default;
        $this->_isRequired = $required;
    }
    
    /**
     * Gets default value.
     * 
     * @return mixed
     */
    public function getDefault()
    {
        return $this->_default;
    }
    
    /**
     * Is the argument required?
     * 
     * @return boolean
     */
    public function isRequired()
    {
        return $this->_isRequired;
    }
    
    /**
     * Does the variable match with this descriptor?
     * 
     * @param mixed $var Arbitrary variable
     * 
     * @return boolean
     */
    public function match($var)
    {
        $ret = false;
        
        foreach ($this->_types as $type) {
            if (array_search($type, array("*", "mixed")) !== false) {
                $ret = true;
            } elseif (array_search($type, array("number", "numeric")) !== false) {
                $ret = is_numeric($var);
            } elseif (array_search($type, array("bool", "boolean")) !== false) {
                $ret = is_bool($var);
            } elseif ($type == "string") {
                $ret = is_string($var);
            } elseif ($type == "array") {
                $ret = is_array($var);
            } elseif ($type == "object") {
                $ret = is_object($var);
            } elseif ($type == "resource") {
                $ret = is_resource($var);
            } elseif ($type == "function") {
                $ret = is_callable($var);
            } elseif ($type == "scalar") {
                $ret = is_scalar($var);
            } else {
                $ret = is_a($var, $type);
            }
            
            if ($ret) {
                break;
            }
        }
        
        return $ret;
    }
}
