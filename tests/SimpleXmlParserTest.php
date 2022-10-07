<?php

declare(strict_types=1);

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

        $result = $parser->parse(
            $this->xml->getMinimalValidXmlContent()
        );

        static::assertInstanceOf('SimpleXmlElement', $result, "Parsed data should be SimpleXmlElement");
        static::assertEquals(
            $this->xml->getMinimalXmlContentAsArray(),
            json_decode(json_encode($result), true),
            "Incorrect xml-decoded data (encode/decode test)"
        );
    }

    /**
     * @test
     */
    function it_throw_an_exception_for_invalid_raw_xml()
    {
        $this->expectException(\Czim\Service\Exceptions\CouldNotInterpretXmlResponseException::class);

        $parser = new SimpleXmlParser();

        $parser->parse(
            $this->xml->getInvalidXmlContent()
        );
    }
}
