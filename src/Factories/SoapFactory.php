<?php
namespace Czim\Service\Factories;

use Czim\Service\Contracts\SoapFactoryInterface;
use ReflectionClass;
use SoapClient;

class SoapFactory implements SoapFactoryInterface
{

    /**
     * Makes a SoapClient instance.
     *
     * @param string $class     the soapclient class to use
     * @param string $wsdl
     * @param array  $config
     * @return SoapClient
     */
    public function make($class, $wsdl, array $config = [])
    {
        $reflectionClass = new ReflectionClass($class);

        /** @var SoapClient $client */
        $client = $reflectionClass->newInstanceArgs([ $wsdl, $config ]);

        return $client;
    }

}
