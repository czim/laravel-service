<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Exceptions\ServiceConfigurationException;
use Czim\Service\Interpreters\BasicDefaultInterpreter;
use Czim\Service\Requests\ServiceRequestDefaults;
use Czim\Service\Responses\ServiceResponseInformation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

abstract class AbstractService implements ServiceInterface
{
    /**
     * The classname of the defaults object to instantiate if none is injected.
     *
     * @var class-string<ServiceRequestDefaultsInterface>
     */
    protected string $requestDefaultsClass = ServiceRequestDefaults::class;

    /**
     * The classname of the interpreter to instantiate if none is injected.
     *
     * @var class-string<ServiceInterpreterInterface>
     */
    protected string $interpreterClass = BasicDefaultInterpreter::class;


    protected ServiceRequestDefaultsInterface $defaults;

    /**
     * The request object to base the call on.
     *
     * @var ServiceRequestInterface
     */
    protected ServiceRequestInterface $request;

    /**
     * Last response without any interpretation or parsing applied (as far as that is possible).
     *
     * @var mixed
     */
    protected mixed $rawResponse;

    /**
     * Extra secondary information about the last response call made, such as headers, status code, etc.
     *
     * @var ServiceResponseInformationInterface
     */
    protected ServiceResponseInformationInterface $responseInformation;

    /**
     * The last successfully interpreted response.
     *
     * @var ServiceResponseInterface
     */
    protected ServiceResponseInterface $response;

    /**
     * The interpreter that normalizes the raw reponse to a ServiceReponse.
     *
     * @var ServiceInterpreterInterface
     */
    protected ServiceInterpreterInterface $interpreter;

    /**
     * Wether any calls have been made since construction.
     *
     * @var bool
     */
    protected bool $firstCallIsMade = false;

    /**
     * Wether to send the full response along with creating a *CallCompleted event
     * This is disabled by default for performance reasons. Enable for debugging
     * or if you need to do event-based full logging, etc.
     *
     * @var bool
     */
    protected bool $sendResponseToEvent = false;


    public function __construct(
        ServiceRequestDefaultsInterface $defaults = null,
        ServiceInterpreterInterface $interpreter = null,
    ) {
        if ($defaults === null) {
            $defaults = $this->buildRequestDefaults();
        }

        if ($interpreter === null) {
            $interpreter = $this->buildInterpreter();
        }

        $this->defaults    = $defaults;
        $this->interpreter = $interpreter;

        $this->initialize();
    }

    /**
     * Applies mass configuration to default request.
     *
     * @param array<string, mixed> $config
     */
    public function config(array $config): void
    {
        $this->validateConfig($config);


        if (array_key_exists('location', $config)) {
            $this->defaults->setLocation($config['location']);
        }

        if (array_key_exists('port', $config)) {
            $this->defaults->setPort($config['port']);
        }

        if (array_key_exists('headers', $config)) {
            $this->defaults->setHeaders($config['headers']);
        }

        if (array_key_exists('credentials', $config)) {
            $this->defaults->setCredentials(
                Arr::get($config['credentials'], 'name'),
                Arr::get($config['credentials'], 'password'),
                Arr::get($config['credentials'], 'domain')
            );
        }

        if (array_key_exists('method', $config)) {
            $this->defaults->setMethod($config['method']);
        }

        if (array_key_exists('parameters', $config)) {
            $this->defaults->setParameters($config['parameters']);
        }

        if (array_key_exists('body', $config)) {
            $this->defaults->setBody($config['body']);
        }

        if (array_key_exists('options', $config)) {
            $this->defaults->setOptions($config['options']);
        }
    }

    /**
     * Performs a call on the service, returning an interpreted response.
     *
     * @param string|null               $method     name of the method to call through the service
     * @param mixed                     $request    either: request object, or the request body
     * @param mixed                     $parameters extra parameters to send along (optional)
     * @param array<string, mixed>|null $headers    extra headers to send along (optional)
     * @param array<string, mixed>      $options    extra options to set on f.i. the soap client used
     * @return ServiceResponseInterface fallback to mixed if no interpreter available; make sure there is one
     */
    public function call(
        ?string $method,
        mixed $request = null,
        mixed $parameters = null,
        array $headers = null,
        array $options = [],
    ): ServiceResponseInterface {
        // Build up ServiceRequest
        if ($request instanceof ServiceRequestInterface) {
            $this->request = $request;
            $this->request->setMethod($method);
        } else {
            $class = $this->requestDefaultsClass;

            $this->request = new $class($request, $parameters, $headers, $method, null, $options);

            $this->checkRequestClassType($this->request);
        }

        $this->checkRequest();

        $this->supplementRequestWithDefaults();

        $this->resetResponseInformation();


        if (! $this->firstCallIsMade) {
            $this->firstCallIsMade = true;
            $this->beforeFirstCall();
        }

        $this->before();

        $this->rawResponse = $this->callRaw($this->request);

        $this->afterRaw();

        $this->interpretResponse();

        $this->after();

        return $this->getLastInterpretedResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function getLastRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastInterpretedResponse(): ServiceResponseInterface
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastReponseInformation(): ServiceResponseInformationInterface
    {
        return $this->responseInformation;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestDefaults(ServiceRequestDefaultsInterface $defaults): void
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestDefaults(): ServiceRequestDefaultsInterface
    {
        return $this->defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function setInterpreter(ServiceInterpreterInterface $interpreter): void
    {
        $this->interpreter = $interpreter;
    }

    /**
     * {@inheritDoc}
     */
    public function getInterpreter(): ServiceInterpreterInterface
    {
        return $this->interpreter;
    }

    public function free(): void
    {
        unset($this->rawResponse);
        unset($this->response);
        unset($this->responseInformation);
    }


    /**
     * Returns a fresh instance of the default service request defaults object.
     *
     * @return ServiceRequestDefaultsInterface
     */
    protected function buildRequestDefaults(): ServiceRequestDefaultsInterface
    {
        return app($this->requestDefaultsClass);
    }

    /**
     * Returns a fresh instance of the default service interpreter.
     *
     * @return ServiceInterpreterInterface
     */
    protected function buildInterpreter(): ServiceInterpreterInterface
    {
        return app($this->interpreterClass);
    }

    /**
     * Checks whether we have the correct request class type for this service.
     *
     * @param mixed $request
     */
    protected function checkRequestClassType(mixed $request): void
    {
        if (! $request instanceof ServiceRequestInterface) {
            throw new InvalidArgumentException(
                "Default requests is not a valid ServiceRequestInterface class ({$this->requestDefaultsClass})"
            );
        }
    }

    /**
     * Checks config against validation rules.
     *
     * @param array<string, mixed> $config
     * @throws ServiceConfigurationException
     */
    protected function validateConfig(array $config): void
    {
        $validator = Validator::make($config, $this->getConfigValidationRules());

        if (! $validator->fails()) {
            return;
        }

        throw new ServiceConfigurationException(
            'Invalid configuration: ' . print_r($validator->messages()->toArray(), true),
            $validator->messages()->toArray()
        );
    }

    /**
     * Returns the rules to validate the config against.
     *
     * @return array<string, mixed>
     */
    protected function getConfigValidationRules(): array
    {
        return [
            'location'             => 'string',
            'port'                 => 'integer',
            'method'               => 'string',
            'headers'              => 'array',
            'parameters'           => 'array',
            'credentials'          => 'array',
            'credentials.name'     => 'string',
            'credentials.password' => 'string',
            'credentials.domain'   => 'string',
            'options'              => 'array',
        ];
    }

    /**
     * Takes the current request and supplements it with the service's defaults
     * to merge them into a complete request.
     */
    protected function supplementRequestWithDefaults(): void
    {
        if (empty($this->request->getLocation())) {
            $this->request->setLocation(
                $this->defaults->getLocation()
            );
        }

        if (empty($this->request->getMethod())) {
            $this->request->setMethod(
                $this->defaults->getMethod()
            );
        }

        if (empty($this->request->getParameters())) {
            $this->request->setParameters(
                $this->defaults->getParameters()
            );
        }

        if (empty($this->request->getBody())) {
            $this->request->setBody(
                $this->defaults->getBody()
            );
        }

        if (
            $this->credentialsAreEmpty($this->request->getCredentials())
            && ! $this->credentialsAreEmpty($this->defaults->getCredentials())
        ) {
            $this->request->setCredentials(
                $this->defaults->getCredentials()['name'],
                $this->defaults->getCredentials()['password'],
                $this->defaults->getCredentials()['domain']
            );
        }


        if (! empty($this->defaults->getHeaders())) {
            $this->request->setHeaders(array_merge(
                $this->request->getHeaders(),
                $this->defaults->getHeaders()
            ));
        }
    }

    /**
     * Returns whether a credentials array should be considered empty.
     *
     * @param array<string, string> $credentials
     * @return bool
     */
    protected function credentialsAreEmpty(array $credentials): bool
    {
        return empty($credentials['name'])
            || empty($credentials['password']);
    }

    /**
     * Resets the response information to an empty instance of the ServiceResponseInformationInterface.
     */
    protected function resetResponseInformation(): void
    {
        $this->responseInformation = app(ServiceResponseInformation::class);
    }

    /**
     * Interprets the raw response if an interpreter is available and stores it in the response property.
     */
    protected function interpretResponse(): void
    {
        $this->response = $this->interpreter->interpret($this->request, $this->rawResponse, $this->responseInformation);
    }


    // ------------------------------------------------------------------------------
    //      Abstract
    // ------------------------------------------------------------------------------

    /**
     * Performs a raw call on the service, returning its uninterpreted/unmodified response.
     *
     * Note that this should also set/update information about the call itself, such as
     * the response code, top-level service errors, et cetera -- in order that the interpreter
     * may access these.
     *
     * @param ServiceRequestInterface $request
     * @return mixed
     */
    abstract protected function callRaw(ServiceRequestInterface $request): mixed;


    // ------------------------------------------------------------------------------
    //      Customizable
    // ------------------------------------------------------------------------------

    /**
     * Runs directly after construction.
     * Extend this to customize your service.
     */
    protected function initialize(): void
    {
    }

    /**
     * Runs before the first call on the service is made, and before before() is called.
     * Extend this to customize your service.
     */
    protected function beforeFirstCall(): void
    {
    }

    /**
     * Runs before any call is made.
     * Extend this to customize your service.
     *
     * Parameters sent to callRaw() can be modified through $this->parameters.
     */
    protected function before(): void
    {
    }

    /**
     * Runs directly after any call is made and interpreted.
     * Extend this to customize your service.
     */
    protected function after(): void
    {
    }

    /**
     * Runs directly after any raw call is made, before interpretation.
     * Extend this to customize your service.
     *
     * The response may be modified before interpretation through $this->rawResponse.
     */
    protected function afterRaw(): void
    {
    }

    /**
     * Checks the request to be used in the next/upcoming call.
     * Extend this to throw exceptions if the request is invalid or incomplete.
     */
    protected function checkRequest(): void
    {
    }
}
