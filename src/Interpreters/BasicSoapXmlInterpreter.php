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
                $this->responseInformation->getStatusCode() > 199
            &&  $this->responseInformation->getStatusCode() < 300
        );

        // no need to call on a parser, since the data from SoapClient calls
        // is already SimpleXml data

        if ($this->asArray) {
            $this->response = $this->xmlConverter->convert($this->response);
        }

        $this->interpretedResponse->setData(
            $this->response
        );
    }

}
