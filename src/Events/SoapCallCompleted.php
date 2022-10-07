<?php

declare(strict_types=1);

namespace Czim\Service\Events;

class SoapCallCompleted extends AbstractCallCompleted
{
    /**
     * @param string|null               $address
     * @param string|null               $method
     * @param array<string, mixed>|null $parameters
     * @param string|null               $response
     */
    public function __construct(?string $address, ?string $method, ?array $parameters, string $response = null)
    {
        $this->type       = 'soap';
        $this->address    = $address;
        $this->method     = $method;
        $this->parameters = $parameters ?? [];
        $this->response   = $response;
    }
}
