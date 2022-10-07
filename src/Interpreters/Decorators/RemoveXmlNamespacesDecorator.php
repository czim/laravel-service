<?php

declare(strict_types=1);

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
    public function __construct(protected readonly ServiceInterpreterInterface $interpreter)
    {
    }


    /**
     * @param ServiceRequestInterface                  $request the request sent in order to retrieve the response
     * @param mixed                                    $response
     * @param ServiceResponseInformationInterface|null $responseInformation
     * @return ServiceResponseInterface
     */
    public function interpret(
        ServiceRequestInterface $request,
        mixed $response,
        ServiceResponseInformationInterface $responseInformation = null,
    ): ServiceResponseInterface {
        return $this->interpreter->interpret(
            $request,
            $this->removeNamespaces($response),
            $responseInformation
        );
    }

    /**
     * Removes XML namespaces entirely.
     *
     * @param string $xml
     * @return string
     */
    protected function removeNamespaces(string $xml): string
    {
        return preg_replace(
            '#((xmlns(:[a-z]+)?)="([^"]+)")#i',
            '',
            preg_replace('#(<\/?)([a-z0-9_-]+:)#i', "\\1", $xml)
        );
    }
}
