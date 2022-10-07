<?php

namespace Czim\Service\Interpreters;

/**
 * Interprets JSON response data by decoding it as an array
 */
class BasicQueryStringInterpreter extends AbstractInterpreter
{
    /**
     * Whether to decode as an associative array.
     *
     * @var bool
     */
    protected $asArray = true;


    public function __construct($asArray = null)
    {
        if ($asArray !== null) {
            $this->asArray = $asArray;
        }

        parent::__construct();
    }


    protected function doInterpretation(): void
    {
        $this->interpretedResponse->setSuccess(
            $this->responseInformation->getStatusCode() > 199
            && $this->responseInformation->getStatusCode() < 300
        );

        $decoded = $this->decodeQueryString($this->response);

        if (! $this->asArray) {
            $decoded = (object) $decoded;
        }

        $this->interpretedResponse->setData($decoded);
    }

    /**
     * Decodes a query string to an array.
     *
     * @param string $string
     * @return array
     */
    protected function decodeQueryString(string $string): array
    {
        $responseArray = [];

        parse_str($string, $responseArray);

        return $responseArray;
    }
}
