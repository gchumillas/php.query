<?php
/**
 * This file contains the SysCmd class.
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
use com\soloproyectos\core\sys\cmd\exception\SysCmdException;

/**
 * Class SysCmd.
 * 
 * This function is used to executes and compose command lines.
 * 
 * @category System
 * @package  SysCmd
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
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
     * <p>Example 1:</p>
     * <code>// list detailed files
     * $cmd = new SysCmd("ls");
     * $cmd->appendFlag("al");
     * echo $cmd->toString();  // prints "ls -al"
     * </code>
     * 
     * <p>Example 2:</p>
     * <code>// example 2
     * $cmd = new SysCmd("find .");
     * $cmd->appendFlag("type", "f");
     * echo $cmd->toString(); // prints "find . -type f"
     * </code>
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
     * <p>Example:</p>
     * <code>// list detailed files under my_dir
     * $cmd = new SysCmd("ls");
     * $cmd->appendFlag("al");
     * $cmd->appendArg("my_dir");
     * echo $cmd->toString(); // prints "ls -al my_dir"
     * </code>
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
     * <p>Example:</p>
     * <code>// converts mkv to avi
     * $cmd = new SysCmd("ffmpeg");
     * $cmd->appendFlag("i", "input.mkv");
     * $cmd->appendArgList("-f avi -c:v mpeg4 -b:v 4000k -c:a libmp3lame -b:a 320k");
     * $cmd->appendArg("output.avi");
     * $cmd->toString();
     * 
     * // the above command prints:
     * // ffmpeg -i input.mkv -f avi -c:v mpeg4 -b:v 4000k \
     * // -c:a libmp3lame -b:a 320k output.avi
     * </code>
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
                throw new CmdException("Invalid arguments");
            }
            
            // appends arguments
            foreach ($args as $arg) {
                array_push($this->args, $arg);
            }
        }
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
