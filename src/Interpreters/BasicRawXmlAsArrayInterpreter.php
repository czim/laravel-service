<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters;

/**
 * Interprets raw XML string response data as an associative array
 */
class BasicRawXmlAsArrayInterpreter extends BasicRawXmlInterpreter
{
    protected bool $asArray = true;
}
