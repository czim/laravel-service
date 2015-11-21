<?php
namespace Czim\Service\Responses\Mergers;

use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Responses\ServiceResponse;
use Illuminate\Support\Arr;

class ResponseMerger implements ResponseMergerInterface
{

    /**
     * Merges parts of a response (or parsed file contents) into a single body
     *
     * @param ServiceResponse[] $parts
     * @return ServiceResponse
     */
    public function merge(array $parts)
    {
        if ( ! count($parts)) return $this->makeNullResponse();

        $response = $parts[0];

        $response->setData(
            Arr::build($parts, function($part) {
                /** @var ServiceResponse $part */
                return $part->getData();
            })
        );

        return $response;
    }


    public function makeNullResponse()
    {
        return new ServiceResponse();
    }
}
