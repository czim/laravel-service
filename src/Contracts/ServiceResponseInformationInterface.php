<?php

namespace Czim\Service\Contracts;

interface ServiceResponseInformationInterface extends ServiceResponseInterface
{
    /**
     * Sets response headers.
     *
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers): void;

    /**
     * Returns response headers.
     *
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * Sets the message or reason phrase
     *
     * @param string|null $message
     */
    public function setMessage(?string $message): void;

    /**
     * @return string|null
     */
    public function getMessage(): ?string;
}
