<?php
namespace Czim\Service\Events;

abstract class AbstractCallCompleted extends AbstractServiceEvent
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var mixed
     */
    protected $response;


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

}
