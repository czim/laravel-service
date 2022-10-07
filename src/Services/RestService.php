<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Czim\Service\Contracts\GuzzleFactoryInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Events\RestCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Requests\ServiceRestRequest;
use Czim\Service\Requests\ServiceRestRequestDefaults;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception as GuzzleException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RestService extends AbstractService
{
    public const METHOD_DELETE  = 'PUT';
    public const METHOD_GET     = 'GET';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_PATCH   = 'PATCH';
    public const METHOD_POST    = 'POST';
    public const METHOD_PUT     = 'PUT';

    protected const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';


    /**
     * {@inheritDoc}
     */
    protected string $requestDefaultsClass = ServiceRestRequestDefaults::class;

    /**
     * @var ServiceRestRequestDefaults
     */
    protected ServiceRequestDefaultsInterface $defaults;

    /**
     * @var ServiceRestRequest
     */
    protected ServiceRequestInterface $request;

    /**
     * The default method to use for the HTTP call.
     *
     * @var string
     */
    protected string $httpMethod = self::METHOD_POST;

    /**
     * Whether to use basic authentication.
     *
     * @var bool
     */
    protected bool $basicAuth = true;

    /**
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * Whether to send form parameters as multipart data.
     *
     * @var bool
     */
    protected bool $multipart = false;

    /**
     * Whether to send POST/PUT/PATCH body data as json (multipart will override this).
     *
     * @var bool
     */
    protected bool $sendJson = false;


    /**
     * @param ServiceRequestDefaultsInterface|null $defaults
     * @param ServiceInterpreterInterface|null     $interpreter
     * @param array<string, mixed>                 $guzzleConfig default config to pass into the guzzle client
     */
    public function __construct(
        ServiceRequestDefaultsInterface $defaults = null,
        ServiceInterpreterInterface $interpreter = null,
        array $guzzleConfig = []
    ) {
        $this->client = $this->createGuzzleClient($guzzleConfig);

        parent::__construct($defaults, $interpreter);
    }


    /**
     * Applies mass configuration to default request.
     *
     * @param array<string, mixed> $config
     */
    public function config(array $config): void
    {
        parent::config($config);

        if (array_key_exists('http_method', $config)) {
            $this->defaults->setHttpMethod($config['http_method']);
        }
    }


    public function setHttpMethod(string $method): void
    {
        $this->httpMethod = $method;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * Disables basic authentication, even if credentials are provided.
     */
    public function disableBasicAuth(): void
    {
        $this->basicAuth = false;
    }

    /**
     * Enables basic authentication, uses the request's credentials.
     */
    public function enableBasicAuth(): void
    {
        $this->basicAuth = true;
    }

    /**
     * Enables sending form parameters as multipart data.
     */
    public function enableMultipart(): void
    {
        $this->multipart = true;
    }

    /**
     * Disables sending form parameters as multipart data.
     */
    public function disableMultipart(): void
    {
        $this->multipart = false;
    }

    /**
     * Enables sending PUT/POST/PATCH as JSON.
     */
    public function enableSendJson(): void
    {
        $this->sendJson = true;
    }

    /**
     * Disables sending PUT/POST/PATCH as JSON.
     */
    public function disableSendJson(): void
    {
        $this->sendJson = false;
    }


    /**
     * Returns the rules to validate the config against.
     *
     * @return array<string, mixed>
     */
    protected function getConfigValidationRules(): array
    {
        return array_merge(
            parent::getConfigValidationRules(),
            [
                'http_method' => 'in:DELETE,GET,PATCH,POST,PUT,OPTIONS',
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function callRaw(ServiceRequestInterface $request): mixed
    {
        $url = rtrim($request->getLocation(), '/') . '/' . $request->getMethod();

        $httpMethod = $this->determineHttpMethod($request);

        $options = $this->prepareGuzzleOptions($request, $httpMethod);

        $this->beforeGuzzleCall($options);

        try {
            $response = $this->client->request($httpMethod, $url, $options);
        } catch (Throwable $e) {
            // Throw as CouldNotConnect if it is a guzzle error or rethrow if unexpected
            if ($this->isGuzzleException($e)) {
                throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }

        $this->afterGuzzleCall($response);

        $this->responseInformation->setStatusCode($response->getStatusCode());
        $this->responseInformation->setMessage($response->getReasonPhrase());
        $this->responseInformation->setHeaders($response->getHeaders());

        $responseBody = $response->getBody()->getContents();

        event(
            new RestCallCompleted(
                $url,
                $options['form_params'] ?? ($options['query'] ?? []),
                $this->sendResponseToEvent ? $responseBody : null
            )
        );

        return $responseBody;
    }

    /**
     * Prepares and returns guzzle options array for next call.
     *
     * @param ServiceRequestInterface $request
     * @param string|null             $httpMethod
     * @return array<string, mixed>
     */
    protected function prepareGuzzleOptions(ServiceRequestInterface $request, ?string $httpMethod = null): array
    {
        if (! $httpMethod) {
            $httpMethod = $this->determineHttpMethod($request);
        }

        $options = [
            'http_errors' => false,
        ];


        // Handle authentication
        $credentials = $request->getCredentials();

        if (
            $this->basicAuth
            && ! empty($credentials['name'])
            && ! empty($credentials['password'])
        ) {
            $options['auth'] = [ $credentials['name'], $credentials['password'] ];
        }


        // Handle parameters and body
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

                if (! empty($parameters)) {
                    $options['query'] = $parameters;
                }
                break;

            case static::METHOD_DELETE:
            case static::METHOD_GET:
                $options['query'] = $request->getBody() ?: [];
                break;

            // default omitted on purpose
        }

        // Hheaders
        $headers = $request->getHeaders();

        if (count($headers)) {
            $options['headers'] = $headers;
        }

        return $options;
    }

    /**
     * Called before any guzzle-based call.
     * Use this to make custom changes to the options array.
     *
     * @param array<string, mixed> $options
     */
    protected function beforeGuzzleCall(array &$options): void
    {
    }

    /**
     * Called directly after a successful guzzle call.
     *
     * @param ResponseInterface $response
     */
    protected function afterGuzzleCall(ResponseInterface $response): void
    {
    }

    /**
     * Returns HTTP method to use based on request & default.
     *
     * @param ServiceRequestInterface&ServiceRestRequest $request
     * @return string
     */
    protected function determineHttpMethod(ServiceRequestInterface $request): string
    {
        // use method set in request, or fall back to default
        return $request->getHttpMethod() ?: $this->httpMethod;
    }

    protected function isGuzzleException(Throwable $e): bool
    {
        return $e instanceof GuzzleException\TransferException;
    }

    protected function checkRequest(): void
    {
        parent::checkRequest();

        if (! $this->request instanceof ServiceRestRequest) {
            throw new InvalidArgumentException('Request class is not a ServiceRestRequest');
        }
    }

    /**
     * Supplements request with soap options, in addition to the standard supplements.
     */
    protected function supplementRequestWithDefaults(): void
    {
        parent::supplementRequestWithDefaults();

        // set the HTTP Method if it is set in the defaults
        if (
            empty($this->request->getHttpMethod())
            && ! empty($this->defaults['http_method'])
        ) {
            $this->request->setHttpMethod(
                $this->defaults['http_method']
            );
        }
    }

    /**
     * Converts a given array with parameters to the multipart array format.
     *
     * @param array<int|string, mixed>|Arrayable $params
     * @return array<int, array<string, mixed>>
     */
    protected function prepareMultipartData(array|Arrayable $params): array
    {
        $multipart = [];

        if ($params instanceof Arrayable) {
            $params = $params->toArray();
        }

        foreach ($params as $key => $value) {
            if (! is_array($value)) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => $value,
                ];

                continue;
            }

            foreach (Arr::dot($value) as $dotKey => $leafValue) {
                $partKey = $key
                    . implode(
                        array_map(
                            fn (string $partKey): string => "[{$partKey}]",
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

    /**
     * @param array<string, mixed> $config
     * @return ClientInterface
     */
    protected function createGuzzleClient(array $config): ClientInterface
    {
        /** @var GuzzleFactoryInterface $factory */
        $factory = app(GuzzleFactoryInterface::class);

        return $factory->make($config);
    }
}
