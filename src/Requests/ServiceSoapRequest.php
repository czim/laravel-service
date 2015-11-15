<?php
namespace Czim\Service\Requests;

/**
 * @property array $options
 */
class ServiceSoapRequest extends ServiceRequest
{
    protected $attributes = [
        'location'    => null,
        'method'      => null,
        'parameters'  => null,
        'headers'     => [],
        'body'        => null,
        'credentials' => [
            'name'     => null,
            'password' => null,
            'domain'   => null,
        ],
        'options'     => [],
    ];


    /**
     * @param mixed   $body
     * @param mixed   $parameters
     * @param mixed[] $headers
     * @param string  $method
     * @param string  $location
     * @param array   $options
     */
    public function __construct($body = null, $parameters = null, array $headers = null, $method = null, $location = null, $options = [])
    {
        $this->setOptions($options);

        parent::__construct($body, $parameters, $headers, $method, $location);
    }

    /**
     * Returns SOAP options
     *
     * @return mixed[]
     */
    public function getOptions()
    {
        return $this->getAttribute('options') ?: [];
    }

    /**
     * Sets request SOAP options
     *
     * @param mixed[] $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->setAttribute('options', $options);

        return $this;
    }

}
