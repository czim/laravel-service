<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * Class BasicJsonInterpreter
 *
 * Interprets JSON response data by decoding it as an array
 */
class BasicJsonInterpreter extends AbstractInterpreter
{

    /**
     * @param mixed $response
     * @return ServiceResponseInterface
     */
    public function interpret($response)
    {
        $response = json_decode($response, true);

        $this->interpretedResponse->setData($response);

        return $this->interpretedResponse;
    }

}
