<?php

declare(strict_types=1);

namespace Czim\Service\Factories;

use Czim\Service\Contracts\SoapFactoryInterface;
use ReflectionClass;
use SoapClient;

class SoapFactory implements SoapFactoryInterface
{
    /**
     * @param string               $class The soapclient class to use
     * @param string|null          $wsdl
     * @param array<string, mixed> $config
     * @return SoapClient
     */
    public function make(string $class, ?string $wsdl, array $config = []): SoapClient
    {
        $reflectionClass = new ReflectionClass($class);

        /** @var SoapClient $client */
        $client = $reflectionClass->newInstanceArgs([ $wsdl, $config ]);

        return $client;
    }
}
