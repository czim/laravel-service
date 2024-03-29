<?php

declare(strict_types=1);

namespace Czim\Service\Test\Helpers;

use Czim\Service\Interpreters\Decorators\AbstractValidationPreDecorator;

class TestPreValidator extends AbstractValidationPreDecorator
{
    /**
     * Validates the (raw) response.
     *
     * @param mixed $response
     * @return bool
     */
    protected function validate(mixed $response): bool
    {
        if ($response == 'wrong') {
            $this->errors[] = 'wrong response';
            return false;
        }

        return true;
    }
}
