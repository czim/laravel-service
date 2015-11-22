<?php
namespace Czim\Service\Test;

use Czim\Service\Interpreters\Xml\DomDocumentBasedXmlParser;
use Czim\Service\Interpreters\Xml\DomObjectToArrayConverter;

/**
 * Tests both the DomDocumentBased parser and the DomObjectToArrayConverter that acompanies it
 */
class DomDocumentBasedXmlParserTest extends TestCase
{

    /**
     * @test
     */
    function it_parses_raw_xml_as_dom_element()
    {
        $parser = new DomDocumentBasedXmlParser();

        $result = $parser->parse( $this->xml->getMinimalValidXmlContent() );

        $this->assertInstanceOf('DOMElement', $result, "Parsed data should be DOMElement");
    }

    /**
     * @test
     * @depends it_parses_raw_xml_as_dom_element
     */
    function it_parses_raw_xml_to_object_that_converts_as_dom_element_to_array()
    {
        $parser = new DomDocumentBasedXmlParser();
        $converter = new DomObjectToArrayConverter();

        $result = $parser->parse( $this->xml->getMinimalValidXmlContent() );
        $result = $converter->convert($result);

        $this->assertArraySubset(
            $this->xml->getMinimalXmlContentAsArray(),
            $result,
            "Incorrect parsed xml-decoded data after conversion"
        );
    }

}
