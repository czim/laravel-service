<?php

declare(strict_types=1);

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
            $this->makeRelativeNamespacesAbsolute($response),
            $responseInformation
        );
    }

    /**
     * Fixes namespaces (which must be absolute for anything to work).
     *
     * @param string $xml
     * @return string
     */
    protected function makeRelativeNamespacesAbsolute(string $xml): string
    {
        return preg_replace('#((xmlns(:[a-z]+)?)="((?!http)[^"]+)")#i', '\\2="http://\\4"', $xml);
    }
}
