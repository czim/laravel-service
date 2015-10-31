<?php
namespace Czim\Service\Contracts;

interface ServiceResponseInformationInterface extends ServiceResponseInterface
{

    /**
     * Sets response headers
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers);

    /**
     * Returns response headers
     *
     * @return array
     */
    public function getHeaders();

}
