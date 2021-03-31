<?php

namespace Czim\Service\Events;

abstract class AbstractCallCompleted extends AbstractServiceEvent
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $address;

    /**
     * @var string|null
     */
    protected $method;

    /**
     * @var array<string, mixed>
     */
    protected $parameters;

    /**
     * @var mixed
     */
    protected $response;


    public function getType(): string
    {
        return $this->type;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
