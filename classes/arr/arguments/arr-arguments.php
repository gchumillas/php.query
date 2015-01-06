<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\arr\arguments;
use \Exception;
use com\soloproyectos\common\arr\ArrHelper;
use InvalidArgumentException;

/**
 * Class ArrArguments.
 *
 * @package Arr\Arguments
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class ArrArguments
{
    /**
     * Arguments.
     * @var array
     */
    private $_arguments;

    /**
     * Associative array of descriptors.
     * @var array
     */
    private $_descriptors;

    /**
     * Constructor.
     *
     * @param array $arguments Arguments
     *
     * @return void
     */
    public function __construct($arguments)
    {
        $this->_arguments = $arguments;
    }

    /**
     * Registers a new descriptor.
     *
     * @param string                 $name       Descriptor name
     * @param ArrArgumentsDescriptor $descriptor Argument descriptor
     *
     * @return void
     */
    public function registerDescriptor($name, $descriptor)
    {
        $this->_descriptors[$name] = $descriptor;
    }

    /**
     * Fetches elements from the arguments that matches specific descriptors.
     *
     * @return array associative array
     * @throws InvalidArgumentException
     */
    public function fetch()
    {
        $ret = array();
        $pos = 0;
        $len = count($this->_arguments);

        foreach ($this->_descriptors as $name => $descriptor) {
            $value = $descriptor->getDefault();

            for ($i = $pos; ; $i++) {
                if ($i < $len && $descriptor->match($this->_arguments[$i])) {
                    $value = $this->_arguments[$i];
                    $pos = $i + 1;
                    break;
                } else {
                    if ($descriptor->isRequired()) {
                        throw new InvalidArgumentException(
                            "Argument is required: `$name`"
                        );
                    }
                    if ($i > $len - 1) {
                        break;
                    }
                }
            }

            $ret[$name] = $value;
        }

        return $ret;
    }
}
