<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets JSON response data by decoding it as an array
 */
class BasicJsonInterpreter extends AbstractInterpreter
{

    /**
     * Whether to decode as an associative array
     *
     * @var bool
     */
    protected $asArray = true;


    protected function doInterpretation()
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() == 200
        );

        $this->interpretedResponse->setData(
            json_decode($this->response, $this->asArray)
        );
    }

}
