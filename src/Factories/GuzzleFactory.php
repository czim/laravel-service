<?php
namespace Czim\Service\Factories;

use GuzzleHttp\Client;

class GuzzleFactory
{

    /**
     * Makes a Guzzle client instance.
     *
     * @param array  $config    guzzle constructor configuration
     * @return Client
     */
    public function make(array $config = [])
    {
        return new Client($config);
    }

}
