<?php
namespace Czim\Service\Test;

use Czim\Service\Interpreters\Xml\SimpleXmlParser;

class SimpleXmlParserTest extends TestCase
{

    /**
     * @test
     */
    function it_parses_raw_xml_as_simple_xml_object()
    {
        $parser = new SimpleXmlParser();

        $result = $parser->parse( $this->xml->getMinimalValidXmlContent() );

        $this->assertInstanceOf('SimpleXmlElement', $result, "Parsed data should be SimpleXmlElement");
        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            json_decode(json_encode($result), true),
            "Incorrect xml-decoded data (encode/decode test)"
        );
    }

    /**
     * @test
     * @expectedException \Czim\Service\Exceptions\CouldNotInterpretXmlResponseException
     */
    function it_throw_an_exception_for_invalid_raw_xml()
    {
        $parser = new SimpleXmlParser();

        $parser->parse( $this->xml->getInvalidXmlContent() );
    }

}
