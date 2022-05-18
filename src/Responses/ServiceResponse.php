<?php

namespace Czim\Service\Responses;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * The processed/interpreted response data finally returned from the service call
 */
class ServiceResponse extends AbstractDataObject implements ServiceResponseInterface
{
    protected bool $magicAssignment = false;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [
        'data'       => null,
        'statusCode' => 0,
        'errors'     => [],
        'success'    => true,
    ];

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->setAttribute('data', $data);
    }

    /**
     * @return mixed
     */
    public function getData()
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
