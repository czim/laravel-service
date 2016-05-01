<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Exceptions\ServiceConfigurationException;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Responses\ServiceResponseInformation;
use Validator;

abstract class AbstractService implements ServiceInterface
{

    /**
     * The classname of the defaults object to instantiate if none is injected
     * @var string
     */
    protected $requestDefaultsClass = \Czim\Service\Requests\ServiceRequestDefaults::class;

    /**
     * The classname of the interpreter to instantiate if none is injected
     *
     * @var string
     */
    protected $interpreterClass = \Czim\Service\Interpreters\BasicDefaultInterpreter::class;


    /**
     * @var ServiceRequestDefaultsInterface
     */
    protected $defaults;

    /**
     * The request object to base the call on
     *
     * @var ServiceRequestInterface
     */
    protected $request;

    /**
     * Last response without any interpretation or parsing applied
     * (as far as that is possible)
     *
     * @var mixed
     */
    protected $rawResponse;

    /**
     * Extra secondary information about the last response call made,
     * such as headers, status code, etc.
     *
     * @var ServiceResponseInformationInterface
     */
    protected $responseInformation;

    /**
     * The last succesfully interpreted response
     *
     * @var ServiceResponseInterface
     */
    protected $response;

    /**
     * The interpreter that normalizes the raw reponse to a ServiceReponse
     *
     * @var ServiceInterpreterInterface
     */
    protected $interpreter;

    /**
     * Wether any calls have been made since construction
     *
     * @var bool
     */
    protected $firstCallIsMade = false;

    /**
     * Wether to send the full response along with creating a *CallCompleted event
     * This is disabled by default for performance reasons. Enable for debugging
     * or if you need to do event-based full logging, etc.
     *
     * @var bool
     */
    protected $sendResponseToEvent = false;


    /**
     * @param ServiceRequestDefaultsInterface $defaults
     * @param ServiceInterpreterInterface     $interpreter
     */
    public function __construct(ServiceRequestDefaultsInterface $defaults = null, ServiceInterpreterInterface $interpreter = null)
    {
        if (is_null($defaults)) {
            $defaults = $this->buildRequestDefaults();
        }

        if (is_null($interpreter)) {
            $interpreter = $this->buildInterpreter();
        }

        $this->defaults    = $defaults;
        $this->interpreter = $interpreter;

        $this->initialize();
    }

    /**
     * Returns a fresh instance of the default service request defaults object
     *
     * @return ServiceRequestDefaultsInterface
     */
    protected function buildRequestDefaults()
    {
        return app($this->requestDefaultsClass);
    }

    /**
     * Returns a fresh instance of the default service interpreter
     *
     * @return ServiceInterpreterInterface
     */
    protected function buildInterpreter()
    {
        return app($this->interpreterClass);
    }

    /**
     * Checks whether we have the correct request class type for this service
     *
     * @param mixed $request
     */
    protected function checkRequestClassType($request)
    {
        if ( ! is_a($request, ServiceRequestInterface::class)) {

            throw new \InvalidArgumentException(
                "Default requests is not a valid ServiceRequestInterface class ({$this->requestDefaultsClass})"
            );
        }
    }


    /**
     * Applies mass configuration to default request
     *
     * @param array $config
     * @return $this
     */
    public function config(array $config)
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
                array_get($config['credentials'], 'name'),
                array_get($config['credentials'], 'password'),
                array_get($config['credentials'], 'domain')
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

        return $this;
    }

    /**
     * Checks config against validation rules
     *
     * @param array $config
     * @throws ServiceConfigurationException
     */
    protected function validateConfig(array $config)
    {
        $validator = Validator::make($config, $this->getConfigValidationRules());

        if ($validator->fails()) {

            throw new ServiceConfigurationException(
                'Invalid configuration: ' . print_r($validator->messages()->toArray(), true),
                $validator->messages()->toArray()
            );
        }
    }

    /**
     * Returns the rules to validate the config against
     *
     * @return array
     */
    protected function getConfigValidationRules()
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
     * Performs a call on the service, returning an interpreted response
     *
     * @param string $method     name of the method to call through the service
     * @param mixed  $request    either: request object, or the request body
     * @param mixed  $parameters extra parameters to send along (optional)
     * @param mixed  $headers    extra headers to send along (optional)
     * @param array  $options    extra options to set on f.i. the soap client used
     * @return ServiceResponse fallback to mixed if no interpreter available; make sure there is one
     */
    public function call($method, $request = null, $parameters = null, $headers = null, $options = [])
    {
        // build up ServiceRequest
        if (is_a($request, ServiceRequestInterface::class)) {
            /** @var ServiceRequestInterface $request */
            $this->request = $request->setMethod($method);

        } else {
            // $request is the request body

            $this->request = app(
                $this->requestDefaultsClass,
                [ $request, $parameters, $headers, $method, null, $options ]
            );

            $this->checkRequestClassType($this->request);
        }

        $this->checkRequest();

        $this->supplementRequestWithDefaults();

        $this->resetResponseInformation();


        if ( ! $this->firstCallIsMade) {
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
     * Takes the current request and supplements it with the service's defaults
     * to merge them into a complete request.
     */
    protected function supplementRequestWithDefaults()
    {
        // properties to set only if they are empty

        if (empty($this->request->getLocation())) {
            $this->request->setLocation( $this->defaults->getLocation() );
        }

        if (empty($this->request->getMethod())) {
            $this->request->setMethod( $this->defaults->getMethod() );
        }

        if (empty($this->request->getParameters())) {
            $this->request->setParameters( $this->defaults->getParameters() );
        }

        if (empty($this->request->getBody())) {
            $this->request->setBody( $this->defaults->getBody() );
        }

        if (    $this->credentialsAreEmpty($this->request->getCredentials())
            &&  ! $this->credentialsAreEmpty($this->defaults->getCredentials())
        ) {
            $this->request->setCredentials(
                $this->defaults->getCredentials()['name'],
                $this->defaults->getCredentials()['password'],
                $this->defaults->getCredentials()['domain']
            );
        }


        // properties to expand

        if ( ! empty($this->defaults->getHeaders())) {

            $this->request->setHeaders(array_merge(
                $this->request->getHeaders(),
                $this->defaults->getHeaders()
            ));
        }

    }

    /**
     * Returns whether a credentials array should be considered empty
     *
     * @param array $credentials
     * @return bool
     */
    protected function credentialsAreEmpty(array $credentials)
    {
        return (empty($credentials['name']) || empty($credentials['password']));
    }

    /**
     * Resets the response information to an empty instance of the ServiceResponseInformationInterface
     */
    protected function resetResponseInformation()
    {
        $this->responseInformation = app(ServiceResponseInformation::class);
    }

    /**
     * Interprets the raw response if an interpreter is available and stores it in the response property
     */
    protected function interpretResponse()
    {
        $this->response = $this->interpreter->interpret($this->request, $this->rawResponse, $this->responseInformation);
    }


    /**
     * Frees up memory where possible
     *
     * @return $this
     */
    public function free()
    {
        unset( $this->rawResponse );
        unset( $this->response );
        unset( $this->responseInformation );

        return $this;
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
    abstract protected function callRaw(ServiceRequestInterface $request);


    // ------------------------------------------------------------------------------
    //      Customizable
    // ------------------------------------------------------------------------------

    /**
     * Runs directly after construction
     * Extend this to customize your service
     */
    protected function initialize()
    {
    }

    /**
     * Runs before the first call on the service is made, and before before() is called
     * Extend this to customize your service
     */
    protected function beforeFirstCall()
    {
    }

    /**
     * Runs before any call is made
     * Extend this to customize your service
     *
     * Parameters sent to callRaw() can be modified through $this->parameters
     */
    protected function before()
    {
    }

    /**
     * Runs directly after any call is made and interpreted
     * Extend this to customize your service
     */
    protected function after()
    {
    }

    /**
     * Runs directly after any raw call is made, before interpretation
     * Extend this to customize your service
     *
     * The response may be modified before interpretation through $this->rawResponse
     */
    protected function afterRaw()
    {
    }

    /**
     * Checks the request to be used in the next/upcoming call
     * Extend this to throw exceptions if the request is invalid or incomplete
     */
    protected function checkRequest()
    {

    }

    
    // ------------------------------------------------------------------------------
    //      Getters and Setters
    // ------------------------------------------------------------------------------
    
    /**
     * Returns the raw response data for the most recent call made
     *
     * @return mixed
     */
    public function getLastRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * Returns best available response: interpreted if an interpreter
     * is available, falls back to raw response
     *
     * @return ServiceResponseInterface
     */
    public function getLastInterpretedResponse()
    {
        return $this->response;
    }

    /**
     * Returns the extra information set during the execution of the last call
     *
     * @return ServiceResponseInformation
     */
    public function getLastReponseInformation()
    {
        return $this->responseInformation;
    }

    /**
     * Sets the default request data to supplement any requests with
     *
     * @param ServiceRequestDefaultsInterface $defaults
     * @return $this
     */
    public function setRequestDefaults(ServiceRequestDefaultsInterface $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Returns the default request data
     *
     * @return ServiceRequestDefaultsInterface
     */
    public function getRequestDefaults()
    {
        return $this->defaults;
    }

    /**
     * Sets the service response interpreter
     *
     * @param ServiceInterpreterInterface $interpreter
     * @return $this
     */
    public function setInterpreter(ServiceInterpreterInterface $interpreter)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Returns the service response interpreter instance
     *
     * @return ServiceInterpreterInterface
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

}
