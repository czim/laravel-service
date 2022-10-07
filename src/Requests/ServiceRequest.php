<?php

declare(strict_types=1);

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
     * {@inheritDoc}
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
        array $options = [],
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
     * {@inheritDoc}
     */
    public function getLocation(): ?string
    {
        return $this->getAttribute('location');
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->getAttribute('headers') ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers): void
    {
        $this->setAttribute('headers', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): mixed
    {
        return $this->getAttribute('parameters');
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters($parameters)
    {
        $this->setAttribute('parameters', $parameters);
    }

    public function getBody(): mixed
    {
        return $this->getAttribute('body');
    }

    public function setBody(mixed $body): void
    {
        $this->setAttribute('body', $body);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials(): array
    {
        return $this->getAttribute('credentials') ?: [];
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->getAttribute('options') ?: [];
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options): void
    {
        $this->setAttribute('options', $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        return $this->getAttribute('port');
    }

    public function setPort(?int $port): void
    {
        $this->setAttribute('port', $port);
    }
}
