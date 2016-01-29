<?php
namespace Czim\Service\Interpreters\Decorators;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * Validates raw response before interpretation
 */
abstract class AbstractValidationPostDecorator extends AbstractValidationPreDecorator
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
    ) {
        $response = $this->interpreter->interpret($request, $response, $responseInformation);

        if ( ! $this->validate($response)) {
            $this->throwValidationException();
        }

        return $response;
    }

    /**
     * Validates the (raw) response (the ServiceResponse from the interpreter, afterwards)
     *
     * @param ServiceResponseInterface $response
     * @return bool
     */
    protected function validate($response)
    {
        return $this->validateResponse($response);
    }

    /**
     * Validates the ServiceResponse
     *
     * @param ServiceResponseInterface $response
     * @return bool
     */
    abstract protected function validateResponse(ServiceResponseInterface $response);

}
