<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets SOAP (XML) response data, directly taking over SOAP response object
 */
class BasicSoapXmlInterpreter extends AbstractXmlInterpreter
{

    /**
     * Whether to decode as an associative array
     *
     * @var bool
     */
    protected $asArray = false;


    protected function doInterpretation()
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() == 200
        );

        // if configured to, use the clunky way to build an array from the object
        if ($this->asArray) {
            $this->response = $this->convertXmlObjectToArray($this->response);
        }

        $this->interpretedResponse->setData(
            $this->response
        );
    }

}
