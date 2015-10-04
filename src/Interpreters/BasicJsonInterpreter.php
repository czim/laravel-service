<?php
namespace Czim\Service\Interpreters;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\ServiceResponse;

/**
 * Class BasicJsonInterpreter
 *
 * Interprets JSON response data by decoding it as an array
 */
class BasicJsonInterpreter implements ServiceInterpreterInterface
{

    /**
     * @param mixed $response
     * @return ServiceResponseInterface
     */
    public function interpret($response)
    {
        $interpreted = new ServiceResponse();

        $response = json_decode($response, true);

        $interpreted->setData($response);

        return $interpreted;
    }

}
