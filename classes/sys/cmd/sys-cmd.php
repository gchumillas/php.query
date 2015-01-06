<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\sys\cmd;
use com\soloproyectos\common\sys\cmd\exception\SysCmdException;

/**
 * Class SysCmd.
 *
 * This function is used to executes and compose command lines.
 *
 * @package Sys\Cmd
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class SysCmd
{
    /**
     * List of arguments.
     * @var array of SysCmdArgument objects
     */
    protected $args;

    /**
     * Command.
     * @var string
     */
    protected $command;

    /**
     * Constructor.
     *
     * @param string $command Command line
     */
    public function __construct($command)
    {
        $this->args = array();
        $this->command = $command;
    }

    /**
     * Appends a flag name.
     *
     * Example 1:
     * ```php
     * // list detailed files
     * $cmd = new SysCmd("ls");
     * $cmd->appendFlag("al");
     * echo $cmd->toString();  // prints "ls -al"
     * ```
     *
     * Example 2:
     * ```php
     * $cmd = new SysCmd("find .");
     * $cmd->appendFlag("type", "f");
     * echo $cmd->toString(); // prints "find . -type f"
     * ```
     *
     * @param string $name  Flag name
     * @param string $value A value (default is "")
     *
     * @return SysCmdArgument
     */
    public function appendFlag($name, $value = "")
    {
        $arg = new SysCmdArgument();
        $arg->setName($name);
        $arg->setValue($value);
        array_push($this->args, $arg);
        return $arg;
    }

    /**
     * Appends a single argument.
     *
     * For example:
     * ```php
     * // list detailed files under my_dir
     * $cmd = new SysCmd("ls");
     * $cmd->appendFlag("al");
     * $cmd->appendArg("my_dir");
     * echo $cmd->toString(); // prints "ls -al my_dir"
     * ```
     *
     * @param string|SysCmdArgument $value A value
     * @param string                $name  Argument name (default is "")
     *
     * @return SysCmdArgument
     */
    public function appendArg($value, $name = "")
    {
        $arg = null;

        if (!$value instanceof SysCmdArgument) {
            $arg = new SysCmdArgument();
            $arg->setValue($value);
            $arg->setName($name);
        } else {
            $arg = $value;
        }

        array_push($this->args, $arg);
        return $arg;
    }

    /**
     * Appends a list of arguments.
     *
     * For example:
     * ```php
     * // converts mkv to avi
     * $cmd = new SysCmd("ffmpeg");
     * $cmd->appendFlag("i", "input.mkv");
     * $cmd->appendArgList("-f avi -c:v mpeg4 -b:v 4000k -c:a libmp3lame -b:a 320k");
     * $cmd->appendArg("output.avi");
     * $cmd->toString();
     *
     * // the above command prints:
     * // ffmpeg -i input.mkv -f avi -c:v mpeg4 -b:v 4000k \
     * // -c:a libmp3lame -b:a 320k output.avi
     * ```
     *
     * @param string $arguments List of arguments
     *
     * @return void
     */
    public function appendArgList($arguments)
    {
        $arguments = trim($arguments);

        if (strlen($arguments) > 0) {
            $p = new SysCmdArgumentsParser($arguments);
            if (!$args = $p->parse()) {
                throw new SysCmdException("Invalid arguments");
            }

            // appends arguments
            foreach ($args as $arg) {
                array_push($this->args, $arg);
            }
        }
    }

    /**
     * Appends a raw string.
     *
     * @param string $value Raw string
     *
     * @return SysCmdArgument
     */
    public function appendString($value)
    {
        $arg = new SysCmdArgument();
        $arg->setRaw(true);
        $arg->setValue($value);
        array_push($this->args, $arg);
        return $arg;
    }

    /**
     * Executes a command.
     *
     * This function executes a command and returns the output.
     *
     * @return string
     */
    public function exec()
    {
        return SysCmdHelper::exec($this->toString());
    }

    /**
     * Executes a command in background.
     *
     * This function executes a command in background and returns the PID.
     *
     * @param string $outputFile Output file (default is "/dev/null")
     *
     * @return int
     */
    public function execBg($outputFile = "/dev/null")
    {
        return SysCmdHelper::execBg($this->toString(), $outputFile);
    }

    /**
     * Is the process running?
     *
     * @return boolean
     */
    public function isRunning()
    {
        return SysCmdHelper::isRunning($this->pid);
    }

    /**
     * Gets the string representation of the instance.
     *
     * @return string
     */
    public function toString()
    {
        $ret = SysCmdHelper::escapeCmd($this->command);

        foreach ($this->args as $arg) {
            $ret .= " " . $arg->toString();
        }

        return $ret;
    }
}
