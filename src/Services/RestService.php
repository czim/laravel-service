<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Events\RestCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;

class RestService extends AbstractService
{
    const USER_AGENT = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

    const METHOD_DELETE  = 'PUT';
    const METHOD_GET     = 'GET';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';


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
     * @var Client
     */
    protected $client;


    /**
     * @param ServiceRequestDefaultsInterface $defaults
     * @param ServiceInterpreterInterface     $interpreter
     * @param array                           $guzzleConfig     default config to pass into the guzzle client
     */
    public function __construct(ServiceRequestDefaultsInterface $defaults = null, ServiceInterpreterInterface $interpreter = null, array $guzzleConfig = [])
    {
        $this->client = new Client($guzzleConfig);

        parent::__construct($defaults, $interpreter);
    }

    /**
     * Performs raw REST call
     *
     * @param ServiceRequestInterface $request
     * @return mixed
     * @throws CouldNotConnectException
     * @throws Exception
     */
    protected function callRaw(ServiceRequestInterface $request)
    {
        $url = rtrim($request->getLocation(), '/') . '/' . $request->getMethod();

        $options = [
            'http_errors' => false,
        ];


        // handle authentication

        $credentials = $request->getCredentials();

        if (    $this->basicAuth
            &&  ! empty($credentials['name'])
            &&  ! empty($credentials['password'])
        ) {
            $options['auth'] = [ $credentials['name'], $credentials['password'] ];
        }


        // handle parameters and body

        switch ($this->method) {

            case static::METHOD_PATCH:
            case static::METHOD_POST:
            case static::METHOD_PUT:
                $options['form_params'] = $request->getBody();

                $parameters = $request->getParameters();

                if ( ! empty($parameters)) {
                    $options['query'] = $parameters;
                }
                break;

            case static::METHOD_DELETE:
            case static::METHOD_GET:
                $options['query'] = $request->getbody() ?: [];
                break;

            // default omitted on purpose
        }

        // headers

        $headers = $request->getHeaders();

        if (count($headers)) {
            $options['headers'] = $headers;
        }

        // perform request

        try {

            $response = $this->client->request($this->method, $url, $options);

        } catch (Exception $e) {

            // throw as CouldNotConnect if it is a guzzle error
            // or rethrow if unexpected

            if ($this->isGuzzleException($e)) {

                throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }

        $this->responseInformation->setStatusCode( $response->getStatusCode() );
        $this->responseInformation->setMessage( $response->getReasonPhrase() );
        $this->responseInformation->setHeaders( $response->getHeaders() );


        event(
            new RestCallCompleted(
                $url,
                isset($options['form_params']) ? $options['form_params'] : $options['query'],
                ($this->sendResponseToEvent) ? $response : null
            )
        );


        return $response->getBody()->getContents();
    }

    /**
     * Returns whether the given is a standard Guzzle exception
     *
     * @param Exception $e
     * @return bool
     */
    protected function isGuzzleException(Exception $e)
    {
        return (    is_a($e, GuzzleException\BadResponseException::class)
                ||  is_a($e, GuzzleException\ClientException::class)
                ||  is_a($e, GuzzleException\ConnectException::class)
                ||  is_a($e, GuzzleException\RequestException::class)
                ||  is_a($e, GuzzleException\SeekException::class)
                ||  is_a($e, GuzzleException\ServerException::class)
                ||  is_a($e, GuzzleException\TooManyRedirectsException::class)
                ||  is_a($e, GuzzleException\TransferException::class)
        );
    }

    // ------------------------------------------------------------------------------
    //      Getters, Setters and Configuration
    // ------------------------------------------------------------------------------

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
