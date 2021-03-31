<?php

namespace Czim\Service\Contracts;

use GuzzleHttp\ClientInterface;

interface GuzzleFactoryInterface
{
    /**
     * @param array<string, mixed>  $config Guzzle constructor configuration
     * @return ClientInterface
     */
    public function make(array $config = []): ClientInterface;
}
