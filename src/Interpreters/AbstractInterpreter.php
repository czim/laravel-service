<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\ServiceResponse;

abstract class AbstractInterpreter implements ServiceInterpreterInterface
{
    /**
     * @var ServiceResponseInterface
     */
    protected $interpretedResponse;


    public function __construct()
    {
        $this->interpretedResponse = app(ServiceResponse::class);

        $this->interpretedResponse->setStatusCode(0);
        $this->interpretedResponse->setError('');
        $this->interpretedResponse->setData(null);

        $this->initialize();
    }

    /**
     * @param mixed $response
     * @return ServiceResponseInterface
     */
    abstract public function interpret($response);


    /**
     * Called right after construction
     * Extend this to customize your response interpreter
     */
    protected function initialize()
    {
    }
}
