<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\Decorators\RemoveXmlNamespacesDecorator;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Test\Helpers\TestMockInterpreter;

class RemoveXmlNamespacesDecoratorTest extends TestCase
{
    /**
     * @test
     */
    function it_removes_namespaces_from_raw_xml()
    {
        $interpreter = new TestMockInterpreter();

        $decorator = new RemoveXmlNamespacesDecorator($interpreter);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $decorator->interpret($mockRequest, $this->xml->getXmlWithRelativeNamespacesFixed());

        static::assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        static::assertEquals(
            $this->xml->getXmlWithNamespacesRemoved(),
            $result->getData(),
            "Incorrect raw de-namespaced XML data"
        );
    }
}
