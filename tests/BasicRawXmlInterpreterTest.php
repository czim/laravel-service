<?php
namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\BasicRawXmlInterpreter;
use Czim\Service\Responses\ServiceResponse;

class BasicRawXmlInterpreterTest extends TestCase
{

    /**
     * @test
     */
    function it_decodes_valid_xml_data_as_array()
    {
        $interpreter = new BasicRawXmlInterpreter(true);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, $this->getMinimalValidXmlContent());

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertArraySubset($this->getMinimalXmlContentAsArray(), $result->getData(), "Incorrect xml-decoded data");
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
        $result = $interpreter->interpret($mockRequest, $this->getMinimalValidXmlContent());

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertInstanceOf('SimpleXmlElement', $result->getData(), "Data should be SimpleXmlElement");
        $this->assertArraySubset($this->getMinimalXmlContentAsArray(), json_decode(json_encode($result->getData()), true), "Incorrect xml-decoded data (encode/decode test)");
    }


    /**
     * @return string
     */
    protected function getMinimalValidXmlContent()
    {
        return <<<MINIMALXML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Minimal XHTML 1.0 Document</title>
</head>
<body>
<p>This is a minimal document.</p>
</body>
</html>
MINIMALXML;
    }

    /**
     * @return array
     */
    protected function getMinimalXmlContentAsArray()
    {
        return [
            "@attributes" => [
                "lang" => "en"
            ],
            "head" => [
                "title" => "Minimal XHTML 1.0 Document"
            ],
            "body" => [
                "p" => "This is a minimal document."
            ],
        ];
    }
}
