<?php
namespace Czim\Service\Contracts;

interface ServiceInterpreterInterface
{

    /**
     * @param mixed $response
     * @return ServiceResponseInterface
     */
    public function interpret($response);

}
