<?php

namespace Czim\Service\Factories;

use Czim\Service\Contracts\GuzzleFactoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class GuzzleFactory implements GuzzleFactoryInterface
{
    /**
     * Makes a Guzzle client instance.
     *
     * @param array<string, mixed> $config Guzzle constructor configuration
     * @return ClientInterface
     */
    public function make(array $config = []): ClientInterface
    {
        return new Client($config);
    }
}
