<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\sys\cmd;
use com\soloproyectos\common\sys\cmd\SysCmdArgument;
use com\soloproyectos\common\text\parser\exception\TextParserException;
use com\soloproyectos\common\text\parser\TextParser;

/**
 * Class SysCmdArgumentsParser.
 *
 * This class parses a list of arguments.
 *
 * @package Sys\Cmd
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
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
