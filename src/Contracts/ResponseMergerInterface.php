<?php

namespace Czim\Service\Contracts;

use Czim\Service\Responses\ServiceResponse;

/**
 * @template TResponse as \Czim\Service\Contracts\ServiceResponseInterface
 */
interface ResponseMergerInterface
{
    /**
     * Merges parts of a response (or parsed file contents) into a single body.
     *
     * @param TResponse[] $parts
     * @return TResponse
     */
    public function merge(array $parts): ServiceResponseInterface;
}
