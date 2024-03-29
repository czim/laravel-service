<?php

declare(strict_types=1);

namespace Czim\Service\Responses;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * The processed/interpreted response data finally returned from the service call
 */
class ServiceResponse extends AbstractDataObject implements ServiceResponseInterface
{
    /**
     * {@inheritDoc}
     */
    protected bool $magicAssignment = false;

    /**
     * {@inheritDoc}
     */
    protected array $attributes = [
        'data'       => null,
        'statusCode' => 0,
        'errors'     => [],
        'success'    => true,
    ];

    public function setData(mixed $data): void
    {
        $this->setAttribute('data', $data);
    }

    public function getData(): mixed
    {
        return $this->getAttribute('data');
    }

    public function setStatusCode(int $code): void
    {
        $this->setAttribute('statusCode', $code);
    }

    public function getStatusCode(): int
    {
        return $this->getAttribute('statusCode') ?: 0;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->getAttribute('errors');
    }

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors): void
    {
        $this->setAttribute('errors', $errors);
    }

    public function addError(string $error): void
    {
        $errors = $this->getAttribute('errors') ?: [];

        $errors[] = $error;

        $this->setAttribute('errors', $errors);
    }

    public function getSuccess(): bool
    {
        return (bool) $this->getAttribute('success');
    }

    public function setSuccess(bool $success): void
    {
        $this->setAttribute('success', $success);
    }
}
