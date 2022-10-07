<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use BeSimple\SoapClient\SoapClient;

class BeSimpleSoapService extends SoapService
{
    /**
     * {@inheritDoc}
     */
    protected string $soapClientClass = SoapClient::class;
}
