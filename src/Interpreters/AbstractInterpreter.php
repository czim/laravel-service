<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Responses\ServiceResponseInformation;

abstract class AbstractInterpreter implements ServiceInterpreterInterface
{

    /**
     * The request that was sent to get the raw response
     *
     * @var ServiceRequestInterface
     */
    protected $request;

    /**
     * The raw response to be interpreted
     *
     * @var mixed
     */
    protected $response;

    /**
     * Extra information about the response
     *
     * @var ServiceResponseInformationInterface
     */
    protected $responseInformation;

    /**
     * The interpreted response to return
     *
     * @var ServiceResponseInterface
     */
    protected $interpretedResponse;


    public function __construct()
    {
        $this->resetInterpretedResponse();

        $this->initialize();
    }

    /**
     * @param ServiceRequestInterface             $request
     * @param mixed                               $response
     * @param ServiceResponseInformationInterface $responseInformation  optional
     * @return ServiceResponseInterface
     */
    public function interpret(
        ServiceRequestInterface $request,
        $response,
        ServiceResponseInformationInterface $responseInformation = null
    ) {
        $this->resetInterpretedResponse();

        if (is_null($responseInformation)) {
            $responseInformation = app(ServiceResponseInformation::class);
        } else {
            $this->interpretedResponse->setStatusCode( $responseInformation->getStatusCode() );
        }

        $this->request             = $request;
        $this->response            = $response;
        $this->responseInformation = $responseInformation;

        $this->before();

        $this->doInterpretation();

        $this->after();

        $this->cleanUp();

        return $this->interpretedResponse;
    }

    /**
     * Resets/initializes the interpreted response to a fresh instance
     * with default values
     */
    protected function resetInterpretedResponse()
    {
        $this->interpretedResponse = app(ServiceResponse::class);

        $this->interpretedResponse->setStatusCode(0);
        $this->interpretedResponse->setErrors([]);
        $this->interpretedResponse->setData(null);
    }


    /**
     * Cleans up the interpreter instance after interpretation process has completed
     */
    protected function cleanUp()
    {
        unset($this->request);
        unset($this->response);
        unset($this->responseInformation);
    }

    // ------------------------------------------------------------------------------
    //      Abstract
    // ------------------------------------------------------------------------------

    /**
     * Handles the interpretation
     * This should update/modify the interpretedResponse property
     */
    abstract protected function doInterpretation();


    // ------------------------------------------------------------------------------
    //      Customizable
    // ------------------------------------------------------------------------------

    /**
     * Called right after construction
     * Extend this to customize your response interpreter
     */
    protected function initialize()
    {
    }

    /**
     * Called before doInterpretation()
     * Extend this to customize your response interpreter
     */
    protected function before()
    {
    }

    /**
     * Called after doInterpretation(), before returning the result for the interpret() method
     * Extend this to customize your response interpreter
     */
    protected function after()
    {
    }

}
