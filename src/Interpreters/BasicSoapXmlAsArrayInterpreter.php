<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets SOAP (XML) response data, directly taking over SOAP response object
 */
class BasicSoapXmlAsArrayInterpreter extends BasicSoapXmlInterpreter
{
    protected $asArray = true;

}
