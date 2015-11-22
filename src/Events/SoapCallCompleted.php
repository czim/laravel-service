<?php
namespace Czim\Service\Events;

class SoapCallCompleted extends AbstractCallCompleted
{

    /**
     * @param string $address
     * @param string $method
     * @param array  $parameters
     * @param string $response
     */
    public function __construct($address, $method, $parameters, $response = null)
    {
        $this->type       = 'soap';
        $this->address    = $address;
        $this->method     = $method;
        $this->parameters = $parameters;
        $this->response   = $response;
    }

}
