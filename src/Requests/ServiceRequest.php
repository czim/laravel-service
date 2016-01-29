<?php
namespace Czim\Service\Requests;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceRequestInterface;

/**
 * @property string   $location
 * @property int      $port
 * @property string   $method
 * @property mixed    $parameters
 * @property mixed[]  $headers
 * @property mixed    $body
 * @property string[] $credentials
 * @property array    $options      key-value pairs
 */
class ServiceRequest extends AbstractDataObject implements ServiceRequestInterface
{

    protected $magicAssignment = false;

    protected $attributes = [
        'location'    => null,
        'port'        => null,
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
    public function __construct(
        $body = null,
        $parameters = null,
        array $headers = null,
        $method = null,
        $location = null,
        $options = []
    ) {
        $this->setBody($body);
        $this->setParameters($parameters);
        if ( ! is_null($headers)) $this->setHeaders($headers);
        $this->setMethod($method);
        $this->setLocation($location);
        $this->setOptions($options);

        parent::__construct();
    }

    /**
     * Returns the base URL or WSDL for the service, if it is set in the request.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->getAttribute('location');
    }

    /**
     * Sets the base URL or WSDL location for the service, as an optional override
     * for the service configuration.
     *
     * @param string $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->setAttribute('location', (string) $location);

        return $this;
    }

    /**
     * Returns the method or endpoint name to send the request to.
     *
     * Note that this does NOT refer to the HTTP method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }

    /**
     * Sets the method name
     *
     * If this is a HTTP-based call, this should be the path that will be appended to
     * the base URI of the service. For SOAP services, it should be the actual method name.
     *
     * Note that this does NOT refer to the HTTP method.
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->setAttribute('method', (string) $method);

        return $this;
    }

    /**
     * Returns headers to be sent with the request
     *
     * @return mixed[]
     */
    public function getHeaders()
    {
        return $this->getAttribute('headers') ?: [];
    }

    /**
     * Sets request headers
     *
     * @param mixed[] $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->setAttribute('headers', $headers);

        return $this;
    }

    /**
     * Returns parameters to be sent alongside the request
     * (For instance: GET parameters for HTTP calls)
     *
     * @return mixed
     */
    public function getParameters()
    {
        return $this->getAttribute('parameters');
    }

    /**
     * Sets the request parameters
     *
     * @param array|object $parameters
     * @return mixed
     */
    public function setParameters($parameters)
    {
        $this->setAttribute('parameters', $parameters);

        return $this;
    }

    /**
     * Returns request body to be sent with the request
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->getAttribute('body');
    }

    /**
     * Sets the request body
     *
     * @param mixed $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->setAttribute('body', $body);

        return $this;
    }

    /**
     * Returns the credentials for authorization
     *
     * @return array    associative: 'name', 'password', 'domain'
     */
    public function getCredentials()
    {
        return $this->getAttribute('credentials') ?: [];
    }

    /**
     * Sets the credentials to be used for the request.
     *
     * @param string $name
     * @param string $password  optional
     * @param string $domain    optional, for NTLM and similar
     * @return $this
     */
    public function setCredentials($name, $password = null, $domain = null)
    {
        $credentials = $this->getCredentials();

        $credentials['name']     = $name;
        $credentials['password'] = $password;
        $credentials['domain']   = $domain;

        $this->setAttribute('credentials', $credentials);

        return $this;
    }

    /**
     * Returns client-specific options (such as for SOAP)
     *
     * @return mixed[]
     */
    public function getOptions()
    {
        return $this->getAttribute('options') ?: [];
    }

    /**
     * Sets request client-specific options
     *
     * @param mixed[] $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->setAttribute('options', $options);

        return $this;
    }

    /**
     * Returns the port number
     * Note that this is optional, and may (for some services) be included in the location string
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->getAttribute('port');
    }

    /**
     * Sets the port number
     *
     * @param int|null $port
     * @return $this
     */
    public function setPort($port)
    {
        if ( ! is_null($port)) $port = (int) $port;

        $this->setAttribute('port', $port);

        return $this;
    }

}
