<?php
namespace Czim\Service;

use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Responses\ServiceResponseInformation;

abstract class AbstractService implements ServiceInterface
{

    /**
     * The classname of the defaults object to instantiate if none is injected
     * @var string
     */
    protected $requestDefaultsClass = 'Czim\\Service\\Requests\\ServiceRequestDefaults';

    /**
     * The classname of the interpreter to instantiate if none is injected
     *
     * @var string
     */
    protected $interpreterClass = 'Czim\\Service\\Interpreters\\BasicDefaultInterpreter';


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
     * Performs a call on the service, returning an interpreted response
     *
     * @param string $method        name of the method to call through the service
     * @param mixed  $request       either: request object, or the request body
     * @param mixed  $parameters    extra parameters to send along (optional)
     * @param mixed  $headers       extra headers to send along (optional)
     * @return ServiceResponse      fallback to mixed if no interpreter available; make sure there is one
     */
    public function call($method, $request = null, $parameters = null, $headers = null)
    {

        // build up ServiceRequest
        if (is_a($request, ServiceRequestInterface::class)) {
            /** @var ServiceRequestInterface $request */
            $this->request = $request->setMethod($method);
        } else {
            $this->request = new ServiceRequest($request, $parameters, $headers, $method);
        }

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

        if (empty($this->request->getCredentials())) {
            $this->request->setCredentials( $this->defaults->getCredentials() );
        }


        // properties to expand

        if ( ! empty($this->defaults->getHeaders())) {

            $this->request->setMethod(array_merge(
                $this->request->getHeaders(),
                $this->defaults->getHeaders()
            ));
        }

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
        // todo: tell the interpreter more about the response..

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
