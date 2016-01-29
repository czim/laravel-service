<?php
namespace Czim\Service\Interpreters\Decorators;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * Fixes raw XML with relative namespaces and potentially other problems
 */
class FixXmlNamespacesDecorator implements ServiceInterpreterInterface
{

    /**
     * @var ServiceInterpreterInterface
     */
    protected $interpreter;


    /**
     * @param ServiceInterpreterInterface $interpreter
     */
    public function __construct(ServiceInterpreterInterface $interpreter)
    {
        $this->interpreter = $interpreter;
    }


    /**
     * @param ServiceRequestInterface             $request the request sent in order to retrieve the response
     * @param mixed                               $response
     * @param ServiceResponseInformationInterface $responseInformation
     * @return ServiceResponseInterface
     */
    public function interpret(
        ServiceRequestInterface $request,
        $response,
        ServiceResponseInformationInterface $responseInformation = null
    ) {
        return $this->interpreter->interpret(
            $request,
            $this->makeRelativeNamespacesAbsolute($response),
            $responseInformation
        );
    }

    /**
     * Fixes namespaces (which must be absolute for anything to work)
     *
     * @param  string $xml
     * @return string
     */
    protected function makeRelativeNamespacesAbsolute($xml)
    {
        return preg_replace('#((xmlns(:[a-z]+)?)="((?!http)[^"]+)")#i', '\\2="http://\\4"', $xml);
    }

}
