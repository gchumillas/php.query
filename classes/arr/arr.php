<?php
/**
 * This file contains the Arr class.
 * 
 * PHP Version 5.3
 * 
 * @category Tools_And_Utilities
 * @package  Arr
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\arr;
use com\soloproyectos\core\arr\arguments\ArrArguments;
use com\soloproyectos\core\arr\arguments\ArrArgumentsDescriptor;

/**
 * Class Arr.
 * 
 * @category Tools_And_Utilities
 * @package  Arr
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class Arr
{
    
    /**
     * Gets an attribute from a given array.
     * 
     * @param array  $arr     Array object
     * @param string $name    Attribute name
     * @param mixed  $default Default value (default is null)
     * 
     * @return mixed
     */
    public static function get($arr, $name, $default = null)
    {
        return array_key_exists($name, $arr)? $arr[$name] : $default;
    }
    
    /**
     * Sets an attribute.
     * 
     * @param array  $arr   Array object (passed by reference)
     * @param string $name  Attribute name
     * @param mixed  $value Value
     * 
     * @return void
     */
    public static function set(&$arr, $name, $value)
    {
        $arr[$name] = $value;
    }
    
    /**
     * Does the attribute exist?
     * 
     * @param array  $arr  Array object
     * @param string $name Attribute name
     * 
     * @return boolean
     */
    public static function exist($arr, $name)
    {
        return array_key_exists($name, $arr);
    }
    
    /**
     * Deletes an attribute.
     * 
     * @param array  $arr  Array object (passed by reference)
     * @param string $name Attribute name
     * 
     * @return void
     */
    public static function del(&$arr, $name)
    {
        if (array_key_exists($name, $arr)) {
            unset($arr[$name]);
        }
    }
    
    /**
     * Fetches the elements of an array that matches a given list of descriptors.
     * 
     * <p>This function is especially suitable for getting optional arguments.
     * In the below example, 'title' is the only required argument. The
     * arguments 'message', 'x' and 'y' are not required and have default values.
     * The argument 'options' is not required and the default value is null. Note
     * that the arguments 'x' and 'y' can be either strings or numbers. Available
     * types are: '*' or 'mixed', 'number' or 'numeric', 'bool' or 'boolean',
     * 'string', 'array', 'objetc', 'resource', 'function'.</p>
     * <pre>
     * function test($title, $message, $x, $y, $options) {
     *      $args = Arr::fetch(func_get_args(), array(
     *          "title" => "string",
     *          "message" => array(
     *              "type" => "string",
     *              "default" => "Default message ..."
     *          ),
     *          "x" => array(
     *              "type" => "string|number",
     *              "default" => 0
     *          ),
     *          "y" => array(
     *              "type" => "string|number",
     *              "default" => 0
     *          ),
     *          "options" => array(
     *              "type"  => "array",
     *              required => false
     *          )
     *      );
     *      print_r($args);
     * }
     * // this throws an InvalidArgumentException, as 'title' is required.
     * test(120, 250);
     * </pre>
     * 
     * @param array $arr         Array of mixed elements
     * @param array $descriptors Associative array of descriptors.
     * 
     * @return array
     * @throws InvalidArgumentException
     */
    public static function fetch($arr, $descriptors)
    {
        $args = new ArrArguments($arr);

        foreach ($descriptors as $name => $descriptor) {
            $types = array();
            $default = null;
            $required = false;
            
            if (is_string($descriptor)) {
                $types = explode("|", $descriptor);
            } elseif (is_array($descriptor)) {
                $types = explode("|", Arr::get($descriptor, "type"));
                $default = Arr::get($descriptor, "default");
                $required = Arr::get(
                    $descriptor, "required", !Arr::exist($descriptor, "default")
                );
            }
            
            $desc = new ArrArgumentsDescriptor($types, $default, $required);
            $args->registerDescriptor($name, $desc);
        }

        return $args->fetch();
    }
}
