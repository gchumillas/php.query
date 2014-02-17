<?php
/**
 * This file contains the TextHelper class.
 * 
 * PHP Version 5.3
 * 
 * @category Text
 * @package  Text
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\text;

/**
 * Class TextHelper.
 * 
 * @category Text
 * @package  Text
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class TextHelper
{
    /**
     * Is the string empty?
     * 
     * <p>This function checks if a given variable is an empty string.
     * For example:</p>
     * 
     * <pre>// empty string examples
     * TextHelper::isEmpty('');         // returns true
     * TextHelper::isEmpty(null);       // returns true
     * TextHelper::isEmpty('testing');  // returns false
     * TextHelper::isEmpty(0);          // returns false (as 0 is not a string)
     * </pre>
     * 
     * @param string $str A string
     * 
     * @return boolean
     */
    public static function isEmpty($str)
    {
        return $str === null || is_string($str) && strlen($str) == 0;
    }
    
    /**
     * Concatenates strings.
     * 
     * <p>This function concatenates several strings into a new one, using the
     * $glue parameter. It ignores empty strings. For example:</p>
     * 
     * <pre>
     * // prints 'John, Maria, Mohamad'
     * echo TextHelper::concat(', ', 'John', '', 'Maria', null, 'Mohamad');
     * // prints 'John'
     * echo TextHelper::concat(', ', 'John');
     * // in this case we are using an array as second argument
     * echo TextHelper::concat('|', array('one', 'two', 'three'));
     * </pre>
     * 
     * @param string $glue Separator
     * 
     * @return string
     */
    public static function concat($glue)
    {
        $ret = "";
        $args = array();
        $len = func_num_args();
        
        for ($i = 1; $i < $len; $i++) {
            $value = func_get_arg($i);
            $values = is_array($value)? array_values($value) : array($value);
            $args = array_merge($args, $values);
        }

        foreach ($args as $arg) {
            if (TextHelper::isempty($arg)) {
                continue;
            }
            
            if (strlen($ret) > 0) {
                $ret .= $glue;
            }
            
            $ret .= $arg;
        }

        return $ret;
    }
    
    /**
     * Removes left spaces from a multiline string.
     * 
     * This function removes left spaces from a multiline string, so the first line
     * starts at the first column. It would be the equivalent to 'align to left' in
     * a text editor.
     * 
     * @param string $str Multiline string
     * 
     * @return string
     */
    public static function trimText($str)
    {
        $ret = "";
        $str = preg_replace("/\t/", "    ", $str);
        $lines = explode("\n", $str);
        $len = count($lines);

        // start index.
        // ignores initial empty lines.
        $i0 = 0;
        for ($i = 0; $i < $len; $i++) {
            $line = $lines[$i];
            $trimLine = trim($line);
            if (strlen($trimLine) > 0) {
                $i0 = $i;
                break;
            }
        }

        // final index.
        // ignores final empty lines.
        $i1 = count($lines) - 1;
        for ($i = $len - 1; $i >= 0; $i--) {
            $line = $lines[$i];
            $trimLine = trim($line);
            if (strlen($trimLine) > 0) {
                $i1 = $i;
                break;
            }
        }

        // calculates spaces to remove
        $spaces = PHP_INT_MAX;
        for ($i = $i0; $i <= $i1; $i++) {
            $line = $lines[$i];
            $leftTrimLine = ltrim($line);
            $spaces = min($spaces, strlen($line) - strlen($leftTrimLine));
        }

        // removes left spaces
        for ($i = $i0; $i <= $i1; $i++) {
            $line = $lines[$i];
            $ret = TextHelper::concat("\n", $ret, substr($line, $spaces));
        }

        return $ret;
    }
}
