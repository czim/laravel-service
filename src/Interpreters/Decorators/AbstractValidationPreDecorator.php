<?php

namespace Czim\Service\Interpreters\Decorators;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Exceptions\CouldNotValidateResponseException;

/**
 * Validates raw response before interpretation
 */
abstract class AbstractValidationPreDecorator implements ServiceInterpreterInterface
{
    /**
     * @var ServiceInterpreterInterface
     */
    protected $interpreter;

    /**
     * Validation errors.
     *
     * @var string[]
     */
    protected $errors = [];


    public function __construct(ServiceInterpreterInterface $interpreter)
    {
        $this->interpreter = $interpreter;
    }


    /**
     * @param ServiceRequestInterface                  $request the request sent in order to retrieve the response
     * @param mixed                                    $response
     * @param ServiceResponseInformationInterface|null $responseInformation
     * @return ServiceResponseInterface
     */
    public function interpret(
        ServiceRequestInterface $request,
        $response,
        ServiceResponseInformationInterface $responseInformation = null
    ): ServiceResponseInterface {
        if (! $this->validate($response)) {
            $this->throwValidationException();
        }

        return $this->interpreter->interpret($request, $response, $responseInformation);
    }


    /**
     * @param mixed $response
     * @return bool
     */
    abstract protected function validate($response): bool;

    /**
     * Throws an exception, indicating that validation failed.
     *
     * @throws CouldNotValidateResponseException
     */
    protected function throwValidationException(): void
    {
        throw new CouldNotValidateResponseException($this->getErrorMessage(), $this->getErrors());
    }

    protected function getErrorMessage(): string
    {
        return print_r($this->getErrors(), true);
    }

    /**
     * Returns validation errors for previous attempt.
     *
     * @return string[]
     */
    protected function getErrors(): array
    {
        return $this->errors;
    }
}
