<?php
namespace Czim\Service\Contracts;

use Czim\DataObject\Contracts\DataObjectInterface;

interface ServiceRequestInterface extends DataObjectInterface
{

    /**
     * Returns the base URL or WSDL for the service, if it is set in the request.
     *
     * @return string
     */
    public function getLocation();

    /**
     * Sets the base URL or WSDL location for the service, as an optional override
     * for the service configuration.
     *
     * @param string $location
     * @return $this
     */
    public function setLocation($location);

    /**
     * Returns the method or endpoint name to send the request to
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sets the method name
     *
     * If this is a HTTP-based call, this should be the path that will be appended to
     * the base URI of the service. For SOAP services, it should be the actual method name.
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Returns headers to be sent with the request
     *
     * @return mixed[]
     */
    public function getHeaders();

    /**
     * Sets request headers
     *
     * @param mixed[] $headers
     * @return $this
     */
    public function setHeaders(array $headers);

    /**
     * Returns parameters to be sent alongside the request
     * (For instance: GET parameters for HTTP calls)
     *
     * @return mixed
     */
    public function getParameters();

    /**
     * Sets the request parameters
     *
     * @param array|object $parameters
     * @return mixed
     */
    public function setParameters($parameters);

    /**
     * Returns request body to be sent with the request
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Sets the request body
     *
     * @param mixed $body
     * @return $this
     */
    public function setBody($body);

    /**
     * Returns the credentials for authorization
     *
     * @return array    associative: 'name', 'password', 'domain'
     */
    public function getCredentials();

    /**
     * Sets the credentials to be used for the request.
     *
     * @param string $name
     * @param string $password
     * @param string $domain        optional, for NTLM and similar
     * @return $this
     */
    public function setCredentials($name, $password = null, $domain = null);

    /**
     * Returns client-specific options (such as for SOAP)
     *
     * @return mixed[]
     */
    public function getOptions();

    /**
     * Sets request client-specific options
     *
     * @param mixed[] $options
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * Returns the port number
     * Note that this is optional, and may (for some services) be included in the location string
     *
     * @return int|null
     */
    public function getPort();

    /**
     * Sets the port number
     *
     * @param int|null $port
     * @return $this
     */
    public function setPort($port);

}
