<?php
namespace Czim\Service\Requests;

use InvalidArgumentException;

class ServiceRestRequest extends ServiceRequest
{

    const METHOD_DELETE  = 'DELETE';
    const METHOD_GET     = 'GET';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';

    /**
     * List of HTTP methods that are accepted for SetHttpMethod
     *
     * @var array
     */
    protected $allowedHttpMethods = [
        self::METHOD_DELETE,
        self::METHOD_GET,
        self::METHOD_OPTIONS,
        self::METHOD_PATCH,
        self::METHOD_POST,
        self::METHOD_PUT,
    ];


    /**
     * Returns the HTTP method to send the request as
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->getAttribute('http_method');
    }

    /**
     * Sets the HTTP method name
     *
     * @param string $method
     * @return $this
     */
    public function setHttpMethod($method)
    {
        $method = strtoupper( (string) $method);

        if ( ! in_array($method, $this->allowedHttpMethods)) {

            throw new InvalidArgumentException("Invalid HTTP method: '{$method}'");
        }

        $this->setAttribute('http_method', $method);

        return $this;
    }

}
