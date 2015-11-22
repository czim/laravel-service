<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Events\SoapCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\CouldNotRetrieveException;
use Czim\Service\Requests\ServiceSoapRequest;
use Exception;
use InvalidArgumentException;
use SoapClient;
use SoapFault;
use SoapHeader;

class SoapService extends AbstractService
{

    /**
     * The classname of the defaults object to instantiate if none is injected
     * @var string
     */
    protected $requestDefaultsClass = \Czim\Service\Requests\ServiceSoapRequestDefaults::class;

    /**
     * Classname/FQN of the SoapClient to use for calls
     *
     * @var string
     */
    protected $soapClientClass = SoapClient::class;

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
     * @param ServiceRequestInterface|ServiceSoapRequest $request
     * @return mixed
     * @throws CouldNotRetrieveException
     */
    protected function callRaw(ServiceRequestInterface $request)
    {
        $this->applySoapHeaders();

        // todo: if soap options changed, need to re-initialize the soap client!

        try {

            if ( ! is_null($this->request->getBody())) {

                $response = $this->client->{$this->request->getMethod()}(
                    $this->request->getBody()
                );

            } else {

                $response = $this->client->{$this->request->getMethod()}();
            }

        } catch (SoapFault $e) {

            throw new CouldNotRetrieveException($e->getMessage(), $e->getCode(), $e);
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
     * Initializes SoapClient before first call is made
     *
     * @throws CouldNotConnectException
     */
    protected function beforeFirstCall()
    {
        if (empty($this->client)) {
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

        $this->wsdl          = $this->getRequestDefaults()->getLocation();
        $this->clientOptions = $this->getRequestDefaults()->getOptions();


        try {

            $this->client = app($this->soapClientClass, [ $this->wsdl, $this->clientOptions ]);

        } catch (Exception $e) {

            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
        }
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
     * @return SoapClient
     */
    public function getClient()
    {
        return $this->client;
    }

}
