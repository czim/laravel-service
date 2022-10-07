<?php

namespace Czim\Service\Contracts;

interface ServiceResponseInformationInterface extends ServiceResponseInterface
{
    /**
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers): void;

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void;

    public function getMessage(): ?string;
}
