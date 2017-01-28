<?php
namespace Czim\Service\Factories;

use Czim\Service\Contracts\GuzzleFactoryInterface;
use GuzzleHttp\Client;

class GuzzleFactory implements GuzzleFactoryInterface
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
