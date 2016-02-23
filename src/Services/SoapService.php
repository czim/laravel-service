<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceRequestInterface;
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

class SoapService extends AbstractService
{

    /**
     * The classname of the defaults object to instantiate if none is injected
     * 
     * @var string
     */
    protected $requestDefaultsClass = ServiceSoapRequestDefaults::class;

    /**
     * Classname/FQN of the SoapClient to use for calls
     *
     * @var string
     */
    protected $soapClientClass = SoapClient::class;

    /**
     * @var ServiceSoapRequestDefaults
     */
    protected $defaults;

    /**
     * @var ServiceSoapRequest
     */
    protected $request;

    /**
     * @var SoapClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $wsdl;

    /**
     * The options to inject into the soap client
     *
     * @var array
     */
    protected $clientOptions = [];

    /**
     * Hash for checking whether client needs to be re-initialized
     *
     * @var string
     */
    protected $clientHash;

    /**
     * Default SoapClient options to set if not explicitly defined
     *
     * @var array
     */
    protected $soapOptionDefaults = [
        'exceptions' => true,
        'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
    ];


    /**
     * @param ServiceRequestInterface|ServiceSoapRequest $request
     * @return mixed
     * @throws CouldNotRetrieveException
     */
    protected function callRaw(ServiceRequestInterface $request)
    {
        $this->applySoapHeaders();

        try {

            if ( ! is_null($this->request->getBody())) {

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
        }

        event(
            new SoapCallCompleted(
                $this->request->getLocation(),
                $this->request->getMethod(),
                $this->request->getParameters(),
                ($this->sendResponseToEvent) ? $response : null
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
    protected function makeCouldNotRetrieveExceptionFromSoapFault(SoapFault $soapFault)
    {
        $exception = new CouldNotRetrieveException($soapFault->getMessage(), $soapFault->getCode(), $soapFault);

        return $exception;
    }

    /**
     * Applies request's headers as soapheaders on the SoapClient
     */
    protected function applySoapHeaders()
    {
        $headers = $this->request->getHeaders() ?: [];

        if (is_a($headers, SoapHeader::class)) {

            $headers = [ $headers ];

        } else {

            foreach ($headers as &$header) {
                if (is_a($header, SoapHeader::class)) continue;

                $namespace      = isset($header['namespace']) ? $header['namespace'] : null;
                $name           = isset($header['name']) ? $header['name'] : null;
                $data           = isset($header['data']) ? $header['data'] : null;
                $mustUnderstand = isset($header['mustunderstand']) ? $header['mustunderstand'] : null;
                $actor          = isset($header['actor']) ? $header['actor'] : null;

                $header = app(
                    SoapHeader::class,
                    [$namespace, $name, $data, $mustUnderstand, $actor]
                );
            }
        }

        unset($header);

        $this->client->__setSoapHeaders($headers);
    }

    /**
     * Extracts information from SOAP client if tracing
     */
    protected function parseTracedReponseInformation()
    {
        // nothing to set if we weren't tracing
        if ( ! isset($this->clientOptions['trace']) || $this->clientOptions['trace'] !== true) {
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

    protected function parseResponseHeaderForStatusCode($headers)
    {
        if ( ! preg_match('#^\s*http/\d\.\d\s+(?<code>\d+)\s+#i', $headers, $matches)) {
            return 200;
        }

        return (int) $matches['code'];
    }
    
    /**
     * Parses a header string to an array
     *
     * @param null|string $headers
     * @return array
     */
    protected function parseResponseHeadersAsArray($headers)
    {
        if (empty($headers)) return [];

        $headersArray = [];

        foreach (preg_split('#[\r\n]+#', $headers) as $headerString) {

            if ( ! preg_match('#^\s*(?<name>.*?)\s*:\s*(?<value>.*)\s*$#', $headerString, $matches)) continue;

            $headersArray[ $matches['name'] ] = $matches['value'];
        }

        return $headersArray;
    }

    /**
     * Runs before any call is made
     *
     * Initializes SoapClient before first call is made or when changed settings require it
     */
    protected function before()
    {
        if (    empty($this->client)
            ||  empty($this->clientHash)
            ||  $this->clientHash !== $this->makeSoapClientHash()
        ) {
            $this->initializeClient();
        }
    }

    /**
     * Initializes Soap Client with WSDL and an options array
     *
     * @throws CouldNotConnectException
     */
    protected function initializeClient()
    {
        // Store some specific soap-client related data locally
        // so it can be injected in the SoapClient and compared
        // for changes later

        $this->wsdl          = $this->request->getLocation();
        $this->clientOptions = $this->request->getOptions();

        // store hash to make it possible to detect changes to the client
        $this->clientHash = $this->makeSoapClientHash();

        $xdebugEnabled = extension_loaded('xdebug') && xdebug_is_enabled();

        try {

            // temporarily disable xdebug to prevent PHP Fatal error
            // while constructing SoapClient

            if ($xdebugEnabled) xdebug_disable();

            $this->client = app($this->soapClientClass, [ $this->wsdl, $this->clientOptions ]);

            if ($xdebugEnabled) xdebug_enable();

        } catch (SoapFault $e) {

            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);

        } catch (Exception $e) {

            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates a hash from the combination of all settings that need to
     * be tracked to see whether a new soapclient should be instantiated
     */
    protected function makeSoapClientHash()
    {
        return sha1(
            $this->request->getLocation()
            . json_encode($this->request->getOptions())
            . json_encode($this->request->getHeaders())
        );
    }


    /**
     * Supplements request with soap options, in addition to the standard supplements
     */
    protected function supplementRequestWithDefaults()
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
    protected function checkRequest()
    {
        parent::checkRequest();

        if ( ! is_a($this->request, ServiceSoapRequest::class)) {

            throw new InvalidArgumentException("Request class is not a ServiceSoapRequest");
        }
    }

    // ------------------------------------------------------------------------------
    //      Getters, Setters and Configuration
    // ------------------------------------------------------------------------------

    /**
     * Runs directly after construction
     * Extend this to customize your service
     *
     * Defaults to 'exceptions' option enabled
     */
    protected function initialize()
    {
        parent::initialize();

        // unless already configured, set default options to include exceptions
        $options = $this->defaults->getOptions() ?: [];

        foreach ($this->soapOptionDefaults as $option => $value) {

            if ( ! array_key_exists($option, $options)) {

                $options[ $option ] = $value;
            }
        }

        $this->defaults->setOptions($options);
    }

    /**
     * @return SoapClient
     */
    public function getClient()
    {
        return $this->client;
    }

}
