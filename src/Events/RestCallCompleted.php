<?php

namespace Czim\Service\Events;

class RestCallCompleted extends AbstractCallCompleted
{
    /**
     * @param string               $address
     * @param array<string, mixed> $parameters
     * @param string|null          $response
     */
    public function __construct(string $address, array $parameters, string $response = null)
    {
        $this->type       = 'rest';
        $this->address    = $address;
        $this->method     = null;
        $this->parameters = $parameters;
        $this->response   = $response;
    }
}
