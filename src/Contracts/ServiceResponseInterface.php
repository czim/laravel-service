<?php

namespace Czim\Service\Contracts;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ServiceResponseInterface extends DataObjectInterface
{
    public function setData(mixed $data): void;
    public function getData(): mixed;

    /**
     * Sets the HTTP or other service status code.
     *
     * @param int $code
     */
    public function setStatusCode(int $code): void;

    public function getStatusCode(): int;

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array;

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors): void;

    /**
     * Adds a single error to the error list.
     *
     * @param string $error
     */
    public function addError(string $error): void;

    public function getSuccess(): bool;
    public function setSuccess(bool $success): void;
}
