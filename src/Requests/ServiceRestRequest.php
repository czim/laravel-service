<?php

declare(strict_types=1);

namespace Czim\Service\Requests;

use InvalidArgumentException;

class ServiceRestRequest extends ServiceRequest
{
    public const METHOD_DELETE  = 'DELETE';
    public const METHOD_GET     = 'GET';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_PATCH   = 'PATCH';
    public const METHOD_POST    = 'POST';
    public const METHOD_PUT     = 'PUT';

    /**
     * List of HTTP methods that are accepted for SetHttpMethod.
     *
     * @var string[]
     */
    protected array $allowedHttpMethods = [
        self::METHOD_DELETE,
        self::METHOD_GET,
        self::METHOD_OPTIONS,
        self::METHOD_PATCH,
        self::METHOD_POST,
        self::METHOD_PUT,
    ];


    /**
     * Returns the HTTP method to send the request as.
     *
     * @return string|null
     */
    public function getHttpMethod(): ?string
    {
        return $this->getAttribute('http_method');
    }

    /**
     * Sets the HTTP method name.
     *
     * @param string $method
     */
    public function setHttpMethod(string $method): void
    {
        $method = strtoupper($method);

        if (! $this->isAllowedMethodName($method)) {
            throw new InvalidArgumentException("Invalid HTTP method: '{$method}'");
        }

        $this->setAttribute('http_method', $method);
    }

    protected function isAllowedMethodName(string $method): bool
    {
        return in_array($method, $this->allowedHttpMethods);
    }
}
