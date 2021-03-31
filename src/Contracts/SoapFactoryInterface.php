<?php

namespace Czim\Service\Contracts;

use SoapClient;

interface SoapFactoryInterface
{
    /**
     * @param string               $class the soapclient class to use
     * @param string|null          $wsdl
     * @param array<string, mixed> $config
     * @return SoapClient
     */
    public function make(string $class, ?string $wsdl, array $config = []): SoapClient;
}
