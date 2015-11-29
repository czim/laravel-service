<?php
namespace Czim\Service\Contracts;

interface ServiceInterface
{

    /**
     * Performs a call on the service, returning an interpreted response
     *
     * @param string $method        name of the method to call through the service
     * @param mixed  $request       either: request object, or the request body
     * @param mixed  $parameters    extra parameters to send along (optional)
     * @param mixed  $headers       extra headers to send along (optional)
     * @return ServiceResponseInterface
     */
    public function call($method, $request = null, $parameters = null, $headers = null);

    /**
     * Applies mass configuration to default request
     *
     * @param array $config
     * @return $this
     */
    public function config(array $config);

    /**
     * Returns the raw response data for the most recent call made
     *
     * @return mixed
     */
    public function getLastRawResponse();

    /**
     * Returns best available response: interpreted if an interpreter
     * is available, falls back to raw response
     *
     * @return ServiceResponseInterface
     */
    public function getLastInterpretedResponse();

    /**
     * Returns the extra information set during the execution of the last call
     *
     * @return ServiceResponseInformationInterface
     */
    public function getLastReponseInformation();

    /**
     * Sets the default request data to supplement any requests with
     *
     * @param ServiceRequestDefaultsInterface $defaults
     * @return $this
     */
    public function setRequestDefaults(ServiceRequestDefaultsInterface $defaults);

    /**
     * Returns the default request data
     *
     * @return ServiceRequestDefaultsInterface
     */
    public function getRequestDefaults();

    /**
     * Sets the service response interpreter
     *
     * @param ServiceInterpreterInterface $interpreter
     * @return $this
     */
    public function setInterpreter(ServiceInterpreterInterface $interpreter);

    /**
     * Returns the service response interpreter instance
     *
     * @return ServiceInterpreterInterface
     */
    public function getInterpreter();

    /**
     * Frees up memory where possible
     *
     * @return $this
     */
    public function free();

}
