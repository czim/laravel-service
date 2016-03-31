<?php
namespace Czim\Service\Interpreters;

/**
 * Interprets JSON response data by decoding it as an array
 */
class BasicQueryStringInterpreter extends AbstractInterpreter
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

        $decoded = $this->decodeQueryString($this->response);


        if ( ! $this->asArray) {

            $decoded = (object) $decoded;
        }


        $this->interpretedResponse->setData($decoded);
    }

    /**
     * Decodes a query string to an array
     *
     * @param $string
     * @return array
     */
    protected function decodeQueryString($string)
    {
        $responseArray = [];

        parse_str($string, $responseArray);

        return $responseArray;
    }

}
