<?php

declare(strict_types=1);

namespace Czim\Service\Exceptions;

use RuntimeException;
use Throwable;

class ServiceConfigurationException extends RuntimeException
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    /**
     * @param string|null    $message
     * @param string[]       $errors list of errors encountered while parsing or validating config
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, array $errors = [], int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
