<?php

declare(strict_types=1);

namespace Czim\Service\Exceptions;

/**
 * Validation exception thrown during interpretation of response.
 * See Interpreters\Decorators.
 */
class CouldNotValidateResponseException extends CouldNotInterpretResponseException
{
}
