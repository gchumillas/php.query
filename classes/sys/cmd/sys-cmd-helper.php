<?php
/**
 * This file contains the SysCmdHelper class.
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
use com\soloproyectos\core\text\Text;

/**
 * Class SysCmdHelper.
 * 
 * This is a helper class.
 * 
 * @category System
 * @package  SysCmd
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class SysCmdHelper
{
    
    /**
     * Escapes an argument.
     * 
     * @param string $arg An argument
     * 
     * @return string
     */
    public static function escape($arg)
    {
        $pattern = '/^[\.\w]+$/';
        $ret = preg_match($pattern, $arg)
            ? $arg
            : escapeshellarg($arg);
        return $ret;
    }
    
    /**
     * Escapes a command.
     * 
     * @param string $command Command line
     * 
     * @return string
     */
    public static function escapeCmd($command)
    {
        return escapeshellcmd($command);
    }
    
    /**
     * Executes a command.
     * 
     * This function executes a command and returns the output.
     * 
     * @param string $command A command line
     * 
     * @return string
     */
    public static function exec($command)
    {
        exec("$command 2>&1", $output, $state);
        $output = implode("\n", $output);
        
        if ($state > 0) {
            throw new SysCmdException("$command:\n\n$output");
        }
        
        return $output;
    }
    
    /**
     * Executes a command in background.
     * 
     * This function executes a command in background and returns the PID of the
     * process.
     * 
     * @param string $command    A command line
     * @param string $outputFile Output file (default is "/dev/null")
     * 
     * @return integer PID of the command
     */
    public static function execBg($command, $outputFile = "/dev/null")
    {
        $pid = trim(
            shell_exec(
                "nohup $command > "
                . SysCmdHelper::escape($outputFile)
                . " 2>&1 & echo $!"
            )
        );
        return $pid;
    }
    
    /**
     * Is a process running?
     * 
     * Checks if a process is still running.
     * 
     * @param integer $pid Process identifier
     * 
     * @return boolean
     */
    public static function isRunning($pid)
    {
        try {
            $result = shell_exec("ps " . SysCmdHelper::escape($pid));
            
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch(Exception $e) {
            // ignore exception
        }
        
        return false;
    }
    
    /**
     * Does a command exist?
     * 
     * @param string $command Command line
     * 
     * @return boolean
     */
    public static function exist($command)
    {
        $result = trim(shell_exec("which $command"));
        return !Text::isEmpty($result);
    }
}
