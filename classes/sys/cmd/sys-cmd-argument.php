<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\sys\cmd;
use com\soloproyectos\common\text\TextHelper;

/**
 * Class SysCmdArgument.
 *
 * This class represents a command line argument.
 *
 * @package Sys\Cmd
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
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
     * Is raw value?
     * @var boolean
     */
    private $_isRaw = false;

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
     * Is a raw value?
     *
     * @return boolean
     */
    public function isRaw()
    {
        return $this->_isRaw;
    }

    /**
     * Sets raw value property.
     *
     * @param boolean $value Is raw value?
     *
     * @return void
     */
    public function setRaw($value)
    {
        $this->_isRaw = $value;
    }

    /**
     * Gets the string representation of the instance.
     *
     * @return string
     */
    public function toString()
    {
        $ret = $this->_name? "-" . SysCmdHelper::escape($this->_name) : null;

        if (!TextHelper::isEmpty($this->_value)) {
            $value = $this->_value;

            if (!$this->_isRaw) {
                $value = SysCmdHelper::escape($value);
            }

            $ret = TextHelper::concat(" ", $ret, $value);
        }

        return $ret;
    }
}
