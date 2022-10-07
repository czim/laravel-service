<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Events\RestCallCompleted;
use Czim\Service\Exceptions\CouldNotConnectException;

/**
 * Same as the RestService, but uses cURL instead of Guzzle.
 * Only use this if you cannot use Guzzle for some reason.
 */
class RestCurlService extends AbstractService
{
    protected const USER_AGENT = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

    /**
     * The method to use for the HTTP call.
     *
     * @var string
     */
    protected string $method = RestService::METHOD_POST;

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


    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
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
     * {@inheritDoc}
     */
    protected function callRaw(ServiceRequestInterface $request): mixed
    {
        $url = rtrim($request->getLocation(), '/') . '/' . $request->getMethod();

        $curl = curl_init();

        if ($curl === false) {
            throw new CouldNotConnectException('cURL could not be initialized');
        }


        $credentials = $request->getCredentials();

        if (
            $this->basicAuth
            && ! empty($credentials['name'])
            && ! empty($credentials['password'])
        ) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $credentials['name'] . ":" . $credentials['password']);
        }

        $headers = $request->getHeaders();


        switch ($this->method) {
            case RestService::METHOD_PATCH:
            case RestService::METHOD_POST:
            case RestService::METHOD_PUT:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request->getBody() ?: []));

                $parameters = $request->getParameters();

                if (! empty($parameters)) {
                    $url .= '?' . http_build_query($request->getParameters());
                }
                break;

            case RestService::METHOD_GET:
                $parameters = $request->getbody();

                $url .= '?' . http_build_query($parameters ?: []);
                break;

            // default omitted on purpose
        }


        if (count($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }


        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new CouldNotConnectException(curl_error($curl), curl_errno($curl));
        }

        $this->responseInformation->setStatusCode( curl_getinfo($curl, CURLINFO_HTTP_CODE) );

        curl_close($curl);


        event(
            new RestCallCompleted(
                $url,
                $parameters ?? [],
                $this->sendResponseToEvent ? $response : null
            )
        );

        return $response;
    }
}
