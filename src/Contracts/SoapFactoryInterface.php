<?php
namespace Czim\Service\Contracts;

use SoapClient;

interface SoapFactoryInterface
{

    /**
     * Makes a SoapClient instance.
     *
     * @param string $class     the soapclient class to use
     * @param string $wsdl
     * @param array  $config
     * @return SoapClient
     */
    public function make($class, $wsdl, array $config = []);

}
