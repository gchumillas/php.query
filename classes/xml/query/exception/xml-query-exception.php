<?php
/**
 * This file contains the XmlQueryException class.
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
use com\soloproyectos\core\xml\exception\XmlException;
use com\soloproyectos\core\xml\query\XmlQuery;

/**
 * Class XmlQueryException.
 * 
 * @category XML
 * @package  XmlQuery
 * @author   Gonzalo Chumillas <gonzalo@soloproyectos.com>
 * @license  https://raw.github.com/soloproyectos/core/master/LICENSE BSD 2-Clause License
 * @link     https://github.com/soloproyectos/core
 */
class XmlQueryException extends XmlException
{
    
    /**
     * Constructor.
     * 
     * @param string   $message Error message
     * @param XmlQuery $query   XmlQuery object (default is null)
     */
    public function __construct($message = "", $query = null)
    {
        if ($query != null && count($query) > 0) {
            $xml = $query->xml();
            $path = $query[0]->getNodePath();
            $message = $message . ":\n$xml\n\nPath:\n$path";
        }
        
        parent::__construct($message);
    }
}
