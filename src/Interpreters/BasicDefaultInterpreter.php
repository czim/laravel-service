<?php
namespace Czim\Service\Interpreters;

/**
 * Does not do any actual interpretation, just serves to format the
 * result as a ServiceResponse object.
 */
class BasicDefaultInterpreter extends AbstractInterpreter
{

    protected function doInterpretation()
    {
        $this->interpretedResponse->setData(
            $this->response
        );
    }

}
