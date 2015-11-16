<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets raw XML string response data as an associative array
 */
class BasicRawXmlAsArrayInterpreter extends BasicRawXmlInterpreter
{

    protected $asArray = true;

}
