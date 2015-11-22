<?php
namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\BasicRawXmlInterpreter;
use Czim\Service\Interpreters\Xml\SimpleXmlParser;
use Czim\Service\Interpreters\Xml\XmlObjectToArrayConverter;
use Czim\Service\Responses\ServiceResponse;

class BasicRawXmlInterpreterTest extends TestCase
{

    /**
     * @test
     */
    function it_decodes_valid_xml_data_as_array_with_default_bindings()
    {
        $interpreter = new BasicRawXmlInterpreter(true);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, $this->xml->getMinimalValidXmlContent());

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            $result->getData(),
            "Incorrect xml-decoded data"
        );
    }

    /**
     * @test
     */
    function it_decodes_valid_xml_data_as_array()
    {
        $interpreter = new BasicRawXmlInterpreter(true, new SimpleXmlParser(), new XmlObjectToArrayConverter());

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, $this->xml->getMinimalValidXmlContent());

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            $result->getData(),
            "Incorrect xml-decoded data"
        );
    }

    /**
     * @test
     */
    function it_decodes_valid_xml_data_as_object()
    {
        $interpreter = new BasicRawXmlInterpreter(false);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, $this->xml->getMinimalValidXmlContent());

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertInstanceOf('SimpleXmlElement', $result->getData(), "Data should be SimpleXmlElement");
        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            json_decode(json_encode($result->getData()), true),
            "Incorrect xml-decoded data (encode/decode test)"
        );
    }

}
