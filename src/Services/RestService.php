<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Events\RestCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Requests\ServiceRestRequest;
use Czim\Service\Requests\ServiceRestRequestDefaults;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;
use InvalidArgumentException;

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
     * The classname of the defaults object to instantiate if none is injected
     *
     * @var string
     */
    protected $requestDefaultsClass = ServiceRestRequestDefaults::class;

    /**
     * @var ServiceRestRequest;
     */
    protected $defaults;

    /**
     * @var ServiceRestRequest
     */
    protected $request;

    /**
     * The default method to use for the HTTP call
     *
     * @var string
     */
    protected $httpMethod = self::METHOD_POST;

    /**
     * Whether to use basic authentication
     *
     * @var bool
     */
    protected $basicAuth = true;

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
    public function __construct(ServiceRequestDefaultsInterface $defaults = null,
                                ServiceInterpreterInterface $interpreter = null,
                                array $guzzleConfig = [])
    {
        $this->client = app(Client::class, [ $guzzleConfig ]);

        parent::__construct($defaults, $interpreter);
    }


    /**
     * Applies mass configuration to default request
     *
     * @param array $config
     * @return $this
     */
    public function config(array $config)
    {
        parent::config($config);

        if (array_key_exists('httpMethod', $config)) {
            $this->defaults->setHttpMethod($config['httpMethod']);
        }
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

        $httpMethod = $this->determineHttpMethod($request);

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

        switch ($httpMethod) {

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

            $response = $this->client->request($httpMethod, $url, $options);

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
                isset($options['form_params'])
                    ?   $options['form_params']
                    :   (isset($options['query'])
                            ?   $options['query']
                            :   []),
                ($this->sendResponseToEvent) ? $response : null
            )
        );


        return $response->getBody()->getContents();
    }

    /**
     * Returns HTTP method to use based on request & default
     *
     * @param ServiceRequestInterface|ServiceRestRequest $request
     * @return string
     */
    protected function determineHttpMethod(ServiceRequestInterface $request)
    {
        // use method set in request, or fall back to default
        return $request->getHttpMethod() ?: $this->httpMethod;
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

    /**
     * Checks the request to be used in the next/upcoming call
     */
    protected function checkRequest()
    {
        parent::checkRequest();

        if ( ! is_a($this->request, ServiceRestRequest::class)) {

            throw new InvalidArgumentException("Request class is not a ServiceRestRequest");
        }
    }

    /**
     * Supplements request with soap options, in addition to the standard supplements
     */
    protected function supplementRequestWithDefaults()
    {
        parent::supplementRequestWithDefaults();

        // set the HTTP Method if it is set in the defaults
        if (empty($this->request->getHttpMethod()) && ! empty($this->defaults['http_method'])) {

            $this->request->setHttpMethod( $this->defaults['http_method'] );
        }
    }


    // ------------------------------------------------------------------------------
    //      Getters, Setters and Configuration
    // ------------------------------------------------------------------------------

    /**
     * Sets the default HTTP method
     *
     * @param string $method GET, POST, etc
     * @return $this
     */
    public function setHttpMethod($method)
    {
        $this->httpMethod = (string) $method;

        return $this;
    }

    /**
     * Returns the default HTTP method
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Disables basic authentication, even if credentials are provided
     */
    public function disableBasicAuth()
    {
        $this->basicAuth = false;
    }

    /**
     * Enables basic authentication, uses the request's credentials
     */
    public function enableBasicAuth()
    {
        $this->basicAuth = true;
    }

}
