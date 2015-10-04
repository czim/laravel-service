<?php
namespace Czim\Service\Contracts;

interface ServiceInterface
{

    /**
     * @param string $method        name of the method to call through the service
     * @param mixed  $parameters    parameters to send along
     * @return ServiceResponseInterface
     */
    public function call($method, $parameters);

    /**
     * @param string $method        name of the method to call through the service
     * @param mixed  $parameters    parameters to send along
     * @return mixed
     */
    public function callRaw($method, $parameters);

}
