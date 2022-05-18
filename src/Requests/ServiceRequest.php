<?php

namespace Czim\Service\Requests;

use Czim\DataObject\AbstractDataObject;
use Czim\Service\Contracts\ServiceRequestInterface;

/**
 * @property string               $location
 * @property int                  $port
 * @property string               $method
 * @property mixed                $parameters
 * @property array<string, mixed> $headers
 * @property mixed                $body
 * @property string[]             $credentials
 * @property array<string, mixed> $options      key-value pairs
 */
class ServiceRequest extends AbstractDataObject implements ServiceRequestInterface
{
    protected bool $magicAssignment = false;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [
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
     * @param mixed                     $body
     * @param mixed                     $parameters
     * @param array<string, mixed>|null $headers
     * @param string|null               $method
     * @param string|null               $location
     * @param array<string, mixed>      $options
     */
    public function __construct(
        $body = null,
        $parameters = null,
        array $headers = null,
        ?string $method = null,
        ?string $location = null,
        array $options = []
    ) {
        $this->setBody($body);
        $this->setParameters($parameters);

        if ($headers !== null) {
            $this->setHeaders($headers);
        }

        $this->setMethod($method);
        $this->setLocation($location);
        $this->setOptions($options);

        parent::__construct();
    }

    /**
     * Returns the base URL or WSDL for the service, if it is set in the request.
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->getAttribute('location');
    }

    /**
     * Sets the base URL or WSDL location for the service, as an optional override
     * for the service configuration.
     *
     * @param string|null $location
     */
    public function setLocation(?string $location): void
    {
        $this->setAttribute('location', $location);
    }

    /**
     * Returns the method or endpoint name to send the request to.
     *
     * Note that this does NOT refer to the HTTP method.
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->getAttribute('method');
    }

    /**
     * Sets the method name.
     *
     * If this is a HTTP-based call, this should be the path that will be appended to
     * the base URI of the service. For SOAP services, it should be the actual method name.
     *
     * Note that this does NOT refer to the HTTP method.
     *
     * @param string|null $method
     */
    public function setMethod(?string $method): void
    {
        $this->setAttribute('method', $method);
    }

    /**
     * Returns headers to be sent with the request.
     *
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->getAttribute('headers') ?: [];
    }

    /**
     * Sets request headers.
     *
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->setAttribute('headers', $headers);
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
     * Sets the request parameters.
     *
     * @param mixed[]|object $parameters
     */
    public function setParameters($parameters)
    {
        $this->setAttribute('parameters', $parameters);
    }

    /**
     * Returns request body to be sent with the request.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->getAttribute('body');
    }

    /**
     * Sets the request body.
     *
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->setAttribute('body', $body);
    }

    /**
     * Returns the credentials for authorization.
     *
     * @return array<string, string> associative: 'name', 'password', 'domain'
     */
    public function getCredentials(): array
    {
        return $this->getAttribute('credentials') ?: [];
    }

    /**
     * Sets the credentials to be used for the request.
     *
     * @param string      $name
     * @param string|null $password
     * @param string|null $domain    NTLM and similar
     */
    public function setCredentials(string $name, ?string $password = null, ?string $domain = null): void
    {
        $credentials = $this->getCredentials();

        $credentials['name']     = $name;
        $credentials['password'] = $password;
        $credentials['domain']   = $domain;

        $this->setAttribute('credentials', $credentials);
    }

    /**
     * Returns client-specific options (such as for SOAP).
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->getAttribute('options') ?: [];
    }

    /**
     * Sets request client-specific options
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->setAttribute('options', $options);
    }

    /**
     * Returns the port number
     * Note that this is optional, and may (for some services) be included in the location string
     *
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->getAttribute('port');
    }

    /**
     * Sets the port number
     *
     * @param int|null $port
     */
    public function setPort(?int $port): void
    {
        $this->setAttribute('port', $port);
    }
}
