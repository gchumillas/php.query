<?php
/**
 * This file contains the XmlHelper class.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  Xml
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\common\xml;

/**
 * Class XmlHelper.
 * 
 * This class is used to create XML documents.
 * 
 * @category XML
 * @package  Xml
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class XmlHelper
{
    /**
     * Gets a CDATA block.
     * 
     * <p>This function wraps a text in a CDATA block. For example:</p>
     * <pre>// cdata example
     * echo Xml::cdata("hello there!"); // prints &lt;![CDATA[hello there!]]&gt;
     * </pre>
     * 
     * @param string $text A string
     * 
     * @return string
     */
    public static function cdata($text)
    {
        $text = str_replace(array("<![", "]>"), array("&lt;![", "]&gt;"), $text);
        return "<![CDATA[$text]]>";
    }
    
    /**
     * Escapes an XML attribute.
     * 
     * <p>This function escapes a text to be used as an argument in an XML node.
     * This function is equivalent to htmlentities. For example:</p>
     * 
     * <pre>// attr example
     * echo Xml::attr("M & Em's"); // prints "M &amp;amp; Em's"
     * </pre>
     * 
     * @param string $text A string
     * 
     * @return string
     */
    public static function attr($text)
    {
        return htmlentities($text);
    }
    
    /**
     * Gets an XML comment.
     * 
     * <p>This function wraps a text in a XML comment. For example:</p>
     * <pre>// comment example
     * echo Xml::comment("hello there!"); // prints "&lt;!--hello there!--&gt;"
     * </pre>
     * 
     * @param string $text A string
     * 
     * @return string
     */
    public static function comment($text)
    {
        $text = str_replace(array("<!--", "-->"), array("&lt;!--", "--&gt;"), $text);
        return "<!--$text-->";
    }
}
