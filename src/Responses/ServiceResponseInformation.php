<?php

namespace Czim\Service\Responses;

use Czim\Service\Contracts\ServiceResponseInformationInterface;

/**
 * Information about the calls response used internally to provide extra information to the interpreter
 */
class ServiceResponseInformation extends ServiceResponse implements ServiceResponseInformationInterface
{
    /**
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->setAttribute('headers', $headers);
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->getAttribute('headers') ?: [];
    }

    public function setMessage(?string $message): void
    {
        $this->setAttribute('message', $message);
    }

    public function getMessage(): ?string
    {
        return $this->getAttribute('message');
    }
}
