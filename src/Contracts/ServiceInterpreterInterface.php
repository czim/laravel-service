<?php
namespace Czim\Service\Contracts;

interface ServiceInterpreterInterface
{

    /**
     * @param ServiceRequestInterface             $request the request sent in order to retrieve the response
     * @param mixed                               $response
     * @param ServiceResponseInformationInterface $responseInformation
     * @return ServiceResponseInterface
     */
    public function interpret(
        ServiceRequestInterface $request,
        $response,
        ServiceResponseInformationInterface $responseInformation = null
    );

}
