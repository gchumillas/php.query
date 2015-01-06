<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\text;
use com\soloproyectos\common\text\exception\TextException;

/**
 * Class TextHelper.
 *
 * @package Text
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class TextHelper
{
    /**
     * Is the string empty?
     *
     * This function checks if a given variable is an empty string.
     *
     * For example:
     * ```php
     * // empty string examples
     * TextHelper::isEmpty('');         // returns true
     * TextHelper::isEmpty(null);       // returns true
     * TextHelper::isEmpty('testing');  // returns false
     * TextHelper::isEmpty(0);          // returns false (as 0 is not a string)
     * ```
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
     * Returns $def if $str is empty.
     *
     * @param string $str A string
     * @param mixed  $def Default value
     *
     * @return mixed
     */
    public static function ifEmpty($str, $def)
    {
        return TextHelper::isEmpty($str)? $def : $str;
    }

    /**
     * Concatenates strings.
     *
     * This function concatenates several strings into a new one, using the
     * $glue parameter. It ignores empty strings.
     *
     * For example:
     * ```php
     * // prints 'John, Maria, Mohamad'
     * echo TextHelper::concat(', ', 'John', '', 'Maria', null, 'Mohamad');
     * // prints 'John'
     * echo TextHelper::concat(', ', 'John');
     * // in this case we are using an array as second argument
     * echo TextHelper::concat('|', array('one', 'two', 'three'));
     * ```
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
     * Replaces arguments in a string.
     *
     * Example 1:
     * ```php
     * echo TextHelper::replaceArgs(
     *      "Hello {name}, how are you? I'm {state} thanks\n", array("Antonio", "fine")
     * );
     * ```
     *
     * Example 2: use a function name
     * ```php
     * echo TextHelper::replaceArgs(
     *      "Hello {name}, how are you? I'm {state} thanks\n", array("  Antonio  ", "  fine  "), "trim"
     * );
     * ```
     *
     * Example 3: use a closure
     * ```php
     * echo TextHelper::replaceArgs(
     *      "Hello {name}, how are you? I'm {state} thanks\n", array("Antonio", "fine"), function ($x) {
     *          return "'$x'";
     *      }
     * );
     * ```
     *
     * Example 3: use a method
     * ```php
     * echo TextHelper::replaceArgs(
     *      "Hello {name}, how are you? I'm {state} thanks\n",
     *      array("Antonio", "fine"),
     *      array($obj, "method")
     * );
     * ```
     *
     * @param string   $str        String
     * @param array    $args       Parameters
     * @param callable $escapeFunc Escape function (not required)
     *
     * @return string
     */
    public static function replaceArgs($str, $args, $escapeFunc = null)
    {
        if ($escapeFunc === null) {
            $escapeFunc = function ($x) {
                return $x;
            };
        }

        if (!is_callable($escapeFunc)) {
            throw new TextException("Escape function is not callable");
        }

        return preg_replace_callback(
            '/(^|[^\\\]){\w+}/',
            function ($match) use (&$args, $escapeFunc) {
                return $match[1] . call_user_func(
                    $escapeFunc, (count($args) > 0? array_shift($args) : $match[0])
                );
            },
            $str
        );
    }

    /**
     * Removes left spaces from a multiline string.
     *
     * <p>This function removes left spaces from a multiline string, so the first line
     * starts at the first column. It would be the equivalent to 'align to left' in
     * a text editor.</p>
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
