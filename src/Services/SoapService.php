<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\SoapFactoryInterface;
use Czim\Service\Events\SoapCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\CouldNotRetrieveException;
use Czim\Service\Requests\ServiceSoapRequest;
use Czim\Service\Requests\ServiceSoapRequestDefaults;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use SoapClient;
use SoapFault;
use SoapHeader;
use Throwable;

class SoapService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    protected string $requestDefaultsClass = ServiceSoapRequestDefaults::class;

    /**
     * Classname/FQN of the SoapClient to use for calls.
     *
     * @var class-string<SoapClient>
     */
    protected string $soapClientClass = SoapClient::class;

    /**
     * @var ServiceSoapRequestDefaults
     */
    protected ServiceRequestDefaultsInterface $defaults;

    /**
     * @var ServiceSoapRequest
     */
    protected ServiceRequestInterface $request;

    protected SoapClient $client;

    protected ?string $wsdl;

    /**
     * The options to inject into the soap client.
     *
     * @var array<string, mixed>
     */
    protected array $clientOptions = [];

    /**
     * Hash for checking whether client needs to be re-initialized.
     *
     * @var string|null
     */
    protected ?string $clientHash = null;

    /**
     * Default SoapClient options to set if not explicitly defined
     *
     * @var array<string, mixed>
     */
    protected array $soapOptionDefaults = [
        'exceptions' => true,
        'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
    ];


    public function getClient(): SoapClient
    {
        return $this->client;
    }


    /**
     * @param ServiceRequestInterface&ServiceSoapRequest $request
     * @return mixed
     * @throws CouldNotRetrieveException
     */
    protected function callRaw(ServiceRequestInterface $request): mixed
    {
        $this->applySoapHeaders();

        try {
            if ($this->request->getBody() !== null) {
                $response = $this->client->{$this->request->getMethod()}(
                    ($this->request->getBody() instanceof Arrayable)
                        ? $this->request->getBody()->toArray()
                        : $this->request->getBody()
                );
            } else {
                $response = $this->client->{$this->request->getMethod()}();
            }
        } catch (SoapFault $e) {
            throw $this->makeCouldNotRetrieveExceptionFromSoapFault($e);
        } catch (Exception $e) {
            throw $this->makeCouldNotRetrieveExceptionFromException($e);
        }

        event(
            new SoapCallCompleted(
                $this->request->getLocation(),
                $this->request->getMethod(),
                $this->request->getParameters(),
                $this->sendResponseToEvent ? $response : null
            )
        );

        $this->parseTracedReponseInformation();

        return $response;
    }

    /**
     * Makes a generic service exception based on the given soapfault.
     * Extend or override this to deal with specific error information that your service might respond with
     *
     * @param SoapFault $soapFault
     * @return CouldNotRetrieveException
     */
    protected function makeCouldNotRetrieveExceptionFromSoapFault(SoapFault $soapFault): Throwable
    {
        return new CouldNotRetrieveException($soapFault->getMessage(), $soapFault->getCode(), $soapFault);
    }

    /**
     * Makes a generic service exception based on the given soapfault.
     * Extend or override this to deal with specific error information that your service might respond with
     *
     * @param Exception $exception
     * @return CouldNotRetrieveException
     */
    protected function makeCouldNotRetrieveExceptionFromException(Exception $exception): Throwable
    {
        return new CouldNotRetrieveException($exception->getMessage(), $exception->getCode(), $exception);
    }

    /**
     * Applies request's headers as soapheaders on the SoapClient.
     */
    protected function applySoapHeaders(): void
    {
        $headers = $this->request->getHeaders() ?: [];


        foreach ($headers as &$header) {
            if ($header instanceof SoapHeader) {
                continue;
            }

            $namespace      = $header['namespace'] ?? null;
            $name           = $header['name'] ?? null;
            $data           = $header['data'] ?? null;
            $mustUnderstand = $header['mustunderstand'] ?? null;
            $actor          = $header['actor'] ?? null;

            $header = new SoapHeader($namespace, $name, $data, $mustUnderstand, $actor);
        }

        unset($header);

        $this->client->__setSoapHeaders($headers);
    }

    /**
     * Extracts information from SOAP client if tracing.
     */
    protected function parseTracedReponseInformation(): void
    {
        // nothing to set if we weren't tracing
        if (! isset($this->clientOptions['trace']) || $this->clientOptions['trace'] !== true) {
            return;
        }

        $responseHeaderString = $this->client->__getLastResponseHeaders();

        $this->responseInformation->setStatusCode(
            $this->parseResponseHeaderForStatusCode($responseHeaderString)
        );

        $this->responseInformation->setHeaders(
            $this->parseResponseHeadersAsArray($responseHeaderString)
        );
    }

    protected function parseResponseHeaderForStatusCode(string $headers): int
    {
        if (! preg_match('#^\s*http/\d\.\d\s+(?<code>\d+)\s+#i', $headers, $matches)) {
            return 200;
        }

        return (int) $matches['code'];
    }

    /**
     * Parses a header string to an array.
     *
     * @param null|string $headers
     * @return array<string, mixed>
     */
    protected function parseResponseHeadersAsArray(?string $headers): array
    {
        if (empty($headers)) {
            return [];
        }

        $headersArray = [];

        foreach (preg_split('#[\r\n]+#', $headers) as $headerString) {
            if (! preg_match('#^\s*(?<name>.*?)\s*:\s*(?<value>.*)\s*$#', $headerString, $matches)) {
                continue;
            }

            $headersArray[ $matches['name'] ] = $matches['value'];
        }

        return $headersArray;
    }

    /**
     * Runs before any call is made.
     *
     * Initializes SoapClient before first call is made or when changed settings require it.
     */
    protected function before(): void
    {
        if (
            empty($this->client)
            || empty($this->clientHash)
            || $this->clientHash !== $this->makeSoapClientHash()
        ) {
            $this->initializeClient();
        }
    }

    /**
     * Initializes Soap Client with WSDL and an options array.
     *
     * @throws CouldNotConnectException
     */
    protected function initializeClient(): void
    {
        // Store some specific soap-client related data locally
        // so it can be injected in the SoapClient and compared
        // for changes later

        $this->wsdl          = $this->request->getLocation();
        $this->clientOptions = $this->request->getOptions();

        // store hash to make it possible to detect changes to the client
        $this->clientHash = $this->makeSoapClientHash();

        $xdebugEnabled = extension_loaded('xdebug') && function_exists('xdebug_is_enabled') && \xdebug_is_enabled();

        try {
            // temporarily disable xdebug to prevent PHP Fatal error
            // while constructing SoapClient

            if ($xdebugEnabled) {
                \xdebug_disable();
            }

            $class = $this->soapClientClass;

            $this->client = $this->getSoapClientFactory()->make($class, $this->wsdl, $this->clientOptions);

            if ($xdebugEnabled) {
                \xdebug_enable();
            }
        } catch (SoapFault $e) {
            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
        } catch (Throwable $e) {
            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getSoapClientFactory(): SoapFactoryInterface
    {
        return app(SoapFactoryInterface::class);
    }

    /**
     * Creates a hash from the combination of all settings that need to
     * be tracked to see whether a new soapclient should be instantiated
     */
    protected function makeSoapClientHash(): string
    {
        return sha1(
            $this->request->getLocation()
            . json_encode($this->request->getOptions())
            . json_encode($this->request->getHeaders())
        );
    }

    /**
     * Supplements request with soap options, in addition to the standard supplements.
     */
    protected function supplementRequestWithDefaults(): void
    {
        parent::supplementRequestWithDefaults();

        // set or expand with default options
        $this->request->setOptions(array_merge(
            $this->request['options'] ?: [],
            $this->defaults['options'] ?: []
        ));
    }

    /**
     * Checks the request to be used in the next/upcoming call
     */
    protected function checkRequest(): void
    {
        parent::checkRequest();

        if (! $this->request instanceof ServiceSoapRequest) {
            throw new InvalidArgumentException('Request class is not a ServiceSoapRequest');
        }
    }

    /**
     * Runs directly after construction.
     * Extend this to customize your service.
     *
     * Defaults to 'exceptions' option enabled.
     */
    protected function initialize(): void
    {
        parent::initialize();

        // unless already configured, set default options to include exceptions
        $options = $this->defaults->getOptions() ?: [];

        foreach ($this->soapOptionDefaults as $option => $value) {
            if (! array_key_exists($option, $options)) {
                $options[ $option ] = $value;
            }
        }

        $this->defaults->setOptions($options);
    }
}
