<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\arr\arguments;

/**
 * Class ArrArgumentsDescriptor.
 *
 * @package Arr\Arguments
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
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
