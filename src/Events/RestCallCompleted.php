<?php
namespace Czim\Service\Events;

class RestCallCompleted extends AbstractCallCompleted
{

    /**
     * @param string $address
     * @param array  $parameters
     * @param string $response
     */
    public function __construct($address, array $parameters, $response = null)
    {
        $this->type       = 'rest';
        $this->address    = $address;
        $this->method     = null;
        $this->parameters = $parameters;
        $this->response   = $response;
    }

}
