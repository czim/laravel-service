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


    /**
     * Sets the message or reason phrase
     *
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string|null
     */
    public function getMessage();

}
