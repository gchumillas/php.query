<?php
/**
 * This file contains the XmlTransformer class.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  XmlTransformer
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\xml\transformer;
use com\soloproyectos\core\arr\Arr;
use com\soloproyectos\core\xml\Xml;

/**
 * Class XmlTransformer.
 * 
 * This class transforma an XML document into another XML document.
 * 
 * @category XML
 * @package  XmlTransformer
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
abstract class XmlTransformer
{
    /**
     * Input XML document.
     * This is a well formed XML document.
     * @var string
     */
    private $_input;
    
    /**
     * Output XML document.
     * This is the transformed XML document.
     * @var string
     */
    private $_output;
    
    /**
     * The current tag name.
     * @var string
     */
    private $_tagName;
    
    /**
     * The current tag attributes
     * @var array
     */
    private $_tagAttrs;
    
    /**
     * Start handler functions.
     * @var array
     */
    private $_startHandlers;
    
    /**
     * End handler functions.
     * @var array
     */
    private $_endHandlers;
    
    /**
     * Data handler functions.
     * @var array
     */
    private $_dataHandlers;
    
    /**
     * The current error message, if any.
     * @var string
     */
    private $_error;
    
    /**
     * The current error code, if any.
     * @var integer
     */
    private $_errorCode;
    
    /**
     * Constructor.
     * 
     * @param string $input A well formed xml document
     */
    public function __construct($input = null)
    {
        $this->_input = $input;
        $this->_output = "";
        $this->_tagName = "";
        $this->_tagAttrs = array();
        $this->_startHandlers = array();
        $this->_endHandlers = array();
        $this->_dataHandlers = array();
        $this->_errorCode = 0;
    }
    
    /**
     * Transform an XML document.
     * 
     * This function transform a well formed XML document into another XML document.
     * 
     * @param string $input A well formed XML document
     * 
     * @return string
     */
    public function transform($input = null)
    {
        if (is_file($this->_input)) {
            $this->_input = file_get_contents($this->_input);
        } else {
            $this->_input = $input;
        }
        
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler(
            $parser,
            array(&$this, "_defaultStartHandler"),
            array(&$this, "_defaultEndHandler")
        );
        xml_set_character_data_handler(
            $parser,
            array(&$this, "_defaultDataHandler")
        );
        if (xml_parse($parser, $this->_input) == 0) {
            $this->_errorCode = xml_get_error_code($parser);
            $this->_error = xml_error_string($this->_errorCode);
        }
        xml_parser_free($parser);
        
        return $this->_output;
    }
    
    /**
     * Gets the error code.
     * 
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }
    
    /**
     * Gets the error message.
     * 
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * Stets the start tag handler.
     * 
     * @param string       $tag     Tagname
     * @param string|array $handler Handlers
     * 
     * @return void
     */
    public function startHandler($tag, $handler)
    {
        $this->_startHandlers[$tag] = $handler;
    }
    
    /**
     * Sets the end tag handler.
     * 
     * @param string       $tag     Tagname
     * @param string|array $handler Handlers
     * 
     * @return void
     */
    public function endHandler($tag, $handler)
    {
        $this->_endHandlers[$tag] = $handler;
    }
    
    /**
     * Sets the data tag handler.
     * 
     * @param string       $tag     Tagname
     * @param string|array $handler Handlers
     * 
     * @return void
     */
    public function dataHandler($tag, $handler)
    {
        $this->_dataHandlers[$tag] = $handler;
    }
    
    /**
     * Default start tag handler.
     * 
     * @param resource $parser XML parser
     * @param string   $name   Handler name
     * @param array    $attrs  Attributes
     * 
     * @return void
     */
    private function _defaultStartHandler($parser, $name, $attrs)
    {
        $this->_tagName = $name;
        $this->_tagAttrs = $attrs;
        if (Arr::exist($this->_startHandlers, $name)) {
            $this->_output .= call_user_func(
                $this->_startHandlers[$name],
                $name,
                $attrs
            );
        } else {
            $attrsStr = null;
            if (count($attrs) > 0) {
                foreach ($attrs as $key => $value) {
                    $attrsStr .= ' ' . $key . '="' . Xml::attr($value) . '"';
                }
            }
            $this->_output .=  "<$name$attrsStr>";
        }
    }
    
    /**
     * Default end tag handler
     * 
     * @param resource $parser XML parser
     * @param string   $name   Handler name
     * 
     * @return void
     */
    private function _defaultEndHandler($parser, $name)
    {
        if (Arr::exist($this->_endHandlers, $name)) {
            $this->_output .= call_user_func(
                $this->_endHandlers[$name],
                $name,
                $this->_tagAttrs
            );
        } else {
            $this->_output .= "</$name>";
        }
        $this->_tagName = null;
        $this->_tagAttrs = array();
    }
    
    /**
     * Default data tag handler.
     * 
     * @param resource $parser XML parser
     * @param string   $data   Data
     * 
     * @return void
     */
    private function _defaultDataHandler($parser, $data)
    {
        if (Arr::exist($this->_dataHandlers, $this->_tagName)) {
            $this->_output .= call_user_func(
                $this->_dataHandlers[$this->_tagName],
                $this->_tagName,
                $this->_tagAttrs,
                $data
            );
        } else {
            $this->_output .= $data;
        }
    }
}
