<?php

declare(strict_types=1);

namespace Czim\Service\Events;

abstract class AbstractCallCompleted extends AbstractServiceEvent
{
    protected string $type;
    protected ?string $address;
    protected ?string $method;

    /**
     * @var array<string, mixed>
     */
    protected array $parameters;

    protected mixed $response;


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

    public function getResponse(): mixed
    {
        return $this->response;
    }
}
