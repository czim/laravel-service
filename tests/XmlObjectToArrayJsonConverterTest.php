<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Interpreters\Xml\XmlObjectToArrayJsonConverter;

class XmlObjectToArrayJsonConverterTest extends TestCase
{
    /**
     * @test
     */
    function it_converts_simple_xml_object_to_array()
    {
        $converter = new XmlObjectToArrayJsonConverter();

        $result = $converter->convert(
            $this->xml->getSimpleXmlElement()
        );

        static::assertEquals(
            $this->xml->getMinimalXmlContentAsArray(),
            $result,
            "Incorrect converted array data"
        );
    }
}
