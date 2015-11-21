<?php
namespace Czim\Service\Contracts;

use Czim\Service\Responses\ServiceResponse;

interface ResponseMergerInterface
{

    /**
     * Merges parts of a response (or parsed file contents) into a single body
     *
     * @param ServiceResponse[] $parts
     * @return ServiceResponse
     */
    public function merge(array $parts);

}
