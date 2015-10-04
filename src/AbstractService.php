<?php
namespace Czim\Service;

use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\ServiceResponse;

abstract class AbstractService implements ServiceInterface
{

    /**
     * Last response without any interpretation or parsing applied
     * (as far as that is possible)
     *
     * @var mixed
     */
    protected $rawResponse;

    /**
     * Last normalized response, if an interpreter is available
     *
     * @var ServiceResponseInterface
     */
    protected $response;

    /**
     * Parameters sent on last call
     *
     * @var mixed
     */
    protected $parameters;

    /**
     * Location the service connects to, usually a URI
     *
     * @var string
     */
    protected $location;

    /**
     * Username for the service
     *
     * @var string
     */
    protected $user;

    /**
     * Password for the service
     *
     * @var string
     */
    protected $password;


    /**
     * The interpreter that normalizes the raw reponse to a ServiceReponse
     *
     * @var ServiceInterpreterInterface
     */
    protected $interpreter;


    /**
     * @param ServiceInterpreterInterface $interpreter
     */
    public function __construct(ServiceInterpreterInterface $interpreter = null)
    {
        $this->interpreter = $interpreter;

        $this->initialize();
    }


    /**
     * Performs a call on the service, returning an interpreted response
     *
     * @param string $method        name of the method to call through the service
     * @param mixed  $parameters    parameters to send along
     * @return ServiceResponse      fallback to mixed if no interpreter available; make sure there is one
     */
    public function call($method, $parameters)
    {
        $this->parameters = $parameters;

        $this->before();

        $this->rawResponse = $this->callRaw($method, $this->parameters);

        $this->afterRaw();

        $this->interpretResponse();

        $this->after();

        return $this->getLastInterpretedResponse();
    }

    /**
     * Interprets the raw response if an interpreter is available
     */
    protected function interpretResponse()
    {
        if (is_null($this->interpreter)) return;

        $this->response = $this->interpreter->interpret($this->rawResponse);
    }

    /**
     * Returns best available response: interpreted if an interpreter
     * is available, falls back to raw response
     *
     * @return ServiceResponseInterface|mixed
     */
    protected function getLastInterpretedResponse()
    {
        if (is_null($this->interpreter)) {
            return $this->rawResponse;
        }

        return $this->rawResponse;
    }


    // ------------------------------------------------------------------------------
    //      Abstract
    // ------------------------------------------------------------------------------

    /**
     * Performs a raw call on the service, returning its uninterpreted/unmodified response.
     *
     * @param string $method        name of the method to call through the service
     * @param mixed  $parameters    parameters to send along
     * @return mixed
     */
    abstract public function callRaw($method, $parameters);


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
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

}
