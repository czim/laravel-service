<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\Decorators\FixXmlNamespacesDecorator;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Test\Helpers\TestMockInterpreter;

class FixXmlNamespacesDecoratorTest extends TestCase
{
    /**
     * @test
     */
    function it_makes_relative_namespaces_absolute_in_raw_xml()
    {
        $interpreter = new TestMockInterpreter();

        $decorator = new FixXmlNamespacesDecorator($interpreter);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $decorator->interpret($mockRequest, $this->xml->getXmlWithRelativeNamespaces());

        static::assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        static::assertEquals(
            $this->xml->getXmlWithRelativeNamespacesFixed(),
            $result->getData(),
            "Incorrect raw XML fixed data"
        );
    }
}
