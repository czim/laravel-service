<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\Decorators\FixXmlNamespacesDecorator;
use Czim\Service\Responses\ServiceResponse;

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

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertEquals(
            $this->xml->getXmlWithRelativeNamespacesFixed(),
            $result->getData(),
            "Incorrect raw XML fixed data"
        );
    }

}
