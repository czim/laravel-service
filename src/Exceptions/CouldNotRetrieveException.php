<?php

declare(strict_types=1);

namespace Czim\Service\Exceptions;

use RuntimeException;

class CouldNotRetrieveException extends RuntimeException
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
