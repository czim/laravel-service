<?php
namespace Czim\Service;

use Czim\Service\Exceptions\CouldNotConnectException;

class RestService extends AbstractService
{
    const USER_AGENT = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';


    /**
     * The method to use for the HTTP call
     *
     * @var string
     */
    protected $method = self::METHOD_POST;

    /**
     * Whether to use basic authentication
     *
     * @var bool
     */
    protected $basicAuth = false;

    /**
     * HTTP headers
     *
     * @var array
     */
    protected $headers = [];


    /**
     * Performs raw REST call
     *
     * @param string $method     name of the method to call through the service
     * @param mixed  $parameters parameters to send along
     * @return mixed
     * @throws CouldNotConnectException
     */
    public function callRaw($method, $parameters = [])
    {
        $url = $this->location;

        $curl = curl_init();

        if ($curl === false) {
            throw new CouldNotConnectException('cURL could not be initialized');
        }


        if ($this->basicAuth && ! empty($this->user) && ! empty($this->password)) {

            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->user . ":" . $this->password);
        }


        switch ($this->method) {

            case static::METHOD_POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->parameters));
                break;

            case static::METHOD_GET:
                $url .= '?' . http_build_query($this->parameters);
                break;

            // default omitted on purpose
        }


        if (count($this->headers)) {

            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        }


        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new CouldNotConnectException(curl_error($curl), curl_errno($curl));
        }

        curl_close($curl);

        return $response;
    }


    // ------------------------------------------------------------------------------
    //      Getters, Setters and Configuration
    // ------------------------------------------------------------------------------

    /**
     * Adds a HTTP header by name
     *
     * @param  string $name
     * @param  mixed  $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->headers[ $name ] = $value;

        return $this;
    }

    /**
     * Adds multiple HTTP headers at once
     *
     * @param array $headers    associative name => value pairs
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param string $method GET, POST, etc
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = (string) $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
