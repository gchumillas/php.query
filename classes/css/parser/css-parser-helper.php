<?php
/**
 * This file is part of Soloproyectos common library.
 *
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
namespace com\soloproyectos\common\css\parser;

/**
 * Class CssParserHelper.
 *
 * @package Css\Parser
 * @author  Gonzalo Chumillas <gchumillas@email.com>
 * @license https://github.com/soloproyectos/php.common-libs/blob/master/LICENSE BSD 2-Clause License
 * @link    https://github.com/soloproyectos/php.common-libs
 */
class CssParserHelper
{
    /**
     * Escapes a css parameter.
     *
     * @param string $str String
     *
     * @return string
     */
    public static function escape($str)
    {
        return preg_replace_callback(
            "/[-()\[\]{}+?*.$\^|,:#<!\\\]/",
            function ($match) {
                return "\\$match[0]";
            },
            $str
        );
    }
}
