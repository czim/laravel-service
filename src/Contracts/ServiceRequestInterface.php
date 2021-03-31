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
    public function getLocation(): ?string;

    /**
     * Sets the base URL or WSDL location for the service, as an optional override
     * for the service configuration.
     *
     * @param string|null $location
     */
    public function setLocation(?string $location): void;

    /**
     * Returns the method or endpoint name to send the request to.
     *
     * @return string|null
     */
    public function getMethod(): ?string;

    /**
     * Sets the method name.
     *
     * If this is a HTTP-based call, this should be the path that will be appended to
     * the base URI of the service. For SOAP services, it should be the actual method name.
     *
     * @param string|null $method
     */
    public function setMethod(?string $method): void;

    /**
     * Returns headers to be sent with the request.
     *
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * Sets request headers.
     *
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers): void;

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
     * Returns request body to be sent with the request.
     *
     * @return mixed
     */
    public function getBody();

    /**
     * Sets the request body.
     *
     * @param mixed $body
     */
    public function setBody($body): void;

    /**
     * Returns the credentials for authorization
     *
     * @return array<string, string> associative: 'name', 'password', 'domain'
     */
    public function getCredentials(): array;

    /**
     * Sets the credentials to be used for the request.
     *
     * @param string      $name
     * @param string|null $password
     * @param string|null $domain    NTLM and similar
     */
    public function setCredentials(string $name, ?string $password = null, ?string $domain = null): void;

    /**
     * Returns client-specific options (such as for SOAP).
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * Sets request client-specific options.
     *
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void;

    /**
     * Returns the port number.
     * Note that this is optional, and may (for some services) be included in the location string
     *
     * @return int|null
     */
    public function getPort(): ?int;

    /**
     * Sets the port number.
     *
     * @param int|null $port
     */
    public function setPort(?int $port): void;
}
