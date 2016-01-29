<?php
namespace Czim\Service\Interpreters\Decorators;

use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInformationInterface;
use Czim\Service\Contracts\ServiceResponseInterface;

/**
 * Removes namespaces from raw XML
 */
class RemoveXmlNamespacesDecorator implements ServiceInterpreterInterface
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
            $this->removeNamespaces($response),
            $responseInformation
        );
    }

    /**
     * Removes XML namespaces entirely
     *
     * @param  string $xml
     * @return string
     */
    protected function removeNamespaces($xml)
    {
        return preg_replace(
            '#((xmlns(:[a-z]+)?)="([^"]+)")#i',
            '',
            preg_replace('#(<\/?)([a-z0-9_-]+:)#i', "\\1", $xml)
        );
    }

}
