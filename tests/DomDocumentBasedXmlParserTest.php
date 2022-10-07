<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Interpreters\Xml\DomDocumentBasedXmlParser;
use Czim\Service\Interpreters\Xml\DomObjectToArrayConverter;
use Illuminate\Support\Arr;

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

        $result = $parser->parse(
            $this->xml->getMinimalValidXmlContent()
        );

        static::assertInstanceOf('DOMElement', $result, "Parsed data should be DOMElement");
    }

    /**
     * @test
     * @depends it_parses_raw_xml_as_dom_element
     */
    function it_parses_raw_xml_to_object_that_converts_as_dom_element_to_array()
    {
        $parser = new DomDocumentBasedXmlParser();
        $converter = new DomObjectToArrayConverter();

        $result = $parser->parse(
            $this->xml->getMinimalValidXmlContent()
        );
        $result = $converter->convert($result);

        static::assertEquals('en', Arr::get($result, '@attributes.lang'));
        static::assertEquals('Minimal XHTML 1.0 Document', Arr::get($result, 'head.title'));
        static::assertEquals('This is a minimal document.', Arr::get($result, 'body.p'));
    }
}
