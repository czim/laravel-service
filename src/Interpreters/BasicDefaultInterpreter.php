<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters;

/**
 * Does not do any actual interpretation, just serves to format the
 * result as a ServiceResponse object.
 */
class BasicDefaultInterpreter extends AbstractInterpreter
{
    protected function doInterpretation(): void
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() > 199
            && $this->responseInformation->getStatusCode() < 300
        );

        $this->interpretedResponse->setData(
            $this->response
        );
    }
}
