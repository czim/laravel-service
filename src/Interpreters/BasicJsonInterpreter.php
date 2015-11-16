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


    /**
     * @param bool|null $asArray
     */
    public function __construct($asArray = null)
    {
        if ( ! is_null($asArray)) {
            $this->asArray = $asArray;
        }

        parent::__construct();
    }


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
