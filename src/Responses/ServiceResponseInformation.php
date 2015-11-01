<?php
namespace Czim\Service\Responses;

use Czim\Service\Contracts\ServiceResponseInformationInterface;

/**
 * Information about the calls response used internally to provide extra
 * information to the interpreter
 */
class ServiceResponseInformation extends ServiceResponse implements ServiceResponseInformationInterface
{

    /**
     * Sets response headers
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->setAttribute('headers', $headers);

        return $this;
    }

    /**
     * Returns response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getAttribute('headers') ?: [];
    }

    /**
     * Sets the message or reason phrase
     *
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        if ( ! is_null($message)) $message = (string) $message;

        $this->setAttribute('message', $message);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getAttribute('message');
    }
}
