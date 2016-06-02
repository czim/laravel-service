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
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

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
     * Whether to send form parameters as multipart data
     *
     * @var bool
     */
    protected $multipart = false;

    /**
     * Whether to send POST/PUT/PATCH body data as json (multipart will override this)
     *
     * @var bool
     */
    protected $sendJson = false;


    /**
     * @param ServiceRequestDefaultsInterface $defaults
     * @param ServiceInterpreterInterface     $interpreter
     * @param array                           $guzzleConfig     default config to pass into the guzzle client
     */
    public function __construct(
        ServiceRequestDefaultsInterface $defaults = null,
        ServiceInterpreterInterface $interpreter = null,
        array $guzzleConfig = []
    ) {
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

        if (array_key_exists('http_method', $config)) {
            $this->defaults->setHttpMethod($config['http_method']);
        }

        return $this;
    }

    /**
     * Returns the rules to validate the config against
     *
     * @return array
     */
    protected function getConfigValidationRules()
    {
        return array_merge(
            parent::getConfigValidationRules(),
            [
                'http_method' => 'in:DELETE,GET,PATCH,POST,PUT,OPTIONS',
            ]
        );
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

        $options = $this->prepareGuzzleOptions($request, $httpMethod);

        $this->beforeGuzzleCall($options);

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

        $this->afterGuzzleCall($response);

        $this->responseInformation->setStatusCode( $response->getStatusCode() );
        $this->responseInformation->setMessage( $response->getReasonPhrase() );
        $this->responseInformation->setHeaders( $response->getHeaders() );

        $responseBody = $response->getBody()->getContents();

        event(
            new RestCallCompleted(
                $url,
                isset($options['form_params'])
                    ?   $options['form_params']
                    :   (isset($options['query'])
                            ?   $options['query']
                            :   []),
                ($this->sendResponseToEvent) ? $responseBody : null
            )
        );

        return $responseBody;
    }

    /**
     * Prepares and returns guzzle options array for next call
     *
     * @param ServiceRequestInterface $request
     * @param null|string             $httpMethod
     * @return array
     */
    protected function prepareGuzzleOptions(ServiceRequestInterface $request, $httpMethod = null)
    {
        if ( ! $httpMethod) {
            $httpMethod = $this->determineHttpMethod($request);
        }

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
                if ($this->multipart) {
                    $options['multipart'] = $this->prepareMultipartData($request->getBody());
                } elseif ($this->sendJson) {
                    $options['json'] = $request->getBody();
                } else {
                    $options['form_params'] = $request->getBody();
                }

                $parameters = $request->getParameters();

                if ( ! empty($parameters)) {
                    $options['query'] = $parameters;
                }
                break;

            case static::METHOD_DELETE:
            case static::METHOD_GET:
                $options['query'] = $request->getBody() ?: [];
                break;

            // default omitted on purpose
        }

        // headers

        $headers = $request->getHeaders();

        if (count($headers)) {
            $options['headers'] = $headers;
        }

        return $options;
    }

    /**
     * Called before any guzzle-based call.
     * Use this to make custom changes to the options array
     *
     * @param array $options
     */
    protected function beforeGuzzleCall(array &$options)
    {
    }

    /**
     * Called directly after a succesful guzzle call
     *
     * @param ResponseInterface $response
     */
    protected function afterGuzzleCall(ResponseInterface $response)
    {
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

    /**
     * Converts a given array with parameters to the multipart array format
     *
     * @param array|Arrayable $params
     * @return array
     */
    protected function prepareMultipartData($params)
    {
        $multipart = [];

        if ($params instanceof Arrayable) {
            $params = $params->toArray();
        }

        foreach ($params as $key => $value) {

            if ( ! is_array($value)) {

                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];

                continue;
            }

            foreach (array_dot($value) as $dotKey => $leafValue) {

                $partKey = $key
                    . implode(
                        array_map(
                            function($partKey) { return "[{$partKey}]"; },
                            explode('.', $dotKey)
                        )
                    );

                $multipart[] = [
                    'name'     => $partKey,
                    'contents' => $leafValue,
                ];
            }
        }
        
        return $multipart;
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

        return $this;
    }

    /**
     * Enables basic authentication, uses the request's credentials
     */
    public function enableBasicAuth()
    {
        $this->basicAuth = true;

        return $this;
    }

    /**
     * Enables sending form parameters as multipart data
     *
     * @return $this
     */
    public function enableMultipart()
    {
        $this->multipart = true;

        return $this;
    }

    /**
     * Disables sending form parameters as multipart data
     *
     * @return $this
     */
    public function disableMultipart()
    {
        $this->multipart = false;

        return $this;
    }

    /**
     * Enables sending PUT/POST/PATCH as json
     *
     * @return $this
     */
    public function enableSendJson()
    {
        $this->sendJson = true;

        return $this;
    }

    /**
     * Disables sending PUT/POST/PATCH as json
     *
     * @return $this
     */
    public function disableSendJson()
    {
        $this->sendJson = false;

        return $this;
    }

}
