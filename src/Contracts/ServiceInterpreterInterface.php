<?php

namespace Czim\Service\Contracts;

interface ServiceInterpreterInterface
{
    public function interpret(
        ServiceRequestInterface $request,
        mixed $response,
        ServiceResponseInformationInterface $responseInformation = null
    ): ServiceResponseInterface;
}
