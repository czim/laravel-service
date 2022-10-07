<?php

namespace Czim\Service\Contracts;

interface ServiceInterface
{
    /**
     * Performs a call on the service, returning an interpreted response.
     *
     * @param string|null               $method     name of the method to call through the service
     * @param mixed|null                $request
     * @param mixed|null                $parameters extra parameters to send along (optional)
     * @param array<string, mixed>|null $headers    extra headers to send along (optional)
     * @return ServiceResponseInterface
     */
    public function call(
        ?string $method,
        mixed $request = null,
        mixed $parameters = null,
        array $headers = null
    ): ServiceResponseInterface;

    /**
     * Applies mass configuration to default request.
     *
     * @param array<string, mixed> $config
     */
    public function config(array $config): void;

    /**
     * Returns the raw response data for the most recent call made.
     *
     * @return mixed
     */
    public function getLastRawResponse(): mixed;

    /**
     * Returns best available response: interpreted if an interpreter is available, falls back to raw response.
     *
     * @return ServiceResponseInterface
     */
    public function getLastInterpretedResponse(): ServiceResponseInterface;

    /**
     * Returns the extra information set during the execution of the last call.
     *
     * @return ServiceResponseInformationInterface
     */
    public function getLastReponseInformation(): ServiceResponseInformationInterface;

    /**
     * Sets the default request data to supplement any requests with.
     *
     * @param ServiceRequestDefaultsInterface $defaults
     */
    public function setRequestDefaults(ServiceRequestDefaultsInterface $defaults): void;

    /**
     * Returns the default request data.
     *
     * @return ServiceRequestDefaultsInterface
     */
    public function getRequestDefaults(): ServiceRequestDefaultsInterface;

    /**
     * Sets the service response interpreter.
     *
     * @param ServiceInterpreterInterface $interpreter
     */
    public function setInterpreter(ServiceInterpreterInterface $interpreter): void;

    /**
     * Returns the service response interpreter instance.
     *
     * @return ServiceInterpreterInterface
     */
    public function getInterpreter(): ServiceInterpreterInterface;

    /**
     * Frees up memory where possible.
     */
    public function free(): void;
}
