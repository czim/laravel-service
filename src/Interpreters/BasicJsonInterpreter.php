<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets JSON response data by decoding it as an array
 */
class BasicJsonInterpreter extends AbstractInterpreter
{

    protected function doInterpretation()
    {
        $this->interpretedResponse->setData(
            json_decode($this->response, true)
        );
    }

}
