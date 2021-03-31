<?php

namespace Czim\Service\Responses\Mergers;

use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\ServiceResponse;

/**
 * Default merger that does not do any content-based selection;
 * this simply concatenates the interpreted data for each part as
 * elements in a single array.
 */
class ResponseMerger implements ResponseMergerInterface
{
    /**
     * Merges parts of a response (or parsed file contents) into a single body.
     *
     * @param ServiceResponseInterface[] $parts
     * @return ServiceResponseInterface
     */
    public function merge(array $parts): ServiceResponseInterface
    {
        if (! count($parts)) {
            return $this->makeNullResponse();
        }

        $response = $parts[0];

        // if there was only one part, just return that
        if (count($parts) == 1) {
            return $response;
        }

        // if there were more parts, combine their data as an array
        $response->setData(
            $this->rebuildArray($parts, function($index, ServiceResponseInterface $part) {
                /** @var ServiceResponse $part */
                return [
                    $index,
                    $part->getData(),
                ];
            })
        );

        return $response;
    }

    /**
     * @param array<string, mixed> $array
     * @param callable             $callback
     * @return array<string, mixed>
     */
    protected function rebuildArray(array $array, callable $callback): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

            $results[$innerKey] = $innerValue;
        }

        return $results;
    }

    protected function makeNullResponse(): ServiceResponseInterface
    {
        return new ServiceResponse();
    }
}
