<?php

declare(strict_types=1);

namespace Czim\Service\Interpreters;

use Czim\Service\Exceptions\CouldNotInterpretJsonResponseException;

/**
 * Interprets JSON response data by decoding it as an array
 */
class BasicJsonInterpreter extends AbstractInterpreter
{
    /**
     * Whether to decode as an associative array.
     *
     * @var bool
     */
    protected bool $asArray = true;


    public function __construct(?bool $asArray = null)
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

        $decoded = json_decode($this->response, $this->asArray);

        if ($decoded === null && $this->response !== null) {
            throw new CouldNotInterpretJsonResponseException('Invalid JSON content in response');
        }

        $this->interpretedResponse->setData($decoded);
    }
}
