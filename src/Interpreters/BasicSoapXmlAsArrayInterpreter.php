<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters;

/**
 * Interprets SOAP (XML) response data, directly taking over SOAP response object
 */
class BasicSoapXmlAsArrayInterpreter extends BasicSoapXmlInterpreter
{
    protected bool $asArray = true;
}
