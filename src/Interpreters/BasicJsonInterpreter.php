<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Exceptions\CouldNotInterpretJsonResponseException;

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
                $this->responseInformation->getStatusCode() > 199
            &&  $this->responseInformation->getStatusCode() < 300
        );

        $decoded = json_decode($this->response, $this->asArray);

        if (is_null($decoded) && ! is_null($this->response)) {

            throw new CouldNotInterpretJsonResponseException('Invalid JSON content in response');
        }

        $this->interpretedResponse->setData($decoded);
    }

}
