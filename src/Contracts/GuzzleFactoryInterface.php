<?php
namespace Czim\Service\Contracts;

use GuzzleHttp\ClientInterface;

interface GuzzleFactoryInterface
{

    /**
     * Makes a Guzzle client instance.
     *
     * @param array  $config    guzzle constructor configuration
     * @return ClientInterface
     */
    public function make(array $config = []);

}
