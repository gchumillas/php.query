<?php
/**
 * This file contains the XmlQueryExceptionInvalidDocument class.
 * 
 * PHP Version 5.3
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
namespace com\soloproyectos\core\xml\query\exception;
use com\soloproyectos\core\xml\query\exception\XmlQueryException;

/**
 * Class XmlQueryExceptionInvalidDocument.
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class XmlQueryExceptionInvalidDocument extends XmlQueryException
{
    
    /**
     * Constructor.
     * 
     * @param string $message Error message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
